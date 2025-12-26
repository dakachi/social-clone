<!-- Alert Toast for Success/Error (Global, đặt ở đầu file, ngoài step-content) -->
<div 
    x-data="{
        show: false, 
        type: 'success', 
        message: '', 
        errors: {},
        showAlert(event) {
            this.type = event.detail.type || 'success';
            this.message = event.detail.message || '';
            this.errors = event.detail.errors || {};
            this.show = true;
            setTimeout(() => this.show = false, this.type === 'success' ? 3000 : 6000);
        }
    }"
    x-on:show-alert.window="showAlert($event)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="fixed top-7 left-1/2 -translate-x-1/2 w-full max-w-md z-50"
    style="display: none;"
>
    <div 
        :class="type === 'success' 
            ? 'bg-green-50 border-green-500 text-green-800' 
            : 'bg-rose-50 border-rose-500 text-rose-800'"
        class="border-l-4 px-6 py-5 rounded-xl shadow-lg flex flex-col gap-2 relative"
    >
        <div class="flex items-center gap-3 font-semibold text-lg">
            <span x-show="type === 'success'">
                <!-- Check -->
                <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" stroke-width="2" fill="#22c55e" opacity=".15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span x-show="type === 'error'">
                <!-- X-circle -->
                <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" stroke-width="2" fill="#f43f5e" opacity=".15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            <span x-text="message"></span>
        </div>
        <template x-if="Object.keys(errors).length">
            <ul class="text-sm pl-7 list-disc space-y-1">
                <template x-for="[field, msg] in Object.entries(errors)" :key="field">
                    <li class="text-rose-600" x-text="msg"></li>
                    <pre x-text="JSON.stringify(errors, null, 2)" class="bg-white p-2 rounded text-xs text-red-700"></pre>
                </template>
            </ul>
        </template>
        <button 
            type="button" 
            x-on:click="show = false" 
            class="absolute top-2 right-2 text-slate-400 hover:text-slate-600 rounded transition"
            aria-label="Close"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-width="2" d="M6 6l8 8M6 14L14 6"/></svg>
        </button>
    </div>
</div>

<!-- Step 3: Configuration -->
<div class="step-content step-3 hidden">
    <div class="text-center mb-8">
        <h1 class="mb-4 text-4xl md:text-5xl leading-tight text-slate-900 font-bold tracking-tight">
            Configuration Setup
        </h1>
        <p class="mb-6 text-xl md:text-2xl text-slate-600 font-medium">
            Configure your application settings and database connection
        </p>
        <div class="inline-flex items-center px-6 py-3 bg-purple-50 text-purple-700 rounded-full text-sm font-medium shadow-sm border border-purple-200">
            <div class="w-2 h-2 bg-purple-500 rounded-full mr-2 animate-pulse"></div>
            Intelligent configuration synchronization
        </div>
    </div>
    <div class="space-y-6">
        <!-- Purchase Code Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Purchase Code</h3>
                    <p class="text-sm text-slate-600">Provide your purchase code for activation</p>
                </div>
            </div>
            <div class="grid md:grid-cols-1 gap-6">
                <div class="form-group">
                    <input name="purchase_code"
                        class="config-input purchase-code w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md"
                        type="text"
                        placeholder="Enter your purchase code"
                    />
                </div>
            </div>
        </div>

        <!-- Database Configuration Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Database Configuration</h3>
                    <p class="text-sm text-slate-600">Configure your database connection settings</p>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Database Host</label>
                    <input name="database_host" class="config-input database-host w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="Enter your database host" value="localhost"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Database Name</label>
                    <input name="database_name" class="config-input database-name w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="Enter your database name"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Database Username</label>
                    <input name="database_username" class="config-input database-username w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="Enter your database username"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Database Password</label>
                    <input name="database_password" class="config-input database-password w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="password" placeholder="Enter your database password"/>
                </div>
            </div>
        </div>

        <!-- Site Configuration Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Site Configuration</h3>
                    <p class="text-sm text-slate-600">Configure your site settings and admin account</p>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="form-group md:col-span-2">
                    <label class="block mb-2 text-slate-700 font-semibold">Site Name</label>
                    <input name="site_name" class="config-input site-name w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="My Application"/>
                </div>
                @php
                    $timezoneList = get_all_timezones_with_offset();
                @endphp
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Timezone</label>
                    <select name="timezone" class="config-input timezone w-full py-3 px-4 text-slate-600 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md">
                        <option value="">Select timezone</option>
                        @foreach($timezoneList as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Full Name</label>
                    <input name="fullname" class="config-input full-name w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="Enter your full name"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Email</label>
                    <input name="admin_email" class="config-input admin-email w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="email" placeholder="admin@example.com"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Username</label>
                    <input name="admin_username" class="config-input admin-username w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="text" placeholder="admin"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Password</label>
                    <input name="admin_password" class="config-input admin-password w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="password" placeholder="Enter password"/>
                </div>
                <div class="form-group">
                    <label class="block mb-2 text-slate-700 font-semibold">Confirm Password</label>
                    <input name="admin_password_confirm" class="config-input admin-password-confirm w-full py-3 px-4 text-slate-600 leading-tight placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 border border-slate-300 rounded-xl shadow-sm transition-all duration-200 focus:shadow-md" type="password" placeholder="Confirm password"/>
                </div>
            </div>
        </div>
    </div>
</div>