<div class="modal fade" id="pubishingPreviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="{{ module_url('save') }}" data-call-success="Main.closeModal('pubishingPreviewModal'); Main.ajaxScroll(true);">
            <input type="text" class="d-none" name="type" value="0">
            <div class="modal-header">
                <h1 class="modal-title fs-16">{{ __("Preview") }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                @php
                    $postType = 'media';
                    $caption = '';
                    $medias = [];
                    $link = '';

                    if ($post) {
                        $postType = $post->type ?? 'media';
                        $postData = json_decode($post->data, false);
                        $caption = $postData->caption ?? '';
                        $medias = $postData->medias ?? [];
                        $link = $postData->link ?? '';
                    }
                @endphp

                <div class="d-none">
                    <input type="hidden" class="preview-post-type" value="{{ $postType }}">

                    @if ($post && $post->account)
                        <input type="hidden" class="preview-profile"
                            data-social-network="{{ $post->account->social_network ?? '' }}"
                            data-avatar="{{ $post->account->avatar ? Media::url($post->account->avatar) : '' }}"
                            data-name="{{ $post->account->name ?? '' }}"
                            data-username="{{ $post->account->username ?? '' }}"
                            data-link="{{ $post->account->link ?? '' }}">
                    @endif

                    <div class="preview-list-medias">
                        @foreach ($medias as $media)
                            <img src="{{ Media::url($media) }}">
                        @endforeach
                    </div>

                    <textarea class="form-control input-emoji fw-4 border" name="caption" placeholder="{{ __("Enter caption") }}">{{ $caption }}</textarea>
                </div>

                @php
                    $module = strtolower($post->module ?? '');
                    $view = $module ? $module.'::preview' : null;
                @endphp

                @if($view && view()->exists($view))
                    <div class="cpvx" data-social-network="{{ $post->social_network ?? '' }}">
                        @include($view)
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    Main.init(false);
    AppPubishing.init(false);
    Files.init(false);
</script>