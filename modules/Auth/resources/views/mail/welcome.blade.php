<h2>{{ __('Welcome to :app', ['app' => get_option("website_title", config('site.title'))]) }}</h2>

<p>{{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}</p>

<p>{{ __('We’re excited to have you on board. You can now explore your dashboard and start using all features.') }}</p>

<div style="margin: 28px 0;">
    <a href="{{ $login_url ?? url('/') }}" class="btn"
       style="background:#00b894; color:#fff; padding:12px 32px; border-radius:5px; text-decoration:none;">
        {{ __('Go to Dashboard') }}
    </a>
</div>

<p>{{ __('Need help? Just reply to this email or contact support.') }}</p>