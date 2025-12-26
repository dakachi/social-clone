<h2 style="margin-top:0;">{{ __('Payment Successful!') }}</h2>
<p>
    {{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}
</p>
<p>
    {{ __('Thank you for your payment. Your transaction has been completed successfully.') }}
</p>
<table style="margin:24px 0 18px 0; width:100%; max-width:400px;">
    <tr>
        <td style="color:#888;">{{ __('Order ID') }}:</td>
        <td style="font-weight:600;">{{ $order_id ?? '-' }}</td>
    </tr>
    <tr>
        <td style="color:#888;">{{ __('Plan') }}:</td>
        <td>{{ $plan_name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="color:#888;">{{ __('Amount') }}:</td>
        <td>{{ $order_amount ?? '-' }} {{ $order_currency ?? '' }}</td>
    </tr>
    <tr>
        <td style="color:#888;">{{ __('Payment date') }}:</td>
        <td>{{ $order_date ?? now()->format('Y-m-d') }}</td>
    </tr>
</table>
<div style="margin:28px 0 14px;">
    <a href="{{ $login_url ?? config('app.url') }}" class="btn" style="background: #248bcb; color: #fff; padding: 12px 32px; border-radius: 5px; text-decoration:none; font-size: 17px;">
        {{ __('Go to Dashboard') }}
    </a>
</div>
<p style="color:#888;">
    {{ __('If you have any questions, please contact our support team.') }}
</p>
