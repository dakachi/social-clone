<div class="row g-3">
    @forelse($medias as $media)
        <div class="col-6 col-md-4 col-lg-3">
            <label class="card hp-100 shadow-sm b-r-20" for="file_{{ md5($media['full'] ?? $media['thumbnail'] ?? uniqid()) }}">
                <div class="position-absolute r-15 t-15 zIndex-1">
                    <div class="form-check">
                        @if(!empty($media['full']))
                        <input class="form-check-input" name="files[]" type="checkbox" value="{{ $media['full'] }}" id="file_{{ md5($media['full']) }}">
                        @endif
                    </div>
                </div>
                <div class="img-wrap">
                    @if(!empty($media['thumbnail']))
                        <a href="{{ $media['link'] ?? '#' }}" target="_blank">
                            <img src="{{ $media['thumbnail'] }}" class="card-img b-r-20" alt="media from {{ $media['source'] ?? '' }}">
                        </a>
                    @else
                        <div class="w-100 text-center text-muted py-5">
                            {{ __('No preview') }}
                        </div>
                    @endif
                </div>
            </label>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                <span class="fs-70 mb-3 text-primary">
                    <i class="fa-light fa-image-polaroid"></i>
                </span>
                <div class="fw-semibold fs-5 mb-2 text-gray-800">
                    {{ __('No media found') }}
                </div>
            </div>
        </div>
    @endforelse
</div>