@extends('layouts/layoutMaster')
@section('title', 'Klasifikasi Sasaran | SKD')
@section('content')
<div class="row mt-5">
<div class="col-lg-12">
    <div class="card">
        <div class="card-header"><h3 class="card-title">SKD | Meningkatnya kualitas dosen pendidikan tinggi</h3></div>
        <div class="card-body">
            <div>
                <a href="#" onclick="ExportToExcel('xlsx')" class="btn btn-info px-2 py-1 mb-2">Save to XLSX</a>
                <a href="{{ route('rka.dua.pdfNonApbn') }}" class="btn btn-info px-2 py-1 mb-2">Save to PDF</a>
            </div>
            <div class="table-responsive">
                <h4 id="loading-msg">Loading ...</h4>
                <table id="tabel-rekat" class="tabel-rekat table mb-0" style="border:2.5px solid black;">
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
    @include('content.laporan.KLASIFIKASI.SASARAN.DUA.scriptdua")
    <script>
        function ExportToExcel(type, fn, dl) {
            var elt = document.getElementById('tabel-rekat');
            var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
            return dl ?
                XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
                XLSX.writeFile(wb, fn || ('Laporan_RKT_SASARAN_2.' + (type || 'xlsx')));
        }
    </script>
@endpush
