@php
	use Carbon\Carbon;

	// Optional: specify range
	$now = Carbon::now();

	$rangeDays = $startDate->diffInDays($now);

	// Get all summarized data
	$info = PaymentReport::paymentInfo($startDate, $now);

	// Charts
	$gatewayChart = PaymentReport::paymentByGateway($startDate, $now);
	$incomeChart = PaymentReport::incomeByDay($startDate, $now);
	$latestPayments = PaymentReport::latestPayments();

	// Extract values for easy use in Blade
	$totalIncome = $info['total_income'];
	$growth = $info['income_growth'];
	$topGateway = $info['top_gateway'];

	$successTransactions = $info['success_transactions'];
	$successGrowth = $info['success_growth'];
	$refundedTransactions = $info['refunded_transactions'];
	$refundGrowth = $info['refund_growth'];


	$incomeByPlanChart = PaymentReport::incomeByPlan($startDate, $now);
	$incomeByPlanTotal = array_sum($incomeByPlanChart['series'][0]['data']);

	$planNames = $incomeByPlanChart['categories']; // Danh sách tên gói
	$planRevenues = $incomeByPlanChart['series'][0]['data']; // Giá trị theo từng gói

	$planChartSeries = [];

	foreach ($planRevenues as $index => $revenue) {
	    $percent = $incomeByPlanTotal > 0 ? round(($revenue / $incomeByPlanTotal) * 100, 1) : 0;

	    $planChartSeries[] = [
	        'y' => $revenue,
	        'percentage' => $percent,
	        'name' => $planNames[$index],
	    ];
	}
@endphp

<div class="row">

	<div class="col-md-4 mb-4">
		<div class="card shadow-sm">
			<div class="card-header">
				<div class="fw-5">{{ __('Total Income') }}</div>
			</div>

			<div class="card-body bg-squared">
				{{-- Icon --}}
				<div class="size-40 b-r-10 bg-primary-100 text-primary fs-20 d-flex align-items-center justify-content-center mb-5">
					<i class="fa-light fa-hands-holding-dollar"></i>
				</div>

				{{-- Amount & Growth --}}
				<div class="d-flex align-items-center mb-3 gap-12">
					<div class="fs-24 fw-bold text-dark">
						${{ number_format($totalIncome, 2) }}
					</div>

					@if ($growth > 0)
						<span class="fs-12 fw-6 text-success">
							<i class="fa-light fa-arrow-trend-up me-1"></i> +{{ $growth }}%
							<i class="fa-solid fa-caret-up"></i>
						</span>
					@elseif ($growth < 0)
						<span class="fs-12 fw-6 text-danger">
							<i class="fa-light fa-arrow-trend-down me-1"></i> -{{ abs($growth) }}%
							<i class="fa-solid fa-caret-down"></i>
						</span>
					@else
						<span class="fs-12 fw-6 text-muted">0%</span>
					@endif
				</div>

				{{-- Compare Text --}}
				<div class="mb-3 text-muted small">
					{{ sprintf(__('Compared to last %d days'), $rangeDays) }}
				</div>

				{{-- Top Gateway --}}
				<div class="text-muted small border-bottom pb-3 mb-3">
					<i class="me-1 fa-light fa-wa	var text-primary"></i>
					{{ __('Most used gateway') }}:
					<strong>{{ ucfirst($topGateway) }}</strong>
				</div>

				{{-- Success Transactions --}}
				<div class="d-flex justify-content-between align-items-center fs-13 mb-2">
					<div>
						<i class="fa-light fa-hexagon-check text-success fs-14"></i>
						{{ __("Success Transactions") }}
					</div>
					<div class="text-end">
						<span class="fw-6 me-2">{{ number_format($successTransactions) }}</span>
						<small class="{{ $successGrowth > 0 ? 'text-success' : ($successGrowth < 0 ? 'text-danger' : 'text-muted') }}">
						    @if ($successGrowth > 0)
						        <i class="fa-light fa-arrow-up-right me-1"></i>
						        +{{ $successGrowth }}%
						    @elseif ($successGrowth < 0)
						        <i class="fa-light fa-arrow-down-right me-1"></i>
						        -{{ abs($successGrowth) }}%
						    @else
						        0%
						    @endif
						</small>
					</div>
				</div>

				{{-- Refunded Transactions --}}
				<div class="d-flex justify-content-between align-items-center fs-13">
					<div>
						<i class="fa-light fa-octagon-xmark text-danger fs-14"></i>
						{{ __("Refund Transactions") }}
					</div>
					<div class="text-end">
						<span class="fw-6 me-2">{{ number_format($refundedTransactions) }}</span>
						<small class="{{ $refundGrowth > 0 ? 'text-success' : ($refundGrowth < 0 ? 'text-danger' : 'text-muted') }}">
						    @if ($refundGrowth > 0)
						        <i class="fa-light fa-arrow-up-right me-1"></i>
						        +{{ $refundGrowth }}%
						    @elseif ($refundGrowth < 0)
						        <i class="fa-light fa-arrow-down-right me-1"></i>
						        -{{ abs($refundGrowth) }}%
						    @else
						        0%
						    @endif
						</small>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="col-md-8 mb-4">
		
		<div class="card hp-100">
			<div class="card-header">
				<div class="fw-5">{{ __('Payments by Gateway') }}</div>
			</div>
			<div class="card-body py-2 px-2">
				<div class="d-flex flex-column gap-35">
					<div>
						<div id="payment-gateway-chart" class="export-chart" style="height: 300px;"></div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="col-md-12 mb-4">
		
		<div class="card">
			<div class="card-header">
				<div class="fw-5">{{ __('Daily Income') }}</div>
			</div>
			<div class="card-body py-2 px-2">
				<div id="income-by-day-chart" class="export-chart" style="height: 350px;"></div>
			</div>
		</div>

	</div>

	<div class="col-md-12 mb-4">
		
		<div class="card">
			<div class="card-header">
				<div class="fw-5">{{ __('Top Plans by Revenue') }}</div>
			</div>
			<div class="card-body py-2 px-2">
				<div id="income-by-plan-chart" class="export-chart" style="height: 400px;"></div>
			</div>
		</div>

	</div>

	<div class="col-md-12 mb-4">
		<div class="card hp-100">
			<div class="card-header">
				<div class="fw-5">{{  __("Latest Payments") }}</div>
			</div>

			<div class="card-body p-0">
				<table class="table">
					@foreach($latestPayments as $payment)
			    	<tr>
						<td class="w-220 text-truncate">
							<div class="d-flex align-items-center max-w-250 gap-12 ps-2">
								<div class="size-45 size-child border border-primary-200 pf-2 b-r-100">
									<img src="{{ Media::url($payment->user_avatar) }}" class="b-r-100">
								</div>
								<div class="d-flex flex-column max-w-250">
									<div>
										<div class="fs-12 fw-5 lh-1.1 text-truncate">{{ $payment->user_fullname }}</div>
										<div class="fs-12 text-gray-600 text-truncate">{{ $payment->user_email }}</div>
									</div>
								</div>
							</div>
						</td>
						<td class="w-70 fs-12">{{ ucfirst($payment->from) }}</td>
						<td  class="w-70">
							@if($payment->status == 1)
								<span class="badge badge-outline badge-sm badge-success">
		                     		{{ __("Success") }}
			                    </span>
							@else
								<span class="badge badge-outline badge-sm badge-danger">
		                     		{{ __("Refund") }}
			                    </span>
							@endif
						</td>
						<td  class="w-70 text-nowrap text-end">
							<div class="fs-12">{{ time_elapsed_string($payment->created) }}</div>
						</td>
			    	</tr>
					@endforeach
				</table>

			</div>
		</div>
	</div>

</div>

<script>
	var incomeChart = {!! json_encode($incomeChart) !!};

    Main.Chart('column', incomeChart.series, 'income-by-day-chart', {
        title: '{{ __("Daily Income") }}',
        xAxis: {
            categories: incomeChart.categories,
            gridLineWidth: 0,
            lineWidth: 0,
            labels: {
                rotation: 0,
                useHTML: true,
                formatter: function () {
                    const pos = this.pos;
                    const total = this.axis.categories.length;

                    if (pos === 0) {
                        return `<div style="text-align: left; transform: translateX(60px); width: 140px;">${this.value}</div>`;
                    } else if (pos === total - 1) {
                        return `<div style="text-align: right; transform: translateX(-55px); width: 140px;">${this.value}</div>`;
                    }
                    return '';
                },
                style: {
                    fontSize: '13px',
                    whiteSpace: 'nowrap',
                },
                overflow: 'none',
                crop: false,
            },
            crosshair: {
                width: 1,
                color: '#ddd',
                dashStyle: 'Dash'
            }
        },
        yAxis: {
            title: { text: ' ' },
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash',
            gridLineWidth: 1
        },
        legend: {
            enabled: false
        },
        tooltip: {
            shared: true,
            valuePrefix: '$',
            backgroundColor: '#fff',
            borderColor: '#ddd',
            borderRadius: 8,
            shadow: true
        },
        plotOptions: {
            spline: {
		        fillOpacity: 0.1,
		        lineWidth: 3,
		        marker: {
		            enabled: false
		        }
		    },

		    series: {
		    	stacking: 'normal',
                marker: {
                    enabled: false,
                    states: {
                        hover: {
                            enabled: false
                        }
                    }
                },
		        color: '#675dff',
		        fillColor: {
		            linearGradient: [0, 0, 0, 200],
		            stops: [
		                [0, 'rgba(103, 93, 255, 0.4)'],
		                [1, 'rgba(255, 255, 255, 0)']
		            ]
		        }
		    }
        },
    });

   	var gatewayChart = {!! json_encode($gatewayChart) !!};

    Main.Chart('column', gatewayChart.series, 'payment-gateway-chart', {
        title: '{{ __("Payments by Gateway") }}',
        xAxis: {
            categories: gatewayChart.categories,
            lineColor: '#ddd',
            lineWidth: 1,
            gridLineWidth: 0,
            labels: {
                style: {
                    fontSize: '13px',
                    color: '#333'
                }
            }
        },
        yAxis: {
            title: {
                text: ' '
            },
            gridLineWidth: 1,
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash'
        },
        legend: {
            enabled: false
        },
        tooltip: {
            valuePrefix: '$',
            shared: true
        },
        plotOptions: {
            column: {
                borderRadius: 6,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return `$${this.y.toLocaleString()}`;
                    }
                }
            }
        }
    });

	var planChartSeries = {!! json_encode($planChartSeries) !!};
	var planNames = {!! json_encode($planNames) !!};

	Main.Chart('column', [{
	    name: '{{ __("Revenue") }}',
	    data: planChartSeries
	}], 'income-by-plan-chart', {
	    title: '{{ __("Top Plans by Revenue") }}',
	    xAxis: {
	        categories: planNames
	    },
	    tooltip: {
	        useHTML: true,
	        formatter: function () {
	            const point = this.point;
	            return `<strong>${point.name}</strong><br>$${point.y.toLocaleString()} (${point.percentage}%)`;
	        }
	    },
	    plotOptions: {
	        column: {
	            borderRadius: 6,
	            colorByPoint: true,
	            dataLabels: {
	                enabled: true,
	                formatter: function () {
	                    return this.point.percentage + '%';
	                },
	                style: {
	                    fontSize: '13px',
	                    fontWeight: 'bold',
	                    color: '#333',
	                    textOutline: 'none'
	                }
	            }
	        }
	    },
	    legend: { enabled: false }
	});
</script>