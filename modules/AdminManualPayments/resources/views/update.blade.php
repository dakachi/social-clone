<div class="modal fade" id="AdminManualPaymentsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('AdminManualPaymentsModal'); Main.DataTable_Reload('#DataTable');">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Add New Payment") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label class="form-label">{{ __('Status') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" {{ data($result, "status", "radio", 0, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Pending') }}
				                  	</label>
				                </div>		                  	
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1" {{ data($result, "status", "radio", 1, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_1">
				                    	{{ __('Approved') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" {{ data($result, "status", "radio", 2, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Cancel') }}
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 					<div class="col-md-12">
                        <div class="mb-4">
                            <label for="user_id" class="form-label">{{ __('User Name') }} (<span class="text-danger">*</span>)</label>
                            <select class="form-select" name="user_id" id="user_id" data-control="select2" data-ajax-url="{{ route('admin.users.search') }}" data-selected-id="">
                            	<option value="-1">{{ __("Select user") }}</option>
                            </select>
                        </div>
                    </div>
 					<div class="col-md-12">
 						<div class="mb-4">
                             <label for="plan" class="form-label">{{ __('Plan') }}</label>
                             <select placeholder="{{ __('Select your plan') }}" class="form-select" id="plan" aria-label="plan">
                               <option selected disabled>{{ __('Select your plans') }}</option>
                               <option value="basic">{{ __('Basic') }}</option>
                               <option value="standard">{{ __('Standard') }}</option>
                               <option value="premium">{{ __('Premium') }}</option>
                             </select>

		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="payment_id" class="form-label">{{ __('Payment ID') }}</label>
	                     	<input placeholder="{{ __('Enter transaction ID') }}" class="form-control" name="payment_id" id="payment_id" type="text" value="{{ data($result, "payment_id") }}">
		                </div>
 					</div>
                     <div class="col-md-12">
                        <div class="mb-4">
                             <label for="amount" class="form-label">{{ __('Amount') }}</label>
                            <input placeholder="{{ __('Enter Amount') }}" class="form-control" name="amount" id="amount" type="text" value="{{ data($result, "amount") }}">
                       </div>
                    </div>  
                     <div class="col-md-12">
                        <div class="mb-4">
                             <label for="notes" class="form-label">{{ __('notes') }}</label>
                            <input placeholder="{{ __('Enter notes') }}" class="form-control" name="notes" id="notes" type="text" value="{{ data($result, "notes") }}">
                       </div>
                    </div>                      
 	<!-- 				<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="notes" class="form-label">{{ __('Note') }}</label>
	                     	<textarea  class="form-control" name="notes" id="notes" placeholder="{{ __('Enter note') }}">{{ data($result, "desc") }}</textarea>
		                </div>
 					</div> -->
                    <div class="col-md-12">
                        <div class="mb-4">
                             <label for="created" class="form-label">{{ __('Created Time') }}</label>
                            <input placeholder="{{ __('Enter Created Time') }}" class="form-control datetime" name="created" id="created" type="datetime" value="">
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
    Main.Select2(); //Seclect User list from User Table/
    Main.DateTime(); // Show normal time //
</script>

