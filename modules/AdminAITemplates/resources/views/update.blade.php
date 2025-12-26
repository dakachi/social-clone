<div class="modal fade" id="AITemplatesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="{{ module_url('save') }}" method="POST"
              data-call-success="Main.closeModal('AITemplatesModal'); Main.DataTable_Reload('#DataTable')">
            
            @csrf

            <div class="modal-header">
                <h1 class="modal-title fs-16">{{ __('Update AI Templates') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input class="d-none" name="id" type="text" value="{{ data($result, 'id_secure') }}">
                <div class="msg-errors mb-3"></div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-3 flex-column flex-lg-row">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" 
                                           {{ data($result, 'status', 'radio', 1, 1) }}>
                                    <label class="form-check-label mt-1" for="status_1">{{ __('Enable') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" value="0" id="status_0"
                                           {{ data($result, 'status', 'radio', 0, 1) }}>
                                    <label class="form-check-label mt-1" for="status_0">{{ __('Disable') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Categories') }}</label>
                            <select class="form-select" data-select2-dropdown-class="mt--1" data-control="select2" name="cate_id">
                                <option value="0">{{ __('Select categories') }}</option>
                                @if (!empty($categories))
                                    @foreach ($categories as $value)
                                        <option value="{{ $value->id }}"
                                                data-icon="{{ $value->icon }} text-{{ $value->color }}"
                                                {{ data($result, 'cate_id', 'select', $value->id) }}>
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="content" class="form-label">{{ __('Content') }}</label>
                            <textarea class="form-control input-emoji" name="content"
                                      placeholder="{{ __('Enter your content') }}">{{ data($result, 'content') }}</textarea>
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
	Main.Emoji();
</script>
