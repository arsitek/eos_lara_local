@extends('layout.layout')
@section('title', 'REKAP | Anggaran Unit')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">REKAP ANGGARAN UNIT KERJA</h3>
            </div>
            <div class="card-body">
                <div>
                </div>
                <div class="table-responsive">
                    <table id="tabel-rekat" class="tabel-rekat table">
                        <thead>
                            <tr>
                                <th>codebase</th>
                                <th >SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>keg</th>
                                <th>item</th>
                                <th>total biaya</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <!-- <tr>
                                <td>41</td>
                                <td class="non-apbn">Non APBN</td>
                            </tr>  -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('content.laporan.ANGGARANUNIT.script')
@endpush 
