@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Crons') }}" 
        description="{{ __('Automates scheduled tasks for efficient time-based execution') }}"
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container pb-5">
    
    <div class="card">
        <div class="card-header">
            <div class="fw-5">{{ __('Secure Cron Key') }}</div>
        </div>
        <div class="card-body">
            <form class="actionForm" method="POST" action="{{ module_url("change") }}" data-confirm="{{ __('Changing the key requires updating all cronjobs in your system for continued operation. Are you sure you want to continue?') }}">
                <div class="input-group mb-0">
                    <span class="btn btn-input">
                        <i class="fa-light fa-key"></i>
                    </span>
                    <input class="form-control disabled" type="text" value="{{ get_option("cron_key", rand_string()) }}">
                    <button type="submit" class="btn btn-dark">
                        <i class="fa-light fa-arrows-rotate"></i> {{ __('Change Key') }}
                    </button>
                </div>
                <span class="fs-12 text-gray-500">{{ __('Use this secret key for secure URL-based cron execution') }}</span>
            </form>
        </div>
    </div>

    @if($crons)
    <div class="fw-5 pt-5 pb-3">{{ __('List Cron') }}</div>

        @foreach($crons as $value)

        <div class="card mb-4">
            <div class="card-header px-3">
                <div class="fw-5"> <i class="{{ $value['icon'] }} me-2" style="color: {{ $value['color'] }}"></i> {{ __($value['module_name'])??__($value['command_name']) }}</div>
            </div>
            @if($value['url']??false)
            <div class="card-body bg-gray-100 fw-5 fs-12 py-2 px-3">
                {{ __('Use cron with URL') }}
            </div>
            <div class="card-body text-success bg-dark">
                <pre class="mb-0">{{ $value['expression'] }} curl -fsS "{{ $value['url'] ?? $value['command']  }}" >/dev/null 2>&1</pre>
            </div>
            @endif
            <div class="card-body bg-gray-100 fw-5 fs-12 py-2 px-3">
                {{ __('Use cron with Artisan Command') }}
            </div>
            <div class="card-body text-success bg-dark bbr-r-10 bbl-r-10">
                <pre class="mb-0">{{ $value['expression'] }} {{ $value['full_command']  }}</pre>
            </div>
        </div>
        @endforeach

    @endif

</div>
@endsection