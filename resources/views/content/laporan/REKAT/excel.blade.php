<table>
    <tr>
        <th>Unit kerja</th>
        <th>Sasaran Program</th>
        <th>INDIKATOR KINERJA KEGIATAN</th>
        <th>Rincian Kegiatan</th>
        <th>Rincian Sub Komponen</th>
        <th>Jenis Belanja</th>
        <th>Kebutuhan Kegiatan</th>
        <th>Jumlah Biaya (RP)</th>
    </tr>
    @foreach($raw_rekat as $data)
    <tr>
        <td>{{ $data->unit->unitkerja }}</td>
        <td>{{ $data->sasaran_program }}</td>
        <td>{{$data->indikator_kinerja_kegiatan}}</td>
        <td>{{$data->rincian_kegiatan}}</td>
        <td>{{$data->rincian_komponen}}</td>
        <td>{{$data->jenis_belanja }}</td>
        <td>
            @if($data->KEBUTUHAN_GDG != NULL)
            {{ $data->KEBUTUHAN_GDG }}
            @elseif($data->KEBUTUHAN_PER != NULL)
            {{ $data->KEBUTUHAN_PER }}
            @elseif($data->KEBUTUHAN_KEG != NULL)
            {{ $data->KEBUTUHAN_KEG }}
            @endif
        </td>
        <td>
            @if($data->TOTAL_GEDUNG != NULL)
            {{ $data->TOTAL_GEDUNG }}
            @elseif($data->TOTAL_PERALATAN != NULL)
            {{ $data->TOTAL_PERALATAN}}
            @elseif($data->TOTAL_KEGIATAN != NULL)
            {{ $data->TOTAL_KEGIATAN }}
            @endif
        </td>
    </tr>
    @endforeach
</table>
