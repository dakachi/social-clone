@extends('layouts.app')

@section('content')
    @include("appprofile::partials.profile-header")

    <div class="container max-w-600 py-5">
        <div class="mb-5">
        	<x-sub-header
	            title="{{ __('Invoice Detail') }}"
	            description="{{ __('View or download your payment invoice.') }}"
	        />
        </div>

        <div class="card mb-4" style="border-radius:1.5rem;">
            <div class="card-body p-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="mb-1 fw-bold text-primary">
                            <i class="fa-light fa-file-invoice-dollar me-1"></i>
                            #<span class="text-uppercase">{{ $invoice->id_secure }}</span>
                        </h5>
                        <div class="text-muted small">
                            {{ __('Created at:') }}
                            {{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div>
                        <span class="badge {{ $invoice->status == 1 ? 'badge-success badge-pill px-4 badge-outline' : 'bg-warning text-dark' }}">
                            {{ $invoice->status == 1 ? __('Paid') : __('Pending') }}
                        </span>
                    </div>
                </div>

                <hr class="mb-4">

                <div class="row mb-3">
                    <div class="col-6">
                        <div class="mb-2 fs-14 fw-semibold">{{ __('Plan') }}</div>
                        @if($invoice->plan)
                            <div class="fw-bold" style="color:#675dff;">
                                {{ $invoice->plan->name }}
                            </div>
                            @if($invoice->plan->desc)
                                <div class="text-muted small">{{ $invoice->plan->desc }}</div>
                            @endif
                        @else
                            <div>-</div>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                        	<div class="mb-2 fs-14 fw-semibold">{{ __('Transaction From') }}</div>
                        	<div class="fs-14">{{ ucfirst($invoice->from) }}</div>
                        </div>
                        <div class="mb-3">
                        	<div class="mb-2 fs-14 fw-semibold">{{ __('Transaction ID:') }}</div>
                        	<div class="fs-14 text-break">{{ $invoice->transaction_id }}</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="mb-2 fs-14 fw-semibold">{{ __('Amount') }}</div>
                        <div class="fs-5 fw-bold">
                            {{ number_format($invoice->amount, 2) }} <span class="text-uppercase">{{ $invoice->currency }}</span>
                        </div>
                    </div>
                </div>

                <hr class="mb-4">

                <div class="d-flex gap-16 flex-column justify-content-between align-items-center">
                    <div class="fs-12">
                        <span class="text-muted">{{ __('Last updated:') }}</span>
                        {{ \Carbon\Carbon::createFromTimestamp($invoice->changed)->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <a href="{{ route('app.profile.billing.download_invoice', $invoice->id_secure) }}" class="btn btn-dark wp-100">
                            <i class="fa-light fa-file-arrow-down me-1"></i> {{ __('Download PDF') }}
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>

        <div>
        	<a href="{{ route('app.profile', ['page' => 'billing']) }}" class="btn btn-link text-gray-600 border-0 wp-100 b-r-50">
	            <i class="fa-light fa-arrow-left"></i> {{ __('Back to Billing') }}
	        </a>
        </div>
    </div>
@endsection
