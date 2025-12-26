<div class="modal fade" id="captionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('captionModal'); Main.ajaxScroll(true);">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Create caption") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input placeholder="{{ __('Name') }}" class="form-control" name="name" id="name" type="text" value="{{ data($result, "name") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="content" class="form-label">{{ __('Caption') }}</label>
	                     	<textarea  class="form-control input-emoji" name="content" id="content" placeholder="{{ __('Enter caption') }}">{{ data($result, "content") }}</textarea>
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
	Main.Emoji();
</script>