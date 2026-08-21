@php
    $currentRoute = Request::route()->getName();
    $fullWidthPages = array("revisi.sasaran.index", "validasi.kegiatan.index", "revisi.output.index");
@endphp
<select name="sumberdana" class="sumberdana select2"
    @if( in_array($currentRoute, $fullWidthPages) )
    style="width: 100%"
    @else style="width: 300px"
    @endif>
    <option value="">Pilih Sumberdana</option>
    @foreach ($sumberdana as $item)
        <option value="{{ $item->kd_sumberdana }}">{{ $item->sumberdana }}</option>
    @endforeach
</select>
