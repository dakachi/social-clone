<!-- Step Indicator -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-6">
        <div class="step-indicator flex items-center w-full">
            <!-- Step 1 -->
            <div class="step-item flex items-center text-emerald-600 font-semibold relative">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-lg ring-4 ring-emerald-100 relative z-10">1</div>
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold text-emerald-600">Welcome</span>
                    <span class="text-xs text-emerald-500 hidden md:block">Getting started</span>
                </div>
            </div>
            <!-- Progress Bar 1 -->
            <div class="flex-1 h-1 bg-slate-200 mx-4 relative rounded-full overflow-hidden">
                <div class="step-progress absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-700 ease-out rounded-full" style="width: 0%"></div>
            </div>
            <!-- Step 2 -->
            <div class="step-item flex items-center text-slate-400 font-medium relative">
                <div class="w-12 h-12 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-sm ring-4 ring-slate-50 relative z-10 transition-all duration-300">2</div>
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold">Requirements</span>
                    <span class="text-xs text-slate-400 hidden md:block">System check</span>
                </div>
            </div>
            <!-- Progress Bar 2 -->
            <div class="flex-1 h-1 bg-slate-200 mx-4 relative rounded-full overflow-hidden">
                <div class="step-progress absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 to-blue-400 transition-all duration-700 ease-out rounded-full" style="width: 0%"></div>
            </div>
            <!-- Step 3 -->
            <div class="step-item flex items-center text-slate-400 font-medium relative">
                <div class="w-12 h-12 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-sm ring-4 ring-slate-50 relative z-10 transition-all duration-300">3</div>
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold">Configuration</span>
                    <span class="text-xs text-slate-400 hidden md:block">Setup details</span>
                </div>
            </div>
            <!-- Progress Bar 3 -->
            <div class="flex-1 h-1 bg-slate-200 mx-4 relative rounded-full overflow-hidden">
                <div class="step-progress absolute top-0 left-0 h-full bg-gradient-to-r from-purple-500 to-purple-400 transition-all duration-700 ease-out rounded-full" style="width: 0%"></div>
            </div>
            <!-- Step 4 -->
            <div class="step-item flex items-center text-slate-400 font-medium relative">
                <div class="w-12 h-12 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-sm ring-4 ring-slate-50 relative z-10 transition-all duration-300">4</div>
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold">Complete</span>
                    <span class="text-xs text-slate-400 hidden md:block">All done</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Progress Summary -->
    <div class="bg-gradient-to-r from-slate-50 to-blue-50 rounded-xl p-4 border border-slate-200 shadow-inner">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center shadow-sm">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                      <rect width="32" height="32" rx="8" fill="#2563eb"/>
                      <path d="M10 17l5 5 7-9" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">Installation Progress</p>
                    <p class="text-xs text-slate-600">
                        Step
                        <span class="current-step-number">1</span>
                        of 4
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-32 h-2 bg-slate-200 rounded-full overflow-hidden shadow-inner">
                    <div class="overall-progress h-full bg-gradient-to-r from-emerald-500 to-blue-500 transition-all duration-500 rounded-full" style="width: 25%"></div>
                </div>
                <span class="progress-percent text-sm font-semibold text-slate-600 min-w-[2.5rem]">25%</span>
            </div>
        </div>
    </div>
</div>