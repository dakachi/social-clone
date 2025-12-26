@extends('layouts.app')

@section('content')
    <div class="container hp-100">
        <div class="text-danger d-flex align-items-center justify-content-center hp-100">
        	<div class="d-flex flex-column align-items-center justify-content-center">
        		<div class="mb-3">
        			{{ $analytics['message'] }}
        		</div>

        		<a href="{{ module_url() }}" class="btn btn-dark">{{ __("Back") }}</a>
        	</div>
        </div>
    </div>
@endsection