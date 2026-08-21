@extends('layout.layout')
@section('title', 'Verifikasi | Rab Langganan')
@push('yss')
    @include('VERIFIKASI.css')
@endpush
@section('content')
    @if( session()->get('Pesan') )
        <div class="alert alert-danger alert-dismissible mt-2">
            {{ session()->get('Pesan')}}
        </div>
    @endif
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
                <div class="card-header">
                    <h3 class="card-title">VERIFIKASI RAB LANGGANAN</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-verlangganan table table-bordered border mb-0" id="new-edit">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Rincian Output</th>
                                <th>Komponen</th>
                                <th>Sub Komponen</th>
                                <th>Detail Kegiatan</th>
                                <th>Tor</th>
                                <th>Jenis<span style="visibility:hidden;">_</span>Belanja</th>
                                <th>Kebutuhan Kegiatan</th>
                                <th>qt</th>
                                <th>sat</th>
                                <th>dr</th>
                                <th>sat</th>
                                <th>keg</th>
                                <th>sat</th>
                                <th>Biaya satuan (Rp)</th>
                                <th>Jumlah Biaya (Rp)</th>
                                @if( $isCrud == 1 )
                                <th>Verifikasi tim</th>
                                <th>Verifikasi aset</th>
                                <th>Verifikasi keuangan</th>
                                <th>Verifikasi pimpinan unit</th>
                                <th>Verifikasi rektor</th>
                                <th>tanggapan</th>
                                <th>Aksi</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="vrablangganan-body">
                            </tbody>
                        </table>
                    </div>
                    @include("VERIFIKASI.COMPONENTS.modalTor")
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    @include('VERIFIKASI.RAB_LANGGANAN.VERIFIKATOR_RKAT.script')
@endpush
