<link href="http://fonts.cdnfonts.com/css/times-new-roman" rel="stylesheet">
<style type="text/css">
table {
  font-size: 14px;
  border-collapse: collapse;
}
table th{
  background-color: rgba(208,206,206,255);
}
</style>
<style type="text/css">
.container-fluid{
    margin:38px;
}
@page {
    size: auto;   /* auto is the initial value */
    margin: 0;  /* this affects the margin in the printer settings */
}
    table th{
        text-align: center;
        font-size: 14px;
    }
    table thead{
        background-color: rgba(208,206,206,255);

    }
    table thead tr th{
        vertical-align: middle;
        color: black;
    }
      table tbody tr td{
        font-size: 12px;
        padding: 5px;
        vertical-align: top;
        color: black;
    }
* {
  box-sizing: border-box;
}

/* Create two unequal columns that floats next to each other */
.column {
  font-weight: bold;
  float: left;
  padding: 10px;
  height: 300px; /* Should be removed. Only for demonstration */
}

.left {
  width: 80%;
}

.right {
  width: 20%;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
</style>

<div id="print" class="container-fluid" style="font-family: Times New Roman;">
    <div style="text-align: center;">
        <img src="{{asset('')}}assets/images/logo_unsyiah.png" width="150" style="margin-bottom: 8px;"><br>
        <span class="text-center" style="font-size: 14px; font-weight: bold;">RAB <br> GEDUNG </span>
    </div>
    {{-- title --}}
    <div style="padding-top: 30px">
        <pre style="font-weight: bold;font-family: Times New Roman; font-size: 16px;">
Satuan Kerja            Universitas Syiah Kuala
Unit Kerja                {{ session()->get('unitkerja_nama') }}
Tahun                       {{ date('Y')}}</pre>
    </div>
    {{-- title --}}
    <table id="example" style="" class="mt-4 table table-inverse table-bordered" border="4">
        <thead>
            <tr>
                <th>Unit Kerja</th>
                <th>Rincian Kegiatan</th>
                <th>Rincian Komponen</th>
                <th>Jenis Belanja</th>
                <th>Kebutuhan Kegiatan </th>
                <th>Latitude/ Longitude</th>
                <th>Luas Bangunan Gedung (dalam meter persegi)</th>
                <th>Jumlah Gedung</th>
                <th>Jumlah Lantai</th>
                <th>Ruang Kuliah (dalam ruang)</th>
                <th>Ruang Laboratorium/Workshop/Bengkel (dalam ruang)</th>
                <th>Ruang Kantor/Management/Penunjang (dalam ruang)</th>
                <th>lainnya (dalam ruang)</th>
                <th>Kesesuaian Gedung dengan Master Plan Kawasan</th>
                <th>Sertifikat</th>
                <th>SIMAK BMN (url)</th>
                <th>dokumen analisis kementerian PUPR (url)</th>
                <th>dokumen IMB  (url)</th>
                <th>Dokumen AMDAL/Ijin Lingkungan (url)</th>
                <th>Dokumen RKS  (url)</th>
                <th>DED AWAL</th>
                <th>DED Review Terakhir</th>
                <th>Nilai Perencanaan (Rp.)</th>
                <th>Nilai Struktur (Rp.)</th>
                <th>Nilai ME (Rp.)</th>
                <th>Nilai Lanscape (Rp.)</th>
                <th>Nilai Pengawasan (Rp.)</th>
                <th>Proposal Project/KAK</th>
                <th>RAB Detail (excel)</th>
                <th>Perencanaan Gambar (pdf)</th>
                <th>Jumlah Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rabgdg as $data)
        <tr>
            <td> {{ $data->unit->unitkerja}}</td>
            <td> {{ $data->rincian_kegiatan }}</td>
            <td> {{ $data->rincian_komponen }}</td>
            <td> {{ $data->jenis_belanja }}</td>
            <td> {{ $data->kebutuhan_kegiatan  }}</td>
            <td> {{ $data->latlong }}</td>
            <td> {{ $data->luas_bangunan }}</td>
            <td> {{ $data->jumlah_gedung }}</td>
            <td> {{ $data->jumlah_lantai }}</td>
            <td> {{ $data->ruang_kuliah }}</td>
            <td> {{ $data->ruang_lab }}</td>
            <td> {{ $data->ruang_kantor }}</td>
            <td> {{ $data->lainnya }}</td>
            <td> {{ $data->kesesuaian_gedung }}</td>
            <td> {{ $data->sertifikat}}</td>
            <td>{{ $data->simak_BMN }}</td>
            <td>{{$data->PUPR}}</td>
            <td> {{$data->dokumen_IMB}}</td>
            <td>{{$data->dokumen_AMDAL}}</td>
            <td>{{$data->dokumen_RKS}}</td>
            <td> {{ $data->DED_AWAL }}</td>
            <td> {{ $data->DED_REVIEW}}</td>
            <td> {{ $data->nilai_perencanaan}}</td>
            <td> {{ $data->nilai_struktur }}</td>
            <td> {{ $data->nilai_me  }}</td>
            <td> {{ $data->nilai_landscape  }}</td>
            <td> {{ $data->nilai_pengawasan  }}</td>
            <td>{{$data->proposal_project}}</td>
            <td>{{$data->rab_detail}}</td>
            <td>{{$data->perencanaan_gambar}}</td>
            <td> {{ $data->jumlah_nilai }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="row">
      <div class="column left">
            <br>
            {{-- <span>{{ $pp['0']->PP_JBT }}</span><br><br><br><br><br><br>
            <span>{{ $pp['0']->PP_REKTOR }}</span><br>
            <span>{{ $pp['0']->PP_NIP }}</span> --}}
            
          </div>
          <div class="column right">
            <span> Di Tempat, {{ date('d-m-Y')}}</span><br>
            @if($ttd != null)
            <span>{{ $ttd['0']->PK_JBT }}</span><br><br><br><br><br><br>
            <span>{{ $ttd['0']->PK_NAMA }}</span><br>                
            <span>{{ $ttd['0']->PK_NIP }}</span>
            @else
            <p style="font-size: 0.8rem; color: green">Data penandatangan tidak tersedia</p>
            @endif
          </div>
        </div>
</div>
<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
<script src="{{ asset('assets/js/jquery.rowspanizer.min.js') }}"></script>
<script>
$(document).on('ready', function() {
  $("#example").rowspanizer({vertical_align: 'top'});
});
</script>

