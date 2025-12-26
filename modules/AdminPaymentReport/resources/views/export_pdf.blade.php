<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Payment Report PDF') }}</title>
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
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .section { margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background: #f4f4f4; }
    </style>
</head>
<body>

    <div class="section">
        <div class="title">{{ __('Payment Summary') }}</div>
        <p><strong>{{ __('Total Income') }}:</strong> ${{ number_format($info['total_income'], 2) }}</p>
        <p><strong>{{ __('Top Gateway') }}:</strong> {{ $info['top_gateway'] }}</p>
        <p><strong>{{ __('Period') }}:</strong> {{ $startDate->format('M d, Y') }} – {{ $endDate->format('M d, Y') }}</p>
    </div>

    <div class="section">
        <div class="title">{{ __('Success & Refunds') }}</div>
        <p><strong>{{ __('Success Transactions') }}:</strong> {{ $info['success_transactions'] }} ({{ $info['success_growth'] }}%)</p>
        <p><strong>{{ __('Refunded Transactions') }}:</strong> {{ $info['refunded_transactions'] }} ({{ $info['refund_growth'] }}%)</p>
    </div>

    <div class="section">
        <div class="title">{{ __('Latest Payments') }}</div>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($latestPayments as $payment)
                <tr>
                    <td>{{ $payment->user_fullname }}</td>
                    <td>{{ $payment->user_email }}</td>
                    <td>{{ $payment->plan_name }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                    <td>{{ date('Y-m-d', $payment->created) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (!empty($charts) && is_array($charts))
        @foreach($charts as $chart)
            @if(isset($chart['base64']) && is_string($chart['base64']))
                <div style="margin-top: 150px;">
                    <img src="{{ $chart['base64'] }}" alt="{{ __('Chart') }}" style="width: 100%;">
                </div>
            @endif
        @endforeach
    @endif

</body>
</html>
