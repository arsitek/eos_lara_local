@extends('layout.layout')
@section('title', 'Verifikasi | Edit Rab Langganan')
@push("yss")
    @include("VERIFIKASI.css")
@endpush
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
                    <th class="anggaran-terpetakan">{{ $alokasi_terpetakan ?? '0' }}</th>
                    <th></th>
                </tr>
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
                                    <th class="text-center">No</th>
                                    <th class="text-center">Sub Komponen</th>
                                    <th class="text-center">Detail Kegiatan</th>
                                    <th class="text-center">tor</th>
                                    <th class="text-center">Jenis<span style="visibility:hidden;">_</span>Belanja</th>
                                    <th class="text-center">RPD</th>
                                    <th class="text-center">Kebutuhan Kegiatan</th>
                                    <th class="text-center">spesifikasi</th>
                                    <th class="text-center">Biaya satuan (Rp)</th>
                                    <th class="text-center">Jumlah Biaya (Rp)</th>
                                    @if( $isCrud == 1 )
                                    <th class="text-center">tanggapan</th>
                                    <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="vrablangganan-body">
                            @foreach($rablangganan as $item)
                            <tr>
                                <td class="id" key="{{ $item->id }}" rekat={{ $item->id_rekat }}> {{ $loop->iteration }} </td>
                                <td class="sk">{{ ' [ ' . $item->kd_rk . ' ] ' . $item->rincian_kegiatan }} </td>
                                <td class="dk">{{ $item->id_rekat }} | {{ $item->sub_judul }} </td>
                                <td class="tor">
                                    @if( !empty($item->tor) )
                                        <div class="document-link-container">
                                            <a href="{{ asset('uploads/tor/'. $item->tor ) }}" class="document-link">
                                                <div class="icon-wrapper">
                                                    <i class="fa fa-file-text" aria-hidden="true"></i>
                                                </div>
                                            </a>
                                        </div>
                                    @else
                                        Tor tidak tersedia
                                    @endif
                                </td>
                                <td class="coa" key="{{$item->id_jenis_belanja}}">{{ ' [ ' . $item->id_jenis_belanja . ' ] ' . $item->jenis_belanja }} </td>
                                <td class="rpd"> {{ $item->rpd }} </td>
                                <td class="kk"> {{ $item->kebutuhan_kegiatan }} </td>
                                <td>{{
                                    $item->kuantitas .' '. $item->satuan_kuantitas .' x '.
                                    $item->durasi .' '. $item->satuan_durasi.' x '.
                                    $item->kegiatan .' '. $item->satuan_kegiatan.' x '.
                                    $item->biaya_satuan}}
                                </td>
                                <td class="biaya_satuan"> {{ $item->formatted_biaya_satuan }}</td>
                                <td class="jumlah_biaya"> {{ $item->formatted_jumlah_biaya }}</td>
                                <td class="tanggapan">
                                    @foreach($item->tanggapan()->get() as $tanggapan)
                                        {{ session()->get('role') ==$tanggapan->role ?
                                        $tanggapan->tanggapan : '' }}
                                    @endforeach
                                </td>
                                @if ( $isCrud == 1 )
                                <td>
                                    <div class="btn-group">
                                        <span class="btn-save">
                                            <i role="button"
                                               class="bg-info px-2 mx-1 py-2 fa-solid fe fe-check-circle"></i></span>
                                        <span class="btn-edit">
                                            <i role="button"
                                               class="bg-success px-2 mx-1 py-2 fa-solid fe fe-edit"></i></span>
                                    </div>
                                    <span class="status pt-2"></span>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
    @include('VERIFIKASI.RAB_LANGGANAN.VERIFIKATOR_RKAT.script')
    @include('COMPONENTS.scriptPagination')
@endpush
