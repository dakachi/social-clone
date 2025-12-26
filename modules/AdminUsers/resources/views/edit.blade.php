@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Edit User') }}" 
        description="{{ __('Update existing user information and privileges') }}" 
    >
        <a class="btn btn-primary btn-sm d-flex align-items-center justify-content-between" href="{{ url_admin("users") }}">
            <span><i class="fa-light fa-list"></i></span>
            <span>
                {{ __('User list') }}
            </span>
        </a>
    </x-sub-header>
@endsection

@section('content')

<div class="container pb-5">

    <div class="row">
        <div class="col-md-12">
            <form class="actionForm" action="{{ url_admin("users/update_info") }}" data-redirect="{{ url_admin("users") }}">
                <input name="id_secure" type="hidden" value="{{ $result->id_secure }}">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ __('User information') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="max-w-220 min-w-220">
                                <div class="p-3 border-1 b-r-6">
                                    @include('appfiles::block_upload', [
                                        "large" => true,
                                        "id" => "avatar",
                                        "name" => __("Upload Avatar"),
                                        "value" => Media::url($result->avatar)
                                    ])
                                </div>
                            </div>
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label">{{ __('Role') }}</label>
                                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="role" id="role_1" value="1" @checked($result->role == 1)>
                                                    <label class="form-check-label mt-1">
                                                        {{ __('User') }}
                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="role" id="role_2" value="2" @checked($result->role == 2)>
                                                    <label class="form-check-label mt-1">
                                                        {{ __('Admin') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label">{{ __('Status') }}</label>
                                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="2" id="status_2" @checked($result->status == 2)>
                                                    <label class="form-check-label mt-1" for="status_2">
                                                        {{ __('Active') }}
                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" @checked($result->status == 1)>
                                                    <label class="form-check-label mt-1" for="status_1">
                                                        {{ __('Inactive') }}
                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="0" id="status_0" @checked($result->status == 0)>
                                                    <label class="form-check-label mt-1" for="status_0">
                                                        {{ __('Banned') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="fullname" class="form-label">{{ __('Fullname') }}</label>
                                            <div class="form-control">
                                                <i class="fa-light fa-user"></i>
                                                <input placeholder="{{ __('Fullname') }}" name="fullname" id="fullname" type="text" value="{{ $result->fullname }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="username" class="form-label">{{ __('Username') }}</label>
                                            <div class="form-control">
                                                <i class="fa-light fa-user"></i>
                                                <input placeholder="{{ __('Username') }}" name="username" id="username" type="text" value="{{ $result->username }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="email" class="form-label">{{ __('Email') }}</label>
                                            <div class="form-control">
                                                <i class="fa-light fa-envelope"></i>
                                                <input placeholder="{{ __('Email') }}" name="email" id="email" type="text" value="{{ $result->email }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                            <div class="form-control pe-0">
                                                <i class="fa-light fa-clock"></i>
                                                <select class="form-select" name="timezone" id="timezone">
                                                    <option value="">{{ __('Select timezone') }}</option>
                                                    @foreach( tz_list() as $key => $value )
                                                        <option value="{{ $key }}" @selected( $result->timezone == $key ) >{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
        <div class="col-md-6">
            <form class="actionForm hp-100" action="{{ url_admin("users/change_password") }}" data-redirect="{{ url_admin("users") }}">
                <input name="id_secure" type="hidden" value="{{ $result->id_secure }}">
                <div class="card mt-4 hp-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ __('Change password') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="msg-errors"></div>
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="password" class="form-label">{{ __('Password') }}</label>
                                            <div class="w-100">
                                                <div class="form-control">
                                                    <i class="fa-light fa-key"></i>
                                                    <input placeholder="{{ __('Password') }}" name="password" id="password" type="password" autocomplete="on" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="confirm_new_password" class="form-label">{{ __('Confirm password') }}</label>
                                            <div class="w-100">
                                                <div class="form-control">
                                                    <i class="fa-light fa-key"></i>
                                                    <input placeholder="{{ __('Confirm password') }}" name="password_confirmation" id="password_confirmation" autocomplete="on" type="password" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>

            </form>

        </div>
        <div class="col-md-6">

            <form class="actionForm hp-100" action="{{ url_admin("users/update_plan") }}" data-redirect="{{ url_admin("users") }}">
                <input name="id_secure" type="hidden" value="{{ $result->id_secure }}">
                <div class="card mt-4 hp-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ __('Plan') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="msg-errors"></div>
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="plan" class="form-label mb-1">{{ __('Plan') }}</label>
                                            <div class="text-gray-600 fs-12 mb-2">{{ __('All previous permissions will be removed when you switch to a new plan.') }}</div>
                                            <div class="form-control pe-0">
                                                <i class="fa-light fa-cubes"></i>
                                                <select class="form-select" name="plan">
                                                    <option value="0">{{ __('Select plan') }}</option>
                                                    @if($plans)
                                                        @foreach($plans as $plan)

                                                            @php

                                                                switch ($plan->type) {
                                                                    case 2:
                                                                        $type = __("Yearly");
                                                                        break;

                                                                    case 3:
                                                                        $type = __("Lifetime");
                                                                        break;
                                                                    
                                                                    default:
                                                                        $type = __("Monthly");
                                                                        break;
                                                                }

                                                            @endphp

                                                            <option value="{{ $plan->id }}" @selected($plan->id == $result->plan_id) >[{{ $type }}] {{ $plan->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="expiration_date" class="form-label mb-1">{{ __('Expiration date') }}</label>
                                            <div class="text-gray-600 fs-12 mb-2">{{ __('Set the value to -1 for unlimited') }}</div>
                                            <div class="input-group">
                                                <div class="form-control">
                                                    <i class="fa-light fa-calendar-clock"></i>
                                                    <input placeholder="{{ __('Expiration date') }}" name="expiration_date" class="dateBtn" id="expiration_date" type="text" value="{{ date_show($result->expiration_date) }}">
                                                    
                                                </div>
                                                <button type="button" class="btn btn-icon btn-light selectDate"><i class="fa-light fa-calendar-days"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    
</div>

@endsection

