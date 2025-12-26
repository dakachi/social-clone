<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->id_secure }}</title>
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
            font-family: 'NotoSans', Arial, sans-serif;
            background: #f8f9fb;
            font-size: 13px;
            color: #23272f;
        }
        .pdf-container {
            max-width: 530px;
            margin: 36px auto 0 auto;
            border-radius: 22px;
            background: #fff;
            border: 1.5px solid #e4e7ec;
            box-shadow: 0 2px 16px #f0f2fa;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        td, th {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header {
            padding: 36px 36px 0 36px;
        }
        .invoice-title {
            color: #000;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .created-at {
            font-size: 12px;
            color: #8c94a6;
            margin-bottom: 0;
        }
        .status-cell {
            text-align: right;
            vertical-align: top;
            padding-top: 12px;
        }
        .badge {
            display: inline-block;
            padding: 7px 26px;
            border-radius: 22px;
            font-size: 13px;
            font-weight: 700;
        }
        .badge-paid { background: #41c073; color: #fff; }
        .badge-pending { background: #ffc978; color: #222; }
        .hr-1 {
            border: none;
            border-top: 1.5px solid #e6e6ef;
            margin: 28px 0 0 0;
        }
        .info-section {
            padding: 24px 36px 0 36px;
        }
        .info-table {
            width: 100%;
        }
        .info-label {
            color: #9d9d9d;
            font-size: 13px;
            font-weight: 700;
            padding-bottom: 6px;
        }
        .plan-title {
            color: #000;
            font-weight: bold;
            font-size: 15px;
            padding-bottom: 2px;
        }
        .plan-desc {
            color: #b2b2c2;
            font-size: 12px;
            padding-bottom: 8px;
        }
        .value-bold { font-weight: bold; }
        .transaction-id-label {
            font-weight: bold;
            font-size: 13px;
            padding-top: 7px;
        }
        .transaction-id-value {
            word-break: break-all;
            color: #000;
            font-size: 12px;
            padding-bottom: 2px;
        }
        .amount-section {
            padding: 24px 36px 0 36px;
        }
        .amount-value {
            font-size: 24px;
            color: #23272f;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .hr-2 {
            border: none;
            border-top: 1.5px solid #e6e6ef;
            margin: 26px 0 0 0;
        }
        .footer {
            padding: 12px 36px 22px 36px;
        }
        .last-updated {
            color: #868b98;
            font-size: 12px;
            padding-top: 3px;
        }
        .mb5{
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <table>
            {{-- Header --}}
            <tr>
                <td class="header" style="width:68%; vertical-align: middle; height:64px;">
                    <div class="invoice-title">#{{ strtoupper($invoice->id_secure) }}</div>
                    <div class="created-at">
                        {{ __('Created at:') }} {{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('d/m/Y H:i') }}
                    </div>
                </td>
                <td class="header status-cell" style="width:32%; vertical-align: middle; text-align:right; height:64px;">
                    @if($invoice->status == 1)
                        <span class="badge badge-paid">Paid</span>
                    @else
                        <span class="badge badge-pending">Pending</span>
                    @endif
                </td>
            </tr>
            <tr><td colspan="2"><hr class="hr-1"></td></tr>
            {{-- Info + Transaction --}}
            <tr>
                <td colspan="2" class="info-section">
                    <table class="info-table">
                        <tr>
                            <td style="width:48%;padding-right:8px;vertical-align:top;">
                                <div class="info-label">Plan</div>
                                <div class="plan-title">{{ $invoice->plan->name ?? '-' }}</div>
                                @if($invoice->plan && $invoice->plan->desc)
                                    <div class="plan-desc">{{ $invoice->plan->desc }}</div>
                                @endif
                            </td>
                            <td style="width:52%;padding-left:8px;vertical-align:top;">
                                <div class="mb5">
                                    <div class="info-label">Transaction From</div>
                                    <div class="value-bold">{{ ucfirst($invoice->from) }}</div>
                                </div>
                                <div class="mb5">
                                    <div class="info-label">Transaction ID:</div>
                                    <div class="transaction-id-value">{{ $invoice->transaction_id }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            {{-- Amount --}}
            <tr>
                <td colspan="2" class="amount-section">
                    <div class="info-label">{{ __('Amount') }}</div>
                    <div class="amount-value">
                        {{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}
                    </div>
                </td>
            </tr>
            <tr><td colspan="2"><hr class="hr-2"></td></tr>
            {{-- Footer --}}
            <tr>
                <td colspan="2" class="footer">
                    <div class="last-updated">
                        {{ __('Last updated:') }} {{ \Carbon\Carbon::createFromTimestamp($invoice->changed)->format('d/m/Y H:i') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
