@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('File Settings') }}" 
        description="{{ __('Configure and manage your file settings easily') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("General configuration") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Stogare server') }}</label>
                            <select class="form-select" name="file_storage_server">
                                <?php foreach (Media::disks() as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("file_storage_server", "local")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Allowed File Types') }}</label>
                            <input type="text" class="form-control" name="file_allowed_file_types" value="{{ get_option("file_allowed_file_types", "jpeg,gif,png,jpg,webp,mp4,csv,pdf,mp3,wmv,json") }}" >
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('File Upload by URL') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_allow_upload_from_url" value="1" id="file_allow_upload_from_url_1" {{ get_option("file_allow_upload_from_url", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_allow_upload_from_url_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_allow_upload_from_url" value="0" id="file_allow_upload_from_url_0"{{ get_option("file_allow_upload_from_url", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_allow_upload_from_url_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Amazon S3") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Click this link to create Amazon S3: ") }}
                            <a href="https://s3.console.aws.amazon.com/s3/home" target="_blank">https://s3.console.aws.amazon.com/s3/home</a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Region') }}</label>
                            <input class="form-control" name="file_aws3_region" id="file_aws3_region" type="text" value="{{ get_option("file_aws3_region", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Access Key') }}</label>
                            <input class="form-control" name="file_aws3_access_key" id="file_aws3_access_key" type="text" value="{{ get_option("file_aws3_access_key", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Secret Access Key') }}</label>
                            <input class="form-control" name="file_awss3_secret_access_key" id="file_awss3_secret_access_key" type="text" value="{{ get_option("file_awss3_secret_access_key", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Bucket Name') }}</label>
                            <input class="form-control" name="file_aws_bucket_name" id="file_aws_bucket_name" type="text" value="{{ get_option("file_aws_bucket_name", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Contabo S3") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Click this link to create Contabo S3: ") }}
                            <a href="https://contabo.com/" target="_blank">https://contabo.com/</a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Region') }}</label>
                            <input class="form-control" name="file_contabos3_region" id="file_contabos3_region" type="text" value="{{ get_option("file_contabos3_region", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Access Key') }}</label>
                            <input class="form-control" name="file_contabos3_access_key" id="file_contabos3_access_key" type="text" value="{{ get_option("file_contabos3_access_key", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Secret Access Key') }}</label>
                            <input class="form-control" name="file_contabo_secret_access_key" id="file_contabo_secret_access_key" type="text" value="{{ get_option("file_contabo_secret_access_key", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Bucket Name') }}</label>
                            <input class="form-control" name="file_contabos3_bucket_name" id="file_contabos3_bucket_name" type="text" value="{{ get_option("file_contabos3_bucket_name", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Endpoint') }}</label>
                            <input class="form-control" name="file_contabos3_endpoint" id="file_contabos3_endpoint" type="text" value="{{ get_option("file_contabos3_endpoint", "") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Public URL') }}</label>
                            <input class="form-control" name="file_contabos3_public_url" id="file_contabos3_public_url" type="text" value="{{ get_option("file_contabos3_public_url", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Adobe Express - Image editor") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_addobe_express_status" value="1" id="file_addobe_express_status_1" {{ get_option("file_addobe_express_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_addobe_express_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_addobe_express_status" value="0" id="file_addobe_express_status_0"{{ get_option("file_addobe_express_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_addobe_express_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="file_addobe_express_app_name" class="form-label">{{ __('App name') }}</label>
                            <input class="form-control" name="file_addobe_express_app_name" id="file_addobe_express_app_name" type="text" value="{{ get_option("file_addobe_express_app_name", "") }}">
                        </div>

                        <div class="mb-4">
                            <label for="file_addobe_express_api_key" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_addobe_express_api_key" id="file_addobe_express_api_key" type="text" value="{{ get_option("file_addobe_express_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Unsplash (Search & Get Media Online)") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_unsplash_status" value="1" id="file_unsplash_status_1" {{ get_option("file_unsplash_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_unsplash_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_unsplash_status" value="0" id="file_unsplash_status_0"{{ get_option("file_unsplash_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_unsplash_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Access Key') }}</label>
                            <input class="form-control" name="file_unsplash_access_key" id="file_unsplash_access_key" type="text" value="{{ get_option("file_unsplash_access_key", "") }}">
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Secret key') }}</label>
                            <input class="form-control" name="file_unsplash_secret_key" id="file_unsplash_secret_key" type="text" value="{{ get_option("file_unsplash_secret_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Pexels (Search & Get Media Online)") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_pexels_status" value="1" id="file_pexels_status_1" {{ get_option("file_pexels_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_pexels_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_pexels_status" value="0" id="file_pexels_status_0"{{ get_option("file_pexels_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_pexels_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_pexels_api_key" id="file_pexels_api_key" type="text" value="{{ get_option("file_pexels_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Pixabay (Search & Get Media Online)") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_pixabay_status" value="1" id="file_pixabay_status_1" {{ get_option("file_pixabay_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_pixabay_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_pixabay_status" value="0" id="file_pixabay_status_0"{{ get_option("file_pixabay_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_pixabay_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_pixabay_api_key" id="file_pixabay_api_key" type="text" value="{{ get_option("file_pixabay_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Google Drive") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_google_drive_status" value="1" id="file_google_drive_status_1" {{ get_option("file_google_drive_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_google_drive_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_google_drive_status" value="0" id="file_google_drive_status_0"{{ get_option("file_google_drive_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_google_drive_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_google_drive_api_key" id="file_google_drive_api_key" type="text" value="{{ get_option("file_google_drive_api_key", "") }}">
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Client ID') }}</label>
                            <input class="form-control" name="file_google_drive_client_id" id="file_google_drive_client_id" type="text" value="{{ get_option("file_google_drive_client_id", "") }}">
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Client Secret') }}</label>
                            <input class="form-control" name="file_google_drive_client_secret" id="file_google_drive_client_secret" type="text" value="{{ get_option("file_google_drive_client_secret", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Dropbox") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_dropbox_status" value="1" id="file_dropbox_status_1" {{ get_option("file_dropbox_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_dropbox_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_dropbox_status" value="0" id="file_dropbox_status_0"{{ get_option("file_dropbox_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_dropbox_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_dropbox_api_key" id="file_dropbox_api_key" type="text" value="{{ get_option("file_dropbox_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("OneDrive") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_onedrive_status" value="1" id="file_onedrive_status_1" {{ get_option("file_onedrive_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_onedrive_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="file_onedrive_status" value="0" id="file_onedrive_status_0"{{ get_option("file_onedrive_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="file_onedrive_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="file_onedrive_api_key" id="file_onedrive_api_key" type="text" value="{{ get_option("file_onedrive_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>

    </form>

</div>

@endsection
