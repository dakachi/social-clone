<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Affiliate Report') }}</title>
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
            line-height: 1.5;
        }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>{{ __('Affiliate Report') }}</h2>
    <p>{{ __('Generated at:') }} {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>{{ __('Total Conversions') }}</td><td>{{ $info['total_conversions'] }}</td></tr>
            <tr><td>{{ __('Total Clicks') }}</td><td>{{ $info['total_clicks'] }}</td></tr>
            <tr><td>{{ __('Total Amount') }}</td><td>{{ $info['total_amount'] }}</td></tr>
            <tr><td>{{ __('Total Commission') }}</td><td>{{ $info['total_commission'] }}</td></tr>
            <tr><td>{{ __('Total Approved') }}</td><td>{{ $info['total_approved'] }}</td></tr>
            <tr><td>{{ __('Total Withdrawal') }}</td><td>{{ $info['total_withdrawal'] }}</td></tr>
            <tr><td>{{ __('Total Balance') }}</td><td>{{ $info['total_balance'] }}</td></tr>
        </tbody>
    </table>

    <h4>Status Summary</h4>
    <table>
        <thead>
            <tr><th>Status</th><th>Count</th><th>Amount</th><th>Commission</th></tr>
        </thead>
        <tbody>
            <tr><td>{{ __('Pending') }}</td><td>{{ $info['pending_count'] }}</td><td>{{ $info['pending_amount'] }}</td><td>{{ $info['pending_commission'] }}</td></tr>
            <tr><td>{{ __('Approved') }}</td><td>{{ $info['approved_count'] }}</td><td>{{ $info['approved_amount'] }}</td><td>{{ $info['approved_commission'] }}</td></tr>
            <tr><td>{{ __('Rejected') }}</td><td>{{ $info['rejected_count'] }}</td><td>{{ $info['rejected_amount'] }}</td><td>{{ $info['rejected_commission'] }}</td></tr>
        </tbody>
    </table>

    @foreach($charts as $chart)
        <div style="margin-top: 150px;">
            <img src="{{ $chart['base64'] }}" alt="Chart" style="width: 100%;">
        </div>
    @endforeach
</body>
</html>
