@php
    $blogs = Home::getRecentBlogs(null, 2);
@endphp

<section class="py-24 md:pb-32 bg-white" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap -m-8">
            <div class="w-full md:w-5/12 p-8">
                <div class="flex flex-col justify-between h-full">
                    <div class="mb-8">
                        <h2 class="mb-5 text-6xl md:text-7xl font-bold font-heading tracking-px-n leading-tight">
                            {{ __("Our Latest News and Articles") }}
                        </h2>
                        <p class="text-gray-600 font-medium leading-relaxed">
                            {{ __("Read the latest stories, in-depth tutorials, expert interviews, and product updates designed to help you grow your business, master new skills, and stay ahead in the digital world.") }}
                        </p>
                    </div>
                    <a class="inline-flex items-center text-indigo-600 hover:text-indigo-700 leading-normal" href="{{ url("blogs") }}">
                        <span class="mr-2 font-semibold">{{ __("See all articles") }}</span>
                        <svg width="18" height="18" viewbox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 3.75L15.75 9M15.75 9L10.5 14.25M15.75 9L2.25 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </a>
                </div>
            </div>
            <div class="w-full md:flex-1 p-8">
                <div class="flex flex-wrap -m-3">
                    @foreach($blogs as $blog)
                        <div class="w-full md:w-1/2 p-3">
                            <div class="max-w-sm mx-auto">
                                <div class="mb-6 overflow-hidden rounded-xl">
                                    <img class="h-56 w-full transform hover:scale-105 transition ease-in-out duration-1000 object-cover"
                                         src="{{ !empty($blog->thumbnail) ? Media::url($blog->thumbnail) : theme_public_asset('images/blog/blog-wide.png') }}"
                                         alt="{{ $blog->title }}">
                                </div>
                                <p class="mb-4 font-sans max-w-max px-3 py-1.5 text-sm text-indigo-600 font-semibold bg-indigo-50 uppercase rounded-md">
                                    {{ $blog->category->name ?? __("Blog") }}
                                </p>
                                <a class="mb-2 inline-block hover:text-gray-800 hover:underline"
                                   href="{{ url('blogs/'.$blog->slug) }}">
                                    <h3 class="text-xl font-bold font-heading leading-normal">
                                        {{ $blog->title }}
                                    </h3>
                                </a>
                                <p class="text-gray-600 font-medium leading-relaxed">
                                    {{ $blog->desc }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

