@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Terms of Use') }}"
        description="{{ __('Understand your rights and responsibilities using the platform') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-1000 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">

        <div class="card border">
            <div class="card-header">
                <div class="fw-6">{{ __("Terms of Use") }}</div>
            </div>
            <div class="card-body">
                <div class="fw-5 fs-14 mb-2">{{ __("Status") }}  
                <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column mb-4">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="terms_of_use_status" value="1" id="terms_of_use_status_1" {{ get_option("terms_of_use_status", 1)==1?"checked":"" }}>
                        <label class="form-check-label mt-1" for="terms_of_use_status_1">
                            {{ __('Enable') }}
                        </label>
                    </div>
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="terms_of_use_status" value="0" id="terms_of_use_status_0"{{ get_option("terms_of_use_status", 1)==0?"checked":"" }}> 
                        <label class="form-check-label mt-1" for="terms_of_use_status_0">
                            {{ __('Disable') }}
                        </label>
                    </div>
                </div>
                <textarea class="textarea_editor border-gray-300 border-1 min-h-600" name="terms_of_use" placeholder="{{ __("Enter content") }}"> {{ get_option("terms_of_use", "") }}</textarea>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-dark b-r-10 w-100">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
        


        </div>



    </form>

</div>

@endsection
