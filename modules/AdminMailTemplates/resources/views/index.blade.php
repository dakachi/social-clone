@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Mail Templates') }}" 
        description="{{ __('Reusable email content layouts for system notifications.') }}" 
    />
@endsection

@section('content')
<div class="container py-4">
    <div class="accordion" id="mailTemplatesAccordion">
        @php $accordionId = 1; @endphp
        @foreach($allTemplates as $module => $templates)
            @php
                $moduleInfo = \Module::find($module);
                $module_path = $moduleInfo->getPath();
            @endphp
            @foreach($templates as $tpl)
                @php
                    $collapseId = "collapse{$accordionId}";
                    $headingId = "heading{$accordionId}";
                    $viewId = preg_replace('/[^\w]/', '_', $tpl['view']);
                    $viewPath = $module_path . '/resources/views/' . $tpl['view'] . '.blade.php';
                @endphp

                @if(File::exists($viewPath))
                    <div class="accordion-item mb-2 border rounded-3">
                        <h2 class="accordion-header" id="{{ $headingId }}">
                            <div class="accordion-button collapsed fw-bold btr-r-6 btl-r-6" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                <div class="mb-0">
                                    <div class="fw-6 fs-14 text-gray-900">{{ $tpl['name'] ?? '' }}</div>
                                    <div class="fw-4 fs-12 text-gray-600">{{ $tpl['description'] ?? '' }}</div>
                                </div>
                            </div>
                        </h2>
                        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#mailTemplatesAccordion">
                            <div class="accordion-body p-0">
                                <form class="actionForm" method="POST" action="{{ module_url("save_template") }}" data-confirm="{{ __('Please confirm that all information is correct and that you intend to proceed with the changes.') }}">

                                    <textarea id="ta-{{ $viewId }}" name="content" class="form-control font-monospace template-content input-code min-h-500" rows="30" spellcheck="false">{!! File::get($viewPath) !!}</textarea>
                                    <input type="hidden" name="view" value="{{ $tpl['view'] }}">

                                    @if(!empty($tpl['variables']))
                                    <div class="px-4 p-2 small text-muted">
                                        <span class="fw-6 fs-12">{{ __('Variables:') }}</span>
                                        @foreach($tpl['variables'] as $var)
                                            <span class="badge badge-light border">&#123;&#123; ${{ $var }} &#125;&#125;</span>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="px-4 py-2 border-top">
                                        <button class="btn btn-success">{{ __("Save Changes") }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @php $accordionId++; @endphp
                @endif
            @endforeach
        @endforeach
    </div>
</div>
@endsection


@section('script')
<script type="text/javascript">
$('.accordion').on('shown.bs.collapse', function(e) {
    $(e.target).find('.input-code').each(function() {
        var editor = $(this).data('codemirror');
        if (editor) setTimeout(function() { editor.refresh(); }, 100);
    });
});
</script>
@endsection