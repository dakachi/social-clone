<section class="relative w-screen min-h-screen flex items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("Create a New Password")])
    
    <div class="flex flex-col justify-center flex-1 px-8 py-16 bg-blueGray-100 z-10" style="background-image: url({{ theme_public_asset('images/pattern-light-big.svg') }}); background-position: center;">
        <form class="actionForm max-w-md mx-auto w-full space-y-5" action="{{ module_url('do_recovery_password') }}" method="POST">
        	<div class="show-on-mobile">
                <a class="mb-4 inline-block" href="{{ url('') }}">
                    <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                </a>
                <h2 class="mb-16 text-4xl md:text-4xl font-bold font-heading tracking-px-n leading-tight">
                    {{ __("Create a New Password") }}
                </h2>
            </div>
        	<input type="hidden" name="token" value="{{ $token ?? request('token') }}">
		    <input type="hidden" name="email" value="{{ $email ?? request('email') }}">

		    <!-- New Password -->
		    <div>
		        <label for="password" class="block text-gray-700 font-semibold mb-2">{{ __("New Password") }}</label>
		        <div class="relative">
		            <input id="password" type="password" name="password"
		                class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg pr-12 focus:ring focus:ring-indigo-300 outline-none"
		                placeholder="{{ __('Enter new password') }}" required autocomplete="new-password">
		            <button type="button" tabindex="-1"
		                class="absolute top-3 right-4 text-base-content/60 hover:text-primary transition"
		                onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
		                <i class="fa fa-eye"></i>
		            </button>
		        </div>
		    </div>
		    <!-- Confirm New Password -->
		    <div>
		        <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">{{ __("Confirm New Password") }}</label>
		        <div class="relative">
		            <input id="password_confirmation" type="password" name="password_confirmation"
		                class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg pr-12 focus:ring focus:ring-indigo-300 outline-none"
		                placeholder="{{ __('Confirm new password') }}" required autocomplete="new-password">
		            <button type="button" tabindex="-1"
		                class="absolute top-3 right-4 text-base-content/60 hover:text-primary transition"
		                onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
		                <i class="fa fa-eye"></i>
		            </button>
		        </div>
		    </div>

		    <div class="mb-3">
                {!! Captcha::render(); !!}
            </div>
            
		    <div class="msg-error mb-4"></div>
		    <button type="submit"
		        class="mb-8 py-4 px-9 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
		        {{ __("Reset Password") }}
		    </button>

		</form>

    </div>
</section>