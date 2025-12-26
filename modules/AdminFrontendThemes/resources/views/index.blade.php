@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Frontend Theme Manager') }}"
        description="{{ __('Easily manage and activate guest site themes.') }}"
    >
        <div>
            <label for="file-upload" class="btn btn-primary btn-sm">
                <span class="me-1 mt-1 text-center"><i class="fa-light fa-file-import"></i></span> {{ __("Import") }}
            </label>
            <input id="file-upload" data-url="{{ module_url("import") }}" class="d-none" name="file" type="file" multiple="true" data-redirect="" />
        </div>
    </x-sub-header>
@endsection

@section('content')
<div class="container py-4">
    <div class="row g-4">
        @foreach($themes as $theme)
        <div class="col-md-4 mb-3">
            <div class="card hp-100 overflow-hidden border border-gray-300">
                @if(!empty($theme['preview']) && file_exists(base_path('resources/themes/guest/' . $theme['id'] . '/' . $theme['preview'])))
                    <img src="{{ asset('resources/themes/guest/' . $theme['id'] . '/' . $theme['preview']) }}"
                         class="card-img-top h-220" style="object-fit:cover;">
                @else
                    <div style="height:160px;background:#f3f3f3;display:flex;align-items:center;justify-content:center;color:#bbb">
                        No Preview
                    </div>
                @endif
                <div class="card-body border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">{{ $theme['name'] ?? $theme['id'] }}</h5>
                        @if(isset($activeTheme) && $activeTheme == $theme['id'])
                            <span class="badge badge-pill badge-outline badge-sm badge-success ms-2">{{ __('Active') }}</span>
                        @endif
                    </div>
                    <p class="card-text small mb-2 text-muted text-truncate-3">{{ $theme['description'] ?? '' }}</p>
                    <ul class="list-unstyled mb-3 small">
                        @if(!empty($theme['author']))
                            <li><strong>{{ __('Author') }}:</strong> {{ $theme['author'] }}</li>
                        @endif
                        @if(!empty($theme['version']))
                            <li><strong>{{ __('Version') }}:</strong> {{ $theme['version'] }}</li>
                        @endif
                    </ul>
                    <div class="mt-auto">
                        @if(!isset($activeTheme) || $activeTheme != $theme['id'])
                            <a class="btn btn-dark w-100 actionItem" href="{{ module_url("set-default") }}" data-id="{{ $theme['id'] }}" data-redirect="">
                                {{ __('Use Theme') }}
                            </a>
                        @else
                            <button class="btn btn-outline-success w-100" disabled>
                                {{ __('Currently Actived') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
