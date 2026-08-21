@extends('layout.layout')
@section('title', 'Verifikasi | Rab Gedung')
@push('yss')
    @include('VERIFIKASI.css')
@endpush
@section('content')
    @if( session()->get('Pesan') )
        <div class="alert alert-danger alert-dismissible mt-2">
            {{ session()->get('Pesan')}}
        </div>
    @endif
    @php
        $kd_sumberdana = request()->kd_sumberdana;
    @endphp
    <div class="row mt-5">
        <div class="col-lg-8 mb-3 mx-2" >
            <div class="table-header-phone" style="display: none">
                <div class="d-flex flex-column">
                    <label for="unitkerja" class="text-muted">Unitkerja</label>
                    @include("VERIFIKASI.COMPONENTS.unitkerja")
                </div>
                <div class="d-flex flex-column my-2">
                    <label for="sumberdana" class="text-muted">Sumberdana</label>
                    @include("VERIFIKASI.COMPONENTS.sumberdana")
                </div>
                <div class="d-flex flex-column my-2">
                    <button class="btn-filter-unitkerja btn btn-info px-3 py-1 ml-5" style="width: 150px">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="10%" class="me-1 searchSVG">
                            <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
                        </svg>
                        Submit
                    </button>
                </div>
                {{-- <div class="d-flex flex-column my-2">
                    <label for="proporsi-alokasi" class="text-muted">Proporsi Alokasi</label>
                    <span>-</span>
                </div>
                <div class="d-flex flex-column my-2">
                    <label for="alokasi-anggaran" class="text-muted">Anggaran Teralokasi</label>
                    <span class="alokasi-anggaran"></span>
                </div>
                <div class="d-flex flex-column">
                    <label for="anggaran-terpetakan" class="text-muted">Anggaran Terpetakan</label>
                    <span class="anggaran-terpetakan"></span>
                </div> --}}
            </div>
            <table class="table-header-desktop">
                <tr>
                    <th style="min-width: 150px">Unit Kerja</th>
                    <th>
                        @include("VERIFIKASI.COMPONENTS.unitkerja")
                    </th>
                    <th>
                        @include("VERIFIKASI.COMPONENTS.sumberdana")
                    </th>
                    <th>
                        <button class="btn-filter-unitkerja btn btn-info px-3 py-1 ml-5" style="width: 150px">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="10%" class="me-1 searchSVG">
                                <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
                            </svg>
                            Submit
                        </button>
                    </th>
                </tr>
                {{-- <tr>
                    <th style="width: 200px">Proporsi Alokasi</th>
                    <th class="proporsi-alokasi"></th>
                    <th></th>
                </tr>
                <tr>
                    <th style="width: 200px">Anggaran Teralokasi</th>
                    <th class="alokasi-anggaran">{{ $alokasi }}</th>
                    <th></th>
                </tr>
                <tr>
                    <th style="width: 200px">Anggaran Terpetakan</th>
                    <th class="anggaran-terpetakan">{{ $alokasi_terpetakan ?? '0' }}</th>
                    <th></th>
                </tr> --}}
            </table>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">PEMUTAKHIRAN RENCANA KEBUTUHAN PRASARANA</h3>
                    @if( null !== request()->kd_sumberdana )
                    @include("VERIFIKASI.COMPONENTS.filter-status")
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @if( null !== request()->kd_sumberdana )
                        <div class="d-flex justify-content-between mb-2">
                            <div class="">
                                <button class="btn btn-info" id="btn-setujui-semua" style="width: 200px">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="11%">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                    </svg>
                                    Setujui semua kegiatan
                                </button>
                            </div>
                        </div>
                        @endif
                        <table class="tabel-vergdg table table-bordered border mb-0" id="new-edit">
                            <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Id</th>
                                <th class="align-middle text-center" rowspan="2">Rincian Output</th>
                                <th class="align-middle text-center" rowspan="2">Komponen</th>
                                <th class="align-middle text-center" rowspan="2">SUB KOMPONEN</th>
                                <th class="align-middle text-center" rowspan="2">DETAIL KEGIATAN</th>
                                <th class="align-middle text-center" rowspan="2">TOR</th>
                                <th class="align-middle text-center" rowspan="2">RPD</th>
                                <th class="align-middle text-center" rowspan="2">KEBUTUHAN KEGIATAN</th>
                                <th class="align-middle text-center" rowspan="2">JENIS BELANJA</th>
                                <th class="align-middle text-center" rowspan="2">KODE ASET</th>
                                <th class="align-middle text-center" rowspan="2">TOTAL BIAYA</th>
                                <th class="text-center" colspan="5">Verifikator</th>
                                <th class="align-middle text-center" rowspan="2">Tanggapan</th>
                                <th class="align-middle text-center" rowspan="2">Aksi</th>
                            </tr>
                            <tr>
                                <th class="text-center">TIM</th>
                                <th class="text-center">ASET</th>
                                <th class="text-center">KEUANGAN</th>
                                <th class="text-center">PIMPINAN UNIT</th>
                                <th class="text-center">PIMPINAN UNIV</th>
                            </tr>
                            </thead>
                            <tbody class="vrabgdg-body">
                            @if( null !== request()->kd_sumberdana )
                            @foreach($rabgdg as $item)
                            <tr>
                                <td class="id" key="{{ $item->id }}"> {{ $loop->iteration }} </td>
                                <td class="ikk"> {{ ' [ ' . $item->kode_ikk . ' ] ' . $item->ikk }} </td>
                                <td class="ikv"> {{ ' [ ' . $item->kode_ikv . ' ] ' . $item->ikv }} </td>
                                <td class="sk">{{ $item->kd_rk . ' | ' . $item->rincian_kegiatan }} </td>
                                <td class="dk"> {{ $item->id_rekat }} | {{ $item->sub_judul }} </td>
                                <td class="tor">
                                     @if(!empty($item->tor))
                                        <div class="document-link-container">
                                            <a href="/rekat/tor?id={{ $item->id_rekat }}" target="_blank" class="document-link" data-id="{{ $item->id_rekat }}" data-subjudul="{{ $item->sub_judul }}">
                                                <div class="icon-wrapper">
                                                    <i class="fa fa-file-text" aria-hidden="true"></i>
                                                </div>
                                            </a>
                                        </div>
                                    @else
                                        Tor tidak diupload
                                    @endif
                                </td>
                                <td class="rpd">{{ $item->rpd }}</td>
                                <td> {{ $item->kebutuhan_kegiatan }} </td>
                                <td>{{ $item->id_jenis_belanja }} | {{ $item->jenis_belanja }}</td>
                                <td>{{ $item->kode_aset ? $item->kode_aset. ' | '. $item->aset : '-'}}</td>
                                <td class="jumlah_biaya"> {{ $item->formatted_jumlah_nilai  }} </td>
                                <!-- ✅ Verifikasi tim -->
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiTim"
                                        @if($item->verifikasi_tim == "Setuju")
                                            checked
                                        @endif @if( $isVerifikator == false ) disabled @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <!-- ✅ Verifikasi aset -->
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiAset"
                                        @if($item->verifikasi_aset == "Setuju")
                                            checked
                                        @endif @if( $isVerifikator == false ) disabled @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <!-- ✅ Verifikasi keu -->
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiKeu"
                                        @if($item->verifikasi_keu == "Setuju")
                                            checked
                                        @endif @if( $isVerifikator == false ) disabled @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <!-- ✅ Verifikasi pimpinan_unit -->
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiPimpinanUnit"
                                        @if($item->verifikasi_pimpinan_unit == "Setuju")
                                            checked
                                        @endif @if( $isVerifikator == false ) disabled @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <!-- ✅ Verifikasi pimpinan_univ -->
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="switchVerifikasi" data-jenis="verifikasiPimpinanUniv"
                                        @if($item->verifikasi_pimpinan_univ == "Setuju")
                                            checked
                                        @endif @if( $isVerifikator == false ) disabled @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <button id="btn_triggerTanggapan" class="btn btn-primary mb-4 mt-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                        </svg>
                                        Tanggapan
                                    </button>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn-edit-modal text-white" href="/verifikasi/gedung/edit/{{ $item->id }}?kd_sumberdana={{ $kd_sumberdana}}" target="_blank">
                                            <i role="button" class="bg-success px-2 mx-1 py-2 fa-solid fe fe-edit"></i>
                                        </a>
                                    </div>
                                    <span class="status mt-1"></span>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    @include("VERIFIKASI.RAB_KEGIATAN.modalTanggapan")
                    @include("VERIFIKASI.COMPONENTS.modalTor")
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include('VERIFIKASI.RAB_GEDUNG.VERIFIKATOR_RKAT.script')
    @include("VERIFIKASI.RAB_GEDUNG.script")
    @include('VERIFIKASI.COMPONENTS.script')
@endpush
