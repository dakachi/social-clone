@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Social pages') }}" 
        description="{{ __('Embrace the journey, every step is magical') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
    
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("All Your Social Pages") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Facebook') }}</label>
                            <input class="form-control" name="social_page_facebook" id="social_page_facebook" type="text" value="{{ get_option("social_page_facebook", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Instagram') }}</label>
                            <input class="form-control" name="social_page_instagram" id="social_page_instagram" type="text" value="{{ get_option("social_page_instagram", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Titkok') }}</label>
                            <input class="form-control" name="social_page_tiktok" id="social_page_tiktok" type="text" value="{{ get_option("social_page_tiktok", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Youtube') }}</label>
                            <input class="form-control" name="social_page_youtube" id="social_page_youtube" type="text" value="{{ get_option("social_page_youtube", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('X (Twitter)') }}</label>
                            <input class="form-control" name="social_page_x" id="social_page_x" type="text" value="{{ get_option("social_page_x", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Pinterest') }}</label>
                            <input class="form-control" name="social_page_pinterest" id="social_page_pinterest" type="text" value="{{ get_option("social_page_pinterest", "") }}">
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
