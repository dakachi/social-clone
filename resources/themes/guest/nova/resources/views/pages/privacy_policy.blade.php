<div class="min-h-screen py-12 px-4 bg-blueGray-50 py-24">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-6xl md:text-7xl font-bold font-heading leading-none text-gray-900 mb-4">{{ __("Privacy Policy") }}</h1>
            <p class="text-lg text-gray-600 font-medium">{{ __('We are committed to safeguarding your personal information.') }}</p>
        </div>
        <!-- Main Content -->
        <div class="bg-white rounded-4xl shadow-sm p-8">
            <div class="prose prose-content prose-lg max-w-none">
                {!! htmlspecialchars_decode(get_option('privacy_policy')) !!}
            </div>
        </div>
    </div>
</div>