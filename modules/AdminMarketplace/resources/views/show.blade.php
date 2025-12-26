@extends('layouts.app')

@section('content')
<div class="border-bottom mb-1 pt-5 bg-polygon">
    

    <div class="container">
        <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
            <div class="d-flex flex-column gap-8 mb-3">
                <h1 class="fs-20 font-medium lh-1 text-gray-900">
                    <span class="fw-6">{{ $product['name'] ?? __('Module Detail') }}</span>
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __("Explore full detail and purchase this module for your Stackposts system") }}</span>
                    </div>
                </div>

                <div class="d-flex gap-20">
                    <div class="d-flex fw-5 gap-10">
                        <span class="text">
                            {{ __('By') }}
                            <a href="javascript:void(0);" class="link text-main fw-6">
                                {{ $product['author'] ?? '' }}
                            </a>
                        </span>
                    </div>
                    <div class="d-flex fw-5 gap-10">
                        <span class="icon">
                            <i class="fa-regular fa-cart-shopping"></i>
                        </span>
                        <span class="text">
                            {{ __(":sales Sales", ['sales' => $product['sales']]) }}
                        </span>
                    </div>
                    <div class="d-flex fw-5 gap-10">
                        <span class="icon text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        <span class="text">{{ __("Recently Updated") }}</span>
                    </div>
                    <div class="d-flex fw-5 gap-10">
                        <span class="icon text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        <span class="text">{{ __("Well Documented") }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-8">
                
            </div>
        </div>
        <nav class="flex items-center justify-center">
            <div class="nav nav-tabs border-bottom-0">
                <a href="{{ route('admin.marketplace.detail', $product['slug']) }}" class="nav-link px-2 {{ request()->segment(5) == ''? 'active' : '' }}" >
                    <i class="fa-light fa-circle-info me-1 fs-16"></i> {{ __('Detail') }}
                </a>
                <a href="{{ route('admin.marketplace.faqs', $product['slug']) }}" class="nav-link px-2 {{ request()->segment(5) == 'faqs'? 'active' : '' }}">
                    <i class="fa-light fa-circle-question me-1 fs-16"></i> {{ __('FAQs') }}
                </a>
                <a href="{{ route('admin.marketplace.support', $product['slug']) }}" class="nav-link px-2 {{ request()->segment(5) == 'support'? 'active' : '' }}">
                    <i class="fa-light fa-life-ring me-1 fs-16"></i> {{ __('Support') }}
                </a>
                <a href="{{ route('admin.marketplace.changelog', $product['slug']) }}" class="nav-link px-2 {{ request()->segment(5) == 'changelog'? 'active' : '' }}">
                    <i class="fa-light fa-clock-rotate-left  me-1 fs-16"></i> {{ __('Changelog') }}
                </a>
            </div>
        </nav>
    </div>

</div>

<div class="container py-4">
    <div class="row g-5">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            
            @php
                $page = request()->segment(5);
            @endphp
            @switch($page)
                @case('faqs')
                    @include('adminmarketplace::partials.faqs')
                    @break

                @case('support')
                    @include('adminmarketplace::partials.support')
                    @break

                @case('changelog')
                    @include('adminmarketplace::partials.changelog')
                    @break

                @default
                    @include('adminmarketplace::partials.detail')

            @endswitch
            
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- Pricing Box --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h3 class="text-dark fw-bold mb-3 fs-40">${{ $product['price'] }}</h3>

                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fa-regular fa-check-circle text-success me-2"></i> {{ __('Quality verified') }}
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fa-regular fa-check-circle me-2 text-success"></i> {{ __('6 months support included') }}
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="fa-regular fa-check-circle me-2 text-success"></i> {{ __('Lifetime updates & usage') }}
                        </li>
                    </ul>

                    <div class="mb-3">
                        @if ($installed)
                            @if ($installedStatus === 0)
                                <button class="btn btn-secondary w-100 fw-semibold rounded-pill" disabled>
                                    <i class="fa fa-power-off me-1"></i> {{ __('Deactivated') }}
                                </button>
                            @elseif ($hasUpdate)
                                <a href="{{ route('admin.marketplace.do_update', ['product_id' => $product['id']]) }}" class="btn btn-warning w-100 fw-semibold rounded-pill actionItem" data-redirect="">
                                    <i class="fa fa-arrow-up me-1"></i> {{ __('Update to :version', ['version' => $product['version']]) }}
                                </a>
                            @else
                                <button class="btn btn-success w-100 fw-semibold rounded-pill" disabled>
                                    <i class="fa fa-check-circle me-1"></i> {{ __('Installed') }}
                                </button>
                            @endif
                        @else
                            @if (!empty($product['product_url']))
                                <a href="{{ $product['product_url'] }}" class="btn btn-dark w-100 fw-semibold rounded-pill">
                                    <i class="fa fa-shopping-cart me-1"></i> {{ __('Buy now') }}
                                </a>
                            @endif
                        @endif
                    </div>

                    <a href="{{ route('admin.marketplace.index') }}" class="btn btn-light w-100 rounded-pill">
                        <i class="fa fa-arrow-left me-1"></i> {{ __('Back to Marketplace') }}
                    </a>
                </div>
            </div>

            <!-- Quick Facts -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                <div class="card-body p-4">
                    <div class="row fs-20 fw-5">
                        <div class="col-6">
                            <span>{{ number_format($product->sales_count ?? 0) }}</span>
                            <span><i class="fa-light fa-fire text-warning me-2"></i> {{ __('Sales') }}</span>
                        </div>
                        <div class="col-6">
                            <span>{{ number_format($product->view_count ?? 0) }}</span>
                            <span><i class="fa-light fa-eye text-success me-2"></i> {{ __('Views') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Block --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent">
                    <div class="text-muted fw-5">{{ __('Product Info') }}</div>
                </div>
                <div class="card-body p-4">
                    <div class="row text-muted small">
                        <div class="col-6 mb-3"><strong>{{ __('Version') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">{{ $product['version'] }}</div>

                        <div class="col-6 mb-3"><strong>{{ __('Last Update') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">{{ date('M d, Y') }}</div>

                        <div class="col-6 mb-3"><strong>{{ __('Published') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">{{ date('M d, Y') }}</div>

                        <div class="col-6 mb-3"><strong>{{ __('Category') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">Laravel</div>

                        <div class="col-6 mb-3"><strong>{{ __('Framework') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">Laravel</div>

                        <div class="col-6 mb-3"><strong>{{ __('Compatible') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">Stackposts 6+</div>

                        <div class="col-6 mb-3"><strong>{{ __('Tags') }}:</strong></div>
                        <div class="col-6 mb-3 text-end">Laravel</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
