@extends('layout.layout')
@section('title', 'Laporan | Daya Serap')
@push('yss')
    @include('content.laporan.DAYASERAP.css')
@endpush
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3">
    <select id="dataBackup" class="select2" style="width: 300px">
        <option value="">Pilih Data</option>
        <option value="current">Data Saat Ini</option>
        @foreach ($dataBackup as $item)
            <option value="{{ $item->id }}">{{ $item->keterangan }}</option>
        @endforeach
    </select>
    </div>
    <div class="col-lg-12 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">DAYA SERAP</h3>
            </div>
            <div class="card-body">
            @include("COMPONENTS.loader")
                <div class="table-responsive tableContainer">
                    <table class="table mb-0" id="tabel-daya-serap" style="border:2.5px solid white;">
                        <thead>
                            <tr>
                                <th>UNIT KERJA</th>
                                <th>PAGU ALOKASI</th>
                                <th>REALISASI</th>
                                <th>DAYA SERAP</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include('content.laporan.DAYASERAP.script')
    @include("COMPONENTS.scriptLoader")
@endpush
