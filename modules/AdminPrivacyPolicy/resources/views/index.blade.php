@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Privacy Policy') }}"
        description="{{ __('How we collect, use, and protect data') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-1000 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">

        <div class="card border mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Privacy Policy") }}</div>
            </div>
            <div class="card-body">
                <div class="fw-5 fs-14 mb-2">{{ __("Status") }}                
                <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column mb-4">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="privacy_policy_status" value="1" id="privacy_policy_status_1" {{ get_option("privacy_policy_status", 1)==1?"checked":"" }}>
                        <label class="form-check-label mt-1" for="privacy_policy_status_1">
                            {{ __('Enable') }}
                        </label>
                    </div>
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="privacy_policy_status" value="0" id="privacy_policy_status_0"{{ get_option("privacy_policy_status", 1)==0?"checked":"" }}>
                        <label class="form-check-label mt-1" for="privacy_policy_status_0">
                            {{ __('Disable') }}
                        </label>
                    </div>
                </div>
                <textarea class="textarea_editor border-gray-300 border-1 min-h-600" name="privacy_policy" placeholder="{{ __("Enter content") }}"> {{ get_option("privacy_policy", "") }}</textarea>
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
