<div class="modal fade" id="updateFolderModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered ">
		<form class="modal-content actionForm" action="{{ module_url("save_folder") }}" data-call-success="Main.closeModal('updateFolderModal'); Main.ajaxScroll(true);">
			<div class="modal-header">
				<h1 class="modal-title fs-16">
					@if( $result )
						{{ __("Edit Folder") }}
					@else
						{{ __("Create Folder") }}
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
	                  		<label for="name" class="form-label">{{ __('Folder name') }}</label>
                     		<input placeholder="{{ __('Enter folder name') }}" class="form-control" name="name" id="name" type="text" value="{{ data($result, "name") }}">
	                  	</div>

	 					<div class="mb-0">
		                  	<label for="name" class="form-label">{{ __('Parent folder') }}</label>
						    <div class="d-flex flex-row border overflow-auto max-h-350">

						    	<ul class="flex-fill">
							    	<li>
									    <label class="w-100 bg-primary-100 py-1 pe-2 pl-12" for="parent_0">
									        <div class="form-check align-items-center gap-8 fs-12">
									            <input class="form-check-input" type="radio" name="parent" value="0" id="parent_0"  {{ data($result, "pid", "radio", 0 , 0) }}>
									            <span class="text-truncate">{{ __("Root Folder") }}</span>
									        </div>
									    </label>
									</li>
							     	@php
							     		$count = 1;
							     		$count_sub = 1;
							     	@endphp

							        @foreach ($folders as $folder)
							        	@php
						                    $count++;
						                @endphp
							            @include('appfiles::partials.folder', ['folder' => $folder])
							        @endforeach
							    </ul>
						    </div>
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