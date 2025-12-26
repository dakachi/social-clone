<section class="py-24 md:pb-32 bg-white overflow-hidden" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <img class="absolute top-0 left-1/2 transform -translate-x-1/2" src="{{ theme_public_asset('images/cta/gradient4.svg') }}" alt=""/>
    <div class="relative z-10 container px-4 mx-auto">
        <div class="flex flex-wrap -m-8">
            <div class="w-full md:w-auto p-8">

                    <img class="w-56 mx-auto transform hover:translate-y-4 transition ease-in-out duration-1000 rounded-lg hide-on-mobile" src="{{ theme_public_asset('images/cta/man-play.png') }}" alt=""/>
                
            </div>
            <div class="w-full md:flex-1 p-8">
                <div class="md:max-w-2xl mx-auto text-center">
                    <h2 class="mb-10 text-6xl md:text-7xl font-bold font-heading text-center tracking-px-n leading-tight">
                        {{ __("Experience every feature. No commitment, no credit card required.") }}
                    </h2>
                    <div class="mb-12 md:inline-block">
                        <a href="{{ route("login") }}" class="py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                            {{ __("Get Started Now") }}
                        </a>
                    </div>
                    <div class="md:max-w-sm mx-auto">
                        <div class="flex flex-wrap -m-2">
                            <div class="w-auto p-2">
                                <svg class="mt-1" width="26" height="20" viewbox="0 0 26 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 20V14.2777C0 12.6321 0.306867 10.921 0.920601 9.14446C1.55293 7.34923 2.40844 5.65685 3.48712 4.06732C4.58441 2.45909 5.81187 1.10332 7.16953 0L11.8562 3.0575C10.7589 4.72183 9.83834 6.46096 9.09442 8.2749C8.3691 10.0701 8.01574 12.0524 8.03433 14.2216V20H0ZM14.1438 20V14.2777C14.1438 12.6321 14.4506 10.921 15.0644 9.14446C15.6967 7.34923 16.5522 5.65685 17.6309 4.06732C18.7282 2.45909 19.9557 1.10332 21.3133 0L26 3.0575C24.9027 4.72183 23.9821 6.46096 23.2382 8.2749C22.5129 10.0701 22.1595 12.0524 22.1781 14.2216V20H14.1438Z" fill="#E0E7FF"></path>
                                </svg>
                            </div>
                            <div class="flex-1 p-2">
                                <p class="mb-4 text-lg font-medium leading-normal text-left">
                                    {{ __("The easiest way to manage all my social channels in one place. It saves me hours every week!") }}
                                </p>
                                <h3 class="font-bold text-left">- {{ __("Anna Brown") }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-auto self-end p-8">
                <img class="w-52 mx-auto transform hover:-translate-y-4 transition ease-in-out duration-1000 rounded-lg" src="{{ theme_public_asset('images/cta/woman-play2.png') }}" alt=""/>
            </div>
        </div>
    </div>
</section>

<section class="pt-15 overflow-hidden border-t border-gray-600" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="pb-9 border-b border-gray-200">
            <div class="flex flex-wrap items-center justify-between -m-4">
                {{-- Logo --}}
                <div class="w-auto p-4">
                    <a href="{{ url('/') }}">
                        <img class="h-9" src="{{ url(get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png'))) }}" alt="">
                    </a>
                </div>
                {{-- Main Menu --}}
                <ul class="flex flex-wrap -m-4 md:-m-9 p-4">
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600 {{ request()->is('/') ? 'text-indigo-600' : '' }}"
                           href="{{ url('') }}">
                            {{ __("Home") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ (request()->is('/') && str_contains(request()->fullUrl(), '#features')) ? 'text-indigo-600' : '' }}"
                           href="{{ url('') }}#features">
                            {{ __("Features") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('pricing*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('pricing') }}">
                            {{ __("Pricing") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('faqs*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('faqs') }}">
                            {{ __("FAQs") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('blogs*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('blogs') }}">
                            {{ __("Blog") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('contact*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('contact') }}">
                            {{ __("Contact") }}
                        </a>
                    </li>
                </ul>

                {{-- Social Icons --}}
                <div class="w-auto p-4">
                    <div class="flex flex-wrap items-center -m-4">
                        @if(get_option("social_page_facebook", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-blue-600 transition duration-200"
                                   href="{{ get_option('social_page_facebook') }}"
                                   title="Facebook" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_instagram", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-pink-500 transition duration-200"
                                   href="{{ get_option('social_page_instagram') }}"
                                   title="Instagram" target="_blank" rel="noopener">
                                    <i class="fab fa-instagram fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_tiktok", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-black transition duration-200"
                                   href="{{ get_option('social_page_tiktok') }}"
                                   title="TikTok" target="_blank" rel="noopener">
                                    <i class="fab fa-tiktok fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_youtube", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-red-600 transition duration-200"
                                   href="{{ get_option('social_page_youtube') }}"
                                   title="YouTube" target="_blank" rel="noopener">
                                    <i class="fab fa-youtube fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_x", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-gray-800 transition duration-200"
                                   href="{{ get_option('social_page_x') }}"
                                   title="X (Twitter)" target="_blank" rel="noopener">
                                    <i class="fab fa-x-twitter fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_pinterest", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-red-600 transition duration-200"
                                   href="{{ get_option('social_page_pinterest') }}"
                                   title="Pinterest" target="_blank" rel="noopener">
                                    <i class="fab fa-pinterest fa-lg"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- Footer --}}
        <div class="pt-4 pb-6">
            <div class="flex flex-wrap justify-between items-center -m-4">
                <div class="w-auto p-4">
                    <p class="tracking-tight">© {{ date('Y') }}, {{ __("All Rights Reserved") }}</p>
                </div>
                <div class="w-auto p-4">
                    <div class="flex flex-wrap">
                        <div class="flex flex-wrap">
                            <div class="w-auto p-4">
                                <a class="tracking-tight" href="{{ url('privacy-policy') }}">{{ __("Privacy Policy") }}</a>
                            </div>
                            <div class="w-auto p-4">
                                <a class="tracking-tight" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
