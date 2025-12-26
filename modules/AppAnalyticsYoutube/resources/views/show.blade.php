@extends('layouts.app')

@section('content')
@php
    $module = Module::find($account->module);
    $moduleInfo = $module->get('menu');
@endphp
<div class="border-bottom mb-5 pt-5 pb-2 bg-polygon">
    <div class="container">
        <div class="d-flex justify-content-center text-center">
            <div class="mb-5">
                <div class="size-80 size-chill position-relative mx-auto mb-3">
                    <img class="rounded-circle border mb-3 wp-100 hp-100" src="{{ Media::url($account->avatar) }}">
                    <div class="fs-12 position-absolute b-0 r-0 size-22 bg-white border b-r-100"><i class="{{ $moduleInfo['icon'] }}" style="color: {{ $moduleInfo['color'] }};"></i></div>
                </div>
                <div class="flex-fill mb-3">
                    <h4 class="mb-1 fs-20 fw-bold">{{ $account->name }}</h4>
                    <div class="text-muted small mb-1">{{ $analytics['account']['pid'] ?? '' }}</div>
                    <a href="{{ $account->url }}" class="small text-gray-600" target="_blank">{{ $account->url }}</a>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-16">
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['subscribers'] ?? 0) }} {{ __('Subscribers') }}</div>
                    <div class="px-1 text-gray-500">|</div>
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['video_count'] ?? 0) }} {{ __('Videos') }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container py-4">

    <form class="auto-submit" action="{{ url()->current(); }}" method="GET">
        <div class="d-flex justify-content-end gap-8">
            <a  href="{{ route('analytics.export.pdf', [ 'social' => request()->segment(3), 'id_secure' => request()->segment(4) ]) }}" class="btn btn-dark exportPDF">{{ __("Export PDF") }}</a>
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div></div>
                <div class="d-flex align-items-center justify-content-between gap-8">
                    <div>
                        <div class="daterange d-none bg-white b-r-4 fs-12 border-gray-300 border" data-open="left"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @php
            $overview = $analytics['overview'];
            $icons = [
                'likes' => 'fa-light fa-thumbs-up', 
                'subscribers' => 'fa-light fa-users', 
                'views' => 'fa-light fa-eye',
                'shares' => 'fa-light fa-share-from-square', 
                'comments' => 'fa-light fa-comment',
                'page_views' => 'fa-light fa-binoculars', 
                'published_videos' => 'fa-light fa-play',
            ];
            $colors = [
                'likes' => 'primary', 
                'subscribers' => 'info', 
                'views' => 'success',
                'shares' => 'warning', 
                'comments' => 'danger',
                'page_views' => 'dark', 
                'published_videos' => 'pink',
            ];
        @endphp

        @foreach ($overview as $key => $item)
            @php
                $icon = $icons[$key] ?? '📊';
                $color = $colors[$key] ?? 'muted';
                $change = $item['change'] ?? 0;
                $value = $item['value'] ?? 0;
                $changeClass = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                $changeLabel = $change > 0 ? '+' . $change . '%' : ($change < 0 ? $change . '%' : '0%');
                
                $direction = $item['direction'] ?? ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'));
                $isNegative = $item['is_negative'] ?? ($value < 0);

                if($isNegative){
                    $changeClass = "text-danger";
                    $changeLabel = $change > 0 ? '-' . $change . '%' : ($change < 0 ? $change . '%' : '0%');
                }

                $specialCase = $isNegative && $direction === 'up';
                $specialNote = $specialCase ? __('Less loss than previous period') : '';
            @endphp
            <div class="col-6 col-md-4 col-lg-4">
                <div class="card hp-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-8">
                            <div class="size-35 d-flex align-items-center justify-content-center b-r-100 bg-{{ $color }}-100 text-{{ $color }}">
                                <span>
                                    @if(str_starts_with($icon, 'fa-'))
                                        <i class="{{ $icon }}"></i>
                                    @else
                                        {{ $icon }}
                                    @endif
                                </span>
                            </div>
                            <span class="text-gray-700">{{ __(ucwords(str_replace('_', ' ', $key))) }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-center pt-3">
                            <div class="text-muted small">
                                @if ($specialNote)
                                    <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="{{ __('You are losing fewer subscribers than before.') }}">
                                        {{ $specialNote }}
                                    </span>
                                @endif
                            </div>
                            <div class="fw-bold fs-30 mb-3 {{ $isNegative ? 'text-danger' : '' }}">
                                {{ number_format($value) }}
                            </div>
                            <div class="small {{ $changeClass }}">
                                @if($direction === 'up')
                                    <i class="fa-light fa-arrow-trend-up"></i>
                                @elseif($direction === 'down')
                                    <i class="fa-light fa-arrow-trend-down"></i>
                                @else
                                    <i class="fa-light fa-minus"></i>
                                @endif
                                {{ $changeLabel }} {{ __('vs last period') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @php $summary = $analytics['dailyViewsChartData']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="dailyViewsChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total views') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views By Location') }}</h5>
                </div>
                <div class="card-body">
                    <div id="viewsLocationMapChart" class="export-chart" style="height: 510px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Top Countries') }}</h5>
                </div>
                <div class="card-body p-0 min-h-550">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="px-4">{{ __('Country') }}</th>
                                <th class="px-4 text-end">{{ __('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($analytics['topCountriesByViews'])
                                @foreach ($analytics['topCountriesByViews'] as $row)
                                    <tr>
                                        <td class="px-4">
                                            <span class="flag-icon flag-icon-{{ strtolower($row['code']) }} me-2"></span>
                                            {{ $row['country'] }}
                                        </td>
                                        <td class="px-4 text-end fw-bold">{{ number_format($row['views']) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-5 border-0" colspan="2">
                                        <div class="empty"></div>
                                    </td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views by Traffic Source') }}</h5>
                </div>
                <div class="card-body">
                    <div id="viewsByTrafficSourceChart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views by Device') }}</h5>
                </div>
                <div class="card-body">
                    <div id="viewsByDeviceChart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>


        @php $summary = $analytics['averageViewDurationChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Avg Watch Duration (seconds)') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="averageViewDurationChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Avg Watch Duration per Video') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['average']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['estimatedMinutesWatchedChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Estimated Minutes Watched') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="estimatedMinutesWatchedChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Avg Minutes Watched per Day') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['average']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['dailyEngagementChartData']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Engagement') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="dailyEngagementChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Engagement') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['engagement']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Likes') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['likes']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Comments') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['comments']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Shares') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['shares']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['dailySubscribersChartData']['summary']; @endphp
        <div class="col-12">
            <div class="row hp-100 g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="fs-5 fs-16">{{ __('Gained & Lost Subscribers') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="dailySubscribersChart" class="export-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 hp-100 min-h-398 max-h-398">
                    <div class="d-flex flex-column hp-100 gap-18">
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['gained']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Gained Subscribers') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-success-100 text-success fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['lost']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Lost Subscribers') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-danger-100 text-danger fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-minus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['net']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Net Subscribers') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-primary-100 text-primary fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Viewer by Age Group') }}</h5>
                </div>
                <div class="card-body">
                    <div id="viewerByAgeChart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Viewer by Gender') }}</h5>
                </div>
                <div class="card-body">
                    <div id="viewerByGenderChart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        @php $posts = $analytics['videoHistoryList']; @endphp
        <div class="card mt-5 px-0">
            <div class="card-header">
                <h5 class="fs-5 fs-16">{{ __('Post History') }}</h5>

                <div class="card-tooltip">
                    <div class="d-flex flex-wrap gap-8">
                        <div class="d-flex">
                            <div class="form-control form-control-sm">
                                <button class="btn btn-icon">
                                    <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                                </button>
                                <input name="search" placeholder="{{ __('Search...') }}" type="text"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="DataTable_Static">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 60px;">{{ __('Image') }}</th>
                                <th>{{ __('Message') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Likes') }}</th>
                                <th>{{ __('Shares') }}</th>
                                <th>{{ __('Comments') }}</th>
                                <th>{{ __('View') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                                <tr>
                                    {{-- Image or icon --}}
                                    <td class="text-center">
                                        @if (!empty($post['full_picture']))
                                            <img src="{{ Media::url($post['full_picture']) }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light border rounded" style="width: 48px; height: 48px;">
                                                <i class="fa-light fa-message-lines text-gray-600 fs-4"></i>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Message --}}
                                    <td>{{ Str::limit($post['message'], 80) }}</td>

                                    {{-- Date --}}
                                    <td class="text-nowrap text-gray-700 fs-14">{{ \Carbon\Carbon::parse($post['created_time'])->format('M d, Y') }}</td>

                                    {{-- Metrics --}}
                                    <td class="text-center text-success">{{ number_format($post['metrics']['likes'] ?? 0) }}</td>
                                    <td class="text-center text-warning">{{ number_format($post['metrics']['shares'] ?? 0) }}</td>
                                    <td class="text-center text-dark">{{ number_format($post['metrics']['comments'] ?? 0) }}</td>

                                    {{-- View button --}}
                                    <td class="text-center">
                                        @if (!empty($post['permalink_url']))
                                            <a href="{{ $post['permalink_url'] }}" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
        

    </div>
</div>


@endsection

@section('script')
<script type="text/javascript">

var dailySubscribersChartData = {!! json_encode($analytics['dailySubscribersChartData']) !!};
dailySubscribersChartData.series[0].color = '#675dff';
dailySubscribersChartData.series[1].color = '#f5222d';
dailySubscribersChartData.series[2].color = '#ffa940';
Main.Chart("mix", dailySubscribersChartData.series, 'dailySubscribersChart', {
    chart: { zoomType: 'xy' },
    title: { text: '{{ __("Gained & Lost Subscribers") }}' },
    xAxis: {
        categories: dailySubscribersChartData.categories,
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
    yAxis: [
        {
            title: { text: '' },
            labels: { format: '{value}' }
        },
        {
            title: { text: '' },
            labels: { format: '{value}%' },
            opposite: true
        }
    ],
    tooltip: {
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: { dataLabels: { enabled: false } },
        line: {
            lineWidth: 2,
            marker: { enabled: true }
        }
    }
});

var dailyEngagementChartData = {!! json_encode($analytics['dailyEngagementChartData']) !!};
dailyEngagementChartData.series[0].color = '#675dff';
dailyEngagementChartData.series[1].color = '#13c2c2';
dailyEngagementChartData.series[2].color = '#ffa940';
Main.Chart('areaspline', dailyEngagementChartData.series, 'dailyEngagementChart', {
    xAxis: {
        categories: dailyEngagementChartData.categories,
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
    title: { text: '{{ __("Engagement") }}' },
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

var dailyViewsChartData = {!! json_encode($analytics['dailyViewsChartData']) !!};
Main.Chart('areaspline', dailyViewsChartData.series, 'dailyViewsChart', {
    xAxis: {
        categories: dailyViewsChartData.categories,
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
    title: { text: '{{ __("Views") }}' },
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

const viewsLocationMapChartData = {!! json_encode($analytics['viewsLocationMapChartData']) !!};
Main.Chart('map', viewsLocationMapChartData, 'viewsLocationMapChart', {
    chart: { map: 'custom/world' },
    title: { text: '{{ __('Views By Location') }}' },
    colorAxis: { min: 0,  minColor: '#ede9fe', maxColor: '#675dff' },
    tooltip: {
        formatter: function () {
            const label = this.point.name || this.key || '{{ __('Unknown') }}';
            const value = this.point.value?.toLocaleString?.() ?? '0';

            return `<div class="d-flex gap-8 justify-content-between align-items-center" style="padding: 4px 12px;">
                <span class="fs-12"><span style="color: ${this.point.color};">●</span> ${label}:</span>
                <span class="fs-12 fw-6">${value}</span>
            </div>`;
        }
    }
});

var viewsByDeviceChartData = {!! json_encode($analytics['viewsByDeviceChartData']) !!};
Main.Chart('column', viewsByDeviceChartData.series, 'viewsByDeviceChart', {
    title: { text: '{{ __("Views by Device") }}' },
    xAxis: {
        categories: viewsByDeviceChartData.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('views') }}'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    }
});

var viewsByTrafficSourceChartData = {!! json_encode($analytics['viewsByTrafficSourceChartData']) !!};
Main.Chart('column', viewsByTrafficSourceChartData.series, 'viewsByTrafficSourceChart', {
    title: { text: '{{ __("Views by Traffic Source") }}' },
    xAxis: {
        categories: viewsByTrafficSourceChartData.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('views') }}'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    }
});

var averageViewDurationChartData = {!! json_encode($analytics['averageViewDurationChartData']) !!};
Main.Chart('areaspline', averageViewDurationChartData.series, 'averageViewDurationChart', {
    xAxis: {
        categories: averageViewDurationChartData.categories,
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
    title: { text: '{{ __("Avg Watch Duration (seconds)") }}' },
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

var estimatedMinutesWatchedChartData = {!! json_encode($analytics['estimatedMinutesWatchedChartData']) !!};
Main.Chart('areaspline', estimatedMinutesWatchedChartData.series, 'estimatedMinutesWatchedChart', {
    xAxis: {
        categories: estimatedMinutesWatchedChartData.categories,
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
    title: { text: '{{ __("Estimated Minutes Watched") }}' },
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

var viewerByAgeChartData = {!! json_encode($analytics['viewerByAgeChartData']) !!};
Main.Chart('column', viewerByAgeChartData.series, 'viewerByAgeChart', {
    title: { text: '{{ __("Viewer by Age Group") }}' },
    xAxis: {
        categories: viewerByAgeChartData.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('age') }}'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    }
});

Main.Chart('pie', {!! json_encode($analytics['viewerByGenderChartData']) !!}, 'viewerByGenderChart', {
    title: { text: '{{ __("Viewer by Gender") }}' },
    legend: { enabled: true },
    plotOptions: { pie: { showInLegend: true } }
});


</script>
@endsection