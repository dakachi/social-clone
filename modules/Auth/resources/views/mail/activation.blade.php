<h2 style="color:#675dff;">{{ __('Activate Your Account') }}</h2>

<p>{{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}</p>

<p>
    {{ __('Thank you for registering at :app.', ['app' => config('app.name')]) }}<br>
    {{ __('To activate your account, please click the button below:') }}
</p>

<div style="margin: 28px 0;">
    <a href="{{ $verify_url ?? '#' }}" class="btn"
       style="background:#675dff; color:#fff; padding:12px 32px; border-radius:5px; text-decoration:none;">
        {{ __('Activate Account') }}
    </a>
</div>

<p>
    {{ __('If the button does not work, copy and paste the following link into your browser:') }}<br>
    <a href="{{ $verify_url ?? '#' }}">{{ $verify_url ?? '#' }}</a>
</p>

<p style="color:#888;">{{ __('If you did not create an account, please ignore this email.') }}</p>
