@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{!! __('Cache & Session') !!}" 
        description="{{ __('Clear application caches and manage sessions safely.') }}" 
    />
@endsection

@section('content')
<div class="container py-4">
    <div class="row g-4">

        <!-- Application Cache -->
        <div class="col-md-6">
            <div class="card shadow-sm hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><i class="fal fa-database me-2"></i>{{ __('Application Cache') }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ __('Clear all cached application data.') }}</p>
                    <a href="{{ route('admin.settings.cache.clear') }}" 
                       class="btn btn-outline btn-primary mt-auto actionItem" 
                       data-type="app" 
                       data-confirm="{{ __('Are you sure?') }}">
                        <i class="fal fa-trash-alt me-1"></i> {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Config Cache -->
        <div class="col-md-6">
            <div class="card shadow-sm hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><i class="fal fa-cogs me-2"></i>{{ __('Config Cache') }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ __('Rebuild or clear cached configuration files.') }}</p>
                    <a href="{{ route('admin.settings.cache.clear') }}" 
                       class="btn btn-outline btn-primary mt-auto actionItem" 
                       data-type="config" 
                       data-confirm="{{ __('Are you sure?') }}">
                        <i class="fal fa-trash-alt me-1"></i> {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Route Cache -->
        <div class="col-md-6">
            <div class="card shadow-sm hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><i class="fal fa-random me-2"></i>{{ __('Route Cache') }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ __('Clear cached routes and rebuild routing.') }}</p>
                    <a href="{{ route('admin.settings.cache.clear') }}" 
                       class="btn btn-outline btn-primary mt-auto actionItem" 
                       data-type="route" 
                       data-confirm="{{ __('Are you sure?') }}"
                       data-redirect="">
                        <i class="fal fa-trash-alt me-1"></i> {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- View Cache -->
        <div class="col-md-6">
            <div class="card shadow-sm hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><i class="fal fa-eye me-2"></i>{{ __('View Cache') }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ __('Remove compiled Blade view files.') }}</p>
                    <a href="{{ route('admin.settings.cache.clear') }}" 
                       class="btn btn-outline btn-primary mt-auto actionItem" 
                       data-type="view" 
                       data-confirm="{{ __('Are you sure?') }}">
                        <i class="fal fa-trash-alt me-1"></i> {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Optimize -->
        <div class="col-md-12">
            <div class="card shadow-sm hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><i class="fal fa-bolt me-2"></i>{{ __('Optimize') }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ __('Run optimize to rebuild all caches for better performance.') }}</p>
                    <a class="btn btn-success mt-auto actionItem" 
                       href="{{ route('admin.settings.cache.clear') }}" 
                       data-type="optimize" 
                       data-confirm="{{ __('Are you sure?') }}">
                        <i class="fal fa-play-circle me-1"></i> {{ __('Run Optimize') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Clear All Sessions (Super Admin only) -->
        <div class="col-md-12">
            <div class="card shadow-sm border-danger hp-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-danger mb-2">
                        <i class="fal fa-user-slash me-2"></i>{{ __('Clear All Sessions') }}
                    </h5>
                    <p class="card-text text-muted flex-grow-1">
                        {{ __('This will log out all users from the system. Use with caution!') }}
                    </p>
                    <a class="btn btn-danger mt-auto actionItem" 
                       href="{{ route('admin.settings.cache.clear') }}" 
                       data-type="session" 
                       data-confirm="{{ __('Are you sure you want to clear all sessions? This will log out everyone!') }}">
                        <i class="fal fa-exclamation-triangle me-1"></i> {{ __('Clear Sessions') }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
