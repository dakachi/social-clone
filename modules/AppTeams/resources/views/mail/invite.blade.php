<h2 style="margin-top:0;">{{ __('You are invited to join a team!') }}</h2>

<p>
    {{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}
</p>

<p>
    {!! __('You have been invited to join the team <b>:team</b>.', ['team' => $team_name ?? '-']) !!}
</p>

<div style="margin:24px 0;">
    <a href="{{ $invite_url ?? config('app.url') }}" style="background:#675dff;color:#fff;padding:12px 32px;border-radius:5px;text-decoration:none;font-size:16px;">
        {{ __('Accept Invitation') }}
    </a>
</div>

<p style="color:#888;">
    {{ __('If you did not expect this email, you can safely ignore it.') }}
</p>