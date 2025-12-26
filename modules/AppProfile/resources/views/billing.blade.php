@extends('layouts.app')

@section('content')
    @include("appprofile::partials.profile-header")

    <div class="container max-w-800 pb-5 pt-5">
        <div class="mb-5">
            <x-sub-header
                title="{{ __('My Billings') }}"
                description="{{ __('View your payment history and download invoices.') }}"
            />
        </div>

        <div class="card mb-4">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 fs-13">
                        <thead class="table-light">
                            <tr>
                                <th class="btl-r-15">{{ __('Invoice #') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end btr-r-15">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($billings as $bill)
                                <tr>
                                    <td class="fw-bold text-uppercase">{{ $bill->id_secure }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->created)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="fw-bold">
                                            {{ number_format($bill->amount, 2) }}
                                            <span class="text-uppercase">{{ $bill->currency }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        @if($bill->status == 1)
                                            <span class="badge badge-outline badge-pill badge-sm badge-success">{{ __('Paid') }}</span>
                                        @elseif($bill->status == 2)
                                            <span class="badge badge-outline badge-pill badge-sm badge-primary">{{ __('Pending') }}</span>
                                        @elseif($bill->status == 0)
                                            <span class="badge badge-outline badge-pill badge-sm badge-danger">{{ __('Rejected') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($bill->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('app.profile.billing.download_invoice', $bill->id_secure) }}" class="btn btn-sm b-r-50 btn-outline btn-primary me-2">
                                            <i class="fa-light fa-file-arrow-down"></i> {{ __('Invoice') }}
                                        </a>
                                        <a href="{{ route('app.profile.billing.show_invoice', $bill->id_secure) }}" class="btn btn-sm b-r-50 btn-light">
                                            <i class="fa-light fa-eye"></i> {{ __('Detail') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fa-light fa-file-invoice-dollar fa-2x mb-2"></i>
                                        <div>{{ __('No billing history yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
   

                @if($billings->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $billings->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .card {border-radius: 1.5rem;}
        .table thead th {border-top: none;}
    </style>
@endsection
