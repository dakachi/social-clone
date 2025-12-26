@extends('layouts.app')

@section('content')

@php
    $affiliate_info = Affiliate::info(false, Auth::id(), false);
@endphp


<div class="container w-100 pb-5 pt-5">
    <div class="row gy-4">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-body px-5 py-5">
                    <div class="mb-4">
                        <h2 class="fw-bold text-primary mb-4">{{ __("Earn with our affiliate program") }}</h2>
                        <p class="my-2 text-gray-700">{{ __("Your rewards will continue as long as their subscription remains active. You will receive a commission on each qualifying purchase at: :pecent%", ["pecent" => get_option("affiliate_commission_percentage", 15)]) }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold">{{ __("Affiliate Link") }}</label>
                        <div class="input-group mb-2">
                            <input id="affiliate-link" class="form-control input-group" disabled type="text" value="{{ url("?ref=".Auth::user()->id_secure ) }}">
                            <button class="btn btn-dark" onclick="Main.copyToClipboard('#affiliate-link')">{{ __('Copy') }}</button>
                        </div>
                        @php
                            $affiliateUrl = url("?ref=" . Auth::user()->id_secure);
                            $encodedUrl = urlencode($affiliateUrl);
                        @endphp

                        <div class="d-flex align-items-center">
                            <div class="me-2 fs-14 text-gray-800">{{ __("Share a link on:") }}</div>
                            <div>
                                <span class="me-1">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener">
                                        <i class="text-dark fs-4 fa-brands fa-square-facebook"></i>
                                    </a>
                                </span>
                                <span class="me-1">
                                    <a href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}" target="_blank" rel="noopener">
                                        <i class="text-dark fs-4 fa-brands fa-square-x-twitter"></i>
                                    </a>
                                </span>
                                <span class="me-1">
                                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $encodedUrl }}" target="_blank" rel="noopener">
                                        <i class="text-dark fs-4 fa-brands fa-linkedin"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    {{-- Send Email to specific email --}}
                    <form class="actionForm" method="POST" action="{{ route('app.send-affiliate') }}">
                        @csrf
                        <label class="form-label fw-bold text-dark">{{ __("Send an invitation via email") }}</label>
                        <div class="input-group">
                            <input name="email" class="form-control" placeholder="{{ __('Enter Email Address') }}" type="email" required>
                            <button class="btn btn-dark" type="submit">{{ __("Send") }}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body border-top bg-gray-100 fw-6 py-2">
                    How it works
                </div>

                <div class="card-body p-0">
                    <div class="row gx-0">
                        @php
                            $steps = [
                                [
                                    'num' => 1,
                                    'title' => __('Share & Promote Link'),
                                    'desc' => __('Share your unique affiliate link with friends and followers to start spreading the word.'),
                                    'icon' => 'fa-light fa-share-nodes'
                                ],
                                [
                                    'num' => 2,
                                    'title' => __('First Sales & More'),
                                    'desc' => __('Earn rewards when someone signs up for a paid plan using your referral link.'),
                                    'icon' => 'fa-light fa-coins'
                                ],
                                [
                                    'num' => 3,
                                    'title' => __('Generate Income Effortlessly'),
                                    'desc' => __('Enjoy automatic, recurring commissions every time your referrals renew their subscription.'),
                                    'icon' => 'fa-light fa-bolt'
                                ]
                            ];
                        @endphp
                        @foreach ($steps as $step)
                            <div class="col-lg-4 col-12">
                                <div class="p-4 border-top hp-100 {{ $step['num'] != 3?"border-end":"" }}">
                                    <div class="d-flex flex-column mb-2">
                                        <div class="step-num mb-3 d-flex align-items-center justify-content-center bg-gray-100 size-50 b-r-100 text-dark border border-gray-900">
                                            <i class="{{ $step['icon'] }} fs-3"></i>
                                        </div>
                                        <div class="fw-6 fs-14 mb-2 text-dark">
                                            {{ $step['title'] }}
                                        </div>
                                        <div class="text-gray-700 fs-12">{{ $step['desc'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card hp-100">
                <div class="card-body px-0 py-5 d-flex flex-column">
                    <div class="mb-0">
                        <div class="d-flex flex-column align-items-center justify-content-center mb-3 gap-1">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-100 mb-2 size-70 border-dashed border border-warning-300">
                                <i class="fa-light fa-trophy-star fs-2 text-warning"></i>
                            </span>
                            <div class="fw-bold fs-20 mb-1">{{ __("Your Balance") }}</div>
                        </div>
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="mb-5 mt-5">
                                <div class="fs-55 fw-bold text-primary">{{ Core::currency(($affiliate_info["total_balance"])) }}</div>
                            </div>

                        </div>
                    </div>
                    <div class="mt-auto">
                        <div class="d-flex flex-column mt-3 w-100 align-items-center border-top border-dashed">
                            @php
                                $items = [
                                    [
                                        'label' => __("Total Paid"),
                                        'value' => $affiliate_info["total_withdrawal"],
                                        'bg'    => 'bg-warning-100',
                                        'icon'  => 'fa-light fa-hourglass-half',
                                        'text'  => 'text-warning'
                                    ],
                                    [
                                        'label' => __("Total Approved"),
                                        'value' => $affiliate_info["total_approved"],
                                        'bg'    => 'bg-success-100',
                                        'icon'  => 'fa-light fa-check-circle',
                                        'text'  => 'text-success'
                                    ],
                                    [
                                        'label' => __("Rejected"),
                                        'value' => $affiliate_info["rejected_commission"],
                                        'bg'    => 'bg-danger-100',
                                        'icon'  => 'fa-light fa-times-circle',
                                        'text'  => 'text-danger'
                                    ],
                                ];
                            @endphp
                            @foreach($items as $item)
                            <div class="d-flex align-items-center justify-content-between w-100 border-bottom border-dashed px-4 py-2">
                                <span class="size-25 {{ $item['text'] }}"><i class="{{ $item['icon'] }}"></i></span>
                                <span class="fs-14">{{ $item['label'] }}</span>
                                <span class="ms-auto fs-14 fw-6">
                                    {{ Core::currency(($item['value'])) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center pt-3">
                            <a href="{{ url('terms-of-service') }}" class="text-dark text-decoration-underline fs-15" target="_blank" rel="noopener">
                                {{ __("Learn more about our conditions") }}
                                <i class="ms-1 fa-solid fa-circle-info"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-12">
            <div class="card overflow-hidden hp-100">
                <div class="card-header">
                    <div>
                        <div class="fw-5">{{ __("Your referral stats") }}</div>
                        <div class="text-gray-600 fs-14">
                            {{ __('See detailed statistics about your affiliate performance') }}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <th scope="row">{{ __("Clicks") }}</th>
                                <td class="fs-14">{{ __("Total times your affiliate link was visited by potential users.") }}</td>
                                <td class="border-start text-end fw-6 fs-14">{{ number_format($affiliate_info["total_clicks"]) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">{{ __("Conversions") }}</th>
                                <td class="fs-14">{{ __("Number of successful sign-ups and purchases through your link.") }}</td>
                                <td class="border-start text-end fw-6 fs-14">{{ number_format($affiliate_info["total_conversions"]) }}</td>

                            </tr>
                            <tr>
                                <th scope="row">{{ __("Pending") }}</th>
                                <td class="fs-14">{{ __("Earnings that are awaiting verification or approval before payout.") }}</td>
                                <td class="border-start text-end fw-6 fs-14">{{ Core::currency(($affiliate_info["pending_commission"])) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">{{ __("Approved") }}</th>
                                <td class="fs-14">{{ __("Earnings that have been approved and are ready for payout.") }}</td>
                                <td class="border-start text-end fw-6 fs-14">{{ Core::currency(($affiliate_info["approved_commission"])) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">{{ __("Rejected") }}</th>
                                <td class="fs-14">{{ __("Commissions that were rejected or cancelled.") }}</td>
                                <td class="border-start text-end fw-6 fs-14">{{ Core::currency(($affiliate_info["rejected_commission"])) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="fw-5">{{ __("Withdrawal Request") }}</div>
                </div>
                <div class="card-body">
                    <form class="actionForm" action="{{ module_url("withdrawal-request") }}" data-confirm="{{ __("I confirm that all provided info is accurate") }}">
                        <div class="mb-3">
                            <label class="form-label input-group">{{ __("Your Bank Information") }}</label>
                            <textarea class="form-control" id="bank" name="bank" type="text" value=""></textarea>
                        </div>
                        <div class="">
                            <label class="form-label input-group">{{ __("Amount ($)") }}</label>
                            <input class="form-control input-group mb-3" placeholder="{{ __('Minimum Amount Withdrawal is :amount', ['amount' => '$' . Affiliate::info('min_withdrawal')]) }}" type="number" id="amount" name="amount" step="any" min="0.01" value="">
                            <div class="fs-12 mb-2">
                                <div class="mb-1">
                                    <span class="text-gray-800">{{ __("Payments are accepted:") }}</span>
                                    <span class="fw-bold">{{ get_option("affiliate_types_of_payments", "") }}</span>
                                </div>
                                <div class="text-left">
                                    <span class="text-gray-800">{{ __("A minimum withdrawal amount:") }}</span>
                                    <span class="text-danger fw-bold">
                                        ${{ Affiliate::info("min_withdrawal") }}
                                </span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">{{ __("Send Request") }}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <form class="actionMulti">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-8">
                            <div class="d-flex gap-16 align-items-center">
                                <div class="fw-5">{{ __("Payout Request") }}</div>
                                <div class="table-info fs-12 text-gray-700"></div>
                            </div>
                            <div class="d-flex flex-wrap gap-8">
                                <div class="d-flex">
                                    <div class="form-control form-control-sm">
                                        <button class="btn btn-icon">
                                            <i class="fa-light fa-magnifying-glass"></i>
                                        </button>
                                        <input name="datatable_filter[search]" placeholder="{{ __('Search') }}" type="text"/>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <select class="form-select form-select-sm datatable_filter" name="datatable_filter[status]">
                                        <option value="-1">{{ __('All') }}</option>
                                        <option value="0">{{ __('Pending') }}</option>
                                        <option value="1">{{ __('Approved') }}</option>
                                        <option value="2">{{ __('Rejected') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 border-0">
                        @if(!empty($Datatable['columns']))
                        <div class="table-responsive">
                            <table id="{{ $Datatable['element'] }}" data-url="{{ module_url("list") }}" class="display table table-bordered table-hide-footer w-100">
                                <thead>
                                    <tr>
                                        @foreach($Datatable['columns'] as $key => $column)

                                            @if($key == 0)
                                                <th class="align-middle w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input checkbox-all" type="checkbox" data-checkbox-parent=".table-responsive"/>
                                                    </div>
                                                </th>
                                            @elseif($key + 1 == count($Datatable['columns']))
                                                <th class="align-middle w-120 max-w-100">
                                                    {{ __('Actions') }}
                                                </th>
                                            @else
                                                <th class="align-middle">
                                                    {{ $column['data'] }}
                                                </th>
                                            @endif

                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="fs-14">
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer justify-center border-top-0">
                        <div class="d-flex flex-wrap justify-content-center align-items-center w-100 justify-content-md-between gap-20">
                            <div class="d-flex align-items-center gap-8 fs-14 text-gray-700 table-size"></div>
                            <div class="d-flex table-pagination"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>




@endsection

@section('script')
    <script type="text/javascript">
        Main.Emoji();
        Main.activeItem();
        var DataTable = Main.DataTable("#{{ $Datatable['element'] }}", {

            @if(!empty($Datatable['columns']))
                "columns": {!! json_encode($Datatable['columns']) !!},
            @endif

            @if(!empty($Datatable['lengthMenu']))
                "lengthMenu": {!! json_encode($Datatable['lengthMenu']) !!},
            @endif

            @if(!empty($Datatable['order']))
                "order": {!! json_encode($Datatable['order']) !!},
            @endif

            "columnDefs": [
                {
                    targets: 'id_secure:name',
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input checkbox-item" name="id[]" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    targets: 'client_name:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex gap-8 align-items-center">
                                <div class="size-40 size-child border b-r-6">
                                    <img src="https://cdn.pixabay.com/photo/2024/07/14/14/42/woman-8894656_1280.jpg" class="b-r-6">
                                </div>
                                <div class="text-start lh-1 text-truncate">
                                    <div class="fw-5 text-gray-900 text-truncate">
                                        <div class="text-truncate">
                                            <a class="text-gray-800 text-hover-primary actionItem" data-id="${row.RecordID}" href="{{ module_url("update") }}" data-popup="AdminPaymentHistoryModal">
                                                ${row.client_name}
                                            </a>
                                        </div>
                                        <div class="text-truncate text-gray-500 fs-12">
                                            ${row.client_position}
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 'content:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex gap-8 align-items-center">
                                <div class="text-start lh-1.1">
                                    <div class="fw-5 text-gray-900 text-truncate-3">
                                         ${row.content}
                                    </div>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 'category_name:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `
                            <div class="fs-12 d-flex gap-6 align-items-center">
                                <i class="${row.category_icon} text-${row.category_color} fs-12" ></i> ${row.category_name}
                            </div>`;
                    }
                },
                {
                    targets: 'status:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        switch(data) {
                          case 1:
                            var status_class = "badge-success";
                            var status_text = "{{ __("Approved") }}";
                            var status_icon = "fas fa-check-circle";
                            break;
                          case 2:
                            var status_class = "badge-danger";
                            var status_text = "{{ __("Rejected") }}";
                            var status_icon = "fas fa-times-circle";
                            break;
                          default:
                            var status_class = "badge-warning";
                            var status_text = "{{ __("Pending") }}";
                            var status_icon = "fas fa-hourglass-half";
                        }

                        return `
                            <div class="btn-group">
                                <span class="badge badge-outline badge-sm ${status_class} dropdown-toggle dropdown-arrow-hide" data-bs-toggle=""><i class="${status_icon} pe-2"></i> ${status_text}</span>
                                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("status/enable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                            <span >{{ __("Enable") }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("status/disable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye-slash"></i></span>
                                            <span>{{ __("Disable") }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("status/pending") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                            <span >{{ __("pending") }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>`;
                    }
                },
            ]
        });

        DataTable.columns(['client_position:name', 'client_avatar:name']).visible(false);

    Main.Chart('pie', [
        { name: "{{ __('Pending') }}", y: {{ floatval($affiliate_info["pending_commission"]) }}, color: '#f59e0b' },
        { name: "{{ __('Approved') }}", y: {{ floatval($affiliate_info["approved_commission"]) }}, color: '#22c55e' },
        { name: "{{ __('Reject') }}", y: {{ floatval($affiliate_info["rejected_commission"]) }}, color: '#ef4444' }
    ], 'affiliate-commission-chart', {
        chart: { type: 'pie', height: 250, width: 250, backgroundColor: 'transparent' },
        title: { text: '' },
        plotOptions: {
            pie: {
                innerSize: '70%',
                borderWidth: 0,
                dataLabels: { enabled: false }
            }
        },
        tooltip: { enabled: false },
        credits: { enabled: false },
        legend: { enabled: false }
    });
    </script>
@endsection

