<div class="card mb-4 ">
    <div class="card-body p-4">
        <div class="row g-4 align-items-stretch">
            <div class="col-md-6">
                <div class="ratio ratio-1x1 bg-cover w-100 b-r-6 border position-relative"
                     style="background-image: url('{{ module_folder_url("/assets/img/bg-watermark.jpg") }}'); background-size: cover; background-position: center;">
                    <img class="watermark-mask h-auto {{ $result['position'] ?? 'lt' }}"
                         src="{{ !empty($result['mark']) ? Media::url($result['mark']) : module_folder_url("/assets/img/mark.png") }}"
                         style="
                            position: absolute;
                            opacity: {{ isset($result['opacity']) ? $result['opacity'] / 100 : 0.3 }};
                            width: {{ 60 + (isset($result['size']) ? $result['size'] : 30) * 1.3 }}px;
                            top: 0; left: 0;
                            pointer-events: none;"
                    >
                </div>
            </div>
            <div class="col-md-6 d-flex flex-column gap-1">
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('Position') }}</label>
                    <div class="watermark-positions d-flex flex-wrap gap-2 mb-2">
                        @foreach(['lt','ct','rt','lc','cc','rc','lb','cb','rb'] as $pos)
                            <div class="watermark-position-item pos-{{ $pos }} {{ ($result['position'] ?? 'lt') == $pos ? 'active' : '' }}"
                                 data-direction="{{ $pos }}" title="{{ strtoupper($pos) }}"></div>
                        @endforeach
                        <input type="hidden" class="watermark-position form-control" name="position" value="{{ $result['position'] ?? 'lt' }}">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label mb-20">{{ __("Size") }}</label>
                    <input type="range"
                           name="size"
                           class="rangeslider d-none watermark-size"
                           min="0" max="100" step="1"
                           value="{{ $result['size'] ?? 30 }}"
                           data-rangeslider data-orientation="vertical">
                </div>
                <div class="mb-4">
                    <label class="form-label mb-20">{{ __("Transparent") }}</label>
                    <input type="range"
                           name="opacity"
                           class="rangeslider d-none watermark-transparent"
                           min="0" max="100" step="1"
                           value="{{ $result['opacity'] ?? 30 }}"
                           data-orientation="vertical">
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between gap-16">
        <div class="d-flex gap-16">
            <a type="button" class="btn btn-outline btn-danger actionMultiItem" href="{{ module_url("destroy") }}" data-id="{{ $account_id }}" data-call-success="Main.ajaxPages();">
                <i class="fa-light fa-trash-can"></i>
                {{ __("Delete") }}
            </a>
            <div class="form-file">
                <label for="wa-file-upload" class="btn btn-outline btn-primary w-100">
                    <i class="fa-light fa-arrows-rotate"></i>
                    {{ __("Change Watermark") }}
                </label>
                <input id="wa-file-upload" class="d-none form-file-input" name="image" type="file" accept="image/*">
            </div>
        </div>
        <button type="submit" class="btn btn-dark">{{ __("Save Changes") }}</button>
    </div>
</div>

<script type="text/javascript">
    Watermark.init();
    setTimeout(function() {
        Main.Range();
    }, 50);
</script>
