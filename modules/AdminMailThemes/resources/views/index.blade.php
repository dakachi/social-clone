@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Mail Themes') }}"
        description="{{ __('Customizable email layouts for consistent, branded communication.') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container py-4">
    <div class="row g-4">
        @foreach($themes as $theme)
            <div class="col-md-4">
                <div class="card hp-100 border border-gray-300 overflow-hidden">
                    @if(!empty($theme['preview']))
                        <img src="{{ $theme['preview'] }}" class="card-img-top" alt="{{ __('Theme Preview') }}" style="object-fit:cover;height:220px;">
                    @else
                        <div class="bg-light text-center py-5 fs-3 text-muted">{{ __('No preview') }}</div>
                    @endif
                    <div class="card-body d-flex flex-column border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $theme['info']['name'] ?? $theme['slug'] }}</h5>
                            @if(isset($active) && $active == $theme['slug'])
                                <span class="badge badge-pill badge-outline badge-sm badge-success ms-2">{{ __('Active') }}</span>
                            @endif
                        </div>
                        <p class="card-text small mb-2 text-muted text-truncate-3">
                            {{ $theme['info']['description'] ?? __('No description') }}
                        </p>
                        <ul class="list-unstyled mb-3 small">
                            @if(!empty($theme['info']['author']))
                                <li><strong>{{ __('Author') }}:</strong> {{ $theme['info']['author'] }}</li>
                            @endif
                            @if(!empty($theme['info']['version']))
                                <li><strong>{{ __('Version') }}:</strong> {{ $theme['info']['version'] }}</li>
                            @endif
                            <li><strong>{{ __('Slug') }}:</strong> <code>{{ $theme['slug'] }}</code></li>
                        </ul>
                        <div class="mt-auto">
                            @if(!isset($active) || $active != $theme['slug'])
                                <a class="btn btn-dark w-100 actionItem" href="{{ module_url("set-default") }}" data-id="{{ $theme['slug'] }}" data-redirect="">
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
