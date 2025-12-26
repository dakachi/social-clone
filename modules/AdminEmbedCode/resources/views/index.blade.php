@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Embed Code') }}"
        description="{{ __('Code snippet to display external content on websites.') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-1000 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card border mb-4">
            <div class="card-body">
                <div class="fw-5 fs-14 mb-2">{{ __("Status") }}                
                    <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column mb-4">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="embed_code_status" value="1" id="embed_code_status_1" {{ get_option("embed_code_status", 0)==1?"checked":"" }}>
                            <label class="form-check-label mt-1" for="embed_code_status_1">
                                {{ __('Enable') }}
                            </label>
                        </div>
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="embed_code_status" value="0" id="embed_code_status_0"{{ get_option("embed_code_status", 0)==0?"checked":"" }}>
                            <label class="form-check-label mt-1" for="embed_code_status_0">
                                {{ __('Disable') }}
                            </label>
                        </div>
                    </div>
                    <textarea class="input-code border-gray-300 border-1 h-min-100" name="embed_code" placeholder="{{ __("Enter content") }}"> {{ get_option("embed_code", "") }}</textarea>
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
