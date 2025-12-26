@php
	$languages = Language::getLanguages();
	$currentLang = app()->getLocale();
@endphp

@if($languages->isNotEmpty())
<div class="btn-group position-static">
    <button class="bg-transparent text-hover-primary dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
        <i class="fa-light fa-globe"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-auto max-w-180 mt-10" data-popper-placement="bottom-end">

    	@foreach($languages as $language)
        <li class="mb-1">
            <a class="dropdown-item px-2 py-1 rounded d-flex gap-8 fw-5 fs-14 text-truncate  {{ $currentLang == $language->code?"bg-primary-100":"" }}" href="{{ url("lang/".$language->code) }}" data-id="" data-popup="uploadFileFromURLModal" data-call-success="Main.ajaxScroll(true)">
                <span class="size-16 me-1 text-center"><i class="{{ $language->icon }}"></i></span>
                <span class="text-truncate">{{ $language->name }}</span>
            </a>
        </li>
    	@endforeach
    	
    </ul>
</div>
@endif