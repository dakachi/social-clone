<table id="{{ $element }}" data-url="{{ $url }}" class="display table table-bordered table-hide-footer w-100">
    <thead>
        <tr>
            @foreach($columns as $key => $column)
                @if($key == 0)
                    <th class="align-middle w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input checkbox-all" type="checkbox" data-checkbox-parent=".table-responsive"/>
                        </div>
                    </th>
                @elseif($key + 1 == count($columns))
                    <th class="align-middle w-120 max-w-100">
                        {{ __('Actions') }}
                    </th>
                @else
                    <th class="align-middle">
                        {{ $column['data'] }}
                    </th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody class="fs-14">
    </tbody>
</table>