@extends('layout.layout')
@section('title', 'Laporan | Pendapatan')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between">
        <div class="rkaUnit">
            <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                <option value="semua" selected>Semua unitkerja</option>
                @if ( in_array( session("role"), ["superadmin", "admin", "verifikator", "Majelis Wali Amanat", "visitor"] ) || session()->get("id_user") == "196709261992031002" )
                    @foreach($unitkerja as $item)
                        @if($item->unitApi->nama ?? "-" != "-")
                        <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja") ) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                        @endif
                    @endforeach
                @endif
            </select>
            <button class="btn btn-info cari">SUBMIT</button>
        </div>
    </div>

    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">LAPORAN PENDAPATAN TAHUN ANGGARAN 2024 & 2025</h3></h3>
            </div>
            <div class="card-body">
                <div class="table-responsive tableStickyContainer">
                    @include("COMPONENTS.loader")
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabelPembiayaan" class="tabel-rekat table mb-0 tabel-sticky" style="border:2.5px solid white;">
                        <thead class="header">
                            <tr>
                                <th rowspan="2" class="text-center" style="vertical-align: middle">SUMBER DANA</th>
                                <th colspan="2" class="text-center" style="border-left: 2px solid white; border-bottom: 2px solid white ">TARGET</th>
                                <th colspan="2" class="text-center" style="border-right: 2px solid white;border-left: 2px solid white; border-bottom: 2px solid white">REALISASI</th>
                                <th colspan="2" class="text-center" style="border-right: 2px solid white; border-bottom: 2px solid white">PERSENTASE</th>
                                <th colspan="2" class="text-center" style="border-bottom: 2px solid white;">SELISIH</th>
                            </tr>
                            <tr style="border-bottom: 2px solid white; text-align: center">
                                <th style="border-left: 2px solid white">2024</th>
                                <th style="border-right: 2px solid white">2025</th>
                                <th>2024</th>
                                <th style="border-right: 2px solid white">2025</th>
                                <th>2024</th>
                                <th style="border-right: 2px solid white">2025</th>
                                <th>2024</th>
                                <th>2025</th>
                            </tr>
                        </thead>
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
    @include('content.laporan.PENDAPATAN.script")
    @include("COMPONENTS.scriptLoader")
@endpush
