<div class="hide-on-mobile flex flex-col justify-center flex-1 px-8 py-16 bg-white z-10">
    <img class="absolute left-0 z-20 bottom-0 pointer-events-none hide-on-mobile" src="{{ theme_public_asset('images/sign-up/gradient.svg') }}" alt="" />
    <div class="max-w-lg mx-auto">
        <a class="mb-28 inline-block" href="{{ url('') }}">
            <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
        </a>
        <h2 class="mb-32 text-6xl md:text-7xl font-bold font-heading tracking-px-n leading-tight">
            {{ $name ?? __("Welcome Back") }}
        </h2>
        <h3 class="mb-9 text-xl font-bold font-heading leading-normal">
            {{ __("Why join our community?") }}
        </h3>
        <ul class="md:max-w-xs">
            <li class="mb-5 flex flex-wrap">
                <svg class="mr-2" width="25" height="26" viewBox="0 0 25 26" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 23C18.0228 23 22.5 18.5228 22.5 13C22.5 7.47715 18.0228 3 12.5 3C6.97715 3 2.5 7.47715 2.5 13C2.5 18.5228 6.97715 23 12.5 23ZM17.1339 11.3839C17.622 10.8957 17.622 10.1043 17.1339 9.61612C16.6457 9.12796 15.8543 9.12796 15.3661 9.61612L11.25 13.7322L9.63388 12.1161C9.14573 11.628 8.35427 11.628 7.86612 12.1161C7.37796 12.6043 7.37796 13.3957 7.86612 13.8839L10.3661 16.3839C10.8543 16.872 11.6457 16.872 12.1339 16.3839L17.1339 11.3839Z" fill="#4F46E5"></path>
                </svg>
                <span class="flex-1 font-medium leading-relaxed">
                    {{ __("Unlock powerful features and get more done in less time") }}
                </span>
            </li>
            <li class="mb-5 flex flex-wrap">
                <svg class="mr-2" width="25" height="26" viewBox="0 0 25 26" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 23C18.0228 23 22.5 18.5228 22.5 13C22.5 7.47715 18.0228 3 12.5 3C6.97715 3 2.5 7.47715 2.5 13C2.5 18.5228 6.97715 23 12.5 23ZM17.1339 11.3839C17.622 10.8957 17.622 10.1043 17.1339 9.61612C16.6457 9.12796 15.8543 9.12796 15.3661 9.61612L11.25 13.7322L9.63388 12.1161C9.14573 11.628 8.35427 11.628 7.86612 12.1161C7.37796 12.6043 7.37796 13.3957 7.86612 13.8839L10.3661 16.3839C10.8543 16.872 11.6457 16.872 12.1339 16.3839L17.1339 11.3839Z" fill="#4F46E5"></path>
                </svg>
                <span class="flex-1 font-medium leading-relaxed">
                    {{ __("Enjoy 24/7 support from our dedicated expert team") }}
                </span>
            </li>
            <li class="mb-5 flex flex-wrap">
                <svg class="mr-2" width="25" height="26" viewBox="0 0 25 26" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 23C18.0228 23 22.5 18.5228 22.5 13C22.5 7.47715 18.0228 3 12.5 3C6.97715 3 2.5 7.47715 2.5 13C2.5 18.5228 6.97715 23 12.5 23ZM17.1339 11.3839C17.622 10.8957 17.622 10.1043 17.1339 9.61612C16.6457 9.12796 15.8543 9.12796 15.3661 9.61612L11.25 13.7322L9.63388 12.1161C9.14573 11.628 8.35427 11.628 7.86612 12.1161C7.37796 12.6043 7.37796 13.3957 7.86612 13.8839L10.3661 16.3839C10.8543 16.872 11.6457 16.872 12.1339 16.3839L17.1339 11.3839Z" fill="#4F46E5"></path>
                </svg>
                <span class="flex-1 font-medium leading-relaxed">
                    {{ __("Secure and reliable – your data is always protected") }}
                </span>
            </li>
        </ul>
    </div>
</div>