@php
    $currentRoute   = Request::route()->getName();
    $fullWidthPages = array("revisi.sasaran.index", "validasi.kegiatan.index", "revisi.output.index");
    $allUnitPages   = array("perkinReport.index");
@endphp
<select name="unitkerja" class="unitkerja select2"
    @if( in_array($currentRoute, $fullWidthPages) )
    style="width: 100%"
    @else style="width: 320px"
    @endif>
    <option value="">Pilih Unit Kerja</option>
    @if ( in_array($currentRoute, $allUnitPages) )
        @foreach ($unit as $item)
            <option value="{{ $item->idunit }}"
                @if ( session('unitkerja') == $item->idunit )
                    selected
                @endif>{{ $item->nama }}</option>
        @endforeach
    @elseif ( in_array( session('role'), ["superadmin", "admin", "Pimpinan USK"] ))
        @foreach ($unit as $item)
            @if ( $item->unitApi->nama ?? '' != '' )
            <option value="{{ $item->unit_kerja }}">{{ $item->unitApi->nama }}</option>
            @endif
        @endforeach
    @else
        <option value="{{ session('unitkerja') }}" selected>{{ session()->get('unitkerja_nama')}}</option>
    @endif
</select>
