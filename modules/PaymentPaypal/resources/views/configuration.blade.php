<div class="modal fade" id="paypalConfigurationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ url_admin("settings/save") }}" data-call-success="Main.closeModal('paypalConfigurationModal');">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Paypal Configuration") }}</h1>
				
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="paypal_status" value="1" id="paypal_status_1" @checked( get_option("paypal_status", 0) == 1 ) >
                                    <label class="form-check-label mt-1" for="paypal_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="paypal_status" value="0" id="paypal_status_0" @checked( get_option("paypal_status", 0) == 0 ) >
                                    <label class="form-check-label mt-1" for="paypal_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
 						<div class="mb-3">
		                  	<label for="name" class="form-label">{{ __('Environment') }}</label>
		                  	<select class="form-select" name="paypal_environment">
		                  		<option value="1" @selected( get_option("paypal_environment", 0) == 1 )>{{ __("Live") }}</option>
		                  		<option value="0" @selected( get_option("paypal_environment", 0) == 0 )>{{ __("Sandbox") }}</option>
		                  	</select>
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-3">
		                  	<label for="name" class="form-label">{{ __('Client ID') }}</label>
	                     	<input class="form-control" name="paypal_client_id" id="paypal_client_id" type="text" value="{{ get_option("paypal_client_id", "") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-3">
		                  	<label for="name" class="form-label">{{ __('Client Secret') }}</label>
	                     	<input class="form-control" name="paypal_client_secret" id="paypal_client_secret" type="text" value="{{ get_option("paypal_client_secret", "") }}">
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