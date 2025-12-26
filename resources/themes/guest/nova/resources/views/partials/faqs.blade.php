@php
    $faqs = Home::getFaqs();
@endphp

<section class="relative pt-15 pb-28 bg-blueGray-50 overflow-hidden">
    <img class="absolute bottom-0 left-1/2 transform -translate-x-1/2" src="{{ theme_public_asset('images/faqs/gradient.svg') }}" alt="">
    <div class="relative z-10 container px-4 mx-auto">
        <div class="md:max-w-4xl mx-auto">
            <p class="mb-7 text-sm text-indigo-600 text-center font-semibold uppercase tracking-px">
                {{ __("Have any questions?") }}
            </p>
            <h2 class="mb-16 text-6xl md:text-8xl xl:text-10xl text-center font-bold font-heading tracking-px-n leading-none">
                {{ __("Frequently Asked Questions") }}
            </h2>
            <div class="mb-11 flex flex-wrap -m-1"
                 x-data="{ open: null }"
            >
                @foreach($faqs as $faq)
                    <div class="w-full p-1">
                        <a
                            href="#"
                            x-on:click.prevent="open === {{ $faq->id }} ? open = null : open = {{ $faq->id }}"
                        >
                            <div :class="{ 'border-indigo-600': open === {{ $faq->id }} }"
                                class="py-7 px-8 bg-white bg-opacity-60 border-2 border-gray-200 rounded-2xl shadow-10xl"
                            >
                                <div class="flex flex-wrap justify-between -m-2">
                                    <div class="flex-1 p-2">
                                        <h3 class="text-lg font-semibold leading-normal">
                                            {{ $faq->title }}
                                        </h3>
                                        <div
                                            x-ref="container_{{ $faq->id }}"
                                            :style="open === {{ $faq->id }} ? 'height: ' + $refs['container_{{ $faq->id }}'].scrollHeight + 'px' : ''"
                                            class="overflow-hidden h-0 duration-500"
                                        >
                                            <p class="mt-4 text-gray-600 font-medium">
                                                {!! $faq->content !!}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="w-auto p-2">
                                        <div :class="{ 'hidden': open === {{ $faq->id }} }">
                                            <!-- chevron down -->
                                            <svg class="relative top-1" width="18" height="18" viewbox="0 0 18 18" fill="none"><path d="M14.25 6.75L9 12L3.75 6.75" stroke="#18181B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </div>
                                        <div :class="{ 'hidden': open !== {{ $faq->id }} }" class="hidden">
                                            <!-- chevron up -->
                                            <svg class="relative top-1" width="20" height="20" viewbox="0 0 20 20" fill="none"><path d="M4.16732 12.5L10.0007 6.66667L15.834 12.5" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="text-gray-600 text-center font-medium">
                <span>{{ __("Still have any questions?") }}</span>
                <a class="font-semibold text-indigo-600 hover:text-indigo-700" href="{{ url('contact') }}">{{ __("Contact us") }}</a>
            </p>
        </div>
    </div>
</section>