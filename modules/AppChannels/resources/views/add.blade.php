@extends('layouts.app')

@section('content')
    <div class="container px-4 max-w-700">

        <div class="mt-4 mb-5">
            <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
                <div class="my-3 d-flex flex-column gap-8">
                    <h1 class="fs-20 font-medium lh-1 text-gray-900">
                        {{ __("Add new channels") }}
                    </h1>
                    <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                        <div class="d-flex gap-8">
                            <span class="text-gray-600"><span class="text-gray-600">{{ __('Add and Start Managing Your Social Profile') }}</span></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-8">
                    <a class="btn btn-light btn-sm" href="{{ $result['reconnect_url'] }}">
                        <span><i class="fa-light fa-rotate-right"></i></span>
                        <span>{{ __('Reconnect') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div class="input-group mb-3">
                <input class="form-control bg-white search-input" placeholder="{{ __("Search ...") }}" type="text" value="">
                <span class="btn btn-icon btn-input min-w-55">
                    <input class="form-check-input checkbox-all" type="checkbox" value="">
                </span>
            </div>
        </div>

        <form class="actionForm" action="{{ $result['save_url'] }}" method="POST">
            @if($result['status'] == 1 && isset($result['channels']) && !empty($result['channels']) )

                @foreach ( $result['channels'] as $key => $value )
                    <div class="mb-2 search-list">
                        <div class="card shadow-none b-r-6">
                            <div class="card-body px-3">
                                <div class="d-flex flex-grow-1 align-items-center gap-8">
                                    <label for="channel_{{ $key }}" class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
                                        <img src="{{ __($value['avatar']) }}" class="b-r-100 w-full h-full border-1">
                                        <span class="size-16 border-1 b-r-100 position-absolute fs-10 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: {{ $result['module']['color'] }};">
                                            <div class="w-100"><i class="{{ $result['module']['icon'] }}"></i></div>
                                        </span>
                                    </label>
                                    <label for="channel_{{ $key }}" class="flex-grow-1 fs-14 fw-5 text-truncate">
                                        <div class="text-truncate">{{ __($value['name']) }}</div>
                                        <div class="fs-12 text-gray-600 text-truncate">{{ __($value['desc']) }}</div>
                                    </label>
                                    <div class="d-flex fs-14 gap-8">
                                        <input class="form-check-input checkbox-item" type="checkbox" name="channels[]" value="{{ $value['id'] }}" id="channel_{{ $key }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="mt-4">
                    <button type="submit" class="btn btn-dark w-100" href="{{ module_url("create") }}">
                        <span><i class="fa-light fa-plus"></i></span>
                        <span>{{ __('Add new') }}</span>
                    </button>
                </div>
            @elseif($result['status'] == 0)
                <div class="mt-5">
                    <div class="alert alert-danger d-flex align-items-center gap-16" role="alert">
                        <div class="fs-45"><i class="fa-light fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="fw-6">{{ __("Error") }}</div>
                            <div>{{ __($result['message']) }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty my-5"></div>
            @endif
        </form>

    </div>
@endsection

@section('script')
    
@endsection
