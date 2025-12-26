@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Affiliate Reports') }}" 
        description="{{ __('Affiliate Reports provide insights into commission earnings and referral performance') }}" 
    >
        <a  href="{{ route("admin.affiliate.export_pdf") }}" class="btn btn-dark exportPDF">{{ __("Export PDF") }}</a>
    </x-sub-header>
@endsection

@section('content')
<div class="container pb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div></div>
            <div class="d-flex align-items-center justify-content-between gap-8">
                <select class="form-select ajax-pages-filter w-220" data-width="220" name="user_id" id="user_id" data-control="select2" data-ajax-url="{{ route('admin.users.search') }}" data-selected-id="">
                    <option value="-1">{{ __("All Affiliates") }}</option>
                </select>

                <div>
                    <div class="daterange d-none bg-white b-r-4 fs-12 border-gray-300 border" data-open="left"></div>
                </div>
            </div>
        </div>

        <div class="ajax-pages" data-url="{{ route('admin.affiliate.statistics') }}" data-resp=".ajax-pages">
            
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