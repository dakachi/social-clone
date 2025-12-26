@php
    $languages = Language::getLanguages();
@endphp

@extends('layouts.app')

@section('content')

    @include("appprofile::partials.profile-header")

    <div class="container max-w-700 pb-5 pt-5">
        <div class="mb-5">
            <x-sub-header
                title="{{ __('Your Profile') }}"
                description="{{ __('Update your personal information and password. Your information is safe with us.') }}"
            />
        </div>

        <form class="actionForm" action="{{ route('app.profile.update_profile') }}" enctype="multipart/form-data" data-redirect="">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="fw-5">{{ __("Update profile") }}</div>
                </div>
                <div class="card-body py-5 px-4">
                    <div class="d-flex flex-column flex-lg-row gap-20 align-items-start">
                        {{-- Avatar --}}
                        <div class="mb-4 mb-lg-0 text-center w-100" >
                            @include('appfiles::block_upload', [
                                "large" => true,
                                "id" => "avatar",
                                "name" => __("Upload Avatar"),
                                "value" => Media::url($user->avatar)
                            ])
                        </div>

                        <div class="flex-fill">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="fullname" class="form-label">{{ __('Fullname') }}</label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-user"></i></span>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="{{ $user->fullname }}" placeholder="{{ __('Fullname') }}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="username" class="form-label">{{ __('Username') }}</label>
                                    <div class="input-group">
                                        <span class="btn btn-input"><i class="fa-light fa-at"></i></span>
                                        <input type="text" class="form-control" id="username" name="username"
                                               value="{{ $user->username }}"
                                               placeholder="{{ __('Username') }}"
                                               @if(!get_option("auth_user_change_username_status", 0)) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <div class="input-group">
                                        <span class="btn btn-input"><i class="fa-light fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="{{ $user->email }}"
                                               placeholder="{{ __('Email') }}"
                                               @if(!get_option("auth_user_change_email_status", 0)) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="language" class="form-label">{{ __('Language') }}</label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-globe"></i></span>
                                        <select class="form-select" name="language" id="language">
                                            <option value="en">{{ __("Select your language") }}</option>
                                            @foreach($languages as $key => $value)
                                                <option value="{{ $value->code }}" {{ $user->language == $value->code ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-clock"></i></span>
                                        <select class="form-select" name="timezone" id="timezone">
                                            @foreach( tz_list() as $key => $value )
                                                <option value="{{ $key }}" {{ $user->timezone==$key?"selected":"" }} >{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark px-4 mt-4">
                                {{ __('Save changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Change Password --}}
        <form class="actionForm" action="{{ route('app.profile.change_password') }}" data-redirect="">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="fw-5">{{ __("Change password") }}</div>
                </div>
                <div class="card-body py-5 px-5">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-lock"></i></span>
                                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" placeholder="{{ __('Enter current password') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">{{ __('New Password') }}</label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" placeholder="{{ __('New password') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-key"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="{{ __('Confirm new password') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark mt-1">
                                {{ __('Save password') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Danger Zone --}}
        <div class="text-center d-none">
            <button type="button" class="btn btn-outline btn-danger btn-lg w-100" onclick="confirmDeleteAccount()">
                <i class="fa-light fa-trash"></i> {{ __('Delete Account') }}
            </button>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Optional: Live preview avatar
    document.getElementById('file-upload')?.addEventListener('change', function(e){
        if(e.target.files && e.target.files[0]){
            const reader = new FileReader();
            reader.onload = function(ev){
                document.querySelector('img[alt="Avatar"]').src = ev.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    function confirmDeleteAccount() {
        if (confirm("{{ __('Are you sure you want to delete your account? This action cannot be undone.') }}")) {
            // Make ajax request or redirect to delete route
            // window.location.href = "{{ url('profile/delete') }}";
        }
    }
</script>
@endpush