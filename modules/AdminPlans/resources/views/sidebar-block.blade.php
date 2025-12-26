<div class="menu-item pt-2 border-bottom-1"></div>
<div class="menu-item mb-2 px-0">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="px-3 py-1 fs-12 border-bottom-1 bg-gray-100">
            {{ __("Your Plan") }}
        </div>
        <div class="card-body d-flex gap-3 py-2 px-3">
            <div class="flex-grow-1">
                <div class="fw-bold fs-13 text-primary mb-1">
                    {{ $user->plan->name ?? __('No Plan') }} <i class="fa-solid fa-crown text-warning"></i>
                </div>
                @php
                    $expired = false;
                    if ($user->expiration_date && $user->expiration_date > 0) {
                        $expired = $user->expiration_date < time();
                    }
                    $summary = Credit::getCreditUsageSummary();
                    $isUnlimited = $summary['is_unlimited'] ?? false;
                    $quotaReached = $summary['quota_reached'] ?? false;
                    $progressValue = $isUnlimited || $quotaReached ? 100 : ($summary['progress_value'] ?? 0);
                    $progressBarClass = $isUnlimited
                        ? 'bg-success'
                        : ($quotaReached ? 'bg-danger' : 'bg-primary');
                @endphp
                <div class="d-flex fs-12">
                    <div class="mb-2">
                        {{ __('Expire:') }}
                        <b class="{{ $expired ? 'text-danger' : 'text-muted' }}">
                            @if($user->expiration_date == -1)
                                {{ __('Unlimited') }}
                            @elseif($user->expiration_date)
                                {{ date_show($user->expiration_date) }}
                            @else
                                {{ __('N/A') }}
                            @endif
                        </b>
                        @if($expired && $user->expiration_date > 0)
                            <span class="badge badge-xs badge-outline b-r-10 badge-danger ms-2">{{ __('Expired') }}</span>
                        @elseif($user->expiration_date == -1)
                            <span class="badge badge-xs badge-outline b-r-10 badge-success ms-2">{{ __('Active') }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between fs-12 mb-1 mt-1">
                    <span>{{ __('Credits used') }}</span>
                    <span>
                        <b>{{ number_format($summary['used']) }}</b>
                        @if($isUnlimited)
                            / <span class="text-success">{{ __('Unlimited') }}</span>
                        @else
                            / {{ number_format($summary['limit']) }}
                        @endif
                    </span>
                </div>
                <div class="progress wp-100 h-8 mb-2" style="background: #eee">
                    <div class="progress-bar {{ $progressBarClass }}"
                        style="width: {{ $progressValue }}%;">
                    </div>
                </div>
                <a href="{{ route('app.profile', 'plan') }}" class="btn btn-sm btn-dark wp-100 mt-2">
                    {{ __('Upgrade / Details') }}
                </a>
            </div>
        </div>
    </div>
</div>