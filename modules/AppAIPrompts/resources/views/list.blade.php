@if($result->Total() > 0)
	@foreach($result as $value)
	<div class="search-prompt">
		<div class="d-flex align-items-center gap-16 py-3 px-3 border-bottom">
			<div class="w-25 min-w-25 max-w-25 flex-grow-1 fs-12">
				<div class="form-check mt-1">
					<input class="form-check-input checkbox-item" type="checkbox" name="prompts[]" data-id="{{ $value->id }}" value="{{ $value->id_secure }}" id="prompt_{{ $value->id_secure }}">
				</div>
			</div>
			<label class="flex-grow-1 fs-13 text-gray-700">
				{{ $value->prompt }}
			</label>
			<div>
				<div class="btn-group position-static d-flex align-items-center gap-8">
		            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" aria-expanded="true">
		                <i class="fa-light fa-grid-2"></i>
		            </div>
		            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
		                <li>
		                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" data-id="{{ $value->id_secure }}" href="{{ url_app("ai-prompts/update") }}" data-offcanvas="updateAIPrompts">
		                        <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
		                        <span >{{ __('Edit') }}</span>
		                    </a>
		                </li>
		                <li><hr class="dropdown-divider"></li>
		                <li>
		                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("destroy") }}" data-id="{{ $value->id_secure }}" data-confirm="{{ __("Are you sure you want to delete this item?") }}" data-call-success="Main.ajaxPages();">
		                        <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
		                        <span>{{ __('Delete') }}</span>
		                    </a>
		                </li>
		            </ul>
		        </div>
			</div>
		</div>
	</div>
	@endforeach
@else
	<div class="empty my-5"></div>
@endif