<nav aria-label="breadcrumb" class="mb-0">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a class="fs-12 text-primary fw-5 text-hover-primary actionItem" href="{{ module_url("categories") }}" data-content="ai-template-data">
            {{ __("All categories") }}
        </a>
    </li>
    <li class="breadcrumb-item fs-12 text-gray-400 active" aria-current="page">{{ __($category->name) }}</li>
  </ol>
</nav>

<div class="mb-3">
  <div class="form-control">
        <i class="fa-light fa-magnifying-glass"></i>
        <input placeholder="{{ __("Search") }}" type="text" class="search-input" value="">
    </div>
</div>

@if(count($templates) > 0)
	@foreach($templates as $value)
		<a href="javascript:void(0);" class="col-12 mb-3 p-3 border b-r-6 addToField text-gray-700 fw-5 fs-13 bg-light bg-hover-success-100 search-list closeAICate" data-field="[name='prompt']" data-refresh="1" data-content="{{ $value->content }}">
			{{ $value->content }}
		</a>
	@endforeach
@else
	<div class="empty px-4 py-5"></div>
@endif