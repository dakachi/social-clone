<section class="relative w-screen min-h-screen flex items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("Forgot password")])

    <div class="flex flex-col justify-center flex-1 px-8 py-16 bg-blueGray-100 z-10" style="background-image: url({{ theme_public_asset('images/pattern-light-big.svg') }}); background-position: center;">
        <form class="actionForm max-w-md mx-auto w-full space-y-5" action="{{ module_url('do_forgot_password') }}" method="POST">
        	<div class="show-on-mobile">
                <a class="mb-4 inline-block" href="{{ url('') }}">
                    <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                </a>
                <h2 class="mb-16 text-4xl md:text-4xl font-bold font-heading tracking-px-n leading-tight">
                    {{ __("Forgot password") }}
                </h2>
            </div>
			<div>
			    <label for="email" class="block text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>
			    <input type="email" id="email" name="email"
			        class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
			        placeholder="{{ __('Enter your email') }}" required autofocus>
			</div>

			<div class="mb-3">
                {!! Captcha::render(); !!}
            </div>

			<div class="msg-error mb-4"></div>

			<button type="submit"
			    class="mb-8 py-4 px-9 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl
			        focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
			    {{ __("Send Reset Link") }}
			</button>

			<p class="text-center text-base-content/80 pt-4">
			    <a href="{{ url('auth/login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
			        <i class="fa fa-arrow-left mr-1"></i>{{ __("Back to login") }}
			    </a>
			</p>
		</form>

    </div>
</section>
