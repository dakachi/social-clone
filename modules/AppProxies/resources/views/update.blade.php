<div class="modal fade" id="proxiesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" 
		      action="{{ module_url('save') }}" 
		      data-call-success="Main.closeModal('proxiesModal'); Main.DataTable_Reload('#DataTable')">

			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Update Proxy") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<input type="hidden" name="id" value="{{ old('id', $result->id_secure ?? '') }}">
				<div class="msg-errors"></div>

				<div class="row">

					{{-- Status --}}
					<div class="col-md-12">
						<div class="mb-4">
							<label class="form-label">{{ __('Status') }}</label>
							<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
								@foreach ([1 => __('Enable'), 0 => __('Disable')] as $value => $label)
									<div class="form-check me-3">
										<input class="form-check-input" type="radio" name="status" 
										       value="{{ $value }}" id="status_{{ $value }}"
										       {{ old('status', $result->status ?? 1) == $value ? 'checked' : '' }}>
										<label class="form-check-label mt-1" for="status_{{ $value }}">{{ $label }}</label>
									</div>
								@endforeach
							</div>
						</div>
					</div>

					{{-- Proxy Address --}}
					<div class="col-md-12">
						<div class="mb-4">
							<label for="proxy" class="form-label mb-1">{{ __('Proxy Address') }}</label>
							<div class="text-muted fs-12 mb-2">
								{{ __('Format: username:password@ip:port or ip:port') }}
							</div>
							<input type="text" class="form-control" name="proxy" id="proxy"
								   placeholder="username:password@ip:port"
								   value="{{ old('proxy', $result->proxy ?? '') }}">
						</div>
					</div>

					{{-- Account Limit --}}
					<div class="col-md-12">
						<div class="mb-4">
							<label for="limit" class="form-label mb-1">{{ __('Account Limit') }}</label>
							<div class="text-muted fs-12 mb-1">
								{{ __('Maximum number of accounts allowed to use this proxy.') }}<br>
								{{ __('Enter -1 for unlimited.') }}
							</div>
							<input type="number" class="form-control" name="limit" id="limit"
								   placeholder="{{ __('e.g. 10 or -1') }}"
								   min="-1"
								   value="{{ old('limit', $result->limit ?? '-1') }}">
						</div>
					</div>

					{{-- Description --}}
					<div class="col-md-12">
						<div class="mb-4">
							<label for="description" class="form-label">{{ __('Description (Optional)') }}</label>
							<input type="text" class="form-control" name="description" id="description"
								   placeholder="{{ __('Proxy description') }}"
								   value="{{ old('description', $result->description ?? '') }}">
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