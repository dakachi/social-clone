@extends('layouts.app')

@section('form', json_encode([
    'action' => module_url("save"),
    'method' => 'POST',
    'class' => 'actionForm',
    'data-redirect' => module_url()
]))

@section('sub_header')
    <x-sub-header
        title="{{ $result ? __('Edit Blog') : __('New Blog') }}"
        description="{{ $result ? __('Revise and enhance blog content seamlessly with precision') : __('Craft and publish a new blog post effortlessly') }}"
    >
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')

<div class="container pb-5">
    <input class="d-none" name="id" type="text" value="{{ data($result, "id_secure") }}">
    <div class="row">
        <div class="col-md-8">
            <div class="card b-r-6 border-gray-300 mb-3">
                <div class="card-body">
                    <div class="msg-errors"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="title" class="form-label">{{ __('Title') }}</label>
                                <input placeholder="{{ __('') }}" class="form-control" name="title" id="title" type="text" value="{{ $result->title ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="code" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control" name="desc">{{ $result->desc ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="content" class="form-label">{{ __('Content') }} (<span class="text-danger">*</span>)</label>
                                <textarea class="textarea_editor border-gray-300 border-1 min-h-1200" name="content" placeholder="{{ __("Enter content") }}">{!! data($result, "content") !!}</textarea>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <div class="col-md-4">

            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        {{ __('Category') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <select class="form-select w-100" name="cate_id" id="cate_id" data-control="select2">
                            <option value="0">{{ __('Select categories') }}</option>
                                @if($categories)
                                    @foreach($categories as $value)
                                        <option value="{{ $value->id }}" {{ data($result, "cate_id", "select", $value->id) }}>{{ $value->name }}</option>
                                    @endforeach
                                @endif

                        </select>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        {{ __("Thumbnail") }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        @include('appfiles::block_select_file_large', [
                            "id" => "thumbnail",
                            "value" => $result->thumbnail ?? ''
                        ])
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        {{ __('Tags') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0 fs-13">
                        @php
                        $tag_ids = [];
                        if(!empty($result) && isset($result->tag_ids) && !empty($result->tag_ids)){
                            $tag_ids = $result->tag_ids;
                        }
                        @endphp

                        <select class="form-select h-auto " name="tags[]" id="tags" data-control="select2" multiple="true" data-placeholder="{{ __("Add Tags") }}">
                            @if($tags)
                                    @foreach($tags as $value)
                                        <option
                                            value="{{ $value->id }}"
                                            data-color="{{ $value->color }}"
                                            {{ (!empty($tag_ids) && in_array($value->id, $tag_ids)) ? "selected" : "" }}
                                        >
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        {{ __('Status') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="status" value="1" id="status_1" @checked(($result->status ?? 1) == 1)>
                                <label class="form-check-label mt-1" for="status_1">
                                    {{ __('Enable') }}
                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="status" value="0" id="status_0" @checked(($result->status ?? 1) == 0)>
                                <label class="form-check-label mt-1" for="status_0">
                                    {{ __('Disable') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                       {{ __('Featured') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="featured" value="1" id="featured_1" @checked(($result->featured ?? 0) == 1)>
                                <label class="form-check-label mt-1" for="featured_1">
                                    {{ __('Yes') }}
                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="featured" value="0" id="featured_2" @checked(($result->featured ?? 0) == 0)>
                                <label class="form-check-label mt-1" for="featured_2">
                                    {{ __('No') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="row">
        <div class="col-md-8">
            <button type="submit" class="btn btn-dark w-100">{{ __("Save changes") }}</button>
        </div>
    </div>
</div>
@endsection

@section('script')
     <script type="text/javascript">
         $(document).ready(function() {
            function formatTag(tag) {
                if (!tag.id) {
                    return tag.text;
                }
                var color = $(tag.element).data('color') || '#d1fae5';
                return $(
                    '<span class="fs-12" style="display:inline-block;padding:4px;border:none;background:' + color + ';color:#222;">' + tag.text + '</span>'
                );
            }
            $('#tags').select2({
                templateResult: formatTag,
                templateSelection: formatTag,
                escapeMarkup: function(m) { return m; },
                width: '100%',
                closeOnSelect: false,
            });
        });
     </script>
@endsection
