@extends('layouts.app')

@section('content')
    <div class="container py-5">
        
        @include("components.main-message")

        <div class="ajax-pages" data-url="{{ route('app.dashboard.statistics') }}" data-resp=".ajax-pages">
            
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
@endsection
