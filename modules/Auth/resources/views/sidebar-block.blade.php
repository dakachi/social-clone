@if(session('impersonate_by'))
    <div class="menu-item mb-2">
        <a href="{{ route('auth.leave_impersonate') }}" class="btn btn-outline btn-primary btn-sm wp-100">
            <i class="fa-light fa-right-from-bracket"></i> {{ __("Back to Admin") }}
        </a>
    </div>
@endif

@if(Auth::user()->role == 2)
	<div class="menu-item mb-2">
		@if(session("login_as") == "client")
			<a href="{{ url("auth/login-as-admin") }}" class="btn btn-outline btn-primary btn-sm wp-100"><i class="fa-light fa-right-from-bracket"></i> {{ __("Admin Dashboard") }}</a>
		@else
			<a href="{{ url("auth/login-as-user") }}" class="btn btn-outline btn-primary btn-sm wp-100"><i class="fa-light fa-right-from-bracket"></i> {{ __("User Dashboard") }}</a>
		@endif
	</div>
@endif