@extends('layouts/layoutMaster')
@section('title', 'laporan rab peralatan')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN RAB PERALATAN</h3>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 10px;">
                    <a role="button" id="exportPDF" href="{{ route('peralatReport.pdf') }}" class="btn btn-secondary bg-info-gradient btn-pill"><i class="fa fa-print"></i> PRINT TO PDF</a>
                    <a role="button" id="exportEXCEL"  href="{{ route('peralatReport.excel') }}" class="btn btn-secondary bg-success-gradient btn-pill"><i class="bi bi-table"></i> SAVE TO EXCEL</a>
                </div>
                <div class="table-responsive">
                    <table class="tabel-per table table-bordered border mb-0">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                            <th>Rincian Kegiatan</th>
                            <th>Rincian Komponen</th>
                            <th>Jenis Belanja</th>
                            <th>Kebutuhan Kegiatan </th>
                            <th>Merk</th>
                            <th>Type</th>
                            <th>e-Catalog (url)</th>
                            <th>Status Produk (lokal/impor)</th>
                            <th>Berkefungsian Untuk</th>
                            <th>kuantitas</th>
                            <th>satuan</th>
                            <th>Harga satuan(Rp)</th> 
                            <th>Jumlah Biaya(Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rabper as $data)
                    <tr>
                        <td> {{ $data->id }} </td>
                        <td> {{ $data->unit->unitkerja}}</td>
                        <td> {{ $data->rincian_kegiatan }}</td>
                        <td> {{ $data->rincian_komponen }}</td>
                        <td> {{ $data->jenis_belanja }}</td>
                        <td> {{ $data->kebutuhan_kegiatan }}</td>
                        <td> {{ $data->merk }}</td>
                        <td> {{ $data->type }}</td>
                        <td>
                            <a class="badge bg-success" href="{{ asset('uploads/Rab_Peralatan/eCatalog/'.$data->eCatalog)}}">Download</a>
                        </td>
                        <td> {{ $data->status_produk }}</td>
                        <td> {{ $data->berkefungsian }}</td>
                        <td> {{ $data->kuantitas }}</td>
                        <td> {{ $data->satuan }}</td>
                        <td> {{ $data->harga_satuan }}</td>
                        <td> {{ $data->jumlah_biaya }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('yss')
@include('content.laporan.PERALATAN.css')
@endpush

@push('scripts')
@include('content.laporan.PERALATAN.script')
@endpush
