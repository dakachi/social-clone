<section class="bg-blueGray-50 relative z-50">
    <div class="overflow-hidden pt-32">
        <div class="container px-4 mx-auto">
            <div class="flex flex-wrap -m-8">
                <div class="w-full md:w-1/2 p-8">
                    <div class="inline-block mb-6 px-2 py-1 font-semibold bg-green-100 rounded-full">
                        <div class="flex flex-wrap items-center -m-1">
                            <div class="w-auto p-1">
                                <a class="text-sm" href="{{ url('auth/login') }}">👋 {{ __("Plan smarter. Post stronger.") }}</a>
                            </div>
                            <div class="w-auto p-1">
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.66667 3.41675L12.75 7.50008M12.75 7.50008L8.66667 11.5834M12.75 7.50008L2.25 7.50008" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <h1 class="mb-6 text-6xl md:text-8xl lg:text-10xl font-bold font-heading md:max-w-xl leading-none">
                        {{ __("Manage Social Smarter Schedule. Track. Succeed.") }}
                    </h1>
                    <p class="mb-11 text-lg text-gray-900 font-medium md:max-w-md">
                        {{ __("Plan and publish content effortlessly across all your social channels. Manage everything in one smart dashboard. Work less. Grow more.") }}
                    </p>
                    <div class="flex flex-wrap -m-2.5 mb-20">
                        <div class="w-full md:w-auto p-2.5">
                            <div class="block">
                                <a href="{{ url('auth/login') }}" class="block py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                                    {{ __("Get Start Now") }}
                                </a>
                            </div>
                        </div>
                        <div class="w-full md:w-auto p-2.5">
                            <div class="block">
                                <a href="{{ url('') }}#features" class="block py-4 px-9 w-full font-semibold border border-gray-300 hover:border-gray-400 rounded-xl focus:ring focus:ring-gray-50 bg-transparent hover:bg-gray-100 transition ease-in-out duration-200" type="button">
                                    <div class="flex flex-wrap justify-center items-center -m-1">
                                        <div class="w-auto p-1">
                                            <span>{{ __("Learn more") }} <i class="fa-light fa-chevron-right"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="mb-6 text-sm text-gray-500 font-semibold uppercase">
                        {{ __("Trusted and loved by 100+ tech first teams") }}
                    </p>
                    <div class="flex flex-wrap -m-4 md:pb-20">

                        <div class="w-auto p-4">
                            <img class="h-10" src="{{ theme_public_asset('logos/brands/brand_9.png') }}" alt="">
                        </div>
                        <div class="w-auto p-4">
                            <img class="h-10" src="{{ theme_public_asset('logos/brands/brand_2.png') }}" alt="">
                        </div>
                        <div class="w-auto p-4">
                            <img class="h-10" src="{{ theme_public_asset('logos/brands/brand_3.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2 p-8">
                    <div class="relative mx-auto md:mr-0 max-w-max">
                      <img class="absolute z-10 -left-14 -top-12 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/circle3-yellow.svg') }}" alt="">
                      <img class="absolute z-10 -right-7 -bottom-8 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/dots3-blue.svg') }}" alt="">
                      <img class="relative rounded-7xl rounded-[50]" src="{{ theme_public_asset('images/headers/header.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 md:pb-32 bg-white overflow-hidden" id="features" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <h2 class="mb-10 text-6xl md:text-7xl xl:text-8xl font-bold font-heading text-center tracking-px-n leading-none">
            {{ __("Supercharge Your Social Media Workflow with AI") }}
        </h2>
        <p class="mb-20 text-xl text-center text-gray-500 font-medium leading-relaxed max-w-2xl mx-auto">
            {{ __("Automate, analyze, and collaborate across all your channels. Unlock your team’s full creative potential with our Platform.") }}
        </p>
        <div class="flex flex-wrap -m-16 md:-m-3">
            <!-- 1. Team Member Collaboration -->
            <div class="w-full md:w-1/3 p-16 md:p-3">
                <div class="px-10 pt-11 text-center bg-gray-100 h-96 rounded-4xl">
                    <h3 class="mb-3 text-xl font-bold font-heading leading-normal">
                        {{ __("Team Member Collaboration") }}
                    </h3>
                    <p class="mb-10 text-gray-600 font-medium leading-relaxed">
                        {{ __("Add unlimited team members, assign roles, and co-create content together in real time. Keep everyone in sync, on every campaign.") }}
                    </p>
                    <img class="mx-auto w-72 h-72 object-cover rounded-3xl shadow-3xl transform hover:translate-y-3 transition ease-in-out duration-1000"
                             src="{{ theme_public_asset('images/features/peoples.png') }}" alt="">
                </div>
            </div>
            <!-- 2. Smart Analytics & Reports -->
            <div class="w-full md:w-1/3 p-16 md:p-3">
                <div class="px-10 pt-11 text-center bg-gray-100 h-96 rounded-4xl">
                    <h3 class="mb-3 text-xl font-bold font-heading leading-normal">
                        {{ __("Smart Analytics & Reports") }}
                    </h3>
                    <p class="mb-10 text-gray-600 font-medium leading-relaxed">
                        {{ __("Track performance, discover trends, and get instant, AI-driven insights with beautiful visual charts. Make smarter, faster decisions.") }}
                    </p>
                    <img class="mx-auto w-72 h-72 object-cover rounded-3xl shadow-3xl transform hover:translate-y-3 transition ease-in-out duration-1000"
                             src="{{ theme_public_asset('images/features/reports.png') }}" alt="">
                </div>
            </div>
            <!-- 3. AI-powered suggestions -->
            <div class="w-full md:w-1/3 p-16 md:p-3">
                <div class="px-10 pt-11 text-center bg-gray-100 h-96 rounded-4xl">
                    <h3 class="mb-3 text-xl font-bold font-heading leading-normal">
                        {{ __("AI-powered Suggestions") }}
                    </h3>
                    <p class="mb-10 text-gray-600 font-medium leading-relaxed">
                        {{ __("Increasingly, composer features include AI writing assistants to help generate captions, suggest improvements to your text.") }}
                    </p>
                    <img class="mx-auto w-72 h-72 object-cover rounded-3xl shadow-3xl transform hover:translate-y-3 transition ease-in-out duration-1000"
                             src="{{ theme_public_asset('images/features/users.png') }}" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-40 md:pb-32 bg-white overflow-hidden" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap items-center -m-8">
            <div class="w-full md:w-1/2 p-8">
                <h2 class="mb-9 text-6xl md:text-8xl xl:text-10xl font-bold font-heading tracking-px-n leading-none">
                    {{ __("Automate Social Posting with AI") }}
                </h2>
                <p class="mb-10 text-lg text-gray-900 font-medium leading-relaxed md:max-w-md">
                    {{ __("Let AI create, schedule, and publish your content to all social channels automatically. Focus on your business, while our Platform keeps your online presence active 24/7 – even while you sleep.") }}
                </p>
                <div class="mb-11 md:inline-block rounded-xl md:shadow-4xl">
                    <a href="{{ route("login") }}" class="py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                        {{ __("Start AI Publishing Now") }}
                    </a>
                </div>
                <div class="flex flex-wrap -m-2">
                    <div class="w-auto p-2">
                        <img class="h-10" src="{{ theme_public_asset('images/features/ai-bot.png') }}" alt="AI Bot">
                    </div>
                    <div class="flex-1 p-2">
                        <p class="text-gray-600 font-medium md:max-w-sm">
                            {{ __("No more manual posting! Instantly generate, schedule, and publish engaging posts across Facebook, Instagram, X, TikTok, and more with a single click — all powered by smart AI.") }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-8">
                <div class="relative mx-auto md:mr-0 max-w-max">
                  <img class="absolute z-10 -left-14 -top-12 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/circle3-yellow.svg') }}" alt="">
                  <img class="absolute z-10 -right-7 -bottom-8 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/dots3-blue.svg') }}" alt="">
                  <img class="relative rounded-7xl rounded-[50]" src="{{ theme_public_asset('images/features/feature-demo-1.png') }}" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 md:pb-32 bg-white overflow-hidden" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap xl:items-center -m-8">
            <div class="w-full md:w-1/2 py-8 pl-8 pr-16">
                <div class="relative mx-auto md:mr-0 max-w-max">
                  <img class="absolute z-10 -left-14 -top-12 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/circle3-yellow.svg') }}" alt="">
                  <img class="absolute z-10 -right-7 -bottom-8 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/dots3-blue.svg') }}" alt="">
                  <img class="relative rounded-7xl rounded-[50]" src="{{ theme_public_asset('images/features/feature-demo-2.png') }}" alt="">
                </div>
            </div>
            <div class="w-full md:w-1/2 p-8">
                <div class="md:max-w-xl">
                    <p class="mb-2 text-sm text-gray-600 font-semibold uppercase tracking-px">
                        🚀 {{ __("Bulk Post Feature") }}
                    </p>
                    <h2 class="mb-10 text-6xl md:text-7xl font-bold font-heading tracking-px-n leading-tight">
                        {{ __("Publish Everywhere, All at Once.") }}
                    </h2>
                    <p class="mb-10 text-lg text-gray-900 font-medium leading-relaxed md:max-w-md">
                        {{ __("Create and schedule dozens of posts to multiple social networks in a single workflow. Plan campaigns, automate your posting calendar, and reach your audience on every channel—faster than ever before.") }}
                    </p>
                    <div class="flex flex-wrap mb-5 -m-8">
                        <div class="w-full md:w-1/2 p-8">
                            <div class="md:max-w-xs">
                                <div class="flex flex-wrap -m-2">
                                    <div class="w-auto p-2">
                                        <!-- Bulk Post Icon -->
                                        <svg class="mt-1" width="26" height="26" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="4" width="18" height="4" rx="2" stroke="#4F46E5" stroke-width="2"/>
                                            <rect x="3" y="10" width="18" height="4" rx="2" stroke="#4F46E5" stroke-width="2"/>
                                            <rect x="3" y="16" width="18" height="4" rx="2" stroke="#4F46E5" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 p-2">
                                        <h3 class="mb-2 text-xl font-semibold leading-normal">
                                            {{ __("Bulk Scheduling") }}
                                        </h3>
                                        <p class="text-gray-600 font-medium leading-relaxed">
                                            {{ __("Upload or create multiple posts at once and schedule them across all your social channels in just a few clicks.") }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2 p-8">
                            <div class="md:max-w-xs">
                                <div class="flex flex-wrap -m-2">
                                    <div class="w-auto p-2">
                                        <!-- Calendar/Automation Icon -->
                                        <svg class="mt-1" width="26" height="26" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="5" width="16" height="15" rx="4" stroke="#4F46E5" stroke-width="2"/>
                                            <path d="M8 2v4M16 2v4M4 10h16" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 p-2">
                                        <h3 class="mb-2 text-xl font-semibold leading-normal">
                                            {{ __("Smart Automation") }}
                                        </h3>
                                        <p class="text-gray-600 font-medium leading-relaxed">
                                            {{ __("Plan campaigns in advance and let it handles publishing on preferred dates and times automatically.") }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="md:inline-block">
                        <a href="{{ route("login") }}" class="py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                            {{ __("Start Bulk Posting") }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 md:pb-32 bg-white overflow-hidden" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap items-center -m-8">
            <div class="w-full md:w-1/2 p-8">
                <h2 class="mb-9 text-6xl md:text-8xl xl:text-10xl font-bold font-heading tracking-px-n leading-none">
                    {{ __("Auto-Post from RSS Feeds to Socials") }}
                </h2>
                <p class="mb-10 text-lg text-gray-900 font-medium leading-relaxed md:max-w-md">
                    {{ __("Connect any RSS feed to automatically fetch, schedule, and publish fresh content across all your social channels. Effortlessly keep your profiles active with news, blog posts, or updates from any website – always up-to-date, even when you're offline.") }}
                </p>
                <div class="mb-11 md:inline-block rounded-xl md:shadow-4xl">
                    <a href="{{ route("login") }}" class="py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                        {{ __("Try RSS Auto-Posting Now") }}
                    </a>
                </div>
                <div class="flex flex-wrap -m-2">
                    <div class="w-auto p-2">
                        <img class="h-10" src="{{ theme_public_asset('images/features/rss-feed.png') }}" alt="RSS Feed">
                    </div>
                    <div class="flex-1 p-2">
                        <p class="text-gray-600 font-medium md:max-w-sm">
                            {{ __("Save time and never miss an update: Instantly share the latest articles, podcasts, or videos from any RSS source to Facebook, Instagram, X, TikTok, and more — hands-free and always on schedule.") }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-8">
                <div class="relative mx-auto md:mr-0 max-w-max">
                  <img class="absolute z-10 -left-14 -top-12 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/circle3-yellow.svg') }}" alt="">
                  <img class="absolute z-10 -right-7 -bottom-8 w-28 md:w-auto" src="{{ theme_public_asset('images/headers/dots3-blue.svg') }}" alt="">
                  <img class="relative rounded-7xl rounded-[50]" src="{{ theme_public_asset('images/features/feature-demo-3.png') }}" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 md:pb-32 bg-white" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="md:max-w-4xl mb-28 mx-auto text-center">
            <span class="inline-block py-px px-2 mb-4 text-xs leading-5 text-indigo-500 bg-indigo-100 font-medium uppercase rounded-full shadow-sm">
                {{ __("Power Features") }}
            </span>
            <h1 class="mb-9 text-6xl md:text-8xl xl:text-10xl font-bold font-heading tracking-px-n leading-none">
                {{ __("Automate. Analyze. Achieve.") }}
            </h1>
            <p class="text-lg md:text-xl text-coolGray-500 font-medium">
                {{ __("Automate your routine, track what matters, and focus on what drives real growth — all in one intuitive platform.") }}
            </p>
        </div>
        <div class="flex flex-wrap -mx-4">
            <!-- 1. Unified Calendar -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-blue-500 bg-blue-100 rounded-4xl text-3xl">
                        <i class="fal fa-calendar-alt"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Unified Calendar") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("See all your scheduled and published content across every channel in a single, intuitive calendar.") }}</p>
                </div>
            </div>
            <!-- 2. Multi-Account Management -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-lime-500 bg-lime-100 rounded-4xl text-3xl">
                        <i class="fal fa-layer-group"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Accounts Management") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Easily manage and switch between multiple brands, clients, or social profiles from one dashboard.") }}</p>
                </div>
            </div>
            <!-- 3. AI Templates -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-violet-500 bg-violet-100 rounded-4xl text-3xl">
                        <i class="fal fa-magic"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("AI Templates") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Ready-made AI templates to instantly generate creative content ideas tailored to your goals.") }}</p>
                </div>
            </div>
            <!-- 3. Drag-and-Drop Scheduling -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-teal-500 bg-teal-100 rounded-4xl text-3xl">
                        <i class="fal fa-arrows-alt"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Drag-and-Drop Scheduling") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Quickly plan, move, or reschedule your posts with simple drag-and-drop gestures.") }}</p>
                </div>
            </div>
            <!-- 4. AI Content Generator -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-violet-500 bg-violet-100 rounded-4xl text-3xl">
                        <i class="fal fa-robot"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("AI Content Generator") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Instantly create captions, posts, and creative ideas using advanced AI models, tailored to your brand.") }}</p>
                </div>
            </div>
            <!-- 5. Smart Analytics Dashboard -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-orange-500 bg-orange-100 rounded-4xl text-3xl">
                        <i class="fal fa-chart-line"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Smart Analytics ") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Get real-time insights and actionable reports to measure your social performance and growth.") }}</p>
                </div>
            </div>
            <!-- 6. Automated Publishing -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-green-500 bg-green-100 rounded-4xl text-3xl">
                        <i class="fal fa-clock"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Automated Publishing") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Set your content to auto-publish at the best times, so you never miss peak engagement hours.") }}</p>
                </div>
            </div>
            <!-- 7. Team Collaboration Tools -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-pink-500 bg-pink-100 rounded-4xl text-3xl">
                        <i class="fal fa-users"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Team Collaboration Tools") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Invite your team, assign roles, comment, review, and approve content seamlessly within the platform.") }}</p>
                </div>
            </div>
            <!-- 8. Integrated Media Library -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-indigo-500 bg-indigo-100 rounded-4xl text-3xl">
                        <i class="fal fa-photo-video"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Integrated Media Library") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Upload, organize, edit, and reuse all your images and videos in one secure place.") }}</p>
                </div>
            </div>
            <!-- 9. Watermark & Branding -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-purple-500 bg-purple-100 rounded-4xl text-3xl">
                        <i class="fal fa-badge-check"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Watermark & Branding") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Automatically add your logo or watermark to every post for consistent, professional branding.") }}</p>
                </div>
            </div>
            <!-- 10. Approval Workflow -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-cyan-500 bg-cyan-100 rounded-4xl text-3xl">
                        <i class="fal fa-tasks"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Approval Workflow") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Draft, review, and approve posts before they go live, ensuring quality and compliance every time.") }}</p>
                </div>
            </div>
            <!-- 11. Link Shortener & Tracking -->
            <div class="w-full md:w-1/2 lg:w-1/3 px-10 mb-12">
                <div class="h-full p-8 text-center bg-gray-50 rounded-4xl border-f5 border hover:shadow-xl hover:border-xl transition duration-200">
                    <div class="inline-flex h-16 w-16 mb-6 mx-auto items-center justify-center text-red-500 bg-red-100 rounded-4xl text-3xl">
                        <i class="fal fa-link"></i>
                    </div>
                    <h3 class="mb-4 text-xl md:text-2xl leading-tight font-bold">{{ __("Link Shortener & Tracking") }}</h3>
                    <p class="text-coolGray-500 font-medium">{{ __("Shorten URLs and track clicks to optimize your campaigns and boost engagement.") }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pt-24 pb-36 bg-white overflow-hidden">
    <div class="container px-4 mx-auto">
        <h2 class="mb-7 text-6xl md:text-8xl xl:text-10xl text-center font-bold font-heading tracking-px-n leading-none">
            {{ __("Let’s see how it works") }}
        </h2>
        <p class="mb-20 font-sans text-lg text-gray-900 text-center md:max-w-lg mx-auto">
            {{ __("Get started in minutes. Manage all your social media, automate posting, and grow your brand with ease.") }}
        </p>
        <div class="relative bg-no-repeat bg-center bg-cover bg-fixed overflow-hidden rounded-4xl"
             style="height: 688px; background-image: url('{{ theme_public_asset('images/how-it-works/bg.jpg') }}');">
             <div class="absolute inset-0 pointer-events-none"
                 style="background: linear-gradient(to right, rgba(0,0,0,0.80) 0%, rgba(0,0,0,0.08) 55%, rgba(0,0,0,0.00) 100%);">
            </div>
            <div class="absolute top-0 left-0 p-14 md:p-20 md:pb-0 overflow-y-auto h-full">
                <div class="flex flex-wrap">

                    <!-- Step 1 -->
                    <div class="w-full">
                        <div class="flex flex-wrap -m-3">
                            <div class="w-auto p-3">
                                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="17.5" cy="17.5" r="17.5" fill="#4F46E5"></circle>
                                    <path d="M11.667 18.3333L15.0003 21.6666L23.3337 13.3333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <img class="mx-auto" src="{{ theme_public_asset('images/how-it-works/line3.svg') }}" alt="">
                            </div>
                            <div class="flex-1 p-3">
                                <div class="md:max-w-xs pb-8">
                                    <p class="mb-5 text-sm text-gray-400 font-semibold uppercase tracking-px">
                                        {{ __("Step 1") }}
                                    </p>
                                    <h3 class="mb-2 text-xl text-white font-bold leading-normal">
                                        {{ __("Choose Your Plan") }}
                                    </h3>
                                    <p class="text-gray-300 font-medium leading-relaxed">
                                        {{ __("Pick a package that fits your needs—start with Free or unlock Pro for advanced features.") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="w-full">
                        <div class="flex flex-wrap -m-3">
                            <div class="w-auto p-3">
                                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="17.5" cy="17.5" r="17.5" fill="#4F46E5"></circle>
                                    <path d="M11.667 18.3333L15.0003 21.6666L23.3337 13.3333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <img class="mx-auto" src="{{ theme_public_asset('images/how-it-works/line3.svg') }}" alt="">
                            </div>
                            <div class="flex-1 p-3">
                                <div class="md:max-w-xs pb-8">
                                    <p class="mb-5 text-sm text-gray-400 font-semibold uppercase tracking-px">
                                        {{ __("Step 2") }}
                                    </p>
                                    <h3 class="mb-2 text-xl text-white font-bold leading-normal">
                                        {{ __("Connect Your Channels") }}
                                    </h3>
                                    <p class="text-gray-300 font-medium leading-relaxed">
                                        {{ __("Link all your social accounts—Facebook, Instagram, X (Twitter), TikTok, YouTube, and more—in just a few clicks.") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="w-full">
                        <div class="flex flex-wrap -m-3">
                            <div class="w-auto p-3">
                                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="17.5" cy="17.5" r="17" stroke="#CBD5E1"></circle>
                                    <path d="M11.667 18.3333L15.0003 21.6666L23.3337 13.3333" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="flex-1 p-3">
                                <div class="md:max-w-xs pb-8">
                                    <p class="mb-5 text-sm text-gray-400 font-semibold uppercase tracking-px">
                                        {{ __("Step 3") }}
                                    </p>
                                    <h3 class="mb-2 text-xl text-white font-bold leading-normal">
                                        {{ __("Start Automating & Growing") }}
                                    </h3>
                                    <p class="text-gray-300 font-medium leading-relaxed">
                                        {{ __("Schedule, publish, and analyze content—all in one dashboard, powered by AI automation and actionable analytics.") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Steps -->
                </div>
            </div>
        </div>
    </div>
</section>

<section class="relative pt-24 pb-32 bg-white overflow-hidden">
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <img class="max-w-full max-h-full" src="{{ theme_public_asset('images/testimonials/gradient3.svg') }}" alt="">
    </div>
    <div class="relative z-10 container px-4 mx-auto">
        <div class="flex flex-wrap justify-between items-end -m-2 mb-12">
            <div class="w-auto p-2">
                <h2 class="text-6xl md:text-7xl font-bold font-heading tracking-px-n leading-tight">
                    {{ __("What our clients are saying") }}
                </h2>
            </div>
        </div>
        <div class="flex flex-wrap -m-2">
            <div class="w-full md:w-1/2 lg:w-1/4 p-2">
                <div class="px-8 py-6 h-full bg-white bg-opacity-80 rounded-3xl">
                    <div class="flex flex-col justify-between h-full">
                        <div class="mb-7 block">
                            <div class="flex flex-wrap -m-0.5 mb-6">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="w-auto p-0.5">
                                        <svg width="19" height="18" viewbox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.30769 0L12.1838 5.82662L18.6154 6.76111L13.9615 11.2977L15.0598 17.7032L9.30769 14.6801L3.55554 17.7032L4.65385 11.2977L0 6.76111L6.43162 5.82662L9.30769 0Z" fill="#F59E0B"></path>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            <h3 class="mb-6 text-lg font-bold font-heading">
                                {{ __("“Scheduling is incredibly easy!”") }}
                            </h3>
                            <p class="text-lg font-medium">
                                {{ __("I can drag and drop posts on the calendar and instantly see my entire week at a glance. Planning campaigns has never been this smooth.") }}
                            </p>
                        </div>
                        <div class="block">
                            <p class="font-bold">{{ __("Sarah Johnson - Social Media Manager") }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/4 p-2">
                <div class="px-8 py-6 h-full bg-white bg-opacity-80 rounded-3xl">
                    <div class="flex flex-col justify-between h-full">
                        <div class="mb-7 block">
                            <div class="flex flex-wrap -m-0.5 mb-6">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="w-auto p-0.5">
                                        <svg width="19" height="18" viewbox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.30769 0L12.1838 5.82662L18.6154 6.76111L13.9615 11.2977L15.0598 17.7032L9.30769 14.6801L3.55554 17.7032L4.65385 11.2977L0 6.76111L6.43162 5.82662L9.30769 0Z" fill="#F59E0B"></path>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            <h3 class="mb-6 text-lg font-bold font-heading">
                                {{ __("“Perfect for managing multiple accounts”") }}
                            </h3>
                            <p class="text-lg font-medium">
                                {{ __("It’s so convenient to handle all my brands and channels from a single dashboard. No more endless tab-switching!") }}
                            </p>
                        </div>
                        <div class="block">
                            <p class="font-bold">{{ __("Michael Lee - Digital Marketer") }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/4 p-2">
                <div class="px-8 py-6 h-full bg-white bg-opacity-80 rounded-3xl">
                    <div class="flex flex-col justify-between h-full">
                        <div class="mb-7 block">
                            <div class="flex flex-wrap -m-0.5 mb-6">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="w-auto p-0.5">
                                        <svg width="19" height="18" viewbox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.30769 0L12.1838 5.82662L18.6154 6.76111L13.9615 11.2977L15.0598 17.7032L9.30769 14.6801L3.55554 17.7032L4.65385 11.2977L0 6.76111L6.43162 5.82662L9.30769 0Z" fill="#F59E0B"></path>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            <h3 class="mb-6 text-lg font-bold font-heading">
                                {{ __("“Insightful analytics, clean reports”") }}
                            </h3>
                            <p class="text-lg font-medium">
                                {{ __("The analytics dashboard makes it simple to track what’s working and what needs improvement. I love exporting reports for my team.") }}
                            </p>
                        </div>
                        <div class="block">
                            <p class="font-bold">{{ __("Emily Carter - Content Creator") }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/4 p-2">
                <div class="px-8 py-6 h-full bg-white bg-opacity-80 rounded-3xl">
                    <div class="flex flex-col justify-between h-full">
                        <div class="mb-7 block">
                            <div class="flex flex-wrap -m-0.5 mb-6">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="w-auto p-0.5">
                                        <svg width="19" height="18" viewbox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.30769 0L12.1838 5.82662L18.6154 6.76111L13.9615 11.2977L15.0598 17.7032L9.30769 14.6801L3.55554 17.7032L4.65385 11.2977L0 6.76111L6.43162 5.82662L9.30769 0Z" fill="#F59E0B"></path>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            <h3 class="mb-6 text-lg font-bold font-heading">
                                {{ __("“Excellent collaboration features”") }}
                            </h3>
                            <p class="text-lg font-medium">
                                {{ __("Feedback and approvals are built right in, so my team stays on the same page and campaigns launch faster.") }}
                            </p>
                        </div>
                        <div class="block">
                            <p class="font-bold">{{ __("James Smith - Marketing Lead") }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include("partials.pricing")
@include("partials.faqs")
@include("partials.home-blog")



