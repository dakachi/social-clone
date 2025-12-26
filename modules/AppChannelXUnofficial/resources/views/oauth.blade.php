@extends('layouts.app')

@section('content')
    <div class="container px-4 max-w-700">

        <div class="mt-4 mb-5">
            <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
                <div class="my-3 d-flex flex-column gap-8">
                    <h1 class="fs-20 font-medium lh-1 text-gray-900">
                        {{ __("X Login with Cookie") }}
                    </h1>
                    <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                        <div class="d-flex gap-8">
                            <span class="text-gray-600"><span class="text-gray-600">{{ __('Connect X accounts using login your browser cookies.') }}</span></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-8">
                    <a class="btn btn-light btn-sm" href="{{ url("app/channels") }}">
                        <span><i class="fa-light fa-angle-left"></i></span>
                        <span>{{ __('Back') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <form class="actionForm" action="{{ module_url("proccess") }}" method="POST">
            
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <label for="x_csrf_token" class="form-label">{{ __("CSRF Token (ct0)") }}</label>
                        <input type="text" class="form-control" name="x_csrf_token" id="x_csrf_token">
                    </div>

                    <div class="mb-4">
                        <label for="x_auth_token" class="form-label">{{ __("Auth Token (auth_token)") }}</label>
                        <input type="text" class="form-control" name="x_auth_token" id="x_auth_token">
                    </div>

                    <div class="mb-0">
                        <label for="x_screen_name" class="form-label">{{ __("Screen name") }}</label>
                        <input type="text" class="form-control" name="x_screen_name" id="x_screen_name" placeholder="{{ __("Enter your url profile https://x.com/{screen_name} or {screen_name}") }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-dark w-100">{{ __("Submit") }}</button>
                </div>
            </div>

        </form>

    </div>
@endsection