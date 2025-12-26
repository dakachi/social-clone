<div class="tiktok-card border rounded bg-black text-white overflow-hidden position-relative">
    
    <!-- Video -->
    <div class="position-relative">
        <div class="tiktok-video-wrapper position-relative h-550">

            <!-- Play button center -->
            <button class="position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow size-60">
                <i class="fas fa-play fs-24"></i>
            </button>
        </div>
    </div>

    <!-- User info -->
    <div class="d-flex align-items-center p-3">
        <img src="{{ theme_public_asset('img/default.png') }}" class="rounded-circle me-2 cpv-avatar" width="32" height="32">
        <div>
            <div class="fw-bold fs-14 cpv-name">{{ __("Your name") }}</div>
            <div class="fs-12 text-white">{{ date("M j") }}</div>
        </div>
    </div>

    <!-- Caption -->
    <div class="px-3 px-1">
        <div class="cpv-text fs-14 mb-3 text-truncate-5"></div>
        <div class="fs-12 text-gray-600 mb-2">🎵 {{ __('Original sound - TikTok') }}</div>
    </div>

    <!-- Actions -->
    <div class="position-absolute top-50 end-0 translate-middle-y me-2">
        <div class="text-center mb-3">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-heart fs-18"></i></div>
            <div class="fs-12">120</div>
        </div>
        <div class="text-center mb-3">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-comment-dots fs-18"></i></div>
            <div class="fs-12">45</div>
        </div>
        <div class="text-center">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-share fs-18"></i></div>
            <div class="fs-12">10</div>
        </div>
    </div>
</div>