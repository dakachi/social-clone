@if(Access::permission('apppublishing'))

@php
    $channels = Channels::channels();
    $teamId = request()->team_id;
    $now = \Carbon\Carbon::now();
    $startDate = $now->copy()->subDays(30);
    $endDate = $now;

    $report = PublishingReport::postInfo($startDate, $endDate, $teamId ?? null);
    $reportStat = PublishingReport::postStatsGrowthInfo($startDate, $endDate, $teamId ?? null);
    $errorSuccessChart = PublishingReport::postStatsByDay($startDate, $endDate, $teamId ?? null);
    $errorSuccessSummary = $errorSuccessChart['summary'];
    $recentPosts = PublishingReport::recentPostsStatus(10, $teamId ?? null);

    $statusMap    = $reportStat['status_map'];
    $statusCounts = $reportStat['status_counts'];
    $statusGrowth = $reportStat['status_growth'];
    $totalPosts   = $reportStat['total_posts'];
    $totalGrowth  = $reportStat['total_growth'];
    $successTotal = $statusCounts[4] ?? 0;
    $failedTotal  = $statusCounts[5] ?? 0;

    $processingTotal = $report['status_counts'][3] ?? 0;
	$processingGrowth = $report['status_growth'][3] ?? 0;
	$processingLabel = $report['status_map'][3]['label'] ?? 'Processing';

    $successRate = ($successTotal + $failedTotal) > 0
        ? round($successTotal * 100 / ($successTotal + $failedTotal), 1)
        : 0;
    $quota = Publishing::checkQuota($teamId ?? null);
@endphp

<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">

    {{-- Quota --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 hp-100 min-h-350">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4 h-100">
                <div class="d-flex flex-column mb-3 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-12 size-50 bg-primary-100 border border-primary-200">
                        <i class="fa-light fa-gauge text-primary fs-22"></i>
                    </span>
                    <span class="fw-6 fs-20">{{ __('Post Quota') }}</span>
                </div>
                @if($quota['limit'] == -1)
                    <div class="fw-bold fs-2 mb-1 text-primary">{{ __('Unlimited') }}</div>
                    <div class="fs-15 text-muted">{{ $quota['message'] }}</div>
                @else
                    <div class="fw-bold fs-2 mb-1 text-dark">
                        {{ $quota['used'] }}/{{ $quota['limit'] }}
                        <span class="fs-12 text-muted">
                            ({{ __('left:') }} {{ $quota['left'] }})
                        </span>
                    </div>
                    <div class="w-100 mb-2">
                        <div class="progress h-7 bg-gray-200">
                            <div class="progress-bar
                                {{ $quota['left'] == 0 ? 'bg-danger' : ($quota['left'] <= 5 ? 'bg-warning' : 'bg-primary') }}"
                                role="progressbar"
                                style="width:{{ $quota['limit'] > 0 ? round($quota['used']/$quota['limit']*100) : 0 }}%;">
                            </div>
                        </div>
                    </div>
                    <div class="fs-15 {{ $quota['left'] == 0 ? 'text-danger' : ($quota['left'] < 5 ? 'text-warning' : 'text-muted') }}">
                        {{ $quota['message'] }}
                    </div>
                @endif
                <div class="mt-3 fw-6 text-dark fs-16">
                    {{ __('Total Posted:') }} <span class="fw-bold">{{ number_format($totalPosts) }}</span>
                </div>
                <div class="fs-14 text-muted">
                    {{ __('Compared to last :days days', ['days' => $startDate->diffInDays($endDate)]) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add Channels --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5">{{ __("Add Channels") }}</div>
            </div>
            <div class="card-body max-h-300 overflow-y-scroll">
                <div class="row">
                    @if( !empty( $channels ) )
                        @foreach( $channels as $channel )
                            <div class="col-md-6 mb-4">
                                <div class="card border-gray-300">
                                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-10">
                                        <div class="d-flex align-items-center justify-content-center size-50 text-white border-1 b-r-100 fs-16" style="background-color: {{ $channel['color'] }};">
                                            <i class="{{ $channel['icon'] }}"></i>
                                        </div>
                                        <div class="fs-14 fw-5">{{ __($channel['name']) }}</div>
                                        <div>
                                            @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                @foreach( $channel['items'] as $item )
                                                    <a href="{{ url($item["uri"]) }}" class="btn btn-outline btn-sm btn-light mb-1"><i class="fa-light fa-plus"></i> {{ __( ucfirst( str_replace("_", " ", $item["category"]) ) ) }}</a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Success --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-success-100 border border-success-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-success-500">
                        <i class="fa-light fa-circle-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ $statusMap[4]['label'] ?? __('Success') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($successTotal) }}</div>
                <div class="fs-14 text-muted">
                    {{ ($statusGrowth[4] ?? 0) == 0 ? '0%' : (($statusGrowth[4] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[4] ?? 0) . '%' }}
                </div>
            </div>
        </div>
    </div>
    {{-- Failed --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-danger-100 border border-danger-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-danger-500">
                        <i class="fa-light fa-circle-xmark text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ $statusMap[5]['label'] ?? __('Failed') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($failedTotal) }}</div>
                <div class="fs-14 text-muted">
                    {{ ($statusGrowth[5] ?? 0) == 0 ? '0%' : (($statusGrowth[5] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[5] ?? 0) . '%' }}
                </div>
            </div>
        </div>
    </div>
    {{-- Success Rate --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-primary-100 border border-primary-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-primary-500">
                        <i class="fa-light fa-badge-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ __('Success Rate') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-primary">{{ $successRate }}%</div>
                <div class="fs-14 text-muted">{{ __('of processed posts') }}</div>
            </div>
        </div>
    </div>

    {{-- Processing --}}
	<div class="col">
	    <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-teal-100 border border-teal-200">
	        <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
	            <div class="d-flex align-items-center mb-2 gap-12">
	                <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-teal-500">
	                    <i class="fa-light fa-arrows-rotate text-white fs-22"></i>
	                </span>
	                <span class="fw-6 fs-14 text-muted">{{ $processingLabel }}</span>
	            </div>
	            <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($processingTotal) }}</div>
	            <div class="fs-14 text-muted">
	                {{ $processingGrowth == 0 ? '0%' : ($processingGrowth > 0 ? '+' : '-') . abs($processingGrowth) . '%' }}
	            </div>
	        </div>
	    </div>
	</div>

    {{-- Area Chart: Success vs Failed over time --}}
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="fs-5 fs-16">{{ __('Successful vs Failed Posts Over Time') }}</h5>
            </div>
            <div class="card-body border-bottom">
                <div id="posts-error-success-chart" style="height: 350px;"></div>
            </div>
            <div class="d-flex card-body p-0">
                <div class="flex-fill px-4 py-3 border-end">
                    <div class="text-gray-500 fs-14 mb-2">{{ __('Success') }}</div>
                    <div class="text-gray-800 fs-25 fw-bold">{{ number_format($errorSuccessSummary['success_total']) }}</div>
                </div>
                <div class="flex-fill px-4 py-3 border-end">
                    <div class="text-gray-500 fs-14 mb-2">{{ __('Error') }}</div>
                    <div class="text-gray-800 fs-25 fw-bold">{{ number_format($errorSuccessSummary['fail_total']) }}</div>
                </div>
                <div class="flex-fill px-4 py-3">
                    <div class="text-gray-500 fs-14 mb-2">{{ __('Success Rate') }}</div>
                    <div class="text-gray-800 fs-25 fw-bold">
                        {{ $errorSuccessSummary['success_rate'] }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm px-0">
            <div class="card-header">
                <h5 class="fs-5 fs-16">{{ __('Recently Posted: Success & Failed') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="RecentPostsTable">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="w-60">{{ __('Thumbnail') }}</th>
                                <th>{{ __('Caption') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('View') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPosts as $post)
                                @php
                                    $data = is_string($post->data) ? json_decode($post->data, true) : $post->data;
                                    $result = is_string($post->result) ? json_decode($post->result, true) : $post->result;
                                    $url = $result['url'] ?? null;
                                    $caption = $data['caption'] ?? '-';
                                    $thumbnail = $data['medias'][0] ?? null;
                                    $networkIcon = match($post->social_network ?? '') {
                                        'facebook' => 'fa-brands fa-facebook text-primary',
                                        'instagram' => 'fa-brands fa-instagram text-danger',
                                        'tiktok' => 'fa-brands fa-tiktok text-dark',
                                        default => 'fa-regular fa-share-nodes text-gray-500',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-center w-60">
                                        @if (!empty($thumbnail))
                                            <img src="{{ Media::url($thumbnail) }}" class="rounded size-48" style="object-fit: cover;">
                                        @elseif($post->type == "link")
                                            <div class="d-flex align-items-center justify-content-center bg-light border rounded size-48">
                                                <i class="fa-light fa-link text-gray-600 fs-4"></i>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light border rounded size-48">
                                                <i class="fa-light fa-align-center text-gray-600 fs-4"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fs-14">{{ \Str::limit($caption, 80) }}</td>
                                    <td class="text-start">
                                        <div class="d-flex align-items-center gap-2 justify-content-start gap-10">
                                            @if(!empty($post->account->avatar))
                                                <img src="{{ Media::url($post->account->avatar) }}" class="rounded-circle size-22" style="object-fit:cover;">
                                            @endif
                                            <span class="fs-14">{{ $post->account->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($post->status == 4)
                                        <span class="badge badge-outline badge-sm badge-success">
                                            {{ __("Success") }}
                                        </span>
                                        @else
                                        <span class="badge badge-outline badge-sm badge-danger">
                                            {{ __("Failed") }}
                                        </span>
                                        @endif
                                        
                                    </td>
                                    <td class="text-nowrap text-gray-700 fs-14">
                                        {{ datetime_show($post->time_post) }}
                                    </td>
                                    <td class="text-center">
                                        @if (!empty($url))
                                            <a href="{{ $url }}" target="_blank" title="View">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ __('No posts found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
    var errorSuccessChart = {!! json_encode($errorSuccessChart) !!};
    errorSuccessChart.series[0].color = '#675dff';
    errorSuccessChart.series[1].color = '#f5222d';
    Main.Chart('areaspline', errorSuccessChart.series, 'posts-error-success-chart', {
        xAxis: {
            categories: errorSuccessChart.categories,
            title: { text: '' },
            crosshair: { width: 2, color: '#ddd', dashStyle: 'Solid' },
            labels: {
                rotation: 0,
                useHTML: true,
                formatter: function () {
                    const pos = this.pos;
                    const total = this.axis.categories.length;
                    if (pos === 0)
                        return `<div style="text-align:left;transform:translateX(60px);width:140px;">${this.value}</div>`;
                    else if (pos === total - 1)
                        return `<div style="text-align:right;transform:translateX(-55px);width:140px;">${this.value}</div>`;
                    return '';
                },
                style: {
                    fontSize: '13px',
                    whiteSpace: 'nowrap'
                },
                overflow: 'none',
                crop: false
            }
        },
        yAxis: {
            title: { text: '' },
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash',
            gridLineWidth: 1
        },
        title: { text: '{{ __("Page Views") }}' },
        legend: { enabled: false },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.1,
                lineWidth: 3,
                marker: { enabled: false }
            },
            series: {
                color: '#675dff',
                fillColor: {
                    linearGradient: [0, 0, 0, 200],
                    stops: [
                        [0, 'rgba(103, 93, 255, 0.4)'],
                        [1, 'rgba(255, 255, 255, 0)']
                    ]
                }
            }
        }
    });
</script>
@endif