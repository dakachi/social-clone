@extends('layouts.app')

@section('form', json_encode([
    'action' => module_url("save"),
    'method' => 'POST',
    'class' => 'actionForm',
    'data-redirect1' => module_url()
]))

@section('sub_header')
    <x-sub-header
        title="{{ $result ? __('Edit plan') : __('Create new plan') }}"
        description="{{ $result ? __('Modify existing subscription plan details and pricing options.') : __('Easily create and customize a new subscription plan.') }}"
    >
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')

    <div class="container max-w-800 pb-5">

        <input type="hidden" name="id" value="{{ $result->id_secure ?? '' }}">

        <div class="card b-r-6 border-gray-300 mb-3">

            <div class="card-header">
                <div class="fw-5">
                    {{ __("Plan info") }}
                </div>
            </div>

            <div class="card-body">

                <div class="msg-errors"></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" @checked(($result->status ?? 1) == 1)>
                                    <label class="form-check-label mt-1" for="status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="0" id="status_0" @checked(($result->status ?? 1) == 0)>
                                    <label class="form-check-label mt-1" for="status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Featured') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="featured" value="1" id="featured_1" @checked(($result->featured ?? 0) == 1)>
                                    <label class="form-check-label mt-1" for="featured_1">
                                        {{ __('Yes') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="featured" value="0" id="featured_2" @checked(($result->featured ?? 0) == 0)>
                                    <label class="form-check-label mt-1" for="featured_2">
                                        {{ __('No') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input placeholder="{{ __('') }}" class="form-control" name="name" id="name" type="text" value="{{ $result->name ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="code" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" name="desc">{{ $result->desc ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="position" class="form-label">{{ __('Position') }}</label>
                            <input class="form-control" name="position" id="position" type="number" value="{{ $result->position ?? 0 }}">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="card b-r-6 border-gray-300 mb-3">

            <div class="card-header">
                <div class="fw-5">
                    {{ __("Pricing") }}
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Price') }}</label>
                            <input class="form-control" name="price" id="price" type="float" value="{{ $result->price ?? 0 }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="type" class="form-label">{{ __('Payment Frequency') }}</label>
                            <select class="form-select" name="type">
                                <option value="1" @selected(($result->type ?? 1) == 1)>{{ __("Monthly") }}</option>
                                <option value="2" @selected(($result->type ?? 1) == 2)>{{ __("Yearly") }}</option>
                                <option value="3" @selected(($result->type ?? 1) == 3)>{{ __("Lifetime") }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="free_plan" class="form-label">{{ __('Free Plan') }}</label>
                            <select class="form-select" name="free_plan">
                                <option value="1" @selected(($result->free_plan ?? 0) == 1)>{{ __("Yes") }}</option>
                                <option value="0" @selected(($result->free_plan ?? 0) == 0)>{{ __("No") }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="trial_day" class="form-label">{{ __('Trial day') }}</label>
                            <input placeholder="{{ __('') }}" class="form-control" name="trial_day" id="trial_day" type="number" value="{{ $result->trial_day ?? 0 }}">
                        </div>
                    </div>
                    <div class="text-gray-600 fs-12 mb-2">
                        <div><i class="fa-light fa-circle-info me-1"></i><b>{{ __("Free Plan: ") }}</b>{{ __("Select YES to make the plan free with no expiration date.") }}</div>
                        <div><i class="fa-light fa-circle-info me-1"></i><b>{{ __("Trial Plan: ") }}</b>{{ __("Select NO (Free Plan) to activate a trial period with a defined expiration date.") }}</div>
                    </div>
                </div>

            </div>

        </div>

        <div class="plan-permissions">

            @php
                $permissions = $result->permissions ?? [];
                $permissions = collect($permissions)->pluck('value', 'key')->toArray();

                $plan_permissions = app('plan_permissions') ?? [];
                $plan_permissions = Plan::syncPlanPermissions($plan_permissions);
                app()->instance('plan_permissions', $plan_permissions);
            @endphp

            @if(app('plan_permissions'))

                @foreach(app('plan_permissions') as $value)

                    @if( $value['view'] ?? false )

                        @php
                            $viewPath = $value['key'] . '::' . $value['view'];
                        @endphp

                        @if (View::exists($viewPath))
                            @include($viewPath)
                        @endif

                    @else

                        <div class="card b-r-6 border-gray-300 mb-3">
                            <div class="card-header">
                                <div class="form-check">
                                    <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="permissions[{{ $value['key'] }}]" name="permissions[{{ $value['key'] }}]" @checked( array_key_exists($value['key'], $permissions ) )>
                                    <input class="form-control d-none" name="labels[{{ $value['key'] }}]" type="text" value="{{ $value['name'] }}">
                                    <label class="fw-6 fs-14 text-gray-700 ms-2" for="permissions[{{ $value['key'] }}]">
                                        {{ __($value['name']) }}
                                    </label>
                                </div>
                            </div>
                        </div>

                    @endif

                @endforeach

            @endif



        </div>

        <div>
            <button type="submit" class="btn btn-dark w-100">{{ __("Save changes") }}</button>
        </div>
    </div>

@endsection
