<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('AI Usage Report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .section { margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f4f4f4; }
    </style>
</head>
<body>

    <div class="section">
        <div class="title">{{ __('Token Usage by Model') }}</div>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Model') }}</th>
                    <th>{{ __('Tokens Used') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modelChart['series'][0]['data'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ number_format($row['y']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="title">{{ __('Daily Token Usage') }}</div>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Tokens Used') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usageChart['categories'] as $i => $day)
                    <tr>
                        <td>{{ $day }}</td>
                        <td>{{ number_format($usageChart['series'][0]['data'][$i]) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (!empty($charts))
        @foreach($charts as $chart)
            <div style="margin-top: 150px;">
                <img src="{{ $chart['base64'] }}" style="width: 100%;" />
            </div>
        @endforeach
    @endif

</body>
</html>
