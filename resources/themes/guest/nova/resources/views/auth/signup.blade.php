<section class="relative w-screen min-h-screen flex items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("Create an account & get started.")])

    <div class="flex flex-col justify-center flex-1 px-8 py-16 bg-blueGray-100 z-10" style="background-image: url({{ theme_public_asset('images/pattern-light-big.svg') }}); background-position: center;">
        <form class="actionForm max-w-md mx-auto w-full space-y-5" action="{{ module_url('do_signup') }}" method="POST">
        	<div class="show-on-mobile">
                <a class="mb-4 inline-block" href="{{ url('') }}">
                    <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                </a>
                <h2 class="mb-16 text-4xl md:text-4xl font-bold font-heading tracking-px-n leading-tight">
                    {{ __("Create an account & get started.") }}
                </h2>
            </div>
		    <!-- Full Name -->
		    <div>
		        <label for="fullname" class="block text-gray-700 font-semibold mb-2">{{ __("Full Name") }}</label>
		        <input type="text" id="fullname" name="fullname"
		               class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		               placeholder="{{ __('Enter your full name') }}" required>
		    </div>

		    <!-- Email -->
		    <div>
		        <label for="email" class="block text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>
		        <input type="email" id="email" name="email"
		               class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		               placeholder="{{ __('Enter your email address') }}" required>
		    </div>

		    <!-- Username -->
		    <div>
		        <label for="username" class="block text-gray-700 font-semibold mb-2">{{ __("Username") }}</label>
		        <input type="text" id="username" name="username"
		               class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		               placeholder="{{ __('Choose a username') }}" required>
		    </div>

		    <!-- Password -->
		    <div>
		        <label for="password" class="block text-gray-700 font-semibold mb-2">{{ __("Password") }}</label>
		        <input type="password" id="password" name="password"
		               class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		               placeholder="{{ __('Enter your password') }}" required>
		    </div>

		    <!-- Confirm Password -->
		    <div>
		        <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">{{ __("Confirm Password") }}</label>
		        <input type="password" id="password_confirmation" name="password_confirmation"
		               class="input input-bordered input-lg w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		               placeholder="{{ __('Re-enter your password') }}" required>
		    </div>

		    <!-- Timezone -->
		    <div>
		        <label for="timezone" class="block text-gray-700 font-semibold mb-2">{{ __("Timezone") }}</label>
		        <select id="timezone" name="timezone"
		                class="select select-bordered input-lg w-full px-4 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
		                required>
		            <option value="">{{ __("Select your timezone") }}</option>
		            @foreach(timezone_identifiers_list() as $tz)
		                <option value="{{ $tz }}" {{ old('timezone') == $tz ? 'selected' : '' }}>
		                    {{ $tz }}
		                </option>
		            @endforeach
		        </select>
		    </div>

		    <div class="mb-3">
                {!! Captcha::render(); !!}
            </div>

		    <div class="flex flex-wrap justify-between mb-4">
                <div class="w-full">
                    <div class="flex items-center">
                        <input class="w-4 h-4" id="accep_terms" name="accep_terms" type="checkbox" value="1" required>
                        <label class="ml-2 text-gray-700 font-medium" for="accep_terms" >
                            <span>{{ __("I agree to the") }}</span>
                            <a class="text-indigo-600 hover:text-indigo-700" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
                        </label>
                    </div>
                </div>
            </div>

		    <!-- Error Message -->
		    <div class="msg-error mb-2"></div>

		    <!-- Submit -->
		    <button type="submit"
		            class="mb-8 py-4 px-9 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
		        {{ __("Sign Up") }}
		    </button>

		    <!-- Switch to Sign In -->
		    <p class="text-center text-base-content/80 pt-4">
		        {{ __("Already have an account?") }}
		        <a href="{{ url('auth/login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __("Sign in") }}</a>
		    </p>
		</form>

    </div>
</section>
