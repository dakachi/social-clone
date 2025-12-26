@php
    $notifications = \Notifier::getLatest(auth()->id(), 20);
    $unread = $notifications->whereNull('read_at');
@endphp

<div>
    <!-- Tabs -->
    <nav class="d-flex align-items-center justify-content-between border-bottom px-3">
        <div class="nav nav-tabs border-bottom-0" id="nav-tab" role="tablist">
            <button class="nav-link active" id="nav-unread-tab" data-bs-toggle="tab" data-bs-target="#notif-unread" type="button" role="tab">
                {{ __('Unread') }}
                @if($unread->count())
                    <span class="badge badge-danger badge-sm ms-1">{{ $unread->count() }}</span>
                @endif
            </button>
            <button class="nav-link" id="nav-all-tab" data-bs-toggle="tab" data-bs-target="#notif-all" type="button" role="tab">
                {{ __('All') }}
            </button>
        </div>
    </nav>

    <!-- Tab Content -->
    <div class="tab-content p-3 max-h-450 overflow-auto" id="nav-tabContent">
        <!-- Unread -->
        <div class="tab-pane fade show active" id="notif-unread" role="tabpanel">
            @forelse($unread as $item)
                @php
                    $message = $item->source === 'manual'
                        ? optional($item->manual)->message
                        : $item->message;
                @endphp
                <div class="border-bottom py-2 px-1">
                    <div class="d-flex justify-content-between">
                        <div class="fw-5 text-dark text-break">
                            <span class="badge badge-dark text-white h-20">{{ __('New') }}</span>
                        	<span>{!! nl2br(e($message)) !!}</span>
                    	</div>
                        <a href="{{ route('app.notifications.markAsRead', $item->id) }}" data-id="{{ $item->id }}" data-content="notif-list" class="min-w-20 min-h-20 max-h-20 size-20 b-r-50 d-flex align-items-center justify-content-center border border-1 bg-hover-success text-hover-white d-block actionItem" data-call-success="checkUnreads(result.count);"><i class="fa-solid fa-check"></i></a>
                    </div>
                    @if($item->url)
                        <a href="{{ $item->url }}" target="_blank" class="fs-12 text-primary d-block mt-1"> {{ __('View') }} &rarr; </a>
                    @endif
                    <div class="fs-12 text-muted mt-1">{{ $item->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="text-center text-muted py-3">{{ __('No unread notifications.') }}</div>
            @endforelse
        </div>

        <!-- All -->
        <div class="tab-pane fade" id="notif-all" role="tabpanel">
            @forelse($notifications as $item)
                @php
                    $message = $item->source === 'manual'
                        ? optional($item->manual)->message
                        : $item->message;
                @endphp
                <div class="border-bottom py-2 px-1">
                    <div class="fw-5 text-dark text-break">
                    	@if(is_null($item->read_at))
                            <span class="badge badge-dark text-white h-20">{{ __('New') }}</span>
                        @endif
                        d
                    	<span>{!! nl2br(e($message)) !!}</span>
                	</div>
                    @if($item->url)
                        <a href="{{ $item->url }}" target="_blank" class="fs-12 text-primary d-block mt-1"> {{ __('View') }} &rarr; </a>
                    @endif
                    <div class="fs-12 text-muted mt-1">{{ $item->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="text-center text-muted py-3">{{ __('No notifications found.') }}</div>
            @endforelse
        </div>
    </div>
</div>

<script>
	
</script>