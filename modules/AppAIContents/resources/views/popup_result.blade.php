<div class="d-flex flex-column flex-fill hp-100">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom position-relative zIndex-3">
        <div class="fs-16 fw-5">{{ __("Result") }}</div>
        <div class="d-block d-lg-none">
            <div class="btn btn-icon btn-sm btn-light btn-hover-danger b-r-50 a-rotate closeAIResult">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 fs-14 hp-100 position-relative">
        
        @if(!empty($result))

            @foreach($result as $value)

                <form class="actionForm" action="{{ url_app("captions/save") }}" method="POST">
                    <input type="text" class="d-none" name="name" value="AI Caption">
                    <input type="text" class="d-none" name="type" value="2">
                    <textarea name="content" class="d-none">{{ $value }}</textarea>

                    <div class="card border-gray-300 shadow-none mb-3">
                        <div class="card-body">{!! nl2br($value) !!}</div>
                        <div class="card-footer d-flex p-0 text-center">
                            <button class="bg-transparent bbl-r-10 flex-fill border-end p-3 bg-hover-dark text-hover-light">{{ __("Save to caption") }}</button>
                            <a href="javascript:void(0);" class="flex-fill p-3 bbr-r-10 bg-hover-primary text-hover-light addToField" data-field=".post-caption" data-refresh="1" data-content="{{ $value }}" onclick="Main.closeModal('aiContentModal');">
                                {{ __("Use this content") }}
                            </a>
                        </div>
                    </div>
                </form>

            @endforeach

        @endif

    </div>
</div>