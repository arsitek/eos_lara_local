<select name="sumberdana" id="" class="s sumberdana" style="width:300px">
    <option value="">Pilih Sumberdana</option>
    @foreach ($sumberdana as $item)
    <option value="{{ $item->kd_sumberdana }}"
        @if ($item->kd_sumberdana == request()->kd_sumberdana)
            selected
        @endif
    >{{ $item->sumberdana }}</option>
    @endforeach
</select>