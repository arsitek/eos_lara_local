@extends('layout.layout')
@section('title', 'RKAT | ')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">LAPORAN RKAT TANPA PEMBATASAN</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="tabel-rkat table table-bordered border mb-0" id="new-edit">
                        <thead>
                            <tr>
                                <th>KODEFIKASI</th>
                                <th>URAIAN</th>
                                <th>VOLUME</th>
                                <th>SATUAN</th>
                                <th>ANGGARAN BIAYA</th>
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
@push("scripts")
    @include('content.laporan.RKAT.TANPA_PEMBATASAN.SATU.script")
@endpush
