@extends('layouts/layoutMaster')
@section('title', 'RKA | Daya Serap')
@section('content')
@php
    $role         = session('role');
    $allowedRoles = ["superadmin", "admin"];
@endphp
<div class="row mt-5">
    <div class="col-lg-8 mb-3 mx-2" >
        <table>
            <tr>
                <th style="width: 150px">Unit Kerja</th>
                <th>
                    <select name="unitkerja" class="s unit_kerja" style="width:300px">
                        <option value="">Pilih unit kerja</option>
                        @if( in_array( $role, $allowedRoles ) )
                        <option value="semua">Semua Unit kerja</option>
                        @endif
                        @if( in_array( $role, $allowedRoles ) || session()->get('role') == "Wakil Rektor" )
                        @foreach ($unitkerja as $item)
                            @if( $item->unitApi->idunit ?? '-' != '-')
                                <option value="{{ $item->unitApi->idunit ?? '-'}}"
                                @if($item->unit_kerja == session()->get('unitkerja'))
                                selected
                                @endif>{{ $item->unitApi->nama ?? '-' }}</option>
                            @endif
                        @endforeach
                        @else
                            <option value="{{ session('unitkerja') }}" selected>{{ session('unitkerja_nama')}}</option>
                        @endif
                    </select>
                </th>
            </tr>
            <tr>
                <th style="width: 150px">Sumberdana</th>
                <th>
                    <select name="sumberdana" style="width:300px" class="s sumberdana bg-dark my-2 mr-2 text-white d-inline w-auto">
                        <option value="">Pilih Sumber Dana</option>
                        <option value="semua">Semua Sumber Dana</option>
                        <option value="ptnbh">Non APBN</option>
                        <option value="bptnbh">APBN</option>
                        @if( in_array( $role, $allowedRoles ) )
                            @foreach($sumberdana as $item_sd)
                            <option value="{{ $item_sd->kd_sumberdana }}">{{ $item_sd->sumberdana }}</option>
                            @endforeach
                        @endif
                    </select>
                </th>
            </tr>
        </table>
        <button class="btn btn-primary my-2" id="btn-filter-unitkerja">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-3" width="1.5rem" height="1.5rem">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg> SUBMIT
        </button>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">LAPORAN RKA DAYA SERAP</h3>
            </div>
            <div class="mt-2 mx-5">
                <div class="mb-5 loading-div align-items-center" style="display: none">
                    <span class="loader" id="loading-spin"></span>
                    <h5 class="loading-msg fw-bold mt-2 mx-2"></h5>
                </div>
                <div class="d-flex gap-3 my-2">
                    <button class="btn btn-info btn-download-pdf d-flex align-items-center px-4 py-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5rem" height="1.5rem">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>Export to PDF
                    </button>
                    <button class="btn btn-success btn-download-xlsx d-flex align-items-center px-3 py-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5rem" height="1.5rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                        </svg> Export to XLSX
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive tableStickyContainer">
                    <table id="tabel-rka-coa" class="tabel-sticky table mb-0" style="border:2.5px solid black;">
                        <thead>
                            <tr>
                                <th>codebase</th>
                                <th>SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>total biaya</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl" style="font-size: 13px">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include("HELPERS.report_function")
    @include('content.laporan.RKA.COA.script")
@endpush
