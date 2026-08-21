@extends('layouts/layoutMaster')
@section('title', 'Laporan | Analisis')
@push('yss')
    @include('content.laporan.ANALISIS.css')
@endpush
@section('content')
@php
    $allowedRoles = ["superadmin", "admin", "verifikator", "Majelis Wali Amanat", "Pimpinan USK", "Reviewer", "Wakil Rektor", "Direktur", "Analis Resiko"];
@endphp
@if( session('error') )
    <div class="alert alert-danger alert-dismissible mt-2">
        {{ session('error')}}
    </div>
@endif
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between flex-column flex-lg-row"">
        <div class="rkaUnit">
            <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                @if (in_array(session()->get('role'), $allowedRoles))
                @foreach($unitkerja as $item)
                    @if($item->unitApi->nama ?? "-" != "-")
                    <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja")) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                    @endif
                @endforeach
                @else
                    <option value="{{ session("unitkerja") }}" selected>{{ session()->get('unitkerja_nama')}}</option>
                @endif
            </select>
            <select name="sumberdana" style="width:300px" type="text"
                    class="s sumberdana bg-dark my-2 mr-2 text-white d-inline select2 w-auto required">
                    <option value="">Pilih Sumber Dana</option>
                    @foreach($sumberdana as $item_sd)
                        <option value="{{ $item_sd->kd_sumberdana }}">{{ $item_sd->sumberdana }}</option>
                    @endforeach
            </select>
            <button class="cari btn btn-info px-3 py-1 ml-5" style="width: 150px">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="10%" class="me-1 searchSVG">
                    <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
                </svg>
                Submit
            </button>
        </div>
    </div>
    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header  d-flex justify-content-between">
                <h3 class="card-title">LAPORAN ANALISIS RESIKO</h3>
                <h4 class="ajax-message"></h4>
            </div>
            <div class="card-body">
                <div>
                    {{-- <button id="btn_save_xlsx" class="btn btn-info px-2 py-1 mb-2">Save to XLSX</button> --}}
                    <button id="btn_save_pdf" class="btn btn-primary px-2 py-1 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5em" height="1.5em">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Save to PDF</button>
                </div>
                @include("COMPONENTS.loader")
                <div class="table-responsive tableStickyContainer" id="wrap">
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabel-analisis" class="tabel-sticky table mb-0" style="border:2.5px solid black; table-layout: fixed">
                        <thead class="header">
                            <tr data-rka="true">
                                <th  style="width: 200px">codebase</th>
                                <th>SD.KRO.RO.KP.SK.DK</th>
                                <th>Tanggapan</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit">

                        </tbody>
                    </table>
                </div>
                @include('content.laporan.ANALISIS.modalRab")
                @include('content.laporan.ANALISIS.modalTor")
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@include('content.laporan.ANALISIS.script')
@include('HELPERS.report_function')
@include("COMPONENTS.scriptLoader")
@include("HELPERS.export")
@endpush
