<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="permissions[]" name="permissions[appfiles]" @checked( array_key_exists("appfiles", $permissions ) )>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="permissions[appfiles]">
                {{ __("Files") }}
            </label>
        </div>
        <input class="form-control d-none" name="labels[appfiles]" type="text" value="Files">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 file_picker">
                <div class="mb-2">
                    <div class="d-flex gap-4 justify-content-between">
                        <div class="fw-5 text-gray-800 fs-14 mb-2">{{ __('File picker') }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-8">
                        <div class="mb-2">
                            <div class="form-check me-3">
                                <input class="form-check-input checkbox-item" type="checkbox" name="permissions[appfiles.google_drive]" value="1" id="appfiles.google_drive" @checked( array_key_exists("appfiles.google_drive", $permissions ) )>
                                <label class="form-check-label mt-1 text-truncate" for="file_google_drive">
                                    {{ __("Google Drive") }}
                                </label>
                            </div>
                            <input class="form-control d-none" name="labels[appfiles.google_drive]" type="text" value="Google Drive">
                        </div>
                        <div class="mb-2">
                            <div class="form-check me-3">
                                <input class="form-check-input checkbox-item" type="checkbox" name="permissions[appfiles.dropbox]" value="1" id="appfiles.dropbox" @checked( array_key_exists("appfiles.dropbox", $permissions ) )>
                                <label class="form-check-label mt-1 text-truncate" for="appfiles.dropbox">
                                    {{ __("Dropbox") }}
                                </label>
                            </div>
                            <input class="form-control d-none" name="labels[appfiles.dropbox]" type="text" value="Dropbox">
                        </div>
                        <div class="mb-2">
                            <div class="form-check me-3">
                                <input class="form-check-input checkbox-item" type="checkbox" name="permissions[appfiles.onedrive]" value="1" id="appfiles.onedrive" @checked( array_key_exists("appfiles.onedrive", $permissions ) )>
                                <label class="form-check-label mt-1 text-truncate" for="appfiles.onedrive">
                                    {{ __("OneDrive") }}
                                </label>
                                <input class="form-control d-none" name="labels[appfiles.onedrive]" type="text" value="OneDrive">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-4">
                    <label class="form-label">{{ __('Image Editor') }}</label>
                    <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="permissions[appfiles.image_editor]" value="1" id="appfiles.image_editor_1" @checked(($permissions['appfiles.image_editor'] ?? 1) == 1)>
                            <label class="form-check-label mt-1" for="appfiles.image_editor_1">
                                {{ __('Enable') }}
                            </label>
                        </div>
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="permissions[appfiles.image_editor]" value="0" id="appfiles.image_editor_0" @checked(($permissions['appfiles.image_editor'] ?? 1) == 0)>
                            <label class="form-check-label mt-1" for="appfiles.image_editor_0">
                                {{ __('Disable') }}
                            </label>
                        </div>
                    </div>
                    <input class="form-control d-none" name="labels[appfiles.image_editor]" type="text" value="Image Editor">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="appfiles.max_storage" class="form-label">{{ __('Max. storage size (MB)') }}</label>
                    <input class="form-control" name="permissions[appfiles.max_storage]" id="appfiles.max_storage" type="number" value="{{ $permissions['appfiles.max_storage'] ?? '1000' }}">
                    <input class="form-control d-none" name="labels[appfiles.image_editor]" type="text" value="Max. storage size (MB)">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="appfiles.max_size" class="form-label">{{ __('Max. file size (MB)') }}</label>
                    <input class="form-control" name="permissions[appfiles.max_size]" id="appfiles.max_size" type="number" value="{{ $permissions['appfiles.max_size'] ?? '10' }}">
                    <input class="form-control d-none" name="labels[appfiles.image_editor]" type="text" value="Max. file size (MB)">
                </div>
            </div>
        </div>
    </div>
</div>