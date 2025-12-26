<div class="fs-20 fw-bold mb-4">{{ __('Changelog') }}</div>

@if(empty($changelog) || !is_array($changelog))
    <div class="alert alert-warning text-center">No changelog entries available.</div>
@else
    <div class="vstack gap-3">
        @foreach($changelog as $log)
            <div class="p-3 px-4 rounded-3 bg-white border shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-top mb-3">
                <div class="me-md-3">
                    <div class="mb-1">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small">
                            Version {{ $log['version'] ?? '?' }}
                        </span>
                    </div>
                    <h6 class="fw-semibold mb-1 text-dark">{{ $log['title'] ?? 'Update' }}</h6>
                    <p class="text-muted mb-0 small">
                        {!! nl2br(e($log['content'] ?? '')) !!}
                    </p>
                </div>
                <div class="text-muted small mt-2 mt-md-0 text-nowrap">
                    {{ !empty($log['published_at']) ? \Carbon\Carbon::parse($log['published_at'])->format('M d, Y') : '—' }}
                </div>
            </div>
        @endforeach
    </div>
@endif