@extends('layout.layout')
@section('title', 'Laporan | Pendapatan')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between">
        <div class="rkaUnit">
            <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                @if ( in_array( session("role"), ["superadmin", "admin", "verifikator", "Majelis Wali Amanat", "visitor", "Wakil Rektor"] ) || session()->get('id_user') == "196709261992031002" )
                <option value="semua" selected>Semua unitkerja</option>
                @endif
                @foreach($unitkerja as $item)
                    @if($item->unitApi->nama ?? "-" != "-")
                    <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja") ) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                    @endif
                @endforeach
            </select>
            <button class="btn btn-info cari">SUBMIT</button>
        </div>
    </div>

    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h3 class="card-title mb-0">LAPORAN PENDAPATAN TAHUN ANGGARAN {{ $tahunSebelumnya}} & {{ $tahunAngka }}</h3>
                <button type="button" class="btn btn-danger btnExportPendapatanPdf">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive tableStickyContainer">
                    @include("COMPONENTS.loader")
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabelPendapatan" class="tabel-rekat table mb-0 tabel-sticky" style="border:2.5px solid white;">
                        @include('content.laporan.PENDAPATAN.remake._dynamic_headers', ['tahunAngka' => $tahunAngka])
                        <tbody style="font-size: 13px">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include('content.laporan.PENDAPATAN.remake.script")
    @include("COMPONENTS.scriptLoader")
@endpush
