@extends('layout.layout')
@section('title', 'Verifikasi | Rab Gedung')
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
                    <h3 class="card-title">PEMUTAKHIRAN RENCANA KEBUTUHAN PRASARANA</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-vergdg table table-bordered border mb-0">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>RPD</th>
                                <th>JENIS BELANJA</th>
                                <th>ASET</th>
                                <th>KEBUTUHAN KEGIATAN</th>
                                <th>TOTAL BIAYA</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody class="vrabkeg-body">
                            @foreach($rabgdg as $item)
                            <tr>
                                <td class="id" key="{{ $item->id }}" rekat="{{ $item->id_rekat }}"> {{ $loop->iteration }} </td>
                                <td class="rpd">
                                    <select class="rpd s" style="width: 50px">
                                        @foreach ($rpd as $itemRpd)
                                            <option value="{{$itemRpd}}" {{ $item->rpd == $itemRpd ? 'selected' : ''}}>{{$itemRpd}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="coa">
                                    <select name="jenis_belanja" class="s jenis_belanja" style="width:300px">
                                        <option value="">Pilih jenis belanja</option>
                                        @foreach ($jenis_belanja as $itemCoa)
                                        <option value="{{ $itemCoa->coa }}" {{ $itemCoa->coa == $item->id_jenis_belanja ? 'selected' : '' }}>
                                            {{ $itemCoa->coa .' | '. $itemCoa->nama }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="aset">
                                    <select class="s kodefikasi_aset" style="width:170px"></select><br>
                                    <span class="kodefikasiAsetDb">{{$item->kode_aset}} | {{$item->aset}}</span>
                                </td>
                                <td contenteditable="true" class="kk"> {{ $item->kebutuhan_kegiatan }} </td>
                                <td class="jumlah_biaya"> {{ $item->formatted_jumlah_nilai }} </td>
                                <td>
                                    <div class="btn-group">
                                        <span class="btn-save">
                                            <i role="button"
                                               class="bg-info px-2 mx-1 py-2 fa-solid fe fe-check-circle"></i></span>
                                    </div>
                                    <span class="status mt-1"></span>
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

@push('scripts')
    @include('VERIFIKASI.RAB_GEDUNG.VERIFIKATOR_RKAT.script')
@endpush
