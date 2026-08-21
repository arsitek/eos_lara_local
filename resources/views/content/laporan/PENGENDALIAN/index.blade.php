@extends('layout.layout')
@section('title', 'Laporan | Pengendalian')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">Lembar Pengendalian</h3>
            </div>
            <div class="card-body">
                <table class="table mb-0" id="tabel-pengendalian" style="border:2.5px solid white;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Sumber Dana</th>
                            <th>Unit Kerja</th>
                            <th>Id Rekat</th>
                            <th>Item Coa</th>
                            <th>Jumlah Biaya</th>
                            <th>Jumlah Amprahan/Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $item)
                        <tr role="button" key="{{ $item->idItemCoa ?? '' }}" jenis="{{ $item->jenisRab ?? ''}}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kodeSd }} | {{ $item->sumberdana }}</td>
                            <td>{{ $item->idunit }} | {{ $item->nama }}</td>
                            <td>{{ $item->idRekat }}</td>
                            <td>{{ $item->itemCoa }}</td>
                            <td class="text-end">Rp {{ number_format($item->jumlah_biaya, 2, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($item->jumlah_amprahan + $item->jumlah_realisasi, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-12 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">Data Sudah Realisasi yang Terhapus</h3>
            </div>
            <div class="card-body">
                <table class="table mb-0" id="tabel-pengendalian" style="border:2.5px solid white;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Sumber Dana</th>
                            <th>Unit Kerja</th>
                            <th>Id Rekat</th>
                            <th>Item Coa</th>
                            <th>Jumlah Biaya</th>
                            <th>Jumlah Amprahan/Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deletedRealisasi as $index => $item)
                        <tr role="button" key="{{ $item->id ?? '' }}" jenis="{{ $item->jenis_rab ?? ''}}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->sd }} | {{ $item->sumberdana }}</td>
                            <td>{{ $item->idunit }} | {{ $item->nama }}</td>
                            <td>{{ $item->id_rekat }}</td>
                            <td>{{ $item->kebutuhan_kegiatan }}</td>
                            <td class="text-end">Rp {{ number_format($item->jumlah_biaya, 2, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($item->jumlah_amprahan + $item->jumlah_realisasi, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('content.laporan.PENGENDALIAN.modalPengendalian')
@endsection
@push('scripts')
    @include('content.laporan.PENGENDALIAN.script')
@endpush
