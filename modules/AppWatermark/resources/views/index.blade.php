@extends('layouts.app')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ module_folder_url('/assets/css/watermark.css'); }}">
@endsection

@section('form', json_encode([
    'method' => 'POST',
    'action' => module_url("save"),
    'class'  => 'actionForm'
]))

@section('sub_header')
    <x-sub-header 
        title="{{ __('Watermark') }}" 
        description="{{ __('Subtle overlay text or logo for content protection') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
    <div class="container max-w-900">
        <div class="row">
            <div class="col-md-12">

                <div class="card mb-3">
                    <div class="card-body">
                        <select class="wp-100 border-gray-200 ajax-pages-filter" name="account_id" data-control="select2">
                            <option value="0">{{ __("All Accounts") }}</option>
                            @if( !empty( Channels::all() ) )
                                @foreach( Channels::all() as $value )
                                    <option value="{{ $value->id }}" data-img="{{ Media::url($value->avatar) }}">{{ __($value->name) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="ajax-pages" data-url="{{ route('app.watermark.load') }}" data-resp=".ajax-pages">
        
                    <div class="pb-30 mt-200 ajax-scroll-loading">
                        <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
            
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ module_folder_url('/assets/js/watermark.js'); }}"></script>
@endsection
