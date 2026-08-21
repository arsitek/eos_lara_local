@extends('layouts/layoutMaster')
@section('title', 'laporan rab kegiatan')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN RAB KEGIATAN</h3>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 10px;">
                    <a role="button" id="exportPDF" href="{{ route('kegReport.pdf')}}" class="btn btn-secondary bg-info-gradient btn-pill"><i class="fa fa-print"></i> PRINT TO PDF</a>
                    <a role="button" id="exportEXCEL" href="{{ route('kegReport.excel')}}" class="btn btn-secondary bg-success-gradient btn-pill"><i class="bi bi-table"></i> SAVE TO EXCEL</a>
                </div>
                <div class="table-responsive">
                    <table class="tabel-keg table table-bordered border mb-0" id="new-edit">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                                <th>Rincian Kegiatan</th>
                                <th>Rincian Komponen</th>
                                <th>Jenis Belanja</th>
                                <th>Kebutuhan Kegiatan</th>
                                <th>kuantitas</th>
                                <th>Satuan</th>
                                <th>durasi</th>
                                <th>Satuan</th>
                                <th>kegiatan</th>
                                <th>Satuan</th>
                                <th>Biaya satuan (Rp)</th>
                                <th>Pajak</th>
                                <th>Jumlah Biaya (Rp)</th>
                                <th>PNBP Uniker (Rp)</th>
                                <th>PNBP Univ (Rp)</th>
                                {{-- <th>tanggapan</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rabkeg as $data)
                            <tr>
                                <td> {{ $data->id }} </td>
                                <td> {{ $data->unit->unitkerja }}</td>
                                <td> {{ $data->rincian_kegiatan }}</td>
                                <td> {{ $data->rincian_komponen }}</td>
                                <td>{{ $data->jenis_belanja}}</td>
                                <td>{{ $data->kebutuhan_kegiatan}}</td>
                                <td>{{ $data->kuantitas}}</td>
                                <td>{{ $data->satuan_kuantitas}}</td>
                                <td>{{ $data->durasi}}</td>
                                <td>{{ $data->satuan_durasi}}</td>
                                <td>{{ $data->kegiatan}}</td>
                                <td>{{ $data->satuan_kegiatan}}</td>
                                <td>{{ $data->biaya_satuan}}</td>
                                <td>{{ $data->pajak}}</td>
                                <td>{{ $data->jumlah_biaya}}</td>
                                <td>{{ $data->PNBP}}</td>
                                <td>{{ $data->APBN}}</td>
                                {{-- <td class="tanggapan" contenteditable="true" onchange="handleUpdate(this)"> {{ $data->tanggapan }}</td> --}}
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
@include('content.laporan.KEGIATAN.css')
@endpush

@push('scripts')
@include('content.laporan.KEGIATAN.script')
@endpush
