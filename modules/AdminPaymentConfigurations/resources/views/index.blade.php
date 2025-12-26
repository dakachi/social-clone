@php
$payments = app("payments") ?? [];
@endphp

@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Payment Getway Configuration') }}" 
        description="{{ __('Integrate payment gateway for secure and seamless transactions') }}" 
    >
    </x-sub-header>
@endsection


@section('content')

    <div class="container pb-5">
        <form class="actionForm" action="{{ url_admin("settings/save") }}">
            <div class="card shadow-none border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-6">
                        {{ __("General Configuration") }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                <select class="form-control" name="currency">
                                    <?php foreach (Payment::listCurrency() as $currency => $name) {?>
                                        <option value="{{ $currency }}" {{ get_option('currency', 'USD') == $currency?"selected":"" }} >[{{ $currency }}] {{ $name }}</option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="currency_symbol" class="form-label">{{ __('Symbol') }}</label>
                                <input type="text" class="form-control form-control-solid" id="currency_symbol" name="currency_symbol" value="{{ get_option("currency_symbol", "$") }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-4">
                                <label for="currency_symbol_postion" class="form-label">{{ __('Symbol Postion') }}</label>
                                <select class="form-select" name="currency_symbol_postion">
                                    <option value="1">{{ __("Before") }}</option>
                                    <option value="2">{{ __("After") }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark b-r-10">
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="fs-16 fw-6 mb-4">{{ __("Payment gateways") }}</div>

        <div class="row">
            @if($payments)
            
                @foreach($payments as $value)

                    <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
                        <label class="card shadow-none border border-gray-300" for="payment_{{ $value['id'] }}">
                            <div class="card-body d-flex justify-content-between align-items-center px-3 gap-16">
                                <div class="d-flex align-items-center gap-8 fs-13 fw-5 text-truncate">
                                    <div class="size-30 d-flex align-items-center justify-content-between fs-20">
                                        <img src="{{ $value['logo'] }}" class="w-100">
                                    </div>
                                    <div>
                                        {{ $value['name'] }}
                                    </div>
                                </div>
                                <div class="d-flex gap-16">
                                    <a class="fw-5 fs-16 text-gray-900 actionItem" href="{{ module_url($value['uri']) }}" data-popup="{{ $value['modal'] }}" data-call-success="">
                                        <i class="fa-light fa-gear"></i>
                                    </a>
                                </div>
                            </div>
                        </label>
                    </div>

                @endforeach

            @endif
            
        </div>
    </div>
@endsection