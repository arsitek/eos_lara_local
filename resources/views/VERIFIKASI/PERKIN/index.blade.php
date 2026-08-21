@extends('layout.layout')
@section('title', 'Verifikasi | Perkin')
@section('content')
<div class="row mt-5">
    <div class="col-lg-8 mb-3 mx-2" >
        <select name="unitkerja" id="" class="s unit_kerja" style="width:300px">
            <option value="">Pilih Unit Kerja</option>
            @if( in_array( session("role"), ["superadmin", "admin"]) )
            @foreach ($unitkerja as $item)
                <option value="{{$item->idunit}}" @if($item->idunit == $idunit) selected @endif>{{ $item->nama }}</option>
            @endforeach
            @else
                <option value="{{ session('unitkerja') }}" selected>{{ session('unitkerja_nama') }}</option>
            @endif
        </select>
        <button class="btn-filter-unitkerja btn btn-info px-3 py-1 ml-5" style="width: 150px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="10%" class="me-1 searchSVG">
                <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
            </svg>
            Submit
        </button>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Verifikasi Perjanjian Kinerja</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="tabel-perkin table table-bordered border mb-0" id="new-edit">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                                    <th>Kode IKK</th>
                                    <th>Indikator<span style="visibility: hidden;">_</span>kinerja<span
                                            style="visibility:hidden;">_</span>kegiatan</th>
                                    <th>KK MENDIKBUD</th>
                                    <th>TW 1</th>
                                    <th>TW 2</th>
                                    <th>TW 3</th>
                                    <th>TW 4</th>
                                    <th>Bobot</th>
                                    <th>Verifikasi tim</th>
                                    <th>Verifikasi<span style="visibility: hidden;" id="under">_</span>pimpinan</th>
                                    <th>tanggapan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($verPerkin as $data)
                                @if($data->verifikasi_tim != "Setuju" || $data->verifikasi_pimpinan != "Setuju")
                                <tr>
                                    <td key="{{$data->id}}">{{ $loop->iteration }}</td>
                                    <td>{{$data->unitApi->nama ?? '-'}}</td>
                                    <td>{{ $data->kode_ikk }}</td>
                                    <td> {{ $data->ro->indikator_kinerja_kegiatan }}</td>
                                    <td> {{ $data->kk_mendikbud }}</td>
                                    <td id="tw_1"> {{ $data->tw_1 }}</td>
                                    <td id="tw_2"> {{ $data->tw_2 }}</td>
                                    <td id="tw_3"> {{ $data->tw_3 }}</td>
                                    <td id="tw_4"> {{ $data->tw_4 }}</td>
                                    <td class="bobot"> {{ $data->bobot }}</td>
                                    <td>
                                        <select name="verifikasi_tim"  style="width:200px" type="text" class="verifikasi_tim bg-dark my-2 text-white d-inline select2 w-auto required">
                                            <option value="" selected>Silahkan Pilih</option>
                                            <option value="Setuju" @if($data->verifikasi_tim == "Setuju") selected="true" @endif>Setuju</option>
                                            <option value="Tolak" @if($data->verifikasi_tim == "Tolak") selected="true" @endif>Tolak</option>
                                            </select>
                                    </td>
                                    <td>
                                        <select name="verifikasi_pimpinan"  style="width:200px" type="text" class="verifikasi_pimpinan bg-dark my-2 text-white d-inline select2 w-auto required">
                                            <option value="" selected>Silahkan Pilih</option>
                                            <option value="Setuju" @if($data->verifikasi_pimpinan == "Setuju") selected="true" @endif>Setuju</option>
                                            <option value="Tolak" @if($data->verifikasi_pimpinan == "Tolak") selected="tolak" @endif>Tolak</option>
                                        </select>
                                    </td>
                                    <td class="tanggapan" contenteditable="true"> {{ $data->tanggapan }}</td>
                                    <td>
                                        @if(!in_array(session('id_role'), [2]))
                                        <span class="save_btn"><a href="javascript:void(0)" class="btn btn-lime">SAVE</a></span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('VERIFIKASI.PERKIN.setuju')
    </div>
@endsection

@push('yss')
    @include('VERIFIKASI.PERKIN.css')
@endpush

@push('scripts')
    @include('VERIFIKASI.PERKIN.script')
@endpush
