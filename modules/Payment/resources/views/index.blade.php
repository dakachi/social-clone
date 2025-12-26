@extends('layouts.app')

@section('content')
    @php
        $calculatePayment = Payment::calculatePayment($plan->price, $plan->id, true);
    @endphp 

    <div class="max-w-600 mx-auto p-5">
        <div class="text-center">
            <div class="mb-2">
                <span class="badge badge-outline badge-sm badge-pill badge-info">
                    @switch($plan->type)

                        @case(2)
                            {{ __("Yearly") }}
                            @break

                        @case(3)
                            {{ __("Lifetime") }}
                            @break

                        @default
                            {{ __("Monthly") }}

                    @endswitch
                </span>
            </div>
            <div class="fw-9 fs-30 mb-2">{{ $plan->name }}</div>
            <div class="text-gray-700 mb-5">{{ $plan->desc }}</div>
        </div>

        @include('admincoupons::block_apply_coupon', [])

        <div class="card border-gray-300 b-r-6 fs-14 mb-5">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-12 mb-2">
                    <div>{{ __("Subtotal") }}</div>
                    <div class="text-end">{{ $calculatePayment['subtotal'] }}</div>
                </div>
                <div class="d-flex justify-content-between gap-12 mb-2">
                    <div>{{ __("Promotion") }}</div>
                    <div class="text-end text-danger">{{ $calculatePayment['discount'] }}</div>
                </div>
                

                <div class="d-flex justify-content-between gap-12 border-top pt-3 mt-3 fw-6">
                    <div>{{ __("Total") }}</div>
                    <div class="text-end">{{ $calculatePayment['total'] }}</div>
                </div>
            </div>
        </div>

        @php
        $paymentTypes = [
            1 => __("One-time Payment"),
            2 => __("Recurring Payment"),
        ];
        $allPayments = Payment::getPaymentsByType(null);
        $havePaymentMethod = false;
        @endphp

        <div class="mb-4">
            <span class="fw-6 text-gray-900 fs-20">{{ __("Payment methods") }}</span>
        </div>

        @foreach($paymentTypes as $type => $label)
            @if(!empty($allPayments[$type]))
                @php
                $havePaymentMethod = true;
                @endphp
                <div class="d-flex align-items-center gap-2 mt-3 mb-1">
                    <span class="fw-6 text-gray-700 fs-16">{{ $label }}</span>
                </div>
                @if($type == 1)
                    <div class="fs-13 text-muted mb-3">{{ __("Pay one time, no auto-renewal") }}</div>
                @elseif($type == 2)
                    <div class="fs-13 text-muted mb-3">{{ __("Subscription will auto-renew until you cancel") }}</div>
                @endif
                <div class="row mb-4">
                    @foreach($allPayments[$type] as $value)
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mb-4">
                            <a 
                                href="{{ route("payment.checkout", [ "gateway" => $value['uri'], "plan" => $plan->id_secure ]) }}" 
                                class="card shadow-none border border-gray-300 b-r-6 bg-hover-primary-100 border-hover-primary payment-option"
                                data-payment-type="{{ $type }}"
                                data-gateway="{{ $value['uri'] }}"
                            >
                                <div class="card-body d-flex justify-content-between align-items-center px-3 gap-16">
                                    <div class="d-flex align-items-center gap-8 fs-13 fw-5 text-truncate">
                                        <div class="size-30 d-flex align-items-center justify-content-between fs-20">
                                            <img src="{{ $value['logo'] }}" class="w-100" alt="{{ $value['name'] }}">
                                        </div>
                                        <div>
                                            {{ $value['name'] }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if( get_option("payment_manual_status", 0) )

        @php
        $havePaymentMethod = true;
        @endphp
        <div class="d-flex align-items-center gap-2 mt-3 mb-1">
            <span class="fw-6 text-gray-700 fs-16">{{ __("Manual Payment") }}</span>
        </div>
        <div class="fs-13 text-muted mb-3">{{ __("Bank transfer, cash, or manual confirmation.") }}</div>

        <div class="card p-4 border border-gray-300 rounded-3 mb-4">
            @include("components.main-message")
            
            <ul class="mb-3 fs-14 text-muted">
                <li class="mb-3">
                    {!! get_option('payment_manual_info', 'Bank Info') !!}
                </li>
                <li>
                    <span class="mb-1 fw-6">{{ __("Transfer content") }}</span>
                    <span class="badge badge-light b-r-6 fs-15 px-3 py-3 w-100">{{ $transactionCode }}</span>
                </li>
            </ul>
            <form action="{{ route("payment.manual_payment") }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id_secure }}">
                <input type="hidden" name="transaction_code" value="{{ $transactionCode }}">
                <div class="mb-3">
                    <label class="form-label fw-6">{{ __("Your transfer information") }}</label>
                    <textarea name="payment_info" class="form-control" rows="3" placeholder="{{ __("E.g. I transferred, account name John Doe, at 09:30 AM") }}" required></textarea>
                </div>
                <div class="mb-3">
                    {!! Captcha::render(); !!}
                </div>
                <button type="submit" class="btn btn-dark w-100 ">
                    {{ __("I have transferred") }}
                </button>
            </form>
        </div>
        @endif

        @if(!$havePaymentMethod)
        <div class="mt-5 d-flex flex-column align-items-center justify-content-center py-5">
            <div class="empty"></div>
            <div class="fw-bold fs-5 mt-3 text-dark">{{ __("No payment methods available") }}</div>
            <div class="text-muted mt-2">
                {{ __("Please contact support or try again later.") }}
            </div>
        </div>
        @endif
    </div>
@endsection


