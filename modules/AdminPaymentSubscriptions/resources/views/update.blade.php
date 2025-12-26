<div class="modal fade" id="AdminPaymentSubscription" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('AdminPaymentSubscription'); Main.DataTable_Reload('#DataTable');">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Add new Subscription") }}</h1>
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
				                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1" {{ data($result, "status", "radio", 1, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_1">
				                    	{{ __('Success') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" {{ data($result, "status", "radio", 0, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Refund') }}
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
                            <select placeholder="{{ __('Enter plan name') }}" class="form-control" name="plan" id="plan" type="text" value="{{ data($result, "plan") }}">
                                <option value="-1">{{ __('Selecte Subcription (Plan)') }}</option>
                                <option value="1">{{ __('Standard') }}</option>
                                <option value="2">{{ __('Premium') }}</option>
                                <option value="0">{{ __('Entrepreneur') }}</option>
                            </select>
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="from" class="form-label">{{ __('Payment Type') }}</label>
                            <select class="form-control" name="from" id="from" type="text" value="{{ data($result, "from") }}">
                                <option value="-1">{{ __('Enter Payment Type') }}</option>
                                <option value="1">{{ __('PayPal') }}</option>
                                <option value="2">{{ __('Stripe') }}</option>
                                <option value="0">{{ __('CoinPayment') }}</option>
                            </select>
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="subscription_id" class="form-label">{{ __('Subscription ID') }}</label>
	                     	<input placeholder="{{ __('Enter Subscription ID') }}" class="form-control" name="subscription_id" id="subscription_id" type="text" value="{{ data($result, "subscription_id") }}">
		                </div>
 					</div>
                     <div class="col-md-12">
                        <div class="mb-4">
                             <label for="amount" class="form-label">{{ __('Amount') }}</label>
                            <input placeholder="{{ __('Enter a number') }}" class="form-control" name="amount" id="amount" type="text" value="{{ data($result, "amount") }}">
                       </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                             <label for="created" class="form-label">{{ __('Created Date') }}</label>
                            <input placeholder="{{ __('Enter Date and Time') }}" class="form-control datetime" name="created" id="created" type="text" value="">
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
    Main.Select2();
    Main.DateTime();
</script>
