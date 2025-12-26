@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Mail Sender') }}"
        description="{{ __('Send custom messages manually to selected user accounts.') }}"
    >
    </x-sub-header>
@endsection

@section('form', json_encode([
    'action' => module_url("save"),
    'method' => 'POST',
    'class' => 'actionForm',
    'data-redirect' => module_url()
]))

@section('content')

<div class="container pb-5 mt-5">
    <input class="d-none" name="id" type="text" value="">
    <div class="row">
        <div class="col-md-8">
            <div class="card b-r-6 border-gray-300 mb-3">
                <div class="card-body">
                    <div class="msg-errors"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="to" class="form-label">{{ __('Recipient(s)') }}</label>
                                <select name="user_ids[]" class="form-select h-auto" data-control="select2" data-select2-tags="true" multiple required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->fullname }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="subject" class="form-label">{{ __('Subject') }}</label>
                                <input placeholder="{{ __('') }}" class="form-control" name="subject" id="subject" type="text" value="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="content" class="form-label">{{ __('Message') }} (<span class="text-danger">*</span>)</label>
                                <textarea class="textarea_editor border-gray-300 border-1 min-h-100" name="content" placeholder="{{ __("Enter content") }}"></textarea>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <div class="col-md-4">
            <div class="card b-r-6 border-gray-300 mb-3">
                <div class="card-header bg-gray-100 fw-5 fs-14">
                  {{ __('Available Variables') }}
                </div>
                <div class="card-body p-3 fs-13">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><code>[username]</code> – {{ __('Username') }}</li>
                        <li class="list-group-item"><code>[fullname]</code> – {{ __('Full name') }}</li>
                        <li class="list-group-item"><code>[email]</code> – {{ __('Email address') }}</li>
                        <li class="list-group-item"><code>[plan_name]</code> – {{ __('Plan name') }}</li>
                        <li class="list-group-item"><code>[plan_desc]</code> – {{ __('Plan description') }}</li>
                        <li class="list-group-item"><code>[plan_price]</code> – {{ __('Plan price') }}</li>
                        <li class="list-group-item"><code>[expiration_date]</code> – {{ __('Expiration date') }}</li>
                        <li class="list-group-item"><code>[plan_type]</code> – {{ __('Plan type') }}</li>
                        <li class="list-group-item"><code>[plan_trial_day]</code> – {{ __('Trial days') }}</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <button type="submit" class="btn btn-dark w-100">{{ __("Send") }}</button>
        </div>
    </div>



</div>
@endsection
