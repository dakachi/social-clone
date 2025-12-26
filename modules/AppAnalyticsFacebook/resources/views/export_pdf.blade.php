<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Facebook Analytics Report') }}</title>
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
            color: #222;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3b5998;
        }

        h3 {
            font-size: 15px;
            margin-bottom: 8px;
            margin-top: 28px;
            color: #333;
        }

        .section {
            margin-bottom: 32px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 10px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        img.chart {
            display: block;
            margin: 0 auto;
            max-width: 95%;
            margin-top: 12px;
            margin-bottom: 30px;
        }

        .highlight {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>

    <h1>{{ __('Facebook Analytics Report') }}</h1>

    <div class="section">
        <strong>{{ __('Page Name') }}:</strong> <span class="highlight">{{ $analytics['account']['name'] ?? '-' }}</span><br>
        <strong>{{ __('Fans') }}:</strong> <span class="highlight">{{ number_format($analytics['account']['fan_count'] ?? 0) }}</span><br>
        <strong>{{ __('Followers') }}:</strong> <span class="highlight">{{ number_format($analytics['account']['followers_count'] ?? 0) }}</span><br>
        <strong>{{ __('Category') }}:</strong> <span class="highlight">{{ $analytics['account']['category'] ?? '-' }}</span><br>
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
                    <img class="chart" src="{{ $chart['base64'] }}" alt="Chart">
                @endif
            @endforeach
        </div>
    @endif

    <div class="section">
        <h3>{{ __('Top Countries') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Country') }}</th>
                    <th>{{ __('Fans') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['topFansCountries'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['country'] }}</td>
                        <td class="highlight">{{ number_format($row['fans']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
