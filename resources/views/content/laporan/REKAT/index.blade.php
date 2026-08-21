@extends('layout.layout')
@section('title', 'Laporan RKT SAKTI')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN RKT SAKTI</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="tabel-rekat table table-bordered border mb-0">
                        <thead>
                            <tr>
                                <th>SD</th>
                                <th>KRO</th>
                                <th>RO</th>
                                <th>kp</th>
                                <th>sk</th>
                                <th>pic</th>
                                <th>dk</th>
                                <th>coa</th>
                                <th colspan="9">keterangan</th>
                                <th>qt</th>
                                <th>unit</th>
                                <th>dr</th>
                                <th>ms</th>
                                <th>keg</th>
                                <th>item</th>
                                <th>vol</th>
                                <th>sat</th>
                                <th>biaya satuan</th>
                                <th>total biaya</th>
                                <th>rpd</th>
                                <th>real</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <tr>
                                <td colspan="8">41</td>
                                <td colspan="9">Non APBN</td>
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
    @include('content.laporan.REKAT.script')
    @include('content.laporan.REKAT.script52')
@endpush 
