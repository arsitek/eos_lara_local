@extends('layouts/layoutMaster')
@section('title', 'Rekap | Subkomponen')
@include('content.laporan.SUBKOMPONEN.style')
@section('content')
@php
    $role = session('role');
    $allowedRoles = ['superadmin', 'admin', 'verifikator', 'Majelis Wali Amanat', 'Pimpinan USK', 'Wakil Rektor', 'Direktur'];
@endphp
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between">
        <div class="rkaUnit">
            <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                @if ( in_array($role, $allowedRoles) )
                <option value="semua">Semua unitkerja</option>
                @foreach($unitkerja as $item)
                    @if($item->unitApi->nama ?? "-" != "-")
                    <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == $id_unit) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                    @endif
                @endforeach
                @else
                    <option value="{{ $id_unit }}" selected>{{ session()->get('unitkerja_nama')}}</option>
                @endif
            </select>
            <select name="sumberdana" style="width:300px" type="text"
                    class="s sumberdana bg-dark my-2 mr-2 text-white d-inline select2 w-auto required">
                    <option value="">Pilih Sumber Dana</option>
                    <option value="!apbn">Non APBN</option>
                    <option value="apbn">APBN</option>
                    @foreach($sumberdana as $item_sd)
                        <option value="{{ $item_sd->kd_sumberdana }}">{{ $item_sd->sumberdana }}</option>
                    @endforeach
            </select>
            <button class="btn btn-info cari">SUBMIT</button>
        </div>
    </div>
    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">REKAP SUBKOMOPNEN</h3>
                {{-- filter --}}
                <select class="s filter-data" style="width: 150px">
                    <option value="">Filter Data</option>
                    <option value="realisasi">Sudah Realisasi</option>
                    <option value="!realisasi">Belum Realisasi</option>
                </select>
            </div>
            <div class="card-body">
                <div>
                    <a href="#" onclick="ExportToExcel('xlsx')" class="btn btn-info px-2 py-1 mb-2">Save to XLSX</a>
                    <button class="btn-export-pdf btn btn-primary px-2 py-1 mb-2">Save to PDF</button>
                </div>
                @include("COMPONENTS.loader")
                <div class="table-responsive tableContainer" id="wrap">
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabel-rekat-unit" class="tabel-rekat table mb-0" style="border:2.5px solid black;">
                        <thead class="header">
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">codebase</th>
                                <th class="text-light align-middle text-center">SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th class="text-light align-middle text-center">total biaya</th>
                                <th class="text-light align-middle text-center">proses</th>
                                <th class="text-light align-middle text-center">real</th>
                                <th class="text-light align-middle text-center">sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit" style="font-size: 11px;">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        // ubah href setiap kali event dropdown onchange ke trigger
        $(document).on("click", ".btn-export-pdf", function(){
            const idunit     = $("select.unit_kerja").val()
            const sumberdana = $("select.sumberdana").val()
            const filter     = $("select.filter-data").val()
            const groupData  = $("select.groupData").val()

            if ( sumberdana == "" || sumberdana == "#" ){
                return tata.warn('Perhatian', 'Silahkan memilih sumber dana terlebih dahulu')
            }
            if ( idunit == "" || idunit == "#" ){
                return tata.warn('Perhatian', 'Silahkan memilih unit kerja terlebih dahulu')
            }
            // Open new tab and navigate to the URL
            const rkaTab = window.open('', '_blank')
            rkaTab.location.href = `/laporan/subkomponen/pdf?groupData=${groupData}&idunit=${idunit}&sumberdana=${sumberdana}&filterdata=${filter}`
        })
        function ExportToExcel(type, fn, dl) {
            const unitkerja = $("select.unit_kerja option:selected").text()
            if ( $("select.unit_kerja").val() == "" ) {
                return tata.warn("Perhatian", "Silahkan memilih unit kerja")
            }
            const bodyTable = document.getElementsByClassName("body-tbl-unit")
            if (bodyTable[0].rows.length === 0) {
                return tata.warn("Perhatian", "Tidak terdapat data")
            }
            var elt = document.getElementById('tabel-rekat-unit');
            var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
            return dl ?
                XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
                XLSX.writeFile(wb, fn || (`RKA-${unitkerja}.` + (type || 'xlsx')));
        }
    </script>
    @include('content.laporan.SUBKOMPONEN.script")
    @include("COMPONENTS.scriptLoader")
@endpush
