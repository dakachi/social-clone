<div class="d-flex border-bottom p-3 align-items-center justify-content-between">
    <div class="fw-5 fs-14">{{ __("Prompt list") }}</div>
    <div>
        <a href="{{ url_app("ai-prompts/update") }}" data-offcanvas="updateAIPrompts" class="btn btn-sm btn-dark actionItem"><i class="fa-light fa-plus"></i> {{ __("Add new") }}</a>
    </div>
</div>

<div class="px-3 py-3 border-bottom">
    <div class="input-group">
        <input type="text" class="form-control bg-white search-input" data-search="search-prompt" placeholder="{{ __("Search...") }}">
        <span class="btn btn-icon btn-input min-w-55">
            <input class="form-check-input checkbox-all" type="checkbox" value="">
        </span>
    </div>
</div>

<div class="list-prompts ajax-pages max-h-600 overflow-auto" data-url="{{ route("app.ai-prompts.list") }}" data-resp=".list-prompts" data-call-success="checkedPrompts();">
    
    <div class="text-center fs-30 my-5 text-primary">
        <i class="fa-light fa-loader fa-spin"></i>
    </div>

</div>


<script>
    var checkedPromptIds = @json($prompts ?? []);
    function checkedPrompts(){
        $('input[name="prompts[]"]').each(function() {
            var value = $(this).data("id");
            if (checkedPromptIds.includes(value)) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
</script>