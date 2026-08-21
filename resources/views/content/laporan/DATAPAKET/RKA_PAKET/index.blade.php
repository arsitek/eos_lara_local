@extends('layouts/layoutMaster')
@section('title', 'Laporan | RKA Paket')
@section('content')
    <div class="row mt-5">
        <div class="col-lg-12 mb-2">
            <div class="mb-2 d-flex justify-content-between">
                <div>
                    <select name="ppk" class="select2 ppk" style="width: 300px">
                        <option value="">Pilih PPK</option>
                        @if (session('role') == 'PPK Unit Kerja' || session('role') == 'PPK Rektorat')
                            <option value="{{ session('id_user') }}">{{ session('name') }}</option>
                        @elseif(session('role') == 'superadmin' || session('role') == 'admin')
                            @foreach ($ppk as $item)
                                <option value="{{ $item->nip }}">{{ $item->nama_pejabat }}</option>
                            @endforeach
                        @else
                            <option value="">PPK Tidak ditemukan</option>
                        @endif
                    </select>
                    <div class="select2-sumberdana-div mt-1">
                        <select name="sumberdana" class="sumberdana select2" style="width: 300px">
                            <option value="">Pilih Sumberdana</option>
                            @foreach ($sumberdana as $item)
                                <option value="{{ $item->kd_sumberdana }}">{{ $item->sumberdana }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <button class="btn btn-primary" id="btn-submit-filter-paket-ppk">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5em" height="1.5em">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m0-3-3-3m0 0-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                    </svg>
                    Submit</button>
            </div>
        </div>
        <div class="mb-5 loading-div" style="display: none">
            <span class="loader" id="loading-spin"></span>
        </div>
        <div class="col-lg-12">
            <div class="card" id="rka-paket-ppk">
                <div class="card-header  d-flex justify-content-between">
                    <h3 class="card-title">LAPORAN RKT SAKTI PPK</h3>
                    <h4 class="ajax-message"></h4>
                </div>
                <div class="card-body">
                    <div>
                        <button class="btn btn-primary px-2 py-1 mb-2" id="exportPdfAnchor">Save to PDF</button>
                    </div>
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
@endsection
@push('scripts')
    @include('COMPONENTS.scriptLoader')
    @include('content.laporan.DATAPAKET.RKA_PAKET.script')
@endpush
