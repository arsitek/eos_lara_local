@extends('layouts/layoutMaster')
@section('title', 'LAPORAN RKA')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">LAPORAN RKA PTNBH</h3>
            </div>
            <div class="card-body">
                <div>
                    <a href="#" class="btn-export-xlsx btn btn-info px-2 py-1 mb-2">Save to XLSX</a>
                    <a href="{{ route('rktReport.pdf') }}" class="btn-export-pdf btn btn-primary px-2 py-1 mb-2">Save to PDF</a>
                </div>
                <div class="table-responsive">
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" style="border:2.5px solid black;">
                        <thead>
                            <tr>
                                <th>codebase</th>
                                <th >SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>qt</th>
                                <th>unit</th>
                                <th>dr</th>
                                <th>ms</th>
                                <th>keg</th>
                                <th>item</th>
                                <!-- <th>vol</th>
                                <th>sat</th> -->
                                <th>biaya satuan</th>
                                <th>total biaya</th>
                                <th>rpd</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <tr>
                                <td>41</td>
                                <td class="non-apbn" colspan="8">Non APBN</td>
                                <td></td>
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
    @include('content.laporan.REKAT.scriptbaru')
    @include('content.laporan.REKAT.scriptbaru52')
@endpush 
