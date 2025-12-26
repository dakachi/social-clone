@php
    $creditUsageSummary = Credit::getcreditUsageSummary();
    $planName = $user->plan->name ?? __("No Plan");
    $isFreePlan = $planName === __("No Plan");
    $isUnlimited = ($user->expiration_date == -1);

    // Xử lý hiển thị ngày hết hạn
    if ($isUnlimited) {
        $expiresAt = __('Unlimited');
        $expireClass = 'text-success';
        $expireIcon = '<i class="fa-light fa-infinity me-1"></i>';
    } elseif ($user->expiration_date && $user->expiration_date < time()) {
        $expiresAt = __('Expired');
        $expireClass = 'text-danger fw-bold';
        $expireIcon = '<i class="fa-light fa-calendar-xmark me-1"></i>';
    } elseif ($user->expiration_date) {
        $expiresAt = date('j M Y', $user->expiration_date);
        $expireClass = 'text-muted';
        $expireIcon = '<i class="fa-light fa-calendar-clock me-1"></i>';
    } else {
        $expiresAt = __('N/A');
        $expireClass = 'text-muted';
        $expireIcon = '<i class="fa-light fa-calendar me-1"></i>';
    }

    function display_limit($value) {
        return $value == -1 ? __('Unlimited') : number_format($value);
    }
@endphp

<div class="row">

    <div class="col-md-8 mb-4">
        <div class="card shadow-sm position-relative overflow-hidden hp-100">
            <div class="card-body py-4 px-5">
                <div class="d-flex flex-wrap align-items-center gap-4 justify-content-between hp-100">
                    <div class="d-flex flex-column flex-grow-1 hp-100 justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-2 text-primary-700 fs-13">
                                <i class="fa-light fa-clock me-2"></i>
                                <span>{{ now()->format('j M Y') }}</span>
                            </div>
                            <div class="fw-bold fs-3 mb-1 text-primary-700">
                                {{ __('Welcome, :name', ['name' => $user->fullname ?? __('No User')]) }}
                            </div>
                            <div class="fw-5 text-gray-700 fs-15 mb-3">
                                {{ __("Here's an overview of your recent activity and content.") }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-8 mt-auto flex-wrap">
                            <span class="badge rounded-pill bg-white border border-primary-200 text-primary-700 px-3 py-1 fw-6 fs-13 shadow-sm">
                                <i class="fa-light fa-star me-1"></i>
                                {{ $planName }}
                            </span>
                            <span class="badge rounded-pill bg-white border px-3 py-1 fw-5 fs-13 shadow-sm {{ $expireClass }}">
                                {!! $expireIcon !!} 
                                {{ __('Expires:') }} {{ $expiresAt }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-md-4 d-flex align-items-end hp-100">
                        {{-- Add any extra content here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
	    $credit = $creditUsageSummary ?? [];
	    $limit = $credit['limit'] ?? 0;
	    $used = $credit['used'] ?? 0;
	    $remaining = $credit['remaining'] ?? 0;
	    $isUnlimitedCredit = isset($limit) && $limit == -1;
	    $quotaReached = !empty($credit['quota_reached']);
	    $percent = !$isUnlimitedCredit && $limit > 0
	        ? min(100, round($used / $limit * 100, 1))
	        : 100;

	    $percentLabel = $isUnlimitedCredit
	        ? __('Unlimited')
	        : ($quotaReached ? '100%' : ($percent . '%'));

	    if ($isUnlimitedCredit) {
	        $progressBarClass = 'bg-success';
	    } elseif ($quotaReached) {
	        $progressBarClass = 'bg-danger';
	    } elseif ($percent < 60) {
	        $progressBarClass = 'bg-success';
	    } elseif ($percent < 85) {
	        $progressBarClass = 'bg-warning';
	    } else {
	        $progressBarClass = 'bg-danger';
	    }
	@endphp

	<div class="col-md-4 mb-4">
	    <div class="card shadow-sm p-4 hp-100 d-flex flex-column">
	        <div class="d-flex align-items-center mb-3 gap-12">
	            <span class="d-inline-flex align-items-center justify-content-center fs-28 b-r-12 size-50 bg-warning-100 border border-warning-200 text-warning">
	                <i class="fa-light fa-gem"></i>
	            </span>
	            <div>
	                <div class="text-muted fs-14">{{ __('Your Credits') }}</div>
	                <div class="fs-24 fw-bold text-black">
	                    {{ $isUnlimitedCredit ? __('Unlimited') : number_format($limit) }}
	                </div>
	            </div>
	        </div>
	        <div class="flex-fill d-flex flex-column gap-3">
	            <div class="d-flex justify-content-between align-items-center">
	                <span class="text-muted fs-14">{{ __('Used') }}</span>
	                <span class="fw-bold text-danger fs-14">{{ number_format($used) }}</span>
	            </div>
	            <div class="d-flex justify-content-between align-items-center">
	                <span class="text-muted fs-14">{{ __('Remaining') }}</span>
	                <span class="fw-bold text-success fs-14">
	                    {{ $isUnlimitedCredit ? __('Unlimited') : number_format($remaining) }}
	                </span>
	            </div>
	            <div class="my-2">
	                <div class="progress h-14 b-r-10 bg-gray-200">
	                    <div class="progress-bar {{ $progressBarClass }}"
	                         role="progressbar min-w-8 b-r-10"
	                         style="width: {{ $percent }}%;"
	                         aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
	                    </div>
	                </div>
	                <div class="d-flex justify-content-between small mt-1 px-1 fs-12">
	                    <span class="text-muted">{{ $percentLabel }}</span>
	                    <span class="text-muted">
	                        {{ $isUnlimitedCredit ? __('No limit') : __(':left left', ['left' => number_format($remaining)]) }}
	                    </span>
	                </div>
	            </div>
	            @if($quotaReached)
	                <div class="alert alert-danger py-1 px-2 mt-2 small mb-0 d-flex align-items-center">
	                    <i class="fa fa-triangle-exclamation me-1"></i>
	                    <span>{{ $credit['message'] }}</span>
	                </div>
	            @endif
	        </div>
	    </div>
	</div>

</div>
