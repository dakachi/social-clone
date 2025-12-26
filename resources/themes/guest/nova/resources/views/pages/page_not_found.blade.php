<section class="relative py-40 bg-white overflow-hidden">
    <img class="absolute left-1/2 bottom-0 transform -translate-x-1/2" src="{{ theme_public_asset('images/http-codes/gradient2.svg') }}" alt="">
    <div class="relative z-10 container px-4 mx-auto">
        <img class="w-full max-w-sm mb-16 mx-auto transform hover:scale-105 transition ease-in-out duration-1000" src="{{ theme_public_asset('images/http-codes/illustration2.png') }}" alt="">
        <h3 class="mb-4 text-4xl text-center font-bold font-heading leading-tight">{{ __("Something went wrong") }}</h3>
        <p class="mb-11 text-gray-600 text-center font-medium leading-relaxed md:max-w-md mx-auto">
            {{ __("Lorem ipsum dolor sit amet consectrtur. Volutpat tempor condimentum vitae vel pur pellentes.") }}
        </p>
        <div class="md:max-w-max mx-auto">
            <button class="inline-flex flex-wrap justify-center items-center py-4 px-6 w-full text-white font-semibold rounded-xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                <svg class="mr-2.5" width="16" height="16" viewbox="0 0 16 16" fill="none"><path d="M6.66667 12.6666L2 7.99998M2 7.99998L6.66667 3.33331M2 7.99998L14 7.99998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                <span>{{ __("Go Back to Homepage") }}</span>
            </button>
        </div>
    </div>
</section>