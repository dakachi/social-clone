<div class="offcanvas offcanvas-end border-start" data-bs-backdrop="false" tabindex="-1" id="getCaptionOffCanvas">
    <div class="offcanvas-header border-bottom pt-23 pb-22">
        <div class="fw-5">{{ __("List caption") }}</div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <form method="POST">
            <div class="ajax-scroll-9" data-url="{{ route("app.captions.popup_list") }}" data-resp=".caption-list" data-scroll=".offcanvas-body">

                <div class="p-3 border-bottom">
                    <div class="form-control">
                        <span class="btn btn-icon">
                            <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="ajax-scroll-filter-9" name="keyword" placeholder="Search" type="text">
                    </div>
                </div>

                <div class="p-4">
                    <div class="row caption-list">
                    
                    </div>
                </div>

                <div class="pb-30 ajax-scroll-loading d-none">
                    <div class="app-loading mx-auto mt-100 pl-0 pr-0">
                        <div></div>   
                        <div></div>    
                        <div></div>    
                        <div></div>    
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
	Main.ajaxScroll(true, 9);
    Main.dataScroll(9);
    Main.ajaxScrollActions(9);
</script>