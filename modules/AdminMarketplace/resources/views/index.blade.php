@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Marketplace') }}" 
        description="{{ __('Discover and install powerful modules') }}" 
    >
        <div class="d-flex gap-8">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <div class="form-control form-control-sm">
                        <span class="btn btn-icon">
                            <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="ajax-scroll-filter" name="search" placeholder="{{ __('Enter your keyword') }}" type="text">
                    </div>
                    <button type="submit" class="btn btn-sm btn-light">
                        {{ __("Search") }}
                    </button>
                </div>
                
            </form>
            <a class="btn btn-outline btn-primary btn-sm text-nowrap" href="{{ module_url("addons") }}">
                <span><i class="fa-light fa-plug"></i></span>
                <span>{{ __('Manage Addons') }}</span>
            </a>
            <a class="btn btn-dark btn-sm actionItem" href="{{ module_url("install") }}" data-popup="installModal">
                <span><i class="fa-light fa-file-zipper"></i></span>
                <span>{{ __('Install') }}</span>
            </a>
        </div>

    </x-sub-header>
@endsection

@section('content')
<div class="container py-5 marketplace-wrapper">
    @if(session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">
        @forelse ($modules as $item)
            <div class="col-md-4">
                <div class="card hp-100 d-flex flex-column rounded-4 overflow-hidden card border-0 shadow-sm rounded-4 mb-4">
                    {{-- <div class="marketplace-thumbnail bg-light">
                        <a href="{{ route('admin.marketplace.detail', $item['slug']) }}">
                            <img src="{{ $item['preview'] }}" alt="{{ $item['name'] }}" class="img-fluid w-100 h-200 object-fit-cover">
                        </a>
                    </div> --}}

                    <div class="card-body d-flex flex-column px-4 py-4">
                        <a href="{{ route('admin.marketplace.detail', $item['slug']) }}">
                            <div class="size-60 size-child mb-4">
                                <img src="{{ $item['thumbnail'] }}" alt="{{ $item['name'] }}" class="img-fluid w-100 h-200 object-fit-cover border border-gray-300 b-r-14 border-3">
                            </div>
                        </a>

                        <a href="{{ route('admin.marketplace.detail', $item['slug']) }}" class="text-dark text-hover-primary">
                            <h5 class="fs-17 fw-semibold mb-1">{{ $item['name'] }}</h5>
                        </a>
                        <p class="text-muted small flex-grow-1 mb-3">{{ \Str::limit($item['description'], 130) }}</p>

                        <div class="d-flex justify-content-between align-items-center mb-3 small">
                            <span class="fw-bold text-primary fs-20">${{ $item['price'] }}</span>
                            <span class="text-muted">{{ $item['version'] }}</span>
                        </div>

                        {{-- Action bar --}}
                        <div>
                            @if (!empty($item['demo_url']))
                            <a href="{{ $item['demo_url'] }}" target="_blank" class="btn btn-sm btn-light rounded-5 border">
                                <i class="fa-light fa-eye"></i> {{ __('Live Demo') }}
                            </a>
                            @endif
                            @if ($item['installed'])
                                @if ($item['addon_status'] === 0)
                                    <button class="btn btn-sm rounded-5 btn-secondary border" disabled>
                                        <i class="fa fa-power-off me-1"></i> {{ __('Deactivated') }}
                                    </button>
                                @elseif ($item['has_update'])
                                    <a href="{{ route('admin.marketplace.do_update', ['product_id' => $item['id']]) }}" class="btn btn-sm btn-warning fw-semibold rounded-pill actionItem" data-redirect="">
                                        <i class="fa fa-arrow-up me-1"></i>
                                        {{ __('Update to :version', ['version' => $item['version']]) }}
                                    </a>
                                @else
                                    <button class="btn btn-sm rounded-5 btn-success border" disabled>
                                        <i class="fa fa-check-circle me-1"></i> {{ __('Installed') }}
                                    </button>
                                @endif
                            @else
                                @if (!empty($item['product_url']))
                                    <a href="{{ $item['product_url'] }}" target="_blank" class="btn btn-sm rounded-5 btn-dark border">
                                        <i class="fa fa-cart-plus me-1"></i> {{ __('Buy Now') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center max-w-600 mx-auto">
                <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                    <span class="fs-70 mb-3 text-primary">
                        <i class="fa-light fa-puzzle-piece"></i>
                    </span>
                    <div class="fw-semibold fs-5 mb-2 text-gray-800">
                        {{ __('No modules found') }}
                    </div>
                    <div class="text-body-secondary mb-4">
                        {{ __('No modules are available in the marketplace at this time. Please check back later for new modules or updates.') }}
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-dark">
                        <i class="fa-light fa-house"></i> {{ __('Go to Dashboard') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($modules->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $modules->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection
