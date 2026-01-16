#!/bin/bash

# Deployment script for Social1Bit application - Auto-detect and sync only changed files
#
# This script automatically detects changed files (using git) and only syncs those files.
# Much faster than full deployment for small changes.
#
# Configuration (REQUIRED):
#   VPS_HOST and DEPLOY_PATH MUST be set via environment variables or .env file
#   Example .env.deploy file:
#     VPS_HOST=your-server-ip
#     DEPLOY_PATH=/var/www/your-app
#     SSH_USER=deploy (optional, defaults to root)
#
# IMPORTANT: For automatic permission setting to work, configure passwordless sudo:
# On the server, run: sudo visudo
# Add this line (replace 'username' with your SSH user):
#   username ALL=(ALL) NOPASSWD: /bin/chown, /bin/chmod
# Or for root user, ensure SSH key is properly configured

set -e  # Exit on error

# Configuration
# These MUST be set via environment variables or .env file
LOCAL_PATH="$(pwd)"           # Current directory (project root)
GIT_REF="${1:-origin/main}"   # Git reference to compare with (default: origin/main)

# Try to load from .env or .env.deploy file if it exists
if [ -f .env.deploy ]; then
    export $(grep -v '^#' .env.deploy | grep -E '^(VPS_HOST|DEPLOY_PATH|SSH_USER)=' | xargs)
elif [ -f .env ]; then
    export $(grep -v '^#' .env | grep -E '^(VPS_HOST|DEPLOY_PATH|SSH_USER)=' | xargs)
fi

# Validate required configuration
if [ -z "$VPS_HOST" ]; then
    echo "Error: VPS_HOST is not set!"
    echo "Please set it via:"
    echo "  - Environment variable: export VPS_HOST=your-server-ip"
    echo "  - .env.deploy file: VPS_HOST=your-server-ip"
    echo "  - .env file: VPS_HOST=your-server-ip"
    exit 1
fi

if [ -z "$DEPLOY_PATH" ]; then
    echo "Error: DEPLOY_PATH is not set!"
    echo "Please set it via:"
    echo "  - Environment variable: export DEPLOY_PATH=/var/www/your-app"
    echo "  - .env.deploy file: DEPLOY_PATH=/var/www/your-app"
    echo "  - .env file: DEPLOY_PATH=/var/www/your-app"
    exit 1
fi

# SSH_USER has a default but can be overridden
SSH_USER="${SSH_USER:-root}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if SSH key is available
check_ssh() {
    print_info "Checking SSH connection..."
    if ssh -o ConnectTimeout=5 -o BatchMode=yes "${SSH_USER}@${VPS_HOST}" exit 2>/dev/null; then
        print_info "SSH connection successful"
    else
        print_error "SSH connection failed. Please ensure:"
        echo "  1. SSH key is added to the server"
        echo "  2. SSH_USER is set correctly (current: ${SSH_USER})"
        echo "  3. Server is accessible"
        exit 1
    fi
}

# Check if rsync is available
check_rsync() {
    if ! command -v rsync &> /dev/null; then
        print_error "rsync is not installed. Please install it first:"
        echo "  macOS: brew install rsync"
        echo "  Linux: sudo apt-get install rsync"
        exit 1
    fi
    print_info "rsync is available"
}

# Check if git is available
check_git() {
    if ! command -v git &> /dev/null; then
        print_error "git is not installed. Please install it first."
        exit 1
    fi
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_error "Not a git repository. Please run this from a git repository."
        exit 1
    fi
    print_info "git is available"
}

# Auto-detect changed files and sync only those
detect_and_sync() {
    local CHANGED_FILES=""
    local SYNC_COUNT=0
    
    print_info "Detecting changed files (comparing with ${GIT_REF})..."
    
    # Check prerequisites
    check_ssh
    check_rsync
    check_git
    
    # Try to fetch latest from remote if comparing with remote branch
    if [[ "$GIT_REF" == origin/* ]]; then
        print_info "Fetching latest from remote..."
        git fetch origin 2>/dev/null || print_warning "Could not fetch from remote, using local reference"
    fi
    
    # Get changed files (modified, added, or renamed)
    # Exclude deleted files as they would cause errors
    if git rev-parse --verify "${GIT_REF}" >/dev/null 2>&1; then
        CHANGED_FILES=$(git diff --name-only --diff-filter=ACMR "${GIT_REF}" HEAD 2>/dev/null)
    else
        print_warning "Git reference '${GIT_REF}' not found, comparing with HEAD"
        CHANGED_FILES=$(git diff --name-only --diff-filter=ACMR HEAD~1 HEAD 2>/dev/null)
    fi
    
    # Also check for untracked files (new files not in git yet)
    UNTRACKED=$(git ls-files --others --exclude-standard 2>/dev/null || echo "")
    if [ -n "$UNTRACKED" ]; then
        CHANGED_FILES="${CHANGED_FILES}"$'\n'"${UNTRACKED}"
    fi
    
    # Filter out excluded files and directories
    EXCLUDED_PATTERNS=(
        ".git"
        ".env"
        ".env.*"
        "node_modules"
        "vendor"
        "storage/logs"
        "storage/framework/cache"
        "storage/framework/sessions"
        "storage/framework/views"
        "bootstrap/cache"
        ".DS_Store"
        "*.log"
        "*.tmp"
        "*.swp"
        "*.swo"
        "*.bak"
        "deploy.sh"
        "deploy-changed.sh"
        "deploy-server.sh"
        "DEPLOYMENT.md"
        "DOCKER_SETUP.md"
        "docker-compose.yml"
        "Dockerfile"
        "package-lock.json"
        "composer.lock"
    )
    
    # Filter changed files
    FILTERED_FILES=""
    while IFS= read -r file; do
        if [ -z "$file" ]; then
            continue
        fi
        
        # Check if file should be excluded
        EXCLUDED=false
        for pattern in "${EXCLUDED_PATTERNS[@]}"; do
            if [[ "$file" == *"$pattern"* ]] || [[ "$file" == "$pattern" ]]; then
                EXCLUDED=true
                break
            fi
        done
        
        # Only include files that exist and are not excluded
        if [ "$EXCLUDED" = false ] && [ -f "$file" ]; then
            FILTERED_FILES="${FILTERED_FILES}${file}"$'\n'
        fi
    done <<< "$CHANGED_FILES"
    
    # Remove empty lines and count
    FILTERED_FILES=$(echo "$FILTERED_FILES" | sed '/^$/d')
    SYNC_COUNT=$(echo "$FILTERED_FILES" | wc -l | tr -d ' ')
    
    if [ -z "$FILTERED_FILES" ] || [ "$SYNC_COUNT" -eq 0 ]; then
        print_info "No changed files detected to sync."
        print_info "All files are up to date with ${GIT_REF}"
        return 0
    fi
    
    print_info "Found ${SYNC_COUNT} changed file(s) to sync:"
    echo "$FILTERED_FILES" | head -20 | while IFS= read -r file; do
        if [ -n "$file" ]; then
            echo "  - $file"
        fi
    done
    
    if [ "$SYNC_COUNT" -gt 20 ]; then
        print_info "... and $((SYNC_COUNT - 20)) more file(s)"
    fi
    
    # Ask for confirmation
    echo ""
    read -p "Do you want to sync these ${SYNC_COUNT} file(s)? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Sync cancelled by user"
        return 0
    fi
    
    # Build rsync command
    # --no-perms: Don't preserve permissions (we'll set them explicitly)
    # --no-owner: Don't preserve ownership (we'll set it explicitly)
    # --no-group: Don't preserve group (we'll set it explicitly)
    RSYNC_CMD="rsync -avz --no-perms --no-owner --no-group"
    
    print_step "Syncing changed files to server..."
    
    # Sync each changed file
    SYNCED_COUNT=0
    FAILED_COUNT=0
    
    while IFS= read -r file; do
        if [ -z "$file" ] || [ ! -f "$file" ]; then
            continue
        fi
        
        # Get directory path for the file
        file_dir=$(dirname "$file")
        
        # Create directory structure on server if needed
        if [ "$file_dir" != "." ]; then
            ssh "${SSH_USER}@${VPS_HOST}" "mkdir -p ${DEPLOY_PATH}/${file_dir}" 2>/dev/null || true
        fi
        
        print_info "Syncing: ${file}"
        
        ${RSYNC_CMD} \
            --progress \
            -e "ssh -o StrictHostKeyChecking=no" \
            "${LOCAL_PATH}/${file}" \
            "${SSH_USER}@${VPS_HOST}:${DEPLOY_PATH}/${file}"
        
        if [ $? -eq 0 ]; then
            ((SYNCED_COUNT++))
        else
            print_error "Failed to sync: ${file}"
            ((FAILED_COUNT++))
        fi
    done <<< "$FILTERED_FILES"
    
    print_info "Sync completed: ${SYNCED_COUNT} succeeded, ${FAILED_COUNT} failed"
    
    if [ "$SYNCED_COUNT" -gt 0 ]; then
        # Run minimal post-deployment tasks
        print_step "Running minimal post-deployment tasks..."
        ssh "${SSH_USER}@${VPS_HOST}" << EOF
            set -e
            
            cd ${DEPLOY_PATH} || {
                echo "Error: Directory ${DEPLOY_PATH} does not exist!"
                exit 1
            }
            
            # Clear and rebuild caches
            echo "Clearing caches..."
            php artisan config:clear 2>/dev/null || true
            php artisan route:clear 2>/dev/null || true
            php artisan view:clear 2>/dev/null || true
            
            echo "Rebuilding caches..."
            php artisan config:cache 2>/dev/null || true
            php artisan route:cache 2>/dev/null || true
            php artisan view:cache 2>/dev/null || true
            
            # Optimize autoloader if composer files changed
            if echo "$FILTERED_FILES" | grep -qE "(composer\.json|composer\.lock|app/|modules/)"; then
                echo "Optimizing autoloader..."
                composer dump-autoload --optimize --classmap-authoritative 2>/dev/null || true
            fi
            
            # Set permissions for synced files
            echo "Setting file permissions..."
            echo "$FILTERED_FILES" | while read -r file; do
                if [ -n "\$file" ] && [ -f "\$file" ]; then
                    # Try with sudo first, fallback to without
                    if sudo -n true 2>/dev/null; then
                        sudo chown www-data:www-data "\$file" 2>/dev/null || true
                        sudo chmod 644 "\$file" 2>/dev/null || true
                    else
                        chown www-data:www-data "\$file" 2>/dev/null || true
                        chmod 644 "\$file" 2>/dev/null || true
                    fi
                fi
            done
            
            # Ensure storage and cache directories have proper permissions
            mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
            
            if sudo -n true 2>/dev/null; then
                sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
                sudo chmod -R 775 storage bootstrap/cache 2>/dev/null || true
            else
                chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
                chmod -R 775 storage bootstrap/cache 2>/dev/null || true
            fi
            
            echo "Post-deployment tasks completed"
EOF
        
        if [ $? -eq 0 ]; then
            print_info "Deployment completed successfully!"
        else
            print_warning "Some post-deployment tasks may have failed, but files were synced"
        fi
    fi
}

# Main execution
main() {
    echo "=========================================="
    echo "  Social1Bit - Deploy Changed Files Only"
    echo "=========================================="
    echo "VPS: ${VPS_HOST}"
    echo "Path: ${DEPLOY_PATH}"
    echo "User: ${SSH_USER}"
    echo "Local: ${LOCAL_PATH}"
    echo "Git Reference: ${GIT_REF}"
    echo "=========================================="
    echo ""
    
    detect_and_sync
}

# Show usage if help requested
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    echo "Usage: $0 [git-reference]"
    echo ""
    echo "Deploy only changed files to the server."
    echo ""
    echo "Arguments:"
    echo "  git-reference    Git reference to compare with (default: origin/main)"
    echo "                  Examples: origin/main, origin/develop, HEAD~1, commit-hash"
    echo ""
    echo "Environment variables (REQUIRED):"
    echo "  VPS_HOST       Server IP or hostname (REQUIRED - no default)"
    echo "  DEPLOY_PATH    Deployment path on server (REQUIRED - no default)"
    echo "  SSH_USER       SSH username (default: root)"
    echo ""
    echo "Configuration can also be set in .env.deploy or .env file:"
    echo "  VPS_HOST=your-server-ip"
    echo "  DEPLOY_PATH=/var/www/your-app"
    echo "  SSH_USER=deploy"
    echo ""
    echo "  (.env.deploy takes precedence over .env if both exist)"
    echo ""
    echo "Examples:"
    echo "  $0                                    # Compare with origin/main"
    echo "  $0 origin/develop                     # Compare with origin/develop"
    echo "  $0 HEAD~1                             # Compare with previous commit"
    echo "  VPS_HOST=1.2.3.4 $0                  # Use different server"
    echo "  DEPLOY_PATH=/var/www/app $0           # Use different path"
    echo "  SSH_USER=deploy $0                    # Use different SSH user"
    echo ""
    exit 0
fi

# Run main function
main "$@"
