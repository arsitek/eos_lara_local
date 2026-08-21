@extends('layout.layout')
@section('title', 'Lampiran | RKAT')
@section('content')
@push('yss')
    @include('content.laporan.RKAT.LAMPIRAN.style")
@endpush
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between flex-column flex-lg-row">
        <div class="rkaUnit">
            <select name="unitkerja" class="unitkerja" style="width:300px">
                <option value="">Pilih unit kerja</option>
                @if (session()->get('role') == "superadmin" ||
                session()->get('role') == "admin" ||
                session()->get('role') == "verifikator" ||
                session()->get("role") == "Majelis Wali Amanat" ||
                session()->get("role") == "Pimpinan USK")
                <option value="semua_unit">Semua unitkerja</option>
                @foreach($unitkerja as $item)
                    @if($item->unitApi->nama ?? "-" != "-")
                    <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja") ) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                    @endif
                @endforeach
                @elseif(session()->get('role') == "Wakil Rektor")
                @foreach($unitkerja as $item)
                    @if($item->unitApi->nama ?? "-" != "-")
                    <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == session("unitkerja") ) selected @endif>{{$item->unitApi->nama ?? "-"}}</option>
                    @endif
                @endforeach
                @else
                    <option value="{{ session("unitkerja") }}" selected>{{ session()->get('unitkerja_nama')}}</option>
                @endif
            </select>
            <select name="sumberdana" style="width:300px" class="s sumberdana">
                <option value="">Pilih Sumber Dana</option>
                <option value="ptnbh">Non APBN</option>
                <option value="bptnbh">APBN</option>
                @foreach($sumberdana as $item_sd)
                    @if( $item_sd->id_parent != 0 )
                    <style>
                        ul.select2-results__options li {
                            padding-left :30px !important
                        }
                    </style>
                    @endif
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
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">LAPORAN RKAT LAMPIRAN</h3></div>
            <div class="card-body">
                @include("COMPONENTS.loader")
                <div>
                    <button class=" btn btn-primary px-2 py-1" id="btn_exportXlsx" style="width:150px">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10%" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                          </svg>
                        Save to XLSX
                    </button>
                    <button class="btn btn-primary px-2 py-1" id="btn_exportPdf" style="width:130px">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10%" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Save to PDF
                    </button>
                </div>
                <div class="table-responsive mt-2 tableContainer">
                    <table class="tabel-rkat table table-bordered border mb-0" id="tabel-rkat">
                        <thead>
                            <tr class="bg-dark">
                                <th class="text-light align-middle">KODEFIKASI</th>
                                <th class="text-light align-middle">URAIAN</th>
                                <th class="text-light align-middle">KUANTITAS</th>
                                <th class="text-light align-middle">SATUAN</th>
                                <th class="text-light align-middle">DURASI</th>
                                <th class="text-light align-middle">SATUAN</th>
                                <th class="text-light align-middle">VOLUME</th>
                                <th class="text-light align-middle">SATUAN</th>
                                <th class="text-light align-middle">ANGGARAN BIAYA</th>
                            </tr>
                        </thead>
                        <tbody class="bodyTbl">
                        </tbody>
                    </table>
                </div>
                @include('content.laporan.RKAT.LAMPIRAN.modalRekapSemuaUnit")
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include("HELPERS.export")
    @include("HELPERS.report_function")
    @include('content.laporan.RKAT.LAMPIRAN.script')
    @include("COMPONENTS.scriptLoader")
@endpush
