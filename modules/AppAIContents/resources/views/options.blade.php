@php
    $options = $options ?? [];
@endphp

@if( isset($include_media) && $include_media )
<div class="col-6">
    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Include media') }}</label>
        <select class="form-select" data-control="select2" name="ai_options[include_media]">
            <option value="0" {{ (isset($options['include_media']) && $options['include_media'] == 0) ? 'selected' : '' }}>{{ __("Disable") }}</option>
            <optgroup label="{{ __('Media Online') }}"> 
                <option value="ai" {{ (isset($options['include_media']) && $options['include_media'] == 'ai') ? 'selected' : '' }}>{{ __('AI Image') }}</option>
                <option value="unsplash" {{ (isset($options['include_media']) && $options['include_media'] == 'unsplash') ? 'selected' : '' }}>{{ __('Unsplash') }}</option>
                <option value="pexels_photo" {{ (isset($options['include_media']) && $options['include_media'] == 'pexels_photo') ? 'selected' : '' }}>{{ __('Pexels Photo') }}</option>
                <option value="pexels_video" {{ (isset($options['include_media']) && $options['include_media'] == 'pexels_video') ? 'selected' : '' }}>{{ __('Pexels Video') }}</option>
                <option value="pixabay_photo" {{ (isset($options['include_media']) && $options['include_media'] == 'pixabay_photo') ? 'selected' : '' }}>{{ __('Pixabay Photo') }}</option>
                <option value="pixabay_video" {{ (isset($options['include_media']) && $options['include_media'] == 'pixabay_video') ? 'selected' : '' }}>{{ __('Pixabay Video') }}</option>
            </optgroup>
            <optgroup label="{{ __('Folder File') }}">
                @foreach($folders??[] as $value)
                    <option value="{{ $value->id }}" {{ (isset($options['include_media']) && $options['include_media'] == $value->id) ? 'selected' : '' }}>{{ $value->name }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>
</div>
@endif

<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Language") }}</label>
    <select class="form-select" data-control="select2" name="ai_options[language]" required="">
        @foreach (languages() as $key => $value)
            <option value="{{ $key }}" {{ (isset($options['language']) ? $options['language'] : get_option("ai_language", "en-US")) == $key ? 'selected' : '' }}>{{ $value }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Tone of voice") }}</label>
    <select class="form-select" data-control="select2" name="ai_options[tone_of_voice]" required="">
        @foreach (tone_of_voices() as $key => $value)
            <option value="{{ $key }}" {{ (isset($options['tone_of_voice']) ? $options['tone_of_voice'] : get_option("ai_tone_of_voice", "Friendly")) == $key ? 'selected' : '' }}>{{ $value }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Creativity") }}</label>
    <select class="form-select" data-control="select2" name="ai_options[creativity]" required="">
        @foreach (ai_creativity() as $key => $value)
            <option value="{{ $key }}" {{ (isset($options['creativity']) ? $options['creativity'] : get_option("ai_creativity", 0)) == $key ? 'selected' : '' }}>{{ $value }}</option>
        @endforeach
    </select>
</div>

@if( isset($hashtags) && $hashtags )
<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Add hashtags") }}</label>
    <select class="form-select" data-control="select2" name="ai_options[hashtags]">
        <option value="">{{ __("Disable") }}</option>
        @for ($i=1; $i <= 10; $i++)
            <option value="{{ $i }}" {{ (isset($options['hashtags']) && $options['hashtags'] == $i) ? 'selected' : '' }}>{{ $i }}</option>
        @endfor
    </select>
</div>
@endif

<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Approximate words") }}</label>
    <input type="number" class="form-control" name="ai_options[max_length]" value="{{ isset($options['max_length']) ? $options['max_length'] : get_option("ai_max_input_lenght", 0) }}" required="">
</div>

@if( isset($total_result) && $total_result )
<div class="col-md-6 mb-3">
    <label class="form-label">{{ __("Total results") }}</label>
    <input type="text" class="form-control" name="ai_options[number_result]" value="{{ $options['number_result'] ?? 3 }}">
</div>
@endif