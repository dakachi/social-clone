@extends('layouts.app')

@section('content')
    <div class="max-w-600 mx-auto p-5 d-flex align-items-center hp-100 min-h-700">
        <div class="card fs-14">
            <div class="card-body py-5">
               	<div class="text-center">
               		<div class="fs-90 text-danger mb-3"><i class="fa-duotone fa-solid fa-circle-xmark"></i></div>
               		<div class="fs-30 fw-9 text-gray-900 mb-3">{{ __("Payment Unsuccessful") }}</div>
               		<div class="fs-14 text-gray-600">{{ __("Unfortunately, your payment could not be completed. Please check your payment details or try again with a different method. If the issue persists, feel free to contact support.") }}</div>
               	</div>
            </div>

            <div class="card-body text-center border-top border-gray-300">
            	<a class="btn btn-dark" href="{{ route("app.dashboard.index") }}">{{ __("Go To Dashboard") }}</a>
            </div>
        </div>

    </div>
@endsection


