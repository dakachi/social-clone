<div class="step-content step-2 hidden">
    @php
        $php_version = PHP_VERSION;
        $php_required = version_compare($php_version, '8.2.0', '>=');
    @endphp

    <div class="requirement-item flex items-center justify-between p-4 bg-white rounded-xl border border-green-200 shadow-sm mb-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-500 text-white rounded-xl flex items-center justify-center shadow-sm">
                <span class="font-bold text-base">PHP</span>
            </div>
            <div>
                <h4 class="text-base font-semibold text-slate-900">PHP Runtime</h4>
                <p class="text-sm text-slate-600">
                    Version {{ $php_version }} (Required: ≥8.2)
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="{{ $php_required ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
                {{ $php_required ? 'Passed' : 'Failed' }}
            </span>
        </div>
    </div>

    <!-- PHP Extensions -->
    @php
        $exts = [
            'PDO' => extension_loaded('pdo'),
            'Mbstring' => extension_loaded('mbstring'),
            'Fileinfo' => extension_loaded('fileinfo'),
            'OpenSSL' => extension_loaded('openssl'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'XML' => extension_loaded('xml'),
            'Ctype' => extension_loaded('ctype'),
            'JSON' => extension_loaded('json'),
            'BCMath' => extension_loaded('bcmath'),
            'GD' => extension_loaded('gd'),
            'Intl' => extension_loaded('intl'),
        ];
    @endphp

    @foreach($exts as $ext => $enabled)
        <div class="requirement-item flex items-center justify-between p-4 bg-white rounded-xl border border-green-200 shadow-sm mb-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 {{ $enabled ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-lg flex items-center justify-center shadow-sm">
                    <span class="font-bold text-base">{{ substr($ext,0,2) }}</span>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-slate-900">{{ $ext }} Extension</h4>
                    <p class="text-xs text-slate-600">
                        {{ $enabled ? 'Enabled' : 'Missing' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="{{ $enabled ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
                    {{ $enabled ? 'Passed' : 'Failed' }}
                </span>
            </div>
        </div>
    @endforeach

    <!-- Database PDO Drivers -->
    @php
        $db_drivers = [
            'MySQL (pdo_mysql)' => extension_loaded('pdo_mysql'),
        ];
    @endphp

    @foreach($db_drivers as $db => $ok)
        <div class="requirement-item flex items-center justify-between p-4 bg-white rounded-xl border border-green-200 shadow-sm mb-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 {{ $ok ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-lg flex items-center justify-center shadow-sm">
                    <span class="font-bold text-base">{{ strtoupper(substr($db,0,2)) }}</span>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-slate-900">{{ $db }}</h4>
                    <p class="text-xs text-slate-600">
                        {{ $ok ? 'Available' : 'Not Available' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="{{ $ok ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
                    {{ $ok ? 'Passed' : 'Failed' }}
                </span>
            </div>
        </div>
    @endforeach

    <!-- Folder permissions -->
    @php
        $storage_writable = is_writable(storage_path());
        $cache_writable = is_writable(base_path('bootstrap/cache'));
    @endphp

    <div class="requirement-item flex items-center justify-between p-4 bg-white rounded-xl border border-green-200 shadow-sm mb-3">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 {{ $storage_writable ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-lg flex items-center justify-center shadow-sm">
                <span class="font-bold text-base">ST</span>
            </div>
            <div>
                <h4 class="text-base font-semibold text-slate-900">storage/ Writable</h4>
                <p class="text-xs text-slate-600">
                    {{ $storage_writable ? 'Writable' : 'Not writable' }}
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="{{ $storage_writable ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
                {{ $storage_writable ? 'Passed' : 'Failed' }}
            </span>
        </div>
    </div>
    <div class="requirement-item flex items-center justify-between p-4 bg-white rounded-xl border border-green-200 shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 {{ $cache_writable ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-lg flex items-center justify-center shadow-sm">
                <span class="font-bold text-base">CA</span>
            </div>
            <div>
                <h4 class="text-base font-semibold text-slate-900">bootstrap/cache Writable</h4>
                <p class="text-xs text-slate-600">
                    {{ $cache_writable ? 'Writable' : 'Not writable' }}
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="{{ $cache_writable ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
                {{ $cache_writable ? 'Passed' : 'Failed' }}
            </span>
        </div>
    </div>

    @php
        $requirements = array_merge(
            [$php_required],
            array_values($exts),
            array_values($db_drivers),
            [$storage_writable, $cache_writable]
        );
        $all_passed = !in_array(false, $requirements, true);
    @endphp
    <input type="hidden" id="all-passed" value="{{ $all_passed ? 1 : 0 }}">
    @if(!$all_passed)
        <div class="mt-8 p-6 bg-red-50 border border-red-300 rounded-2xl text-red-700 text-center font-bold shadow-inner">
            You need to complete all system requirements to continue the installation!
        </div>
    @endif

</div>
