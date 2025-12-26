<li>
    <label class="w-100 {{ $count%2==0?"bg-gray-200":"bg-gray-100" }} py-1 pe-2" for="parent_{{ $folder['id_secure'] }}">
        <div class="form-check align-items-center gap-8 fs-12" style="padding-left: {{ $count_sub*30 }}px;">
            <input class="form-check-input" type="radio" name="parent" value="{{ $folder['id_secure'] }}" id="parent_{{ $folder['id_secure'] }}" {{ data($result, "pid", "radio", $folder['id'], 0) }}>
            <span class="text-truncate">{{ $folder['name'] }}</span>
        </div>
    </label>
    @if (!empty($folder['subfolders']))
        <ul class="d-flex flex-column">
            @php
                $count_sub++;
            @endphp

            @foreach ($folder['subfolders'] as $subfolder)
                @php
                    $count++;
                @endphp
                @include('appfiles::partials.folder', ['folder' => $subfolder])
            @endforeach
        </ul>
    @endif
</li>