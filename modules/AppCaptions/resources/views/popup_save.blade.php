<div class="modal fade" id="saveCaptionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('saveCaptionModal');">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Save caption") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-0">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input placeholder="{{ __('Enter caption name') }}" class="form-control" name="name" id="name" type="text" value="">
	                     	<textarea class="form-control caption-content d-none" name="content"></textarea>
		                </div>
 					</div>
 				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="submit" class="btn btn-dark">{{ __('Save changes') }}</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	if ($(".post-caption").length > 0){
        var text = $(".post-caption")[0].emojioneArea.getText();
        $(".caption-content").val(text);
    }
</script>