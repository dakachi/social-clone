<div class="modal fade" id="addAffiliateWithdrawalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save-note") }}" data-call-success="Main.closeModal('addAffiliateWithdrawalModal'); Main.DataTable_Reload('#DataTable');">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16">
				@if($result)
					{{ __("Notes") }}
				@else
					{{ __("Create") }}
				@endif
				</h1>
				
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="note" class="form-label">{{ __('Note') }}</label>
	                     	<textarea  class="form-control input-emoji" name="note" id="note" placeholder="{{ __('Enter your note') }}">{{ data($result, "note") }}</textarea>
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