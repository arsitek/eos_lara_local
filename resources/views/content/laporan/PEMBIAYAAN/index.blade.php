@extends('layouts/layoutMaster')
@section('title', 'Laporan | Pembiayaan')
@section('content')
<div class="row mt-5">
    @if( session("role") == "disable" )
    <div class="col-lg-12 mb-3 d-flex justify-content-between">
        <div class="rkaUnit">
            <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                <option value="semua" selected>Semua unitkerja</option>
                {{-- @if ( in_array( session("role"), ["superadmin", "admin", "verifikator", "Majelis Wali Amanat", "Pimpinan USK"] ) )
                    @foreach($unitkerja as $item)
                        @if($item->unitApi->nama ?? "-" != "-")
                        <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja") ) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                        @endif
                    @endforeach
                @else
                    <option value="{{ session("unitkerja") }}" selected>{{ session()->get('unitkerja_nama')}}</option>
                @endif --}}
            </select>
            <button class="btn btn-info cari">SUBMIT</button>
        </div>
    </div>
    @endif
    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">LAPORAN PEMBIAYAAN TAHUN ANGGARAN 2024 & 2025</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="wrap">
                    @include("COMPONENTS.loader")
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabelPembiayaan" class="tabel-rekat table mb-0 table-bordered" style="border:2.5px solid black;">
                        <thead class="header">
                                <tr>
                                    <th rowspan="2" class="text-center" style="vertical-align: middle">SUMBER DANA</th>
                                    <th colspan="3" class="text-center" style="border-left: 2px solid black; border-bottom: 2px solid black">TARGET</th>
                                    <th colspan="3" class="text-center" style="border-right: 2px solid black;border-left: 2px solid black; border-bottom: 2px solid black">REALISASI</th>
                                    <th colspan="3" class="text-center" style="border-right: 2px solid; border-bottom: 2px solid black">PERSENTASE</th>
                                    <th colspan="3" class="text-center" style="border-bottom: 2px solid black">SELISIH</th>
                                </tr>
                                <tr style="border-bottom: 2px solid black; text-align: center">
                                    <th style="border-left: 2px solid black">2023</th>
                                    <th>2024</th>
                                    <th style="border-right: 2px solid black">2025</th>
                                    <th>2023</th>
                                    <th>2024</th>
                                    <th style="border-right: 2px solid black">2025</th>
                                    <th>2023</th>
                                    <th>2024</th>
                                    <th style="border-right: 2px solid black">2025</th>
                                    <th>2023</th>
                                    <th>2024</th>
                                    <th>2025</th>
                                </tr>
                        </thead>
                        <tbody style="font-size: 13px">
                            @foreach ($masterData as $item )
                                <tr>
                                    <td style="width: 250px" class="{{ $item['key'] }}">{{ $item['desc'] }}</td>
                                    <td style="width: 170px" class="target" tahun="2023">-</td>
                                    <td class="target" tahun="2024">{{ $item["totalDB2024"]["0"]->total ?? '-' }}</td>
                                    <td style="width: 170px" class="target" tahun="2025">{{ $item["totalDB2025"]["0"]->total ?? $item["total"] }}</td>
                                    <td style="width: 170px" class="realisasi" tahun="2023">{{ $item['total_realisasi']["2023"] ?? '-' }}</td>
                                    <td style="width: 170px" class="realisasi" tahun="2024">{{ $item['total_realisasi']["2024"] ?? '-' }}</td>
                                    <td class="realisasi" tahun="2025">-</td>
                                    <td style="width: 50px" class="persentase" tahun="2023">-</td>
                                    <td style="width: 50px" class="persentase" tahun="2024">-</td>
                                    <td style="width: 50px" class="persentase" tahun="2025">-</td>
                                    <td style="width: 170px" class="selisih" tahun="2023">-</td>
                                    <td style="width: 220px" class="selisih" tahun="2024">-</td>
                                    <td style="width: 170px" class="selisih" tahun="2025">-</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="text-center">TOTAL</td>
                                <td class="totaltarget" tahun="2023"></td>
                                <td class="totaltarget" tahun="2024"></td>
                                <td class="totaltarget" tahun="2025"></td>
                                <td class="totalrealisasi" tahun="2023"></td>
                                <td class="totalrealisasi" tahun="2024"></td>
                                <td class="totalrealisasi" tahun="2025"></td>
                                <td class="totalpersentase" tahun="2023"></td>
                                <td class="totalpersentase" tahun="2024"></td>
                                <td class="totalpersentase" tahun="2025"></td>
                                <td class="totalselisih" tahun="2023"></td>
                                <td class="totalselisih" tahun="2024"></td>
                                <td class="totalselisih" tahun="2025"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include('content.laporan.PEMBIAYAAN.script")
    @include("COMPONENTS.scriptLoader")
@endpush
