@foreach(\AdminDashboard::getDashboardItems() as $dashboardItem)
    @php
        $isVisible = $dashboardItem['visible'] ?? fn() => true;
    @endphp
    @if($isVisible())
        {!! is_callable($dashboardItem['item']) ? $dashboardItem['item']() : $dashboardItem['item'] !!}
    @endif
@endforeach