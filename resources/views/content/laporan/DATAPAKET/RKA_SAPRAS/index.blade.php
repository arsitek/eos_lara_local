@extends('layouts/layoutMaster')
@section('title', 'Laporan | RKA Sapras')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-2">
        <div class="mb-2 d-flex justify-content-start">
            <div>
                <select name="ppk" class="ppk select2" style="width: 300px">
                    <option value="">Pilih PPK</option>
                    @if( session('role') == "PPK Unit Kerja" || session('role') == "PPK Rektorat" )
                    <option value="{{ session('id_user')}}">{{ session('name') }}</option>
                    @elseif( session('role') == "superadmin" || session('role') == "admin" )
                    @foreach ($ppk as $item)
                    <option value="{{ $item->nip }}">{{$item->nama_pejabat}}</option>
                    @endforeach
                    @else
                    <option value="">PPK Tidak ditemukan</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="mb-2 d-flex">
            <div>
                <select name="filter-data" class="filter-data select2" style="width: 300px">
                    <option value="">Filter terpaketkan</option>
                    <option value="terpaketkan">Terpaketkan</option>
                    <option value="!terpaketkan">Belum Terpaketkan</option>
                </select>
            </div>
        </div>
        <div class="mb-2 d-flex">
            <div>
                <select name="sumberdana" class="sumberdana select2" style="width: 300px">
                    <option value="">Pilih Sumberdana</option>
                    @foreach ($sumberdana as $sd)
                        <option value="{{ $sd->kd_sumberdana }}">{{ $sd->sumberdana }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-2">
            <button class="btn btn-primary" id="btn-submit-filter-rka-ppk">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5em" height="1.5em">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Submit</button>
        </div>
    </div>
    <div class="mb-5 loading-div" style="display: none">
        <span class="loader" id="loading-spin"></span>
    </div>
    <div class="col-lg-12">
        <div class="card" id="rka-ppk">
            <div class="card-header d-block">
                <h3 class="card-title">LAPORAN RKT SAKTI PPK</h3>
                <div class="loading-msg" style="display: none">
                    <div class="spinner-border mt-2"></div>
                    <span class="mx-3" id="loading-msg-text">Memuat data</span>
                </div>
            </div>
            <div class="card-body">
                <div>
                    <button class="btn btn-primary px-2 py-1 mb-2" id="exportPdfAnchor">Save to PDF</button>
                </div>
                <div class="table-responsive" id="wrap">
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <div class="table-responsive tableContainer" id="wrap">
                        <h4 id="loading-msg" style="display: none;"></h4>
                        <table id="tabel-rekat-unit" class="tabel-rekat table mb-0" style="border:2.5px solid black; font-size:13px;">
                            <thead class="header">
                                <tr class="bg-dark">
                                    <th class="text-light align-middle text-center">codebase</th>
                                    <th class="text-light align-middle text-center">SD/KRO/RO/KP/SK/PIC/DK/COA/SBM</th>
                                    <th class="text-light align-middle text-center">spesifikasi</th>
                                    <th class="text-light align-middle text-center">total biaya</th>
                                    <th class="text-light align-middle text-center">rpd</th>
                                    <th class="text-light align-middle text-center">proses</th>
                                    <th class="text-light align-middle text-center">real</th>
                                    <th class="text-light align-middle text-center">rev</th>
                                    <th class="text-light align-middle text-center">sisa</th>
                                </tr>
                            </thead>
                            <tbody class="body-tbl-unit"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push("scripts")
    @include("COMPONENTS.scriptLoader")
    @include('HELPERS.report_function')
    @include('content.laporan.DATAPAKET.RKA_SAPRAS.script2")
@endpush
