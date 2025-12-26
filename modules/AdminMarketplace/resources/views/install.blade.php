<div class="modal fade" id="installModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-light-subtle border-0">
                <h5 class="modal-title fw-semibold text-dark fs-16">
                    <i class="fa-light fa-file-zipper me-2 text-primary"></i> {{ __("Install Plugins / Addons") }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <ul class="nav nav-pills nav-fill bg-light border-bottom border-top" id="installTabs" role="tablist">
                <li class="nav-item wp-50" role="presentation">
                    <button class="nav-link fs-14 b-r-0 active" id="tab-purchase" data-bs-toggle="pill" data-bs-target="#pane-purchase" type="button" role="tab">
                        <i class="fa-light fa-key"></i> {{ __("Via Purchase Code") }}
                    </button>
                </li>
                <li class="nav-item wp-50" role="presentation">
                    <button class="nav-link fs-14 b-r-0" id="tab-zip" data-bs-toggle="pill" data-bs-target="#pane-zip" type="button" role="tab">
                        <i class="fa-light fa-file-zipper"></i> {{ __("Via ZIP File") }}
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="installTabsContent">
                {{-- Tab 1: Via Purchase Code --}}
                <div class="tab-pane fade show active" id="pane-purchase" role="tabpanel">
                    <form class="actionForm p-4" action="{{ module_url('do-install') }}" data-call-success="afterAddonInstall(result);">
                        <div class="msg-errors mb-3"></div>
                        <div class="mb-3">
                            <label for="purchase_code" class="form-label fw-semibold text-muted">{{ __("Purchase Code") }}</label>
                            <input 
                                type="text"
                                class="form-control form-control-lg rounded-3"
                                name="purchase_code"
                                id="purchase_code"
                                placeholder="Enter your Envato purchase code"
                                required
                            >
                        </div>
                        <div class="alert alert-light small d-flex align-items-start mt-3 mb-0 b-r-10">
                            <ul>
                                <li class="mb-2">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("This form is for installing plugins or themes only.") }}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("Main script reinstallation is not allowed.") }}</span>
                                </li>
                                <li class="mb-0">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("Ensure your server has permission.") }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer border-0 px-0 pb-0 mt-3">
                            <button type="submit" class="btn btn-dark w-100 rounded-pill btn-lg">
                                <i class="fa fa-check-circle me-1"></i> {{ __("Install Now") }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tab 2: Via ZIP File --}}
                <div class="tab-pane fade" id="pane-zip" role="tabpanel">
                    <form class="actionForm p-4" action="{{ module_url('do-install-zip') }}" method="POST" enctype="multipart/form-data" data-call-success="afterAddonInstall(result);">
                        @csrf
                        <div class="msg-errors mb-3"></div>
                        <div class="mb-3">
                            <label for="module_zip" class="form-label fw-semibold text-muted">{{ __("Select ZIP File") }}</label>
                            <input 
                                type="file"
                                class="form-control form-control-lg rounded-3"
                                name="module_zip"
                                id="module_zip"
                                accept=".zip"
                                required
                            >
                        </div>
                        <div class="alert alert-light small d-flex align-items-start mt-3 mb-0 b-r-10">
                            <ul>
                            	<li class="d-flex gap-2 mb-2">
								    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
								    <span>{{ __("You can use this to install a new module or update an existing one.") }}</span>
								</li>
                                <li class="d-flex gap-2 mb-2">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("The ZIP file must be a valid module package.") }}</span>
                                </li>
                                <li class="d-flex gap-2 mb-2">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("Do not upload the main script or core modules.") }}</span>
                                </li>
                                <li class="d-flex gap-2 mb-0">
                                    <i class="fa-light fa-info-circle me-1 mt-1 text-primary"></i>
                                    <span>{{ __("Ensure your server has write permission to /modules directory.") }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer border-0 px-0 pb-0 mt-3">
                            <button type="submit" class="btn btn-dark w-100 rounded-pill btn-lg">
                                <i class="fa fa-check-circle me-1"></i> {{ __("Install from ZIP") }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function afterAddonInstall(res) {
    if(res.status === 1 && res.id) {
        let $dummyBtn = $('<button/>');

        Main.ajaxPost(
            $dummyBtn, 
            "{{ module_url('active') }}/" + res.id, "",
            function(enableRes) {
                if(enableRes.status === 1) {
                    Main.closeModal('installModal');
                    setTimeout(() => location.reload(), 1200);
                }
            }
        );
    }
}
</script>