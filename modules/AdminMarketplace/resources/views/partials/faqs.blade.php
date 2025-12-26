<div class="accordion" id="faqsAccordion">
    @php $index = 1; @endphp
    @foreach($faqs as $faq)
        @php
            $collapseId = 'faqCollapse' . $index;
            $headingId  = 'faqHeading' . $index;
        @endphp

        <div class="accordion-item mb-3 border rounded-3">
            <h2 class="accordion-header" id="{{ $headingId }}">
                <button class="accordion-button collapsed fw-5 fs-14 btr-r-6 btl-r-6" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                    {{ $faq['name'] ?? 'FAQ' }}
                </button>
            </h2>
            <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#faqsAccordion">
                <div class="accordion-body">
                    {!! $faq['content'] ?? '' !!}
                </div>
            </div>
        </div>

        @php $index++; @endphp
    @endforeach

    @if(empty($faqs))
        <div class="alert alert-warning text-center">
            {{ __('No FAQs found for this module.') }}
        </div>
    @endif
</div>
