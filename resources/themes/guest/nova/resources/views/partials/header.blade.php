<section x-data="{ mobileNavOpen: false }" class="bg-blueGray-50 z-60 relative">
    <div class="container mx-auto">
        <div class="flex items-center justify-between px-4 py-5 bg-blueGray-50">
            <div class="w-auto">
                <div class="flex flex-wrap items-center">
                    <div class="w-auto mr-14">
                        <a href="{{ url("") }}">
                            <img class="h-9" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                        </a>
                    </div>
                </div>
            </div>
            <div class="w-auto">
                <div class="flex items-center justify-between">
                    <div class="w-auto hidden lg:block">
                        <ul class="flex items-center mr-16">
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('') }}"
                                   class="{{ request()->is('/') ? 'text-indigo-600 font-bold' : '' }}">
                                    {{ __("Home") }}
                                </a>
                            </li>
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('') }}#features"
                                   class="{{ request()->is('/') && str_contains(request()->fullUrl(), '#features') ? 'text-indigo-600' : '' }}">
                                    {{ __("Features") }}
                                </a>
                            </li>
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('pricing') }}"
                                   class="{{ request()->is('pricing*') ? 'text-indigo-600' : '' }}">
                                    {{ __("Pricing") }}
                                </a>
                            </li>
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('faqs') }}"
                                   class="{{ request()->is('faqs*') ? 'text-indigo-600' : '' }}">
                                    {{ __("FAQs") }}
                                </a>
                            </li>
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('blogs') }}"
                                   class="{{ request()->is('blogs*') ? 'text-indigo-600' : '' }}">
                                    {{ __("Blog") }}
                                </a>
                            </li>
                            <li class="mr-9 font-medium hover:text-gray-700">
                                <a href="{{ url('contact') }}"
                                   class="{{ request()->is('contact*') ? 'text-indigo-600' : '' }}">
                                    {{ __("Contact") }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="w-auto">
                        <div class="flex flex-wrap items-center">
                            <!-- Language Dropdown -->
                            <div class="dropdown dropdown-hover dropdown-center">
                                <div tabindex="0" class="flex items-center gap-1 px-3 min-h-[2rem] h-[2rem] cursor-pointer">
                                    <!-- Globe Icon -->
                                    <svg class="size-6 text-base-content" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>
                                    </svg>
                                    <!-- Downward Arrow Icon -->
                                    <svg class="size-3 text-base-content" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                @php
                                    $languages = Language::getLanguages();
                                    $currentLang = app()->getLocale();
                                @endphp

                                @if($languages->isNotEmpty())
                                <ul tabindex="0" class="dropdown-content z-[999] menu p-3 border-1 border-gray-100 shadow bg-base-100 rounded-box w-40">
                                    @foreach($languages as $language)
                                        <li>
                                            <a
                                                href="{{ url('lang/' . $language->code) }}"
                                                class="flex items-center gap-2 {{ $currentLang == $language->code ? 'bg-indigo-600 text-white font-semibold' : 'font-medium' }}"
                                            >
                                                @if($language->icon)
                                                    <span class="size-4 text-center d-block -mt-1"><i class="{{ $language->icon }}"></i></span>
                                                @endif
                                                <span class="truncate">{{ $language->name }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>

                            @if(Auth::check())
                                <div class="w-auto mr-5 hidden lg:block">
                                    <a href="{{ url('app/dashboard') }}" class="py-3 px-5 w-full text-white font-semibold rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
                                        {{ __('Dashboard') }}
                                    </a>
                                </div>
                            @else
                                <div class="w-auto hidden mr-5 hidden lg:block">
                                    <a href="{{ url('auth/login') }}" class="py-3 px-5 w-full hover:text-gray-700 font-medium rounded-xl bg-transparent transition ease-in-out duration-200">
                                        {{ __("Sign In") }}
                                    </a>
                                </div>
                                @if(get_option("auth_signup_page_status", 1))
                                <div class="w-auto hidden lg:block">
                                    <a href="{{ url('auth/signup') }}" class="py-3 px-5 w-full text-white font-semibold rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
                                        {{ __("Sign Up") }}
                                    </a>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="w-auto lg:hidden">
                        <button x-on:click="mobileNavOpen = !mobileNavOpen">
                            <svg class="text-indigo-600" width="51" height="51" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="56" height="56" rx="28" fill="currentColor"></rect>
                                <path d="M37 32H19M37 24H19" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div :class="{'block': mobileNavOpen, 'hidden': !mobileNavOpen}" class="hidden fixed top-0 left-0 bottom-0 w-4/6 sm:max-w-xs z-50">
            <div x-on:click="mobileNavOpen = !mobileNavOpen" class="fixed inset-0 bg-gray-800 opacity-80"></div>
            <nav class="relative z-10 px-9 pt-8 bg-white h-full overflow-y-auto">
                <div class="flex flex-wrap justify-between h-full">
                    <div class="w-full">
                        <div class="flex items-center justify-between -m-2">
                            <div class="w-auto p-2">
                                <a class="inline-block" href="{{ url("") }}">
                                    <img class="h-9" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                                </a>
                            </div>
                            <div class="w-auto p-2">
                                <button x-on:click="mobileNavOpen = !mobileNavOpen">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 18L18 6M6 6L18 18" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center py-16 w-full">
                        <ul>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ request()->is('/') ? 'text-indigo-600' : '' }}"
                                   href="{{ url('') }}">
                                    {{ __("Home") }}
                                </a>
                            </li>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ (request()->is('/') && str_contains(request()->fullUrl(), '#features')) ? 'text-indigo-600' : '' }}"
                                   href="{{ url('') }}#features">
                                    {{ __("Features") }}
                                </a>
                            </li>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ request()->is('pricing*') ? 'text-indigo-600' : '' }}"
                                   href="{{ url('pricing') }}">
                                    {{ __("Pricing") }}
                                </a>
                            </li>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ request()->is('faqs*') ? 'text-indigo-600' : '' }}"
                                   href="{{ url('faqs') }}">
                                    {{ __("FAQs") }}
                                </a>
                            </li>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ request()->is('blogs*') ? 'text-indigo-600' : '' }}"
                                   href="{{ url('blogs') }}">
                                    {{ __("Blog") }}
                                </a>
                            </li>
                            <li class="mb-12">
                                <a class="font-medium hover:text-gray-700 {{ request()->is('contact*') ? 'text-indigo-600' : '' }}"
                                   href="{{ url('contact') }}">
                                    {{ __("Contact") }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="flex flex-col justify-end w-full pb-8">
                        <div class="flex flex-wrap">
                            @if(Auth::check())
                                <div class="w-full mb-3">
                                    <a href="{{ url('app/dashboard') }}"
                                       class="py-3 px-5 w-full text-white font-semibold rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200 block text-center">
                                        {{ __('Dashboard') }}
                                    </a>
                                </div>
                            @else
                                <div class="w-full mb-3">
                                    <a href="{{ url('auth/login') }}"
                                       class="py-3 px-5 w-full hover:text-gray-700 font-medium rounded-xl bg-transparent transition ease-in-out duration-200 block text-center">
                                        {{ __("Sign In") }}
                                    </a>
                                </div>
                                @if(get_option("auth_signup_page_status", 1))
                                    <div class="w-full">
                                        <a href="{{ url('auth/signup') }}"
                                           class="py-3 px-5 w-full text-white font-semibold rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200 block text-center">
                                            {{ __("Sign Up") }}
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</section>
