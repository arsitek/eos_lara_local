<link href="http://fonts.cdnfonts.com/css/times-new-roman" rel="stylesheet">
<style type="text/css">
table{
  font-size: 14px;
  border-collapse: collapse;
}table th{
  background-color: rgba(208,206,206,255);
}.container-fluid{
    margin:38px;
}@page {
    size: auto;   /* auto is the initial value */
    margin: 0;  /* this affects the margin in the printer settings */
}table th{
    text-align: center;
    font-size: 14px;
}table thead{
    background-color: rgba(208,206,206,255);
}table thead tr th{
    vertical-align: middle;
    color: black;
}table tbody tr td{
    font-size: 12px;
    padding: 5px;
    vertical-align: top;
    color: black;
}*{
  box-sizing: border-box;
}.column {
  font-weight: bold;
  float: left;
  padding: 10px;
  height: 300px;
}.left {
  width: 80%;
}.right {
  width: 20%;
}.row:after {
  content: "";
  display: table;
  clear: both;
}
</style>

<div id="print" class="container-fluid" style="font-family: Times New Roman;">
    <div style="text-align: center;">
        <img src="{{asset('')}}assets/images/logo_unsyiah.png" width="150" style="margin-bottom: 8px;"><br>
        <span class="text-center" style="font-size: 14px; font-weight: bold;">RENCANA KINERJA <br> TAHUNAN </span>
    </div>
    <div style="padding-top: 30px">
        <pre style="font-weight: bold;font-family: Times New Roman; font-size: 16px;">
Satuan Kerja            Universitas Syiah Kuala
Unit Kerja                {{ session()->get('unitkerja_nama') }}
Tahun                       {{ date('Y')}}</pre>
    </div>
    <table id="example" style="" class="mt-4 table table-inverse table-bordered" border="4">
    <thead>
        <tr>
            <th>Sasaran<span style="visibility: hidden;" id="under">_</span>Program</th>
            <th>Indikator<span style="visibility: hidden;" id="under">_</span>Kinerja<span
                                        style="visibility: hidden;" id="under">_</span>Kegiatan</th>
            <th>Rincian<span style="visibility: hidden;">_</span>Kegiatan</th>
            <th>Rincian Sub Komponen</th>
            <th>Rencana Pelaksanaan</th>
            <th>Jenis Belanja</th>
            <th>Kebutuhan Kegiatan</th>
            <th>Jumlah Biaya (RP)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rekat as $data)
        <tr>
			<td>{{$data->sasaran_program}}</td>
			<td>{{$data->indikator_kinerja_kegiatan}}</td>
			<td>{{$data->rincian_kegiatan}}</td>
			<td>{{$data->rincian_komponen}}</td>
            <td>{{$data->rencana_pelaksanaan}}</td>
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
                @elseif($data->TOTAL_PERALATAN!= NULL)
                {{ $data->TOTAL_PERALATAN}}
                @elseif($data->TOTAL_KEGIATAN != NULL)
                {{ $data->TOTAL_KEGIATAN }}
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    
    <div class="row">
        <div class="column left"><br>
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
         </div> {{-- akhir untuk kolom sebelah kanan --}}
</div>
<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
<script src="{{ asset('assets/js/jquery.rowspanizer.min.js') }}"></script>
<script>
$(document).on('ready', function() {
  $("#example").rowspanizer({vertical_align: 'top'});
});
</script>
