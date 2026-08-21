<select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
    <option value="">Pilih unit kerja</option>
    @if( session()->get('role') === "Pimpinan Unit" )
        <option value="{{ session()->get('unitkerja') }}" selected>{{ session()->get('unitkerja_nama') }}</option>
    @else
    @foreach ($unitkerja as $item)
        @if( $item->unitApi->idunit ?? '-' != '-')
            <option value="{{ $item->unitApi->idunit ?? '-'}}"
            @if($item->unit_kerja == $unit_kerja)
            selected
            @endif>{{ $item->unitApi->nama ?? '-' }}</option>
        @endif
    @endforeach
    @endif
</select>
