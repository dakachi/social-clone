@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Payment Manual Configurations') }}"
        description="{{ __('Set up manual payment options and instructions easily') }}"
    >
    </x-sub-header>
@endsection

@section('content')


<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Manual Payment Settings") }}
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="fw-5 fs-14 mb-4">{{ __("Status") }}
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column mb-4">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="payment_manual_status" value="1" id="payment_manual_status_1" {{ get_option("payment_manual_status", 0)==1?"checked":"" }}>
                                <label class="form-check-label mt-1" for="payment_manual_status_1">
                                    {{ __('Enable') }}
                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="payment_manual_status" value="0" id="payment_manual_status_0"{{ get_option("payment_manual_status", 0)==0?"checked":"" }}>
                                <label class="form-check-label mt-1" for="payment_manual_status_0">
                                    {{ __('Disable') }}
                                </label>
                            </div>
                        </div>
            </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="payment_manual_prefix" class="fw-5 fs-14 mb-2">{{ __('Prefix') }}</label>
                            <input class="form-control" name="payment_manual_prefix" id="payment_manual_prefix" type="text"
                            value="{{ get_option('payment_manual_prefix', 'PAY-') }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="fw-5 fs-14 mb-2">{{ __('Manual Payment Information') }}</label>
                            <textarea class="textarea_editor border-gray-300 border-1 min-h-100"
                                name="payment_manual_info"
                                placeholder="{{ __('Please enter your manual payment details, including Account Name, Account Number, Branch, SWIFT/BIC Code, Reference Information, and Payment Amount. This ensures that your clients can complete their transactions accurately.') }}">
                                {{ get_option('payment_manual_info', 'Bank Info') }}
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>
        </div>



    </form>
</div>

@endsection
