@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Team Collaboration') }}"
        description="{{ __('Collaborate seamlessly with teams through shared access.') }}"
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container px-4">

    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5 max-w-400 mx-auto">
        <span class="fs-70 mb-3 text-primary">
            <i class="fa-light fa-users"></i>
        </span>
        <div class="fw-semibold fs-5 mb-2 text-gray-800">
            {{ __('Set your team name') }}
        </div>
        <div class="text-body-secondary mb-4 text-center">
            {{ __('Please choose a clear and unique name for your team. This will make it easier for everyone to collaborate and share resources together.') }}
        </div>
        <form action="{{ route('app.teams.save_team_name') }}" method="POST" class="actionForm wp-100">
            <div class="mb-3">
                <input type="text" name="team_name" class="form-control form-control-lg text-center" placeholder="{{ __('Enter your team name') }}" value="{{ old('team_name', $team->name) }}" required>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-dark btn-lg px-5 wp-100">
                    {{ __('Save changes') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

