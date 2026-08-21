@extends('layout.layout')
@section('title', 'RKAT | ')
@section('content')
<div class="row mt-5">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">LAPORAN RKAT TANPA PEMBATASAN</h3></div>
                <div class="card-body">
                    <div>
                        {{-- <a href="#" class="btn-export-pdf btn btn-primary px-2 py-1 mb-2">Save to
                            PDF</a>
                        <a href="#" class="btn-export-xlsx btn btn-info px-2 py-1 mb-2">Save to XLSX</a> --}}
                    </div>
                    <div class="table-responsive">
                        <h5 id="loading-msg">Memuat Data RKAT...Harap Menunggu.....</h5>
                        <table class="tabel-rkat table table-bordered border mb-0" id="new-edit">
                            <thead>
                                <tr>
                                    <th>codebase</th>
                                    <th>SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                    <th>qt</th>
                                    <th>unit</th>
                                    <th>dr</th>
                                    <th>ms</th>
                                    <th>keg</th>
                                    <th>item</th>
                                    <th>biaya satuan</th>
                                    <th>total biaya</th>
                                    <th>rpd</th>
                                    <th>real</th>
                                    <th>sisa</th>
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
    @include('content.laporan.RKAT.TANPA_PEMBATASAN.script")
@endpush
