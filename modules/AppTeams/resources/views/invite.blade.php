<div class="modal fade" id="inviteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("send-invite") }}" data-call-success="Main.closeModal('inviteModal'); Main.ajaxScroll(true);">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Invite Member") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4">
                @php
			        $permissions = $team->permissions ?? '';
			        $permissions = groupArray($permissions);
			    @endphp

         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="email" class="form-label mb-1">{{ __('Team Member Email') }}</label>
		                  	<div class="fs-12 text-gray-600 mb-2">
						        {{ __('We will send an invite to this email address.') }}
						    </div>
	                     	<input placeholder="{{ __('Enter team member email address') }}" class="form-control" name="email" id="email" type="email" value="">
		                </div>

		                <div class="mb-4">
							<label for="name" class="form-label">{{ __('Select permissions') }}</label>
							<div class="mb-3">
					            <div class="input-group">
					                <div class="form-control">
				                     	<i class="fa-light fa-magnifying-glass"></i>
				                     	<input placeholder="{{ __("Search") }}" type="text" class="search-input" value="">
					                </div>
					                <span class="btn btn-icon btn-input min-w-55">
					                    <input class="form-check-input checkbox-all" type="checkbox" value="">
					                </span>
					            </div>
							</div>
	 						<div class="mb-4 pf-0 b-r-4">
	 							@if($permissions)
	 							@php
	 								$selected_permissions = [];
	 							@endphp

			                  	<ul class="list-group border overflow-y-scroll max-h-350">
			                  		@foreach($permissions as $value)

			                  			@if(Module::find($value['key']) && $value['key'] != 'appteams')
			                  				
				                  			<li class="search-list border-start-2 border-primary">
				                  				<input type="hidden" name="team_permissions[]" value="{{ $value['key'] }}">
						                        <div class="list-group-item border-start-0 border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8">
											  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_{{ $value['key'] }}">
											  			<div class="size-26 min-w-26 border b-r-6 d-flex justify-content-between align-items-center text-center bg-gray-100 text-success fs-14 border-success-200">
											  				<i class="fa-light fa-key wp-100"></i>
											  			</div>
											  			<div class="text-truncate">
											  				<div class="fs-12 lh-sm mb-1 fw-5">{{ $value['label'] }}</div>
											  			</div>
											  		</label>
											  		<span>
											  			<input class="form-check-input checkbox-item" type="checkbox" name="permissions[]" value="{{ $value['key'] }}" id="id_{{ $value['key'] }}" {{ __( in_array($value['key'], $selected_permissions)?"checked":"" ) }} >
											  		</span>
											  	</div>
				                  			</li>

				                  			@if(isset($value['children']))

					                  			@foreach($value['children'] as $children)

					                  				@if((int)$children['value'] == 1)
						                  				<li class=" search-list">
					                  						<input type="hidden" name="team_permissions[]" value="{{ $children['key'] }}">
									                        <div class="list-group-item border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8  border-start-2 ps-5">
														  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_{{ $children['key'] }}">
														  			<div class="size-26 min-w-26 border b-r-6 d-flex justify-content-between align-items-center text-center bg-primary-100 text-primary fs-14 border-primary-200">
														  				<i class="fa-light fa-key wp-100"></i>
														  			</div>
														  			<div class="text-truncate">
														  				<div class="fs-12 lh-sm mb-1 fw-5">{{ $children['label'] }}</div>
														  			</div>
														  		</label>
														  		<span>
														  			<input class="form-check-input checkbox-item" type="checkbox" name="permissions[]" value="{{ $children['key'] }}" id="id_{{ $children['key'] }}" {{ __( in_array($children['key'], $selected_permissions)?"checked":"" ) }} >
														  		</span>
														  	</div>
							                  			</li>
							                  		@endif

					                  			@endforeach

				                  			@endif

				                  		@endif
			                  		@endforeach
								</ul>
								@else
								<div class="empty"></div>
								@endif
			                </div>
		                </div>

 					</div>
 				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="submit" class="btn btn-dark">{{ __('Send invite') }}</button>
			</div>
		</form>
	</div>
</div>