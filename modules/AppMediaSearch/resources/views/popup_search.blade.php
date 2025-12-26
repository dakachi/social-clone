<div class="modal fade" id="searchMediaModel" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content searchMediaForm actionForm" action="{{ route("app.search_media.search") }}" data-content="search-media-result">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Search Media Online") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="d-flex border-bottom p-4">
	            <div class="form-control pe-0">
	                <span class="btn btn-icon">
	                    <i class="fa-light fa-magnifying-glass"></i>
	                </span>
	                <input name="keyword" placeholder="{{ __('Enter keyword') }}" type="text">
	                <select class="max-w-120 border-start ps-3" name="source">
	                	@php $services = SearchMedia::services(); @endphp

                        @if(empty($services))
                            <option value="">{{ __('No provider enabled') }}</option>
                        @else
                            @foreach($services as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        @endif
	                </select>
	                <button class="btn btn-dark btl-r-0 bbl-r-0">{{ __('Search') }}</button>
	            </div>
	        </div>
			<div class="modal-body p-0">
		        <div class="p-4 search-media-result">

		        	<div class="empty my-5"></div>

		        </div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<a href="{{ route('app.files.save_files') }}" class="btn btn-dark actionMultiItem" data-form=".searchMediaForm" data-call-before="Main.closeModal('searchMediaModel');" data-call-after="Main.ajaxScroll(true);">{{ __('Save') }}</a>
			</div>
		</form>
	</div>
</div>