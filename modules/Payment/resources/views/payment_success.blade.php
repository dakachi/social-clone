@extends('layouts.app')

@section('content')
<div class="max-w-600 mx-auto p-5 d-flex align-items-center hp-100 min-h-700">
    <div class="card fs-14">
        <div class="card-body py-5">
            <div class="text-center">
                <div class="fs-90 text-success mb-3">
                    <i class="fa-duotone fa-solid fa-circle-check"></i>
                </div>
                <div class="fs-30 fw-9 text-gray-900 mb-3">
                    @if(!empty($result))
                        @if($result['type'] == 2)
                            {{ __("Thank you for your subscription!") }}
                        @else
                            {{ __("Thank you for your payment!") }}
                        @endif
                    @else
                        {{ __("Thank you!") }}
                    @endif
                </div>
                <div class="fs-14 text-gray-600">
                    @if(!empty($msg))
                        {{ $msg }}
                    @else
                        {{ __("Your order is being processed and will be activated shortly. We appreciate your trust and are excited to have you onboard. Enjoy all the benefits coming your way.") }}
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($result))
            @if($result['type'] == 2)
                {{-- Recurring Payment (Subscription) --}}
                <div class="card-body bg-gray-200 border-top border-bottom border-gray-300">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Subscription ID") }}</div>
                            <div class="fw-6">{{ $result['subscription_id'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Gateway") }}</div>
                            <div class="fw-6">{{ $result['gateway'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Amount") }}</div>
                            <div class="fw-6">
                                {{ isset($result['amount']) ? Core::currency($result['amount'], $result['currency'] ?? 'USD') : '-' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Currency") }}</div>
                            <div class="fw-6">{{ $result['currency'] ?? '-' }}</div>
                        </div>
                        {{-- Add more subscription info if needed --}}
                    </div>
                </div>
            @else
                {{-- One-time Payment --}}
                <div class="card-body bg-gray-200 border-top border-bottom border-gray-300">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Transaction ID") }}</div>
                            <div class="fw-6">{{ $result['transaction_id'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Gateway") }}</div>
                            <div class="fw-6">{{ $result['gateway'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Amount") }}</div>
                            <div class="fw-6">
                                {{ isset($result['amount']) ? Core::currency($result['amount'], $result['currency'] ?? 'USD') : '-' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Currency") }}</div>
                            <div class="fw-6">{{ $result['currency'] ?? '-' }}</div>
                        </div>
                        {{-- Add more transaction info if needed --}}
                    </div>
                </div>
            @endif
        @endif

        <div class="card-body text-center">
            <a class="btn btn-dark" href="{{ route("app.dashboard.index") }}">{{ __("Go To Dashboard") }}</a>
        </div>
    </div>
</div>
@endsection
