@extends('layout.layout')
@section('title', 'Verifikasi | Rab Peralatan')
@section('content')
    <div class="row mt-5">
         <div class="col-lg-8 mb-3 mx-2" >
            <table>
                <tr>
                    <th style="width: 150px">Proporsi Alokasi</th>
                    <th class="proporsi-alokasi"></th>
                </tr>
                <tr>
                    <th style="width: 150px">Anggaran Teralokasi</th>
                    <th class="alokasi-anggaran">{{ $alokasi ?? '-' }}</th>
                </tr>
                <tr>
                    <th style="width: 150px">Anggaran Terpetakan</th>
                    <th class="anggaran-terpetakan">{{ $alokasi_terpetakan ?? '-'}}</th>
                </tr>
            </table>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PEMUTAKHIRAN RENCANA OPERASIONAL</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-verper table table-bordered border mb-0" id="new-edit">
                            <thead>
                            <tr>
                                <th>NO</th>
                                <th>RPD</th>
                                <th>KEBUTUHAN KEGIATAN</th>
                                <th>MERK</th>
                                <th>TYPE</th>
                                <th>JENIS BELANJA</th>
                                <th>KODE ASET</th>
                                <th>SPEK</th>
                                <th>BIAYA SATUAN</th>
                                <th>TOTAL BIAYA</th>
                                <th>AKSI</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rabper as $item)
                                <tr>
                                    <td class="id" key="{{$item->id}}" rekat={{ $item->id_rekat }}> {{ $loop->iteration }}</td>
                                    <td class="rpd">
                                        <select name="rpd" class="rpd s" style="width: 50px">
                                            @foreach ($rpd as $itemRpd)
                                                <option value="{{$itemRpd}}" {{ $item->rpd == $itemRpd ? 'selected' : ''}} >{{$itemRpd}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="kk" contenteditable="true">{{ $item->kebutuhan_kegiatan }}</td>
                                    <td class="merk" contenteditable="true">{{ $item->merk }}</td>
                                    <td class="type" contenteditable="true">{{ $item->type }}</td>
                                    <td class="coa">
                                        <select name="jenis_belanja" class="s jenis_belanja" style="width:200px">
                                            <option value="">Pilih jenis belanja</option>
                                            @foreach ($jenis_belanja as $itemCoa)
                                            <option value="{{ $itemCoa['id_coa'] }}" {{ $item->id_jenis_belanja == $itemCoa['id_coa'] ? 'selected' : '' }}>
                                                {{ $itemCoa['id_coa'] .' | '.$itemCoa['coa'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="aset">
                                        <select class="s kodefikasi_aset" style="width:170px"></select>
                                        <span class="kodefikasiAsetDb">{{$item->kode_aset}} | {{$item->aset}}</span>
                                    </td>
                                    <td class="spek">{{ $item->kuantitas }} {{ $item->satuan }} X {{ $item->harga_satuan }} </td>
                                    <td class="harga_satuan"> {{ $item->formatted_harga_satuan }} </td>
                                    <td class="jumlah_biaya"> {{ $item->formatted_jumlah_biaya }} </td>
                                    <td>
                                        <div class="btn-group">
                                            <span class="btn-save">
                                                <i role="button" class="bg-info px-2 mx-1 py-2 fa-solid fe fe-check-circle"></i></span>
                                        </div>
                                        <span class="status mt-2"></span>
                                       </td>
                                    </td>
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
@push("scripts")
    @include("VERIFIKASI.RAB_PERALATAN.VERIFIKATOR_RKAT.script")
@endpush

