@php
$editor_id = "comment_".rand_string(); 
@endphp

<div class="modal fade" id="editCommentPopup" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save_comment") }}" data-call-success="Main.closeModal('editCommentPopup'); Main.ajaxScrollTop(true);">
			<input type="text" class="d-none" name="comment_id" value="{{ data($result, "id_secure") }}">
			<input type="text" class="d-none" name="ticket_id" value="{{ $ticket_id }}">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Edit comment") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-0">

		    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
              	<textarea  class=" border-0 border-gray-300 b-r-10 max-h-300" name="comment" id="{{ $editor_id }}" placeholder="{{ __('Enter caption') }}">{{ data($result, "comment") }}</textarea>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="submit" class="btn btn-dark">{{ __('Save changes') }}</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	Main.Tinymce("#{{ $editor_id }}");
</script>