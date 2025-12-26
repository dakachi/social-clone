<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('YouTube Analytics Report') }}</title>
    <style>
        @font-face {
            font-family: 'NotoSans';
            src: url("{{ base_path('resources/fonts/NotoSans-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'NotoSans';
            src: url("{{ base_path('resources/fonts/NotoSans-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'NotoSans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #675dff;
            padding-bottom: 6px;
        }

        h3 {
            font-size: 15px;
            margin-bottom: 10px;
            margin-top: 30px;
            color: #444;
        }

        .section {
            margin-bottom: 32px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .table th, .table td {
            border: 1px solid #ccc;
            padding: 7px 10px;
            text-align: left;
        }

        .table th {
            background-color: #f1f1f1;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        img.chart {
            display: block;
            max-width: 100%;
            margin: 12px auto 25px;
        }

        .text-muted {
            color: #777;
        }

        .bold {
            font-weight: bold;
        }

        .chart-title {
            text-align: center;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <h1>{{ __('YouTube Analytics Report') }}</h1>

    <div class="section">
        <strong>{{ __('Channel Name') }}:</strong> {{ $analytics['account']['name'] ?? '-' }}<br>
        <strong>{{ __('Subscribers') }}:</strong> <span class="bold">{{ number_format($analytics['account']['subscribers'] ?? 0) }}</span><br>
        <strong>{{ __('Views') }}:</strong> <span class="bold">{{ number_format($analytics['account']['views'] ?? 0) }}</span><br>
        <strong>{{ __('Video Count') }}:</strong> {{ number_format($analytics['account']['video_count'] ?? 0) }}<br>
        @if (!empty($startDate) && !empty($endDate))
        <br>
        <strong>{{ __('From') }}:</strong> {{ $startDate }}<br>
        <strong>{{ __('To') }}:</strong> {{ $endDate }}
        @endif
    </div>

    @if (!empty($charts) && is_array($charts))
        <div class="section">
            <h3>{{ __('Charts') }}</h3>
            @foreach ($charts as $chart)
                @if(isset($chart['base64']))
                    <div class="chart-title">{{ $chart['title'] ?? '' }}</div>
                    <img class="chart" src="{{ $chart['base64'] }}" alt="Chart">
                @endif
            @endforeach
        </div>
    @endif

    <div class="section">
        <h3>{{ __('Top Countries by Views') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Country') }}</th>
                    <th>{{ __('Views') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['topCountriesByViews'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['country'] }}</td>
                        <td class="bold">{{ number_format($row['views']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (!empty($analytics['videoHistoryList']))
        <div class="section">
            <h3>{{ __('Video History') }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Thumbnail') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Published') }}</th>
                        <th>{{ __('Views') }}</th>
                        <th>{{ __('Likes') }}</th>
                        <th>{{ __('Comments') }}</th>
                        <th>{{ __('Watch Time (min)') }}</th>
                        <th>{{ __('Avg Duration (s)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($analytics['videoHistoryList'] as $video)
                        <tr>
                            <td>
                                @if (!empty($video['full_picture']))
                                    <img src="{{ $video['full_picture'] }}" alt="thumb" width="60">
                                @endif
                            </td>
                            <td>
                                <a href="{{ $video['permalink_url'] }}" target="_blank">
                                    {{ \Illuminate\Support\Str::limit($video['message'], 60) }}
                                </a>
                            </td>
                            <td>{{ $video['created_time'] }}</td>
                            <td class="bold">{{ number_format($video['metrics']['views'] ?? 0) }}</td>
                            <td>{{ number_format($video['metrics']['likes'] ?? 0) }}</td>
                            <td>{{ number_format($video['metrics']['comments'] ?? 0) }}</td>
                            <td>{{ number_format($video['metrics']['estimatedMinutesWatched'] ?? 0, 2) }}</td>
                            <td>{{ number_format($video['metrics']['averageViewDuration'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>
</html>