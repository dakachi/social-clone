<div class="fs-20 fw-bold mb-4">{{ __('Support Information') }}</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 small text-muted">
        {!! $support['info'] ?? '' !!}
    </div>
</div>

<a href="{{ $support['link'] ?? '#' }}" class="btn btn-dark mt-3 rounded-pill wp-100">
    <i class="fa fa-headset me-1"></i> {{ __('Contact Support') }}
</a>