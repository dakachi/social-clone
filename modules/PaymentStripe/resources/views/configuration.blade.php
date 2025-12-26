<div class="modal fade" id="stripeConfigurationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ url_admin("settings/save") }}" data-call-success="Main.closeModal('stripeConfigurationModal');">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Stripe Configuration") }}</h1>
				
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
                                    <input class="form-check-input" type="radio" name="stripe_status" value="1" id="stripe_status_1" @checked( get_option("stripe_status", 0) == 1 ) >
                                    <label class="form-check-label mt-1" for="stripe_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="stripe_status" value="0" id="stripe_status_0" @checked( get_option("stripe_status", 0) == 0 ) >
                                    <label class="form-check-label mt-1" for="stripe_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
 					<div class="col-md-12">
 						<div class="mb-3">
		                  	<label for="name" class="form-label">{{ __('Publishable key') }}</label>
	                     	<input class="form-control" name="stripe_publishable_key" id="stripe_publishable_key" type="text" value="{{ get_option("stripe_publishable_key", "") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-3">
		                  	<label for="name" class="form-label">{{ __('Secret key') }}</label>
	                     	<input class="form-control" name="stripe_secret_key" id="stripe_secret_key" type="text" value="{{ get_option("stripe_secret_key", "") }}">
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