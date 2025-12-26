@php
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$appBase   = rtrim(preg_replace('#/installer$#', '', $scriptDir), '/');
$homeUrl   = $appBase === '' ? '/' : $appBase . '/';
@endphp

<!-- Step 4: Install Final -->
<div class="step-content step-4 hidden">
    <div class="text-center">
        <div class="mb-8">
            <div class="w-24 h-24 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-12 h-12"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mb-4 text-4xl md:text-5xl leading-tight text-slate-900 font-bold tracking-tight">Installation Complete!</h1>
            <p class="mb-6 text-xl md:text-2xl text-slate-600 font-medium">Your application has been successfully installed and is ready to use.</p>
        </div>
        <div class="install-progress mb-8 space-y-3">
            <div class="progress-item flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-green-800 font-semibold">Database connection established</span>
                </div>
            </div>
            <div class="progress-item flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-green-800 font-semibold">Database tables created</span>
                </div>
            </div>
            <div class="progress-item flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-green-800 font-semibold">Admin account created</span>
                </div>
            </div>
            <div class="progress-item flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-green-800 font-semibold">Configuration files generated</span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-slate-50 to-blue-50 p-8 rounded-2xl mb-8 border border-slate-200 shadow-inner">
            <h4 class="mb-4 text-2xl md:text-3xl leading-tight text-slate-900 font-bold tracking-tight">Next Steps</h4>
            <p class="mb-4 text-lg text-slate-600">Your installation is complete! You can now access your admin panel and start customizing your site.</p>
            <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-medium shadow-sm">
                <svg id="svg_83cdc90c0424c7e825a9a14f2d627fce" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewbox="0 0 24 24"></svg>
                Security: Please delete installation files from your server
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center py-4 px-8 text-lg leading-7 text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 font-semibold text-center focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-transparent rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105 login-btn">
                <svg id="svg_8d8dccd598d05f76d132a2b4841ec664" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewbox="0 0 24 24"></svg>
                View Site Home
            </a>
        </div>
    </div>
</div>