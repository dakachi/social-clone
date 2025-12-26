{{-- resources/views/admin/apppublishing/dashboard.blade.php --}}
@php
    use Carbon\Carbon;

    $now = Carbon::now();
    $startDate = $now->copy()->subDays(30);
    $endDate = $now;

    // Dữ liệu toàn hệ thống (admin)
    $report        = PublishingReport::postInfo($startDate, $endDate);
    $reportStat    = PublishingReport::postStatsGrowthInfo($startDate, $endDate);
    $errorSuccessChart = PublishingReport::postStatsByDay($startDate, $endDate);
    $errorSuccessSummary = $errorSuccessChart['summary'];
    $recentPosts   = PublishingReport::recentPostsStatus(10);

    $postsByTeamChart     = PublishingReport::postsByTeamForChart($startDate, $endDate);
    $postsBySocialChart   = PublishingReport::postsBySocialForChart($startDate, $endDate);
    $postsByStatusChart   = PublishingReport::postsByStatusForChart($startDate, $endDate);

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
@endphp

<div class="fw-bold fs-20 pb-4 pt-5">{{ __("Post Stats") }}</div>

<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">

    <div class="col-md-6">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-pink-100 border border-pink-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-pink-500">
                        <i class="fa-light fa-rectangle-list text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ __('Total') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($totalPosts) }}</div>
                <div class="fs-14 text-muted">
                    {{ $totalGrowth == 0 ? '0%' : ($totalGrowth > 0 ? '+' : '-') . abs($totalGrowth) . '%' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Success Rate --}}
    <div class="col-md-6">
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

    {{-- Success --}}
    <div class="col-md-4">
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
    <div class="col-md-4">
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

    {{-- Processing --}}
    <div class="col-md-4">
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

</div>

<div class="row mt-4">

    {{-- Chart: Success vs Failed Over Time --}}
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="fw-5 fs-16">{{ __('Successful vs Failed Posts Over Time') }}</h5>
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
                    <div class="text-gray-500 fs-14 mb-2">{{ __('Failed') }}</div>
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

    {{-- Chart: Posts by Team --}}
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5">
                    {{ __('Posts by Team') }}
                </div>
            </div>
            <div class="card-body"><div id="posts-by-team-chart" style="height:400px"></div></div>
        </div>
    </div>

    {{-- Chart: Posts by Social Network --}}
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5">
                    {{ __('Posts by Social Network') }}
                </div>
            </div>
            <div class="card-body"><div id="posts-by-social-chart" style="height:400px"></div></div>
        </div>
    </div>

    {{-- Chart: Posts by Status --}}
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5">
                    {{ __('Posts by Status') }}
                </div>
            </div>
            <div class="card-body"><div id="posts-by-status-chart" style="height:400px"></div></div>
        </div>
    </div>

    {{-- Recent Posts --}}
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
    // Chart: Success vs Failed Over Time
    var errorSuccessChart = {!! json_encode($errorSuccessChart) !!};
    errorSuccessChart.series[0].color = '#675dff';
    errorSuccessChart.series[1].color = '#f5222d';
    Main.Chart('areaspline', errorSuccessChart.series, 'posts-error-success-chart', {
        title: '{{ __('Daily AI Credit Usage') }}',
        legend: {
            enabled: false
        },
        xAxis: {
            categories: errorSuccessChart.categories,
            title: { text: ' ' },
            crosshair: {
                width: 2,
                color: '#ddd',
                dashStyle: 'Solid'
            },
            labels: {
                rotation: 0,
                useHTML: true,
                formatter: function () {
                    const pos = this.pos;
                    const total = this.axis.categories.length;

                    if (pos === 0) {
                        return `<div style="text-align: left; transform: translateX(60px); width: 140px;">${this.value}</div>`;
                    } else if (pos === total - 1) {
                        return `<div style="text-align: right; transform: translateX(-55px); width: 140px;">${this.value}</div>`;
                    }
                    return '';
                },
                style: {
                    fontSize: '13px',
                    whiteSpace: 'nowrap',
                },
                overflow: 'none',
                crop: false,
            },
        },
        yAxis: {
            title: { text: ' ' },
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash',
            gridLineWidth: 1
        },
        tooltip: {
            shared: true,
            valueSuffix: ' credits',
            backgroundColor: '#fff',
            borderColor: '#ddd',
            borderRadius: 8,
            shadow: true
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.1,
                lineWidth: 3,
                marker: {
                    enabled: false
                }
            },
            series: {
                stacking: 'normal',
                marker: {
                    enabled: false,
                    states: {
                        hover: {
                            enabled: false
                        }
                    }
                },
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

    // Chart: Posts by Team
    Main.Chart('column', {!! json_encode($postsByTeamChart['series']) !!}, 'posts-by-team-chart', {
        title: '{{ __("Posts by Team") }}',
        xAxis: { categories: {!! json_encode($postsByTeamChart['categories']) !!} }
    });

    // Chart: Posts by Social Network
    Main.Chart('pie', {!! json_encode($postsBySocialChart['series'][0]['data']) !!}, 'posts-by-social-chart', {
        title: '{{ __("Posts by Social Network") }}'
    });

    // Chart: Posts by Status
    Main.Chart('column', {!! json_encode($postsByStatusChart['series']) !!}, 'posts-by-status-chart', {
        title: '{{ __("Posts by Status") }}',
        xAxis: { categories: {!! json_encode($postsByStatusChart['categories']) !!} }
    });
</script>