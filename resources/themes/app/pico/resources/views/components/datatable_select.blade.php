@if(!empty($options))
<div class="d-flex">
    <select class="form-select form-select-sm datatable_filter" name="{{ $name }}">
        @foreach($options as $option)
            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
        @endforeach
    </select>
</div>
@endif