<div class="header">
    <div class="container1 px-3 hp-100">
        <div class="hp-100 d-flex justify-content-between">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-block d-sm-block d-md-none">
                    <button class="btn btn-icon btn-light sidebar-toggle">
                        <i class="fa-light fa-chevron-right"></i>
                    </button>
                </div>
                @foreach(\HeaderManager::getHeaderItems('start') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach

                @yield('header_start')
            </div>

            <div class="d-flex flex-grow-1 justify-content-between wp-100">
                @foreach(\HeaderManager::getHeaderItems('center') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach

                @yield('header_center')
            </div>

            <div class="d-flex align-items-center gap-16">
                @yield('header_end')

                @foreach(\HeaderManager::getHeaderItems('end') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach
                
            </div>
        </div>
    </div>
</div>