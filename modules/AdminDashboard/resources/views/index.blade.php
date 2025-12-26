@extends('layouts.app')

@section('content')
    <div class="border-bottom mb-1 py-4 bg-polygon">
        <div class="container">
            <div class="d-flex justify-content-between align-self-center">
                <div class="mb-0">
                    <div class="fw-7 fs-20 mx-auto mb-2 text-primary-700">{{ __('Welcome, Admin') }} 🚀</div>
                    <div class="fw-5 text-gray-700">{{ __('Overview of platform performance, user activity, and system health.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">

        <div class="ajax-pages" data-url="{{ route('admin.dashboard.statistics') }}" data-resp=".ajax-pages">
            
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
