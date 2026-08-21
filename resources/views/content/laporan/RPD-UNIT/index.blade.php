@extends('layouts/layoutMaster')
@section('title', 'Laporan | RPD UNIT')
@section('content')
<div class="row mt-5">
    <div class="mb-2 d-flex justify-content-between">
        <div>
            <select name="sumberdana" class="select2" style="width: 300px">
                <option value="">Pilih Sumber Dana</option>
                @foreach ($sumberdana as $item)
                    <option value="{{ $item->kd_sumberdana }}">{{ $item->sumberdana }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-filter-sumberdana px-3" style="width: 100px">Submit</button>
        </div>
        <div>
            <button class="btn btn-success btn-tanpa-rpd">TANPA RPD</button>
            <button class="btn btn-success btn-dengan-rpd" style="display: none">RPD</button>
        </div>
    </div>
    <div class="col-lg-12 dengan-rpd">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">REKAPITULASI RPD</h3>
            </div>
            <div class="card-body">
                <div class="mb-5 loading-div" style="display: none">
                    <span class="loader" id="loading-spin"></span>
                    <h4 style="font-weight:bold; display: inline; margin-left:10px;"
                        class="loading-msg">MEMUAT DATA REKAPITULASI RPD... MOHON MENUNGGU</h4>
                </div>
                <div class="table-responsive" id="wrap">
                    <table id="tabel-rpd" class="tabel-rekat table mb-0 table-bordered" style="border:2.5px solid black;">
                        <thead class="header">
                            <tr>
                                <td colspan="14" style="text-align: center">RPPA/RPD</td>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <tr>
                                <td colspan="2" style="text-align: center">UNITKERJA</td>
                                <td>JANUARI</td>
                                <td>FEBRUARI</td>
                                <td>MARET</td>
                                <td>APRIL</td>
                                <td>MEI</td>
                                <td>JUNI</td>
                                <td>JULI</td>
                                <td>AGUSTUS</td>
                                <td>SEPTEMBER</td>
                                <td>OKTOBER</td>
                                <td>NOVEMBER</td>
                                <td>DESEMBER</td>
                            </tr>
                            @foreach( $unitkerja as $unit )
                            @if ( $unit->unitApi->nama ?? '' != '' )
                            <tr style="font-size: 11px">
                                <td> {{ $unit->unit_kerja }} </td>
                                <td class="{{$unit->unit_kerja}} idunit"> {{ $unit->unitApi->nama }} </td>
                                <td class="01"></td>
                                <td class="02"></td>
                                <td class="03"></td>
                                <td class="04"></td>
                                <td class="05"></td>
                                <td class="06"></td>
                                <td class="07"></td>
                                <td class="08"></td>
                                <td class="09"></td>
                                <td class="10"></td>
                                <td class="11"></td>
                                <td class="12"></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12 tanpa-rpd" style="display: none">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">REKAPITULASI TANPA RPD</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="wrap">
                    <table id="tabel-rpd-null" class="tabel-rekat table table-responsive mb-0 table-bordered"
                    style="border:2.5px solid black;">
                        <thead class="header">
                            <tr>
                                <td>Sumber Dana</td>
                                <td>Id Rekat</td>
                                <td>Idunit</td>
                                <td>Unit Kerja</td>
                                <td>Sub Judul</td>
                                <td>Kebutuhan Kegiatan</td>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                           @foreach ($rab as $item)
                               <tr>
                                      <td>{{ $item->kd_sumberdana ?? '-' }} | {{ $item->sumberdana ?? '-' }}</td>
                                      <td>{{ $item->id_rekat ?? '' }}</td>
                                      <td>{{ $item->nama_unit ?? '' }}</td>
                                      <td>{{ $item->nama_unit ?? '' }}</td>
                                      <td>{{ $item->sub_judul ?? ''}}</td>
                                      <td>{{ $item->itemCoa ?? '-'}}</td>
                               </tr>
                           @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    @include('content.laporan.RPD-UNIT.script")
@endpush
