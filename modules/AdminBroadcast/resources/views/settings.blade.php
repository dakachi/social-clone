@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Broadcast Configuration') }}" 
        description="{{ __('Configure real-time broadcast settings for your application.') }}" 
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
                            <label for="name" class="form-label">{{ __('Broadcast Driver') }}</label>
                            @php
                                $drivers = [
                                    '0' => __('Disable'),
                                    'pusher' => __('Pusher'),
                                ];
                                $selected = get_option("broadcast_driver", 0);
                            @endphp
                            <select class="form-select" name="broadcast_driver">
                                @foreach($drivers as $value => $label)
                                    <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Pusher") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="pusher_app_id" class="form-label">{{ __('App ID') }}</label>
                            <input class="form-control" name="pusher_app_id" id="pusher_app_id" type="text" value="{{ get_option("pusher_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="pusher_app_key" class="form-label">{{ __('App Key') }}</label>
                            <input class="form-control" name="pusher_app_key" id="pusher_app_key" type="text" value="{{ get_option("pusher_app_key", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="pusher_app_secret" class="form-label">{{ __('App Secret') }}</label>
                            <input class="form-control" name="pusher_app_secret" id="pusher_app_secret" type="text" value="{{ get_option("pusher_app_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="pusher_cluster" class="form-label">{{ __('Cluster') }}</label>
                            <input class="form-control" name="pusher_cluster" id="pusher_cluster" type="text" value="{{ get_option("pusher_cluster", "") }}">
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