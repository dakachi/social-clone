@php
    $coupon_plans = $coupon ? json_decode($coupon->plans) : [];
@endphp 

<div class="card border-gray-300 b-r-6 mb-4">
            
    <div class="card-body">
        
        <div class="d-flex justify-content-between mb-4">
            
            <div class="">
                <div class="fw-5 fs-18">{{ __("Coupon") }}</div>
                <div class="fs-12 text-gray-700">{{ __("Enter coupon code and secure exclusive savings today.") }}</div>
            </div>
        </div>

        <form class="actionForm" action="{{ route("app.coupons.apply") }}">
            <div class="mb-0">
                <div class="input-group">
                    <div class="form-control">
                        <i class="fa-light fa-ticket"></i>
                        <input placeholder="{{ __("Enter coupon") }}" name="code" type="text" value="{{ $coupon ? $coupon->code : '' }}">
                    </div>
                    <button type="submit" class="btn btn-input">
                        {{ __("Apply") }}
                    </button>
                </div>
                @if($coupon)
                <span class="fs-12 text-danger">
                    @if(!in_array($plan->id, $coupon_plans))
                        {{ __("This coupon does not apply to this plan.") }}
                    @elseif($coupon->start_date > time())
                        {{ sprintf( __("The coupon becomes active on %s."), datetime_show( $coupon->start_date )) }}
                    @elseif($coupon->end_date < time())
                        {{ __("The coupon you entered has expired. Please try another one or contact support for help.") }}
                    @elseif($coupon->usage_limit <= $coupon->usage_count)
                        {{ __("This coupon has reached its usage limit and can no longer be used.") }}
                    @endif
                </span>
                @endif
            </div>
        </form>

    </div>

</div>