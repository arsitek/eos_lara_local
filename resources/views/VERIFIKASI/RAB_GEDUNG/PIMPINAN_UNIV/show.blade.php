@extends('layout.layout')
@section('title', 'Verifikasi | Rab Gedung')
@section('content')
    <div class="row mt-5">
        <div class="col-lg-8 mb-3 mx-2" >
            <table>
                <tr>
                    <th style="width: 150px">Unit Kerja</th>
                    <th>
                        <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
                            <option value="">Pilih unit kerja</option>
                            @foreach ($unitkerja as $item)
                                <option value="{{ $item->unit->idunit ?? '-'}}">{{ $item->unit->unitkerja ?? '-' }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>
                        <a class="btn-filter-unitkerja btn btn-info px-3 py-2" style="margin-left: 5px">SUBMIT</a><br>
                    </th>
                </tr>
                {{-- <tr>
                    <th style="width: 150px">Proporsi Alokasi</th>
                    <th class="proporsi-alokasi"></th>
                </tr>
                <tr>
                    <th style="width: 150px">Anggaran Teralokasi</th>
                    <th class="alokasi-anggaran"></th>
                </tr>
                <tr>
                    <th style="width: 150px">Anggaran Terpetakan</th>
                    <th class="anggaran-terpetakan"></th>
                </tr> --}}
            </table>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PEMUTAKHIRAN RENCANA OPERASIONAL</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-verkeg table table-bordered border mb-0" id="new-edit">
                            <thead>
                            <tr>
                                <th>NO</th>
                                <th>RINCIAN OUTPUT</th>
                                <th>SUB KOMPONEN</th>
                                <th>DETAIL KEGIATAN</th>
                                <th>TOR</th>
                                <th>KEBUTUHAN KEGIATAN</th>
                                <th>TOTAL BIAYA</th>
                                <th>VERIFIKASI PIMPINAN UNIV</th>
                                <th>TANGGAPAN</th>
                                <th>AKSI</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rabkeg as $item)
                                <tr>
                                    <td class="id" key="{{$item->id}}"> {{ $loop->iteration }} </td>
                                    <td> {{ $item->indikator_kinerja_kegiatan }} </td>
                                    <td> [ {{ $item->kd_rk . ' ] ' . $item->rincian_kegiatan }} </td>
                                    <td> {{ $item->sub_judul }} </td>
                                    <td>
                                        @if(!empty($item->tor))
                                            <a href="/rekat/tor?id={{ $item->id_rekat }}" class="document-link" target="_blank" data-id="{{ $item->id_rekat }}" data-subjudul="{{ $item->sub_judul }}">{{ $item->tor }}</a>
                                        @else
                                            Tor tidak diupload
                                        @endif
                                    </td>
                                    <td> {{ $item->kebutuhan_kegiatan }} </td>
                                    <td> {{ $item->formatted_jumlah_nilai }} </td>
                                    <td>
                                        <select name="verifikasi_pimpinan_univ"
                                                class="s my-2 bg-dark text-white
                                                        text-white d-inline w-auto">
                                            <option value="">Pilih Status</option>
                                            <option value="Setuju"
                                                @if($item->verifikasi_pimpinan_univ == "Setuju")
                                                    selected
                                                @endif>Setuju</option>
                                            <option value="Tolak"
                                                    @if($item->verifikasi_pimpinan_univ == "Tolak")
                                                        selected
                                                @endif>Tolak</option>
                                        </select>
                                    </td>
                                    <td contenteditable="true">
                                        @foreach($item->tanggapan()->get() as $tanggapan)
                                        {{ session()->get('role') == $tanggapan->role ?
                                            $tanggapan->tanggapan : ''}}
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <span class="btn-save">
                                            <i role="button"
                                               class="bg-info px-2 mx-1 py-2 fa-solid fe fe-check-circle"></i></span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @include("VERIFIKASI.COMPONENTS.modalTor")
                </div>
            </div>
        </div>
    </div>
@endsection
@push("scripts")
    @include("VERIFIKASI.RAB_KEGIATAN.PIMPINAN_UNIV.script")
@endpush
