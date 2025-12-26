<h2 style="margin-top:0;">{{ __('Payment Failed') }}</h2>

<p>
    {{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}
</p>

<p>
    {{ __('Unfortunately, we were unable to process your recent payment.') }}
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
        <td style="color:#888;">{{ __('Attempted on') }}:</td>
        <td>{{ $order_date ?? now()->format('Y-m-d') }}</td>
    </tr>
</table>

<div style="margin:28px 0 14px;">
    <a href="{{ $login_url ?? config('app.url') }}" class="btn"
       style="background: #e63946; color: #fff; padding: 12px 32px; border-radius: 5px;
              text-decoration:none; font-size: 17px;">
        {{ __('Try Again') }}
    </a>
</div>

<p style="color:#888;">
    {{ __('If the issue persists, please contact our support team at :email.', ['email' => $support_email ?? 'support@yourdomain.com']) }}
</p>