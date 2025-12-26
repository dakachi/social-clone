{{-- Product Preview --}}
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="rounded overflow-hidden border">
            <img src="{{ $product['preview'] }}" class="img-fluid w-100 object-fit-cover" style="aspect-ratio: 16/9;" alt="{{ $product['name'] }}">
        </div>

        @if (!empty($product['demo_url']))
            <div class="mt-3 text-center">
                <a href="{{ $product['demo_url'] }}" target="_blank" class="btn btn-dark rounded-pill px-4">
                    <i class="fa fa-eye me-1"></i> {{ __('Live Demo') }}
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Full Description --}}
@if (!empty($product['content']))
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            {!! $product['content'] !!}
        </div>
    </div>
@endif