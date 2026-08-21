@extends('layout.layout')
@section('title', 'Laporan | Proporsi Anggaran')
@push('yss')
    @include('content.laporan.PROPORSI.css')
@endpush
@section('content')
<div class="row mt-5">
    <div class="col-lg-8 mb-3 mx-2" >
        <table style="font-size: 16px">
            <tr style="height: 40px;">
                <td style="width: 200px">Unit Kerja</td>
                <td>
                    <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                        <option value="">Pilih unit kerja</option>
                        @if(in_array(session()->get("role"), ["superadmin", "admin"]) || session()->get("id_user") == "196709261992031002")
                        <option value="semua">Semua Unitkerja</option>
                        @endif
                        @if (session()->get('role') == "operator")
                        <option value="{{ $idunit }}" selected>{{ session()->get('unitkerja_nama')}}</option>
                        @else
                        @foreach($unitKerja as $item)
                            @if($item->unitApi->nama ?? "-" != "-" )
                            <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == $idunit) selected @endif
                                >{{$item->unitApi->nama}}</option>
                            @endif
                        @endforeach
                        @endif
                    </select>
                </td>
            </tr>
            <tr style="height: 40px;">
                <td style="width: 150px">Sumberdana</td>
                <td>
                    <select name="sumberdana" class="s sumberdana" style="width:300px">
                        <option value="">Pilih sumberdana</option>
                            <option value="semua">Semua Sumber Dana</option>
                            @foreach ($sumberdana as $item)
                                <option value="{{ $item->kd_sumberdana }}">
                                    {{ $item->sumberdana }}</option>
                            @endforeach
                    </select>
                </td>
            </tr>
            <tr style="height: 30px;">
                <td style="width: 150px">Anggaran Teralokasi</td>
                <td class="alokasi-anggaran">0</td>
            </tr>
            <tr>
                <td style="width: 150px">Anggaran Terpetakan</td>
                <td class="anggaran-terpetakan">0</td>
            </tr>
        </table>
        <button class="cari btn btn-info px-3 py-1 my-2 ml-5" style="width: 170px" id="btn-cari">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="15%" class="me-1 searchSVG">
                <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
            </svg>
            Submit
        </button>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN PROPORSI ANGGARAN</h3>
            </div>
            <div class="card-body">
                <div>
                    <button class="btn-export-xlsx btn btn-success px-2 py-1 mb-2" style="width: 170px">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="15%" class="me-1">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M9.5 12.5l5 5M14.5 12.5l-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save to XLSX
                    </button>
                    <button id="btn_exportPdf" class="btn btn-primary px-2 py-1 mb-2" style="width: 170px">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="15%" class="me-1">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save to PDF
                    </button>
                </div>
                <div class="mb-5 loading-div">
                    <span class="loader" id="loading-spin"></span>
                    <h4 style="font-weight:bold; display: inline; margin-left:10px;"
                        class="loading-msg">MEMUAT DATA PROPORSI ANGGARAN... MOHON MENUNGGU</h4>
                </div>
                <div class="table-responsive" id="wrap">
                    <table id="tabel-proporsi" class="table mb-0" style="border:2.5px solid black;">
                        <thead class="header">
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">URAIAN</th>
                                <th class="text-light align-middle text-center">PAGU ALOKASI</th>
                                <th class="text-light align-middle text-center">PROPORSI BIAYA</th>
                                <th class="text-light align-middle text-center">JUMLAH BIAYA</th>
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
@push("scripts")
    @include('HELPERS.export')
    @include('content.laporan.PROPORSI.script')
@endpush
