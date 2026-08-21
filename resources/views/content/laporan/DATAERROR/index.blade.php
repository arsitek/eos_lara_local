@extends('layouts/layoutMaster')
@section('title', 'Laporan | Data Error')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-2">
        <select name="sumberdana" class="select2" style="width:300px">
            <option value="">Pilih sumberdana</option>
            @foreach ($sumberdana as $item_sd)
                <option value="{{ $item_sd->kd_sumberdana }}">{{ $item_sd->sumberdana }}</option>
            @endforeach
        </select>
        <select name="unitkerja" class="select2" style="width:300px">
            <option value="">Pilih unitkerja</option>
            @if( session('role') == "operator" )
                <option value="{{ session('unitkerja') }}" selected>{{ session('unitkerja_nama') }}</option>
            @else
            @foreach ($unitkerja as $item_unit)
                @if ( $item_unit->unitApi )
                    <option value="{{ $item_unit->unit_kerja }}"
                    @if ( $item_unit->unit_kerja == session('unitkerja') )
                        selected @endif>{{ $item_unit->unitApi->nama }}</option>
                @endif
            @endforeach
            @endif
        </select>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN DATA ERROR</h3>
            </div>
            <div class="card-body">
                <div class="mb-5 loading-div" style="display: none">
                    <span class="loader" id="loading-spin"></span>
                    <h4 style="font-weight:bold; display: inline; margin-left:10px;"
                        class="loading-msg">MEMUAT DATA.. MOHON MENUNGGU</h4>
                </div>
                <button class="btn btn-primary mb-2" id="pejabatTidakDitemukan">Pejabat tidak ditemukan</button>
                <div class="table-responsive">
                    <table class="tabel-data-error-tidak-ditemukan table table-bordered table-striped
                    border mb-0" style="display: none">
                        <thead>
                            <tr>
                                <td>Id Rekat</td>
                                <td>Sub Judul</td>
                                <td>Id Kegiatan</td>
                                <td>Kegiatan</td>
                                <td>Coa</td>
                                <td>RPD</td>
                                <td>Total Biaya Kegiatan</td>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push("scripts")
    @include('content.laporan.DATAERROR.script')
@endpush
