@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('System Information') }}"
        description="{{ __('Exploring essential requirements for optimal performance') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container w-900 pb-5">

    <!-- Web Server Information Table -->
    <div class="card border-gray-300 mb-5">
        <div class="card-header fw-5">{{ __("Web Server Information") }}<i class="fa-light fa-server fs-20"></i></div>
        <div class="card-body p-0">
            <table class="table table-hover w-100">
                <thead class="">
                    <tr class="">
                        <th class="max-w-200 min-w-200 w-200"><span class="fw-5 fw-12">{{ __("Setting") }}</span></th>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Value") }}</th>
                        <th>Requires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("Web Server Type") }}</td>
                        <td class="fw-5 fs-13">{{ $serverSoftware }}</td>
                        <td class="fw-5 fs-13">{{ __("Apache or Nginx recommended") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHP Configuration Table -->
    <div class="card border-gray-300 mb-5">
        <div class="card-header fw-5">{{ __("PHP Configuration") }}<i class="fa-light fa-sliders fs-20"></i></div>
        <div class="card-body p-0">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="max-w-200 min-w-200 w-200"><span class="fw-5 fw-12">Setting</span></th>
                        <th class="max-w-200 min-w-200 w-200">Value</th>
                        <th>Requires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("PHP Version") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['phpversion'] }}</td>
                        <td class="fw-5 fs-13">{{ __("PHP >= 8.2") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("file_uploads") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['file_uploads'] ? __('Enabled') : __('Disabled') }}</td>
                        <td class="fw-5 fs-13">{{ __("Enabled") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("max_execution_time") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['max_execution_time'] }} {{ __("seconds") }}</td>
                        <td class="fw-5 fs-13">{{ __("120 or more seconds") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("SMTP") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['SMTP'] }}</td>
                        <td class="fw-5 fs-13">{{ __("Set as per email configuration") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("smtp_port") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['smtp_port'] }}</td>
                        <td class="fw-5 fs-13">{{ __("Typically 587, 25, 465 or None") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("upload_max_filesize") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['upload_max_filesize'] }}</td>
                        <td class="fw-5 fs-13">{{ __("At least 1024M") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("allow_url_fopen") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['allow_url_fopen'] ? __('Enabled') : __('Disabled') }}</td>
                        <td class="fw-5 fs-13">{{ __("Enabled") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("allow_url_include") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['allow_url_include'] ? __('Enabled') : __('Disabled') }}</td>
                        <td class="fw-5 fs-13">{{ __("Disabled (for security)") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("memory_limit") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['memory_limit'] }}</td>
                        <td class="fw-5 fs-13">{{ __("512M or more") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("post_max_size") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['post_max_size'] }}</td>
                        <td class="fw-5 fs-13">{{ __("At least 1024M") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("max_input_time") }}</td>
                        <td class="fw-5 fs-13">{{ $phpSettings['max_input_time'] }} {{ __("seconds") }}</td>
                        <td class="fw-5 fs-13">{{ __("120 seconds") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MySQL Configuration Table -->
    <div class="card border-gray-300 mb-5">
        <div class="card-header fw-5">
            {{ __("MySQL Configuration") }}<i class="fa-light fa-wrench fs-20"></i>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="max-w-200 min-w-200 w-200"><span class="fw-5 fw-12">{{ __("Setting") }}</span></th>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Value") }}</th>
                        <th>{{ __("Requires") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("max_connections") }}</td>
                        <td class="fw-5 fs-13">{{ $mysqlSettings['max_connections'] ?? __('Not Available') }}</td>
                        <td class="fw-5 fs-13">{{ __("100 or more") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("max_user_connections") }}</td>
                        <td class="fw-5 fs-13">{{ $mysqlSettings['max_user_connections'] ?? __('Not Available') }}</td>
                        <td class="fw-5 fs-13">{{ __("At least 5 per user") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("wait_timeout") }}</td>
                        <td class="fw-5 fs-13">{{ $mysqlSettings['wait_timeout'] ?? __('Not Available') }} {{ __("seconds") }}</td>
                        <td class="fw-5 fs-13">{{ __("300 seconds") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("max_allowed_packet") }}</td>
                        <td class="fw-5 fs-13">{{ $mysqlSettings['max_allowed_packet'] ?? __('Not Available') }} {{ __("bytes") }}</td>
                        <td class="fw-5 fs-13">{{ __("At least 16M") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHP Extensions Table -->
    <div class="card border-gray-300 mb-5">
        <div class="card-header fw-5">
            {{ __("PHP Extensions") }}<i class="fa-light fa-sliders-up fs-20"></i>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Extension") }}</th>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Status") }}</th>
                        <th>{{ __("Requires") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("PDO MySQL") }}</td>
                        <td class="{{ $extensions['pdo_mysql'] === 'Disabled' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($extensions['pdo_mysql']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Required for database connection") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("intl") }}</td>
                        <td class="{{ $extensions['intl'] === 'Disabled' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($extensions['intl']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Required for localization") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("OpenSSL") }}</td>
                        <td class="{{ $extensions['openssl'] === 'Disabled' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($extensions['openssl']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Required for HTTPS support") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("Zip") }}</td>
                        <td class="{{ $extensions['zip'] === 'Disabled' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($extensions['zip']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Required for zip archive handling") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Image Support Table -->
    <div class="card mb-4 border-none shadow">
        <div class="card-header fw-5">
            {{ __("Image Format Support") }}<i class="fa-light fa-images fs-20"></i>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Image Format") }}</th>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Status") }}</th>
                        <th>{{ __("Requires") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("JPEG Support") }}</td>
                        <td class="{{ $imageSupport['jpeg'] === 'Not Supported' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($imageSupport['jpeg']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Enabled for compression") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("PNG Support") }}</td>
                        <td class="{{ $imageSupport['png'] === 'Not Supported' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($imageSupport['png']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Enabled for compression") }}</td>
                    </tr>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("WebP Support") }}</td>
                        <td class="{{ $imageSupport['webp'] === 'Not Supported' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($imageSupport['webp']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Enabled for compression") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Server Tools Table -->
    <div class="card mb-4 border-none shadow">
        <div class="card-header fw-5">
            {{ __("Server Tools") }}<i class="fa-light fa-gear-complex-code fs-20"></i>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Tool") }}</th>
                        <th class="max-w-200 min-w-200 w-200">{{ __("Status") }}</th>
                        <th>{{ __("Requires") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-5 fs-13">{{ __("FFMPEG") }}</td>
                        <td class="{{ $tools['ffmpeg'] === 'Not Installed' ? 'fs-13 fw-6 text-danger' : 'fs-13 fw-6 text-success' }}">
                            {{ __($tools['ffmpeg']) }}
                        </td>
                        <td class="fw-5 fs-13">{{ __("Required for video processing") }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
