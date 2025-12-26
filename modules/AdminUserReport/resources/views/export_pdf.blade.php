<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('User Report') }}</title>
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

        h1 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 14px;
            margin-bottom: 6px;
            margin-top: 24px;
        }

        .section {
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        .table th {
            background-color: #f9f9f9;
        }

        img.chart {
            max-width: 100%;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .text-muted {
            color: #777;
        }
    </style>
</head>
<body>

    <h1>{{ __('User Report') }}</h1>

    <div class="section">
        <strong>{{ __('Total Users') }}:</strong> {{ $info['total'] ?? 0 }}<br>
        <strong>{{ __('Active') }}:</strong> {{ $info['active'] ?? 0 }}<br>
        <strong>{{ __('Inactive') }}:</strong> {{ $info['inactive'] ?? 0 }}<br>
        <strong>{{ __('Banned') }}:</strong> {{ $info['banned'] ?? 0 }}<br>
        <br>
        <strong>{{ __('Weekly Growth') }}:</strong> {{ $info['weekly_growth']['total'] ?? 0 }}%<br>
        <strong>{{ __('Monthly Growth') }}:</strong> {{ $info['monthly_growth']['total'] ?? 0 }}%
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
        <h3>{{ __('Latest Registered Users') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Login Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Registered At') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($latestUsers as $user)
                    <tr>
                        <td>{{ $user->fullname }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->login_type ?? __('Direct') }}</td>
                        <td>
                            @switch($user->status)
                                @case(2)
                                    {{ __('Active') }}
                                    @break
                                @case(1)
                                    {{ __('Inactive') }}
                                    @break
                                @default
                                    {{ __('Banned') }}
                            @endswitch
                        </td>
                        <td>{{ date('Y-m-d', $user->created) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
