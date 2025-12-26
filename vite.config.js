import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';

function getThemeFromEnv() {
    return process.env.npm_config_theme || process.env.THEME || null;
}

export default defineConfig(() => {
    const theme = getThemeFromEnv();

    if (!theme) {
        console.error("\n❗ Please provide a theme using --theme=guest/sophia");
        console.error("Example:");
        console.error("  npm run dev --theme=guest/sophia");
        console.error("  npm run build --theme=guest/sophia\n");
        process.exit(1);
    }

    const isDev = process.env.VITE_DEV_SERVER === 'true';

    const jsPath = `resources/themes/${theme}/assets/js/app.js`;
    const cssPath = `resources/themes/${theme}/assets/css/app.css`;

    const input = [];
    if (fs.existsSync(jsPath)) input.push(jsPath);
    if (fs.existsSync(cssPath)) input.push(cssPath);

    if (input.length === 0) {
        console.warn(`⚠️  No valid Vite input files found for theme "${theme}". Skipping build.`);
        process.exit(0);
    }

    const outputDir = `resources/themes/${theme}/public`;

    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    return {
        publicDir: false,
        server: {
            cors: true,
            watch: {
                usePolling: true,
                interval: 100,
                ignored: ['**/resources/modules_statuses.json', '**/storage/**', '**/public/**']
              },
        },
        build: {
            manifest: true,
            outDir: outputDir,
            emptyOutDir: false,
            rollupOptions: {
                input,
                output: {
                    entryFileNames: `js/[name].js`,
                    chunkFileNames: `js/[name].js`,
                    assetFileNames: (assetInfo) => {
                        if (/\.(woff2?|ttf|otf|eot)$/.test(assetInfo.name)) {
                            return 'fonts/[name].[ext]';
                        }
                        if (/\.(png|jpe?g|gif|svg|webp)$/.test(assetInfo.name)) {
                            return 'images/[name].[ext]';
                        }
                        if (/\.css$/.test(assetInfo.name)) {
                            return 'css/[name].[ext]';
                        }
                        return 'assets/[name].[ext]';
                    },
                },
            },
        },
        plugins: [
            tailwindcss(),
            laravel({
                input,
                refresh: [`resources/themes/${theme}/resources/views/**/*.blade.php`],
                buildDirectory: outputDir,
            }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, `resources/themes/${theme}/assets/js`),
            },
        },
    };
});