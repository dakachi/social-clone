@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Credit Rates') }}"
        description="{{ __('View and set credit rates for all features in detail.') }}"
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin('settings/save') }}" method="POST">
        @foreach($creditRates as $creditRate)
            
            @php
                $view = $creditRate['key'].'::'.$creditRate['view'];
            @endphp

            @if(view()->exists($view))
                @include($view)
            @endif

        @endforeach

        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
