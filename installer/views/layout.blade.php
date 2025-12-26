<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Installer</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="base-url" content="{{ current_url() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto py-10">
        @yield('content')
    </div>
</body>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 1;
    const totalSteps = 4;

    // Cache selectors
    const stepContents = document.querySelectorAll('.step-content');
    const stepItems = document.querySelectorAll('.step-item');
    const stepProgresses = document.querySelectorAll('.step-progress');
    const currentStepNumber = document.querySelector('.current-step-number');
    const overallProgress = document.querySelector('.overall-progress');
    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');
    const progressPercent = document.querySelector('.progress-percent');

    function updateStepIndicator() {
        stepItems.forEach((item, idx) => {
            const stepNum = idx + 1;
            const circle = item.querySelector('div');
            const text = item.querySelector('span');
            if (stepNum < currentStep) {
                circle.className = 'w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-lg ring-4 ring-green-100 relative z-10';
                item.className = 'step-item flex items-center text-green-600 font-semibold relative';
                text.className = 'text-sm md:text-base font-semibold text-green-600';
            } else if (stepNum === currentStep) {
                circle.className = 'w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-lg ring-4 ring-emerald-100 relative z-10';
                item.className = 'step-item flex items-center text-emerald-600 font-semibold relative';
                text.className = 'text-sm md:text-base font-semibold text-emerald-600';
            } else {
                circle.className = 'w-12 h-12 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-sm ring-4 ring-slate-50 relative z-10 transition-all duration-300';
                item.className = 'step-item flex items-center text-slate-400 font-medium relative';
                text.className = 'text-sm md:text-base font-semibold';
            }
        });
        stepProgresses.forEach((progress, idx) => {
            progress.style.width = (idx < currentStep - 1) ? '100%' : '0%';
        });
        const percent = Math.round((currentStep / totalSteps) * 100);
        overallProgress.style.width = `${percent}%`;
        if (progressPercent) progressPercent.textContent = `${percent}%`;
        currentStepNumber.textContent = currentStep;
    }

    function showStep(step) {
        stepContents.forEach((content, idx) => {
            if (idx + 1 === step) {
                content.classList.remove('hidden');
                content.style.opacity = '0';
                content.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    content.style.transition = 'all 0.5s';
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                }, 30);
            } else {
                content.classList.add('hidden');
            }
        });

        prevBtn.classList.toggle('hidden', step === 1);
        if (step === totalSteps) {
            nextBtn.classList.add('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            nextBtn.innerHTML = (step === totalSteps - 1)
                ? `Install <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>`
                : `Continue <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>`;
        }
    }

    nextBtn.addEventListener('click', () => {
        if (currentStep === 2) {
            const allPassed = document.getElementById('all-passed');
            if (allPassed && allPassed.value != "1") return;
        }
        if (currentStep === 3) {
            handleInstall();
            return;
        }
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepIndicator();
            showStep(currentStep);
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepIndicator();
            showStep(currentStep);
        }
    });

    updateStepIndicator();
    showStep(currentStep);

    function handleInstall() {
        // Clear previous errors and highlights
        document.querySelectorAll('.form-group').forEach(group => {
            group.classList.remove('has-error');
            const errorEl = group.querySelector('.input-error-message');
            if (errorEl) errorEl.remove();
        });

        // Collect trimmed data
        const data = {};
        document.querySelectorAll('.config-input').forEach(input => {
            if (input.name) data[input.name] = input.value.trim();
        });

        // Disable install button & show loading
        const submitBtn = document.querySelector('#install-submit-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'pointer-events-none');
            submitBtn.innerHTML = 'Installing... <span class="loader ml-2"></span>';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';

        fetch(`${baseUrl}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
            },
            body: JSON.stringify(data)
        })
        .then(async res => {
            const raw = await res.text();
            let json;
            try {
                json = JSON.parse(raw);
            } catch {
                throw { message: "Server returned invalid JSON: " + raw.substring(0, 180) };
            }
            if (!res.ok || !json) throw json;
            return json;
        })
        .then(json => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'pointer-events-none');
                submitBtn.innerHTML = 'Install';
            }

            if (json.success) {
                currentStep = 4;
                updateStepIndicator();
                showStep(currentStep);
                prevBtn.classList.toggle('hidden');
                showSuccessToast(json.message || 'Installation successful!');
            } else {
                showErrorToast(json.message || 'Installation failed!', json.errors || {});
                displayFieldErrors(json.errors);

            }
        })
        .catch(error => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'pointer-events-none');
                submitBtn.innerHTML = 'Install';
            }

            if (typeof error === 'object' && error !== null) {
                if (error.errors && Object.keys(error.errors).length) {
                    showErrorToast(error.message || "Please fix the highlighted errors.", error.errors);
                    displayFieldErrors(error.errors);
                } else if (error.message) {
                    showErrorToast('Install failed: ' + error.message);
                } else {
                    showErrorToast('Install failed: ' + JSON.stringify(error));
                }
            } else {
                showErrorToast('Install failed: ' + error);
            }
        });
    }

    // Helper to show errors below each input field
    function displayFieldErrors(errors) {
        let firstErrorInput = null;
        Object.entries(errors).forEach(([field, msg]) => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                const group = input.closest('.form-group');
                if (group) {
                    group.classList.add('has-error');
                    if (!group.querySelector('.input-error-message')) {
                        const err = document.createElement('span');
                        err.className = "text-sm text-red-600 input-error-message block mt-1";
                        err.innerText = msg;
                        input.after(err);
                    }
                    if (!firstErrorInput) firstErrorInput = input;
                }
            }
        });
        if (firstErrorInput) {
            firstErrorInput.focus();
            firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function showSuccessToast(message) {
        window.dispatchEvent(new CustomEvent('show-alert', {
            detail: { type: 'success', message }
        }));
    }

    function showErrorToast(message, errors = {}) {
        console.log("Toast Errors:", errors); // Debug log
        window.dispatchEvent(new CustomEvent('show-alert', {
            detail: { type: 'error', message, errors }
        }));
    }
});
</script>
</html>