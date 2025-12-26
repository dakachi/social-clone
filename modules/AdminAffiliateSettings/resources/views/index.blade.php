@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Affiliate Settings') }}" 
        description="{{ __('Configure affiliate commissions, tracking, and payment options easily') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Affiliate Minimum Withdrawal') }}</label>
                            <input placeholder="{{ __('Enter Minimum Withdrawal Amount') }}" class="form-control" name="affiliate_minimum_withdrawal" id="affiliate_minimum_withdrawal" type="text" value="{{ get_option("affiliate_minimum_withdrawal", 50) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Affiliate Commission Percentage (%)') }}</label>
                            <input placeholder="{{ __('Enter Affiliate Commission Percentage (%)') }}" class="form-control" name="affiliate_commission_percentage" id="affiliate_commission_percentage" type="text" value="{{ get_option("affiliate_commission_percentage", 15) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('The types of payments accepted for withdrawal') }}</label>
                            <input placeholder="{{ __('Enter types of payments') }}" class="form-control" name="affiliate_types_of_payments" id="affiliate_types_of_payments" type="text" value="{{ get_option("affiliate_types_of_payments", "") }}">
                        </div>
                    </div>                    
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><strong>{{ __('Onetime Commission') }}</strong><i class="fa fa-info-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('If you enable this feature, the affiliate will receive a commission only once. If you disable this feature, the affiliate will receive a recurring commission for each purchase.') }}"></i></label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="affiliate_onetime_commission_status" value="1" id="affiliate_onetime_commission_status_1" {{ get_option("affiliate_onetime_commission_status", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="affiliate_onetime_commission_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="affiliate_onetime_commission_status" value="0" id="affiliate_onetime_commission_status_0"{{ get_option("affiliate_onetime_commission_status", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="affiliate_onetime_commission_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
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
    </form>

</div>

@endsection
