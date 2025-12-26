<h2>{{ __('Reset Your Password') }}</h2>

<p>{{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}</p>

<p>{{ __('We received a request to reset your password. Click the button below to set a new one:') }}</p>

<div style="margin: 28px 0;">
    <a href="{{ $reset_url ?? '#' }}" class="btn"
       style="background:#675dff; color:#fff; padding:12px 32px; border-radius:5px; text-decoration:none;">
        {{ __('Reset Password') }}
    </a>
</div>

<p>{{ __('If you did not request this, you can safely ignore this email.') }}</p>