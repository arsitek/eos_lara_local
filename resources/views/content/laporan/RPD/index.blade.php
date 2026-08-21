@extends('layouts/layoutMaster')
@section('title', 'Laporan | RPD')
@push('yss')
    @include('content.laporan.RPD.css')
@endpush
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3 my-2 d-flex flex-column flex-lg-row">
        <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
            <option value="">Pilih unit kerja</option>
            @if (session()->get('role') == "operator")
            <option value="{{ $id_unit }}" selected>{{ session()->get('unitkerja_nama')}}</option>
            @else
            @foreach($unitkerja as $item)
                @if($item->unitApi->nama ?? "-" != "-" )
                <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == $id_unit) selected @endif
                    >{{$item->unitApi->nama}}</option>
                @endif
            @endforeach
            @endif
        </select>
        <select name="sumberdana" style="width:300px"
                class="s sumberdana bg-dark my-2 mr-2 text-white d-inline select2 w-auto required">
                <option value="">Pilih Sumber Dana</option>
                @foreach($sumberdana as $item_sd)
                    <option value="{{ $item_sd->kd_sumberdana }}">{{ $item_sd->sumberdana }}</option>
                @endforeach
        </select>
        <select name="rpd" style="width:100px"
            class="s rpd bg-dark my-2 mr-2 text-white d-inline select2 w-auto required">
            <option value="">Pilih RPD</option>
            @foreach($bulan as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>
        <button class="cari btn btn-info px-3 py-1 ml-5" style="width: 150px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.5em" height="1.5em" class="me-2">
                <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
            </svg>
            Submit
        </button>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN RPD</h3>
            </div>
            <div class="card-body">
                <div>
                    <button class="btn-export-xlsx btn btn-success px-2 py-1 mb-2">Save to XLSX</button>
                    <button class="btn-export-pdf btn btn-primary px-2 py-1 mb-2">Save to PDF</button>
                </div>
                <div class="mb-5 loading-div">
                    <span class="loader" id="loading-spin"></span>
                    <h4 style="font-weight:bold; display: inline; margin-left:10px;"
                        class="loading-msg">MEMUAT DATA RPD... MOHON MENUNGGU</h4>
                </div>
                <div class="table-responsive" id="wrap">
                    <table id="tabel-rekat-unit" class="tabel-rekat table mb-0" style="border:2.5px solid black; font-size:13px;">
                        <thead class="header">
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">CODEBASE</th>
                                <th class="text-light align-middle text-center">URAIAN</th>
                                <th class="text-light align-middle text-center">TOTAL BIAYA</th>
                                <th class="text-light align-middle text-center">REALISASI</th>
                                <th class="text-light align-middle text-center">SISA</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include("HELPERS.export")
    @include("HELPERS.report_function")
    @include('content.laporan.RPD.script")
@endpush
