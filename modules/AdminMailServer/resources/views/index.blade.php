@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Mail Server') }}" 
        description="{{ __('Configure and manage your email server settings') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
    
<div class="container pb-5">
    <div class="row">
        <div class="col-md-6">
            
            <form class="actionForm mb-4" action="{{ url_admin("settings/save") }}">
                <div class="card shadow-none border-gray-300 mb-4">
                    <div class="card-header">
                        <div class="fw-5">
                            {{ __("General settings") }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label">{{ __('Email Protocol') }}</label>
                                    <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="mail_protocol" value="mail" id="mail_protocol_mai" {{ get_option("mail_protocol", "mail")=="mail"?"checked":"" }}>
                                            <label class="form-check-label mt-1" for="mail_protocol_mai">
                                                {{ __('Mail') }}
                                            </label>
                                        </div>
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="mail_protocol" value="smtp" id="mail_protocol_smtp"{{ get_option("mail_protocol", "mail")=="smtp"?"checked":"" }}>
                                            <label class="form-check-label mt-1" for="mail_protocol_smtp">
                                                {{ __('SMTP') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label">{{ __('Sender Email') }}</label>
                                    <input class="form-control" name="mail_sender_email" id="mail_sender_email" type="text" value="{{ get_option("mail_sender_email", "example@gmail.com") }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label">{{ __('Sender Name') }}</label>
                                    <input class="form-control" name="mail_sender_name" id="mail_sender_name" type="text" value="{{ get_option("mail_sender_name", "Admin") }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-none border-gray-300 mb-4">
                    <div class="card-header">
                        <div class="fw-5">
                            {{ __("SMTP Settings") }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label for="smtp_server" class="form-label">{{ __('SMTP Server') }}</label>
                                    <input class="form-control" name="smtp_server" id="smtp_server" type="text" value="{{ get_option("smtp_server", "") }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="smtp_username" class="form-label">{{ __('SMTP Username') }}</label>
                                    <input class="form-control" name="smtp_username" id="smtp_username" type="text" value="{{ get_option("smtp_username", "") }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="smtp_password" class="form-label">{{ __('SMTP Password') }}</label>
                                    <input class="form-control" name="smtp_password" id="smtp_password" type="text" value="{{ get_option("smtp_password", "") }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="smtp_port" class="form-label">{{ __('SMTP Port') }}</label>
                                    <input class="form-control" name="smtp_port" id="smtp_port" type="text" value="{{ get_option("smtp_port", "") }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="smtp_encryption" class="form-label">{{ __('SMTP Encryption') }}</label>
                                    <select class="form-select" name="smtp_encryption">
                                        <option value="NONE" {{ get_option("smtp_encryption", "TLS")=="NONE"?"selected":"" }}>{{ __("NONE") }}</option>
                                        <option value="TLS" {{ get_option("smtp_encryption", "TLS")=="TLS"?"selected":"" }}>{{ __("TLS") }}</option>
                                        <option value="SSL" {{ get_option("smtp_encryption", "TLS")=="SSL"?"selected":"" }}>{{ __("SSL") }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-0">
                    <button type="submit" class="btn btn-dark b-r-10 w-100">
                        {{ __('Save changes') }}
                    </button>
                </div>

            </form>

        </div>

        <div class="col-md-6">
            <form class="actionForm mb-4" action="{{ route('admin.mail-server.test') }}">
                <div class="card shadow-none border-gray-300 mb-4">
                    <div class="card-header">
                        <div class="fw-5">
                            {{ __("Test Send Email") }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-0">
                                    <label for="test_email" class="form-label">{{ __('Test Email') }}</label>
                                    <input class="form-control" name="test_email" id="test_email" type="text" placeholder="{{ __('Recipient email address') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-outline btn-info b-r-10 w-100">
                        {{ __('Send now') }}
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

@endsection
