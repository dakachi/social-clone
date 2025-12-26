@php
	$summary = \UserReport::summary();
	$dailyRegistrations = \UserReport::dailyRegistrations();
	$monthly_growth_total =  $summary['monthly_growth']['total'];
	$monthly_growth_active =  $summary['monthly_growth']['active'];
	$monthly_growth_inactive =  $summary['monthly_growth']['inactive'];
	$monthly_growth_banned =  $summary['monthly_growth']['banned'];

	$weekly_growth_total =  $summary['weekly_growth']['total'];
	$weekly_growth_active =  $summary['weekly_growth']['active'];
	$weekly_growth_inactive =  $summary['weekly_growth']['inactive'];
	$weekly_growth_banned =  $summary['weekly_growth']['banned'];

	$latestUsers = \UserReport::latestUsers();
	$loginTypeChart = \UserReport::loginTypeStats();
@endphp

<div class="row">

	<div class="col-md-4">
		
		<div class="row">
			<div class="col-6 mb-4">
				<div class="card bg-squared">
					<div class="card-body">
						<div class="d-flex flex-column gap-35">
							<div class="size-40 b-r-10 bg-success-100 text-success fs-20 d-flex align-items-center justify-content-center ">
								<i class="fa-light fa-user"></i>
							</div>

							<div class="mt-auto">
								<div class="d-flex align-items-end gap-8">
									<div class="fw-6 fs-25">{{ Number::abbreviate($summary['total']); }}</div>
									<div class="{{ $monthly_growth_total >= 0 ? 'text-success':'text-danger' }} fs-12 fw-6">
										<i class="fa-light {{ $monthly_growth_total >= 0 ? 'fa-arrow-trend-up':'fa-arrow-trend-down' }}"></i> 
										{{ Number::abbreviate($monthly_growth_total) }}% 
										<i class="fa-solid {{ $monthly_growth_total >= 0 ? 'fa-caret-up':'fa-caret-down' }}"></i>
									</div>
								</div>
								<div class="fw-4 fs-13 text-gray-600">{{ __('Total user') }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 mb-4">
				<div class="card bg-squared">
					<div class="card-body">
						<div class="d-flex flex-column gap-35">
							<div class="size-40 b-r-10 bg-primary-100 text-primary fs-20 d-flex align-items-center justify-content-center ">
								<i class="fa-light fa-user-check"></i>
							</div>

							<div class="mt-auto">
								<div class="d-flex align-items-end gap-8">
									<div class="fw-6 fs-25">{{ Number::abbreviate($summary['active']); }}</div>
									<div class="{{ $monthly_growth_active >= 0 ? 'text-success':'text-danger' }} fs-12 fw-6">
										<i class="fa-light {{ $monthly_growth_active >= 0 ? 'fa-arrow-trend-up':'fa-arrow-trend-down' }}"></i> 
										{{ Number::abbreviate($monthly_growth_active) }}% 
										<i class="fa-solid {{ $monthly_growth_active >= 0 ? 'fa-caret-up':'fa-caret-down' }}"></i>
									</div>
								</div>
								<div class="fw-4 fs-13 text-gray-600">{{ __('Active user') }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 mb-4">
				<div class="card bg-squared">
					<div class="card-body">
						<div class="d-flex flex-column gap-35">
							<div class="size-40 b-r-10 bg-warning-100 text-warning fs-20 d-flex align-items-center justify-content-center ">
								<i class="fa-light fa-user-minus"></i>
							</div>

							<div class="mt-auto">
								<div class="d-flex align-items-end gap-8">
									<div class="fw-6 fs-25">{{ Number::abbreviate($summary['inactive']); }}</div>
									<div class="{{ $monthly_growth_inactive >= 0 ? 'text-success':'text-danger' }} fs-12 fw-6">
										<i class="fa-light {{ $monthly_growth_inactive >= 0 ? 'fa-arrow-trend-up':'fa-arrow-trend-down' }}"></i> 
										{{ Number::abbreviate($monthly_growth_inactive) }}% 
										<i class="fa-solid {{ $monthly_growth_inactive >= 0 ? 'fa-caret-up':'fa-caret-down' }}"></i>
									</div>
								</div>
								<div class="fw-4 fs-13 text-gray-600">{{ __('Inactive user') }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-6 mb-4">
				<div class="card bg-squared">
					<div class="card-body">
						<div class="d-flex flex-column gap-35">
							<div class="size-40 b-r-10 bg-danger-100 text-danger fs-20 d-flex align-items-center justify-content-center ">
								<i class="fa-light fa-user-xmark"></i>
							</div>

							<div class="mt-auto">
								<div class="d-flex align-items-end gap-8">
									<div class="fw-6 fs-25">{{ Number::abbreviate($summary['banned']); }}</div>
									<div class="{{ $monthly_growth_banned >= 0 ? 'text-success':'text-danger' }} fs-12 fw-6">
										<i class="fa-light {{ $monthly_growth_banned >= 0 ? 'fa-arrow-trend-up':'fa-arrow-trend-down' }}"></i> 
										{{ Number::abbreviate($monthly_growth_banned) }}% 
										<i class="fa-solid {{ $monthly_growth_banned >= 0 ? 'fa-caret-up':'fa-caret-down' }}"></i>
									</div>
								</div>
								<div class="fw-4 fs-13 text-gray-600">{{ __('Banned user') }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<div class="col-md-8 mb-4">
		<div class="card hp-100">
			<div class="card-header">
				<div class="fw-5">{{  __("Daily User Registrations") }}</div>
			</div>

			<div class="card-body py-2 px-2">
				<div class="export-chart h-290" id="daily-registrations-chart"></div>
			</div>
		</div>
	</div>

	<div class="col-md-6 mb-4">
		<div class="card hp-100">
			<div class="card-header">
				<div class="fw-5">{{  __("Latest Users") }}</div>
			</div>

			<div class="card-body p-0 max-h-400 overflow-auto">
				<table class="table">
				@foreach($latestUsers as $user)
			    	<tr>
						<td class="w-40 max-w-45">
							<div class="size-45 size-child border border-primary-200 pf-2 b-r-100">
								<img src="{{ Media::url($user->avatar) }}" class="b-r-100">
							</div>
						</td>
						<td class="w-220 text-truncate">
							<div class="d-flex flex-column max-w-250">
								<div class="fs-12 fw-5 lh-1.1 text-truncate">{{ $user->fullname }}</div>
								<div class="fs-12 text-gray-600 text-truncate">{{ $user->email }}</div>
							</div>
						</td>
						<td  class="w-70">
							@if($user->status == 2)
								<span class="badge badge-outline badge-sm badge-success">
		                     		{{ __("Active") }}
			                    </span>
							@elseif($user->status == 1)
								<span class="badge badge-outline badge-sm badge-warning">
		                     		{{ __("Inactive") }}
			                    </span>
							@else
								<span class="badge badge-outline badge-sm badge-danger">
		                     		{{ __("Banned") }}
			                    </span>
							@endif
						</td>
						<td  class="w-70 text-nowrap">
							<div class="fs-12">{{ time_elapsed_string($user->created) }}</div>
						</td>
			    	</tr>
				@endforeach
				</table>

			</div>
		</div>
	</div>
	<div class="col-md-6 mb-4">
		<div class="card hp-100">
			<div class="card-header">
				<div class="fw-5">{{  __("Login Method Breakdown") }}</div>
			</div>

			<div class="card-body py-2 px-2">
				<div class="export-chart h-350" id="login-type-chart"></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	

	var chartData = {
        categories: {!! json_encode($dailyRegistrations['categories']) !!},
        series: {!! json_encode($dailyRegistrations['series']) !!}
    };

    Main.Chart('areaspline', chartData.series, 'daily-registrations-chart', {
    	
        title: ' ',
        legend: {
		    enabled: false
		},
        xAxis: {
            categories: chartData.categories,
            title: { text: ' ' },
            crosshair: {
                width: 2,
                color: '#ddd',
                dashStyle: 'Solid'
            },
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
        
        yAxisTitle: ' '
    });

    Main.Chart('pie', {!! json_encode($loginTypeChart) !!}, 'login-type-chart', {
	    title: ' ',
        plotOptions: {
            pie: {
                showInLegend: true  // Ensure the pie chart slices are displayed in the legend
            }
        },
        legend: {
            enabled: true, // Make sure the legend is enabled
            align: 'center', // Align legend to the center
            verticalAlign: 'bottom', // Position the legend at the bottom
            layout: 'horizontal', // Display legend items horizontally
            itemStyle: {
                fontSize: '12px',
                fontWeight: 'normal',
            }
        }
	});

</script>