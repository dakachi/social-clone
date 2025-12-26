<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                {{ __("Facebook") }}
            </div>
        </div>
        <div class="card-body px-3">
            <div class="mb-0">
                <label class="form-label">{{ __('Post To') }}</label>
                <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="options[fb_type]" value="feed" id="fb_type_1" checked>
                        <label class="form-check-label mt-1" for="fb_type_1">
                            {{ __('Feed') }}
                        </label>
                    </div>
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="options[fb_type]" value="reels" id="fb_type_2">
                        <label class="form-check-label mt-1" for="fb_type_2">
                            {{ __('Reels') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>