@extends('layout.layout')
@section('title', 'Laporan | Rekap Revisi')

@push('yss')
    @include("VALIDASI.style")
@endpush

@section('content')
<div class="row rekapSasaran">
    <div class="col-lg-12">
        <div class="card mt-5">
            <div class="card-header">
                <div class="col-lg-12 rekap-sasaran-filter-slot"></div>
            </div>
            <div class="card-body">
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped" id="tabel-rekap-sasaran" style="width: 100%; font-size: 13px">
                        <thead>
                            <tr>
                                <th rowspan="2" colspan="2" class="text-center align-middle">Codebase</th>
                                <th class="text-center align-middle">Semula</th>
                                <th class="text-center align-middle">Perubahan</th>
                                <th rowspan="2" class="text-center align-middle">Selisih</th>
                                <th rowspan="2" class="text-center align-middle">(%)</th>
                            </tr>
                            <tr>
                                <th class="text-center align-middle">Jumlah Biaya</th>
                                <th class="text-center align-middle">Jumlah Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Pilih filter lalu klik Tampilkan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include("COMPONENTS.REKAPSUBKOMPONEN.table")
@endsection

@push("scripts")
    @include("COMPONENTS.scriptLoader")
    @include('content.laporan.REKAP_REVISI.script")
    @include("COMPONENTS.REKAPSUBKOMPONEN.script")
@endpush
