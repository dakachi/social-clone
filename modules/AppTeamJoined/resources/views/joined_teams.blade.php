@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Joined Team') }}"
        description="{{ __('Access teams you’re a member of here.') }}"
        :count="$total"
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container px-4">

        <div class="ajax-scroll" data-url="{{ route("app.teams.joined.list") }}" data-resp=".team-list" data-scroll="document">

            <div class="row team-list">
                
            </div>

            <div class="pb-30 ajax-scroll-loading d-none">
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

