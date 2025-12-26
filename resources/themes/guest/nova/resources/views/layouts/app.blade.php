<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  dir="{{ Language::getCurrent('dir') }}">
<head>
    <title>
        @hasSection('pagetitle')
            @yield('pagetitle')
        @else
            {{ get_option("website_title", config('site.title')) }}
        @endif
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="{{ get_option("website_keyword", config('site.keywords')) }}">
    <meta name="description" content="{{ get_option("website_description", config('site.description')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ url( get_option("website_favicon", asset('public/img/favicon.png')) ) }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.cdnfonts.com/css/general-sans?styles=135312,135310,135313,135303" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ theme_public_asset('css/flags/flag-icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ theme_public_asset('css/fontawesome/css/all.min.css') }}">
    {!! theme_vite('guest/nova') !!}
    {!! Script::globals() !!}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
</head>
<body class="antialiased bg-body text-body font-body">

<body class="sm:overflow-x-hidden">
    @if( request()->segment(1) != "auth")
        @include('partials.header')
    @endif

    @yield('content')

    @if( request()->segment(1) != "auth")
        @include('partials.footer')
    @endif

    <div class="fixed bottom-0 z-50 w-full cookie-policy-bar">
        <div class="p-10 md:px-20 lg:px-36 bg-white border border-coolGray-100 shadow-md">
            <div class="container mx-auto">
                <div class="flex flex-wrap items-center -mx-4">
                    <div class="w-full md:w-1/2 px-4 mb-8 md:mb-0">
                        <h3 class="mb-4 text-lg md:text-xl text-coolGray-900 font-semibold">{{ __("Cookie Policy") }}</h3>
                        <p class="mb-2 text-coolGray-500 font-medium">{{ __("We use third-party cookies in order to personalise your experience") }}</p>
                        <a class="flex items-center font-medium text-indigo-500 hover:text-indigo-600" href="{{ url("privacy-policy") }}">
                            <span class="mr-2">{{ __("Read our cookie policy") }}</span>
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.71 12.71C15.801 12.6149 15.8724 12.5028 15.92 12.38C16.02 12.1365 16.02 11.8635 15.92 11.62C15.8724 11.4972 15.801 11.3851 15.71 11.29L12.71 8.29C12.5217 8.1017 12.2663 7.99591 12 7.99591C11.7337 7.99591 11.4783 8.1017 11.29 8.29C11.1017 8.4783 10.9959 8.7337 10.9959 9C10.9959 9.2663 11.1017 9.5217 11.29 9.71L12.59 11L9 11C8.73479 11 8.48043 11.1054 8.2929 11.2929C8.10536 11.4804 8 11.7348 8 12C8 12.2652 8.10536 12.5196 8.2929 12.7071C8.48043 12.8946 8.73479 13 9 13L12.59 13L11.29 14.29C11.1963 14.383 11.1219 14.4936 11.0711 14.6154C11.0203 14.7373 10.9942 14.868 10.9942 15C10.9942 15.132 11.0203 15.2627 11.0711 15.3846C11.1219 15.5064 11.1963 15.617 11.29 15.71C11.383 15.8037 11.4936 15.8781 11.6154 15.9289C11.7373 15.9797 11.868 16.0058 12 16.0058C12.132 16.0058 12.2627 15.9797 12.3846 15.9289C12.5064 15.8781 12.617 15.8037 12.71 15.71L15.71 12.71ZM22 12C22 10.0222 21.4135 8.08879 20.3147 6.4443C19.2159 4.79981 17.6541 3.51808 15.8268 2.7612C13.9996 2.00433 11.9889 1.80629 10.0491 2.19215C8.10929 2.578 6.32746 3.53041 4.92894 4.92893C3.53041 6.32746 2.578 8.10929 2.19215 10.0491C1.8063 11.9889 2.00433 13.9996 2.76121 15.8268C3.51809 17.6541 4.79981 19.2159 6.4443 20.3147C8.08879 21.4135 10.0222 22 12 22C14.6522 22 17.1957 20.9464 19.0711 19.0711C19.9997 18.1425 20.7363 17.0401 21.2388 15.8268C21.7413 14.6136 22 13.3132 22 12ZM4 12C4 10.4177 4.4692 8.87103 5.34825 7.55544C6.2273 6.23985 7.47673 5.21446 8.93854 4.60896C10.4003 4.00346 12.0089 3.84504 13.5607 4.15372C15.1126 4.4624 16.538 5.22433 17.6569 6.34315C18.7757 7.46197 19.5376 8.88743 19.8463 10.4393C20.155 11.9911 19.9965 13.5997 19.391 15.0615C18.7855 16.5233 17.7602 17.7727 16.4446 18.6518C15.129 19.5308 13.5823 20 12 20C9.87827 20 7.84344 19.1571 6.34315 17.6569C4.84286 16.1566 4 14.1217 4 12Z" fill="currentColor"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="w-full md:w-1/2 px-4">
                        <div class="flex flex-wrap justify-end">
                            <div class="w-full md:w-auto py-1 md:py-0 md:mr-4"><a class="inline-block py-3 px-5 w-full leading-5 text-coolGray-800 bg-white hover:bg-coolGray-100 font-medium text-center focus:ring-2 focus:ring-coolGray-200 focus:ring-opacity-50 border border-coolGray-200 rounded-md shadow-sm btn-decline" href="javascript:void(0);">{{ __("Decline") }}</a></div>
                            <div class="w-full md:w-auto py-1 md:py-0"><a class="inline-block py-3 px-5 w-full leading-5 text-white bg-indigo-500 hover:bg-indigo-600 font-medium text-center focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 border border-transparent rounded-md shadow-sm btn-accept" href="javascript:void(0);">{{ __("Allow") }}</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{ theme_public_asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ theme_public_asset('js/main.js') }}"></script>
</body>
</html>