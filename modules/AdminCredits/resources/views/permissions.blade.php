<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <label class="fw-6 fs-14 text-gray-700">
            {{ __("Credits Usage") }}
        </label>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <input class="form-control" name="permissions[credits]" id="credits" type="number" value="{{ $permissions['credits'] ?? '100000' }}">
                </div>
            </div>
        </div>
    </div>
</div>
  