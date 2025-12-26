@php
    $plan = $user->plan;
    $credit_summary = Credit::getCreditUsageSummary();
    $credit_summary = array_merge([
        'used' => 0,
        'limit' => 0,
        'is_unlimited' => false,
        'progress_value' => 0,
        'progress_label' => '0%',
        'quota_reached' => false,
        'message' => '',
    ], $credit_summary ?? []);

    $expired = false;
    if ($user->expiration_date && $user->expiration_date > 0) {
        $expired = $user->expiration_date < time();
    }
@endphp

<div>
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-clear btn-light rounded-circle dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
            <img src="{{ Media::url($user->avatar) }}" class="border-2 rounded-circle w-full h-full">
        </button>
        <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-100 max-w-220">
            <div class="mb-2 px-2">
                <div class="d-flex justify-content-start align-items-center gap-8 px-2">
                    <div class="d-flex flex-column gap-4 overflow-hidden">
                        <span class="text-gray-800 fw-6 text-truncate lh-1 mt-1">
                            {{ $user->fullname }}
                        </span>
                        <span class="fs-11 text-gray-600 text-truncate fw-4 lh-1">
                            {{ $user->email }}
                        </span>
                    </div>
                </div>
            </div>
            <div><hr class="dropdown-divider"></div>
            <div class="px-2">
                <a class="dropdown-item py-2 px-2 rounded d-flex gap-6" href="{{ url_app('profile') }}">
                    <span class="size-18 me-1 text-center"><i class="fa-light fa-user"></i></span>
                    <span>{{ __("Profile") }}</span>
                </a>
                <a class="dropdown-item py-2 px-2 rounded d-flex gap-6" href="{{ url_app('profile/plan') }}">
                    <span class="size-18 me-1 text-center"><i class="fa-light fa-box-open"></i></span>
                    <span>{{ __("Plan") }}</span>
                </a>
                <a class="dropdown-item py-2 px-2 rounded d-flex gap-6" href="{{ url_app('profile/billing') }}">
                    <span class="size-18 me-1 text-center"><i class="fa-light fa-ballot-check"></i></span>
                    <span>{{ __("Billing") }}</span>
                </a>
                <a class="dropdown-item py-2 px-2 rounded d-flex gap-6  d-none" href="{{ url_app('profile/settings') }}">
                    <span class="size-18 me-1 text-center"><i class="fa-light fa-gear"></i></span>
                    <span>{{ __("Settings") }}</span>
                </a>
            </div>
            <div><hr class="dropdown-divider"></div>
            <div class="px-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex flex-column gap-1 text-truncate-1">
                        <div class="fw-bold fs-13 text-truncate lh-1">{{ $plan->name ?? __('No Plan') }}</div>
                        <div class="fs-11 text-truncate lh-1
                            @if($user->expiration_date == -1)
                                text-success
                            @elseif($expired)
                                text-danger
                            @else
                                text-gray-600
                            @endif
                        ">
                            @if($user->expiration_date == -1)
                                {{ __("Unlimited") }}
                            @elseif($expired)
                                {{ __("Expired") }}
                            @elseif($user->expiration_date)
                                {{ date_show($user->expiration_date) }}
                            @else
                                {{ __('N/A') }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('app.profile', 'plan') }}" class="btn btn-primary btn-sm fs-12 w-100">{{ __("Upgrade") }}</a>
                    </div>
                </div>
            </div>
            <div class="mt-2 mb-2 border-top pt-2 px-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fs-11 text-gray-600">{{ __('Credits used') }}</span>
                    <span class="fs-11 fw-bold text-primary">
                        @if($credit_summary['is_unlimited'])
                            {{ __('Unlimited') }}
                        @elseif($credit_summary['quota_reached'])
                            100%
                        @else
                            {{ $credit_summary['progress_label'] }}
                        @endif
                    </span>
                </div>

                <div class="progress h-8" style="background:#eee;">
                    <div class="progress-bar
                        @if($credit_summary['is_unlimited'])
                            bg-success
                        @elseif($credit_summary['quota_reached'])
                            bg-danger
                        @else
                            bg-dark
                        @endif
                    "
                    style="width:
                        @if($credit_summary['is_unlimited'] || $credit_summary['quota_reached'])
                            100%
                        @else
                            {{ $credit_summary['progress_value'] }}%
                        @endif
                    "></div>
                </div>

                @if(!empty($credit_summary['message']))
                    <div class="fs-11 text-muted mt-1">{{ $credit_summary['message'] }}</div>
                @endif
            </div>
            <div><hr class="dropdown-divider"></div>
            <div class="px-3 py-2">
                <a class="btn btn-dark btn-sm w-100" href="{{ url('auth/logout') }}">
                    <i class="fa-light fa-right-from-bracket"></i> {{ __("Logout") }}
                </a>
            </div>
        </div>
    </div>
</div>