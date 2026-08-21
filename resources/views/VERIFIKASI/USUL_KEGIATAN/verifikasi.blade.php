@extends('layout.layout')
@section('title', 'verifikasi usul')
@section('content')
  <div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">VERIFIKASI USUL</h3>
            </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="tabel-usul table table-bordered border mb-0" id="new-edit">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Sasaran<span style="visibility: hidden;" id="under">_</span>Program</th>
                        <th>INDIKATOR<span style="visibility: hidden;" id="under">_</span>KINERJA<span style="visibility: hidden;" id="under">_</span>KEGIATAN</th>
                        <th>Rincian<span style="visibility: hidden;">_</span>Kegiatan</th>
                        <th>kriteria</th>
                        <th>Verifikasi tim</th>
                        <th>Verifikasi pimpinan</th>
                        <th>Tanggapan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($usul_ver as $data)
                @if($data->verifikasi_tim != "Setuju" || $data->verifikasi_pimpinan != "Setuju")
                    <tr>
                       <td> {{ $data->id }} </td>
                       <td> {{ $data->sasaran_program }} </td>
                       <td> {{ $data->indikator_kinerja_kegiatan }} </td>
                       <td> {{ $data->rincian_kegiatan }} </td>
                       <td> {{ $data->kriteria }} </td>
                        <td>
                            <select name="verifikasi_tim" onchange="handleUpdate(this)"  style="width:200px" type="text" class="verifikasi_tim bg-dark my-2 text-white d-inline select2 w-auto required">
                                <option value="SILAHKAN PILIH" selected="selected">Pilih</option>
                                 @if($data->verifikasi_tim == "Setuju")
                                    <option value="Setuju" selected="true">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @elseif($data->verifikasi_tim == "Tolak")
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak" selected="true">Tolak</option>
                                @else
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="verifikasi_pimpinan" onchange="handleUpdate(this)" style="width:200px" type="text" class="verifikasi_pimpinan bg-dark my-2 text-white d-inline select2 w-auto required">
                                <option value="SILAHKAN PILIH" selected="selected">Pilih</option>
                                @if($data->verifikasi_pimpinan == "Setuju")
                                    <option value="Setuju" selected="true">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @elseif($data->verifikasi_pimpinan == "Tolak")
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak" selected="true">Tolak</option>
                                @else
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @endif
                            </select>
                        </td>
                        <td class="tanggapan" contenteditable="true" onchange="handleUpdate(this)"> {{ $data->tanggapan }}</td>
                        <td>  
                            @if(!in_array(session('id_role'), [2]))
                            <span class="save_btn"><a href="javascript:void(0)"
                            class="btn btn-lime">SAVE</a></span>
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
</div>
@endsection

@push('scripts')
    @include('VERIFIKASI.USUL_KEGIATAN.script')
@endpush
