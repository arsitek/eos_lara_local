@extends('layouts/layoutMaster')
@section('title', 'RKA | Rincian Output')
@section('content')
<div class="row mt-5">
<div class="col-lg-8 mb-3">
        <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
            <option value="">Pilih unit kerja</option>
            @foreach($unitkerja as $item)
                @if($item->unitApi->nama ?? "-" != "-")
                <option value="{{$item->unit_kerja}}" @if($item->unit_kerja == $idunit) selected @endif>{{$item->unitApi->nama}}</option>
                @endif
            @endforeach
        </select>
        <button class="btn btn-filter-unitkerja btn-info">SUBMIT</button>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">LAPORAN RKA RINCIAN OUTPUT</h3>
            </div>
            <div class="card-body">
                <div>
                    <a href="#" class="btn-export-xlsx btn btn-info px-2 py-1 mb-2">Save to XLSX</a>
                    <a href="{{ route('rktReport.pdf') }}" class="btn-export-pdf btn btn-primary px-2 py-1 mb-2">Save to PDF</a>
                </div>
                <div class="table-responsive">
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" style="border:2.5px solid black;">
                        <thead>
                            <tr>
                                <th>codebase</th>
                                <th >SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>keg</th>
                                <th>item</th>
                                <th>biaya satuan</th>
                                <th>total biaya</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <tr style="border-top:2.5px solid black">
                                <td>41</td>
                                <td class="non-apbn">Non APBN</td>
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
    @include('content.laporan.RKA.RINCIAN_OUTPUT.script')
@endpush
