@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('X (Twitter) API') }}" 
        description="{{ __('Easy Configuration Steps for X (Twitter) API') }}"
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="x_status" value="1" id="x_status_1" {{ get_option("x_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="x_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="x_status" value="0" id="x_status_0"{{ get_option("x_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="x_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Client ID') }}</label>
                            <input class="form-control" name="x_client_id" id="x_client_id" type="text" value="{{ get_option("x_client_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Client Secret') }}</label>
                            <input class="form-control" name="x_client_secret" id="x_client_secret" type="text" value="{{ get_option("x_client_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL: ") }} 
                            <a href="{{ url_app("x/profile") }}" target="_blank">{{ url_app("x/profile") }}</a>
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
