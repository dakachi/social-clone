@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Search Media Online') }}" 
        description="{{ __('Search and download high-quality images and videos online.') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
    <div class="container py-4">
        <form class="searchMediaForm actionForm" action="{{ route("app.search_media.search") }}" data-content="search-media-result">
            <div class="d-flex border b-r-20 p-4 gap-12">
                <div class="form-control form-control-lg pe-0">
                    <span class="btn btn-icon">
                        <i class="fa-light fa-magnifying-glass"></i>
                    </span>
                    <input name="keyword" placeholder="{{ __('Enter keyword') }}" type="text">
                    <select class="max-w-120 border-start ps-3" name="source">
                        @php $services = SearchMedia::services(); @endphp

                        @if(empty($services))
                            <option value="">{{ __('No provider enabled') }}</option>
                        @else
                            @foreach($services as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        @endif
                    </select>
                    <button class="btn btn-dark btn-lg btl-r-0 bbl-r-0"><i class="fa-light fa-magnifying-glass"></i> {{ __('Search') }}</button>

                </div>
                <a href="{{ route('app.files.save_files') }}" class="btn btn-primary btn-lg actionMultiItem ms-auto text-nowrap" data-form=".searchMediaForm" data-redirect="">{{ __('Save To Files') }}</a>
            </div>
            <div class="modal-body p-0">
                <div class="py-4 search-media-result">

                    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                        <span class="fs-70 mb-3 text-primary">
                            <i class="fa-light fa-image-polaroid"></i>
                        </span>
                        <div class="fw-semibold fs-5 mb-2 text-gray-800">
                            {{ __('No media found') }}
                        </div>
                        <div class="text-body-secondary mb-4">
                            {{ __('Start by searching for high-quality images or videos from popular online sources.') }}
                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>
@endsection
