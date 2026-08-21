@extends('layout.layout')
@section('title', 'verifikasi rekat')
@section('content')
  <div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">VERIFIKASI REKAT</h3>
            </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="tabel-verrekat table table-bordered border mb-0" id="new-edit">
                <thead>
                    <tr>
                         <th>id</th>
                        <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                        <th>Sasaran Program</th>
                        <th>INDIKATOR<span style="visibility: hidden;" id="under">_</span>KINERJA<span style="visibility: hidden;" id="under">_</span>KEGIATAN</th>
                        <th>Rincian<span style="visibility: hidden;">_</span>Kegiatan</th>
                        <th>Tor</th>
                        <th>Rincian Sub Komponen</th>
                        <th>Jenis Belanja</th>
                        <th>Kebutuhan Kegiatan</th>
                        <th>Jumlah Biaya</th>
                        <th>Jumlah Biaya</th>
                        <th>verifikasi tim</th>
                        <th>verifikasi<span style="visibility: hidden">_</span>pimpimnan</th>
                        <th>verifikasi univ</th>
                        <th>tanggapan</th>
                        <th>aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $id = 0;?>
                @foreach($rekat as $data)
                 @if($data->veririkasi_tim != "Setuju" || $data->verifikasi_pimpinan != "Setuju")
                 <?php $dir ='uploads/tor/' ;?>
                 <?php $id++;?>
                    <tr>
                        <td> {{ $data->id }} </td>
                        <td unitkerja>{{ $data->unit->unitkerja ?? '-'}}</td>
                        <td> {{ $data->sasaran_program }} </td>
                        <td> {{ $data->indikator_kinerja_kegiatan }} </td>
                        <td> {{ $data->rincian_kegiatan }}</td>
                        <td>  
                            <a href="<?php echo $dir.$data->tor?>" class="badge btn-success">Download pdf</a>
                            {{-- <a class="modal-effect btn btn-info d-grid mb-3" data-bs-effect="effect-newspaper" data-bs-toggle="modal" href="#modalAmdal{{$id}}">Pdf</a>
                            <div class="modal fade" id="modalAmdal{{$id}}"> <div class="modal-dialog modal-lg modal-dialog-centered text-center" role="document"><div class="modal-content modal-content-demo">
                                <div class="modal-header"><h6 class="modal-title">Dokumen Tor</h6><button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
                                    <div class="modal-body"><object width="600" height="500" data="<?php echo $dir.$data->tor?>" type="application/pdf"></object> </div><div class="modal-footer"> <button class="btn btn-light" data-bs-dismiss="modal">Close</button></div></div></div></div>--}}
                        </td> 
                        <td> {{ $data->rincian_komponen }}</td>
                        <td>
                            @if($data->JENIS_GDG != NULL)
                            {{ $data->JENIS_GDG }}
                            @elseif($data->JENIS_PER != NULL)
                            {{ $data->JENIS_PER }}
                            @elseif($data->JENIS_KEG != NULL)
                            {{ $data->JENIS_KEG }}
                            @endif
                        </td>
                        <td> 
                            @if($data->KEBUTUHAN_GDG != NULL)
                            {{ $data->KEBUTUHAN_GDG }}
                            @elseif($data->KEBUTUHAN_PER != NULL)
                            {{ $data->KEBUTUHAN_PER }}
                            @elseif($data->KEBUTUHAN_KEG != NULL)
                            {{ $data->KEBUTUHAN_KEG }}
                            @endif
                        </td>
                        <td> 
                            @if($data->TOTAL_GEDUNG != NULL)
                            {{ $data->TOTAL_GEDUNG }}
                            @elseif($data->TOTAL_PERALATAN != NULL)
                            {{ $data->TOTAL_PERALATAN}}
                            @elseif($data->TOTAL_KEGIATAN != NULL)
                            {{ $data->TOTAL_KEGIATAN }}
                            @endif
                        </td>
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
                        <td>
                           <select name="verifikasi_univ" onchange="handleUpdate(this)" style="width:200px" type="text" class="verifikasi_univ bg-dark my-2 text-white d-inline select2 w-auto required">
                                <option value="SILAHKAN PILIH" selected="selected">Pilih</option>
                                @if($data->verifikasi_univ == "Setuju")
                                    <option value="Setuju" selected="true">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @elseif($data->verifikasi_univ == "Tolak")
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak" selected="true">Tolak</option>
                                @else
                                    <option value="Setuju">Setuju</option>
                                    <option value="Tolak">Tolak</option>
                                @endif
                            </select>
                        </td>
                        <td class="tanggapan" contenteditable="true"> {{ $data->tanggapan }}</td>
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

@push('yss')
    @include('VERIFIKASI.REKAT.css')
@endpush

@push('scripts')
    @include('VERIFIKASI.REKAT.script')
@endpush
