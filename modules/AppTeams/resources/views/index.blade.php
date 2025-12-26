@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Team Collaboration')  }}"
        description="{{ __('Collaborate seamlessly with teams through shared access.') }}"
        :count="$total"
    >
        <a class="btn btn-success btn-sm" href="{{ route('app.teams.set_team_name') }}">
            <span><i class="fa-light fa-pen-to-square"></i></span>
            <span>{{ __('Change Team Name') }}</span>
        </a>
        <a class="btn btn-dark btn-sm actionItem" href="{{ module_url('invite') }}" data-popup="inviteModal" >
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Invite') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
<div class="container px-4">

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".team-list" data-scroll="document">

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

