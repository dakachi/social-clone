@if(!empty($actions))
<div class="d-flex">
    <div class="btn-group">
        <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
            <i class="fa-light fa-grid-2"></i> {{ __('Actions') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-auto max-w-300">
            @foreach($actions as $action)

            	@if(!isset($action['divider']))
	                <li class="mx-2">
	                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ $action['url'] }}" data-confirm="{{ $action['confirm'] ?? '' }}" data-call-success="{{ $action['call_success'] }}">
	                        <span class="size-16 me-1 text-center"><i class="fa-light {{ $action['icon'] }}"></i></span>
	                        <span class="text-truncate">{{ $action['label'] }}</span>
	                    </a>
	                </li>
            	@else
            		<li><hr class="dropdown-divider"></li>
            	@endif
            @endforeach
        </ul>
    </div>
</div>
@endif