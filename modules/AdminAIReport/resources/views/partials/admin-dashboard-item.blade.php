@php
    $aiModelChart = Credit::getCreditUsageByModel(-1, null, null, 'ai_%');
    $aiUsageData = Credit::getCreditUsageChartData(-1, null, null, 'ai_%');
@endphp
<div class="fw-bold fs-20 pb-4 pt-5">{{ __("AI Stats") }}</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="fw-5">{{ __('Daily AI Credit Usage') }}</div>
            </div>
            <div class="card-body py-2 px-2">
                <div id="ai-credit-usage-chart" class="export-chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="fw-5">{{ __('Credit Usage by AI Model') }}</div>
            </div>
            <div class="card-body py-2 px-2">
                <div id="ai-credit-model-chart" class="export-chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var aiModelChart = {!! json_encode($aiModelChart) !!};

    Main.Chart('column', aiModelChart.series, 'ai-credit-model-chart', {
        title: '{{ __("Credit Usage by AI Model") }}',
        xAxis: {
            type: 'category',
            labels: {
                style: {
                    fontSize: '13px',
                    color: '#333',
                }
            }
        },
        yAxis: {
            title: { text: ' ' },
            gridLineWidth: 1,
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash'
        },
        tooltip: {
            valueSuffix: ' credits',
            shared: true
        },
        legend: { enabled: false },
        plotOptions: {
            column: {
                borderRadius: 5,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return this.y.toLocaleString();
                    }
                }
            }
        }
    });

    var aiUsageData = {!! json_encode($aiUsageData) !!};

    Main.Chart('areaspline', aiUsageData.series, 'ai-credit-usage-chart', {
        title: '{{ __('Daily AI Credit Usage') }}',
        legend: {
            enabled: false
        },
        xAxis: {
            categories: aiUsageData.categories,
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
        yAxis: {
            title: { text: ' ' },
            gridLineColor: '#f3f4f6',
            gridLineDashStyle: 'Dash',
            gridLineWidth: 1
        },
        tooltip: {
            shared: true,
            valueSuffix: ' credits',
            backgroundColor: '#fff',
            borderColor: '#ddd',
            borderRadius: 8,
            shadow: true
        },
        plotOptions: {
            areaspline: {
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
        }
    });
</script>
