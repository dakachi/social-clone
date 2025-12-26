<h2 style="margin-top:0;">
    {{ __('Welcome to :site!', ['site' => get_option("website_title", config('site.title'))]) }}
</h2>

<p>
    {{ __('Hello :name,', ['name' => $user_name ?? 'User']) }}
</p>

<p>
    {{ __('Thank you for registering at :site. We are excited to have you on board!', [
        'site' => get_option("website_title", config('site.title'))
    ]) }}
</p>

@if(!empty($invite_url))
    <div style="margin:24px 0;">
        <a href="{{ $invite_url }}" style="background:#675dff;color:#fff;padding:12px 32px;border-radius:5px;text-decoration:none;font-size:16px;">
            {{ __('Register Now') }}
        </a>
    </div>
@endif

<p style="color:#888;">
    {{ __('If you did not sign up for this account, please ignore this email.') }}
</p>