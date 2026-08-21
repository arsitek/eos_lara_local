@extends('layout.layout')
@section('title', 'laporan usul')
@section('content')
  <div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN USUL</h3>
            </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="tabel-usul table table-bordered border mb-0" id="new-edit">
                <thead>
                    <tr>
                        <th>id</th>
                         <th>Unit<span style="visibility:hidden;">_</span>kerja</th>
                        <th>Sasaran<span style="visibility: hidden;" id="under">_</span>Program</th>
                        <th>INDIKATOR<span style="visibility: hidden;" id="under">_</span>KINERJA<span style="visibility: hidden;" id="under">_</span>KEGIATAN</th>
                        <th>Rincian<span style="visibility: hidden;">_</span>Kegiatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($usul_ver as $data)
                @if ($data->verifikasi_tim != "Tolak" && $data->verifikasi_pimpinan != "Tolak")
                    <tr>
                       <td> {{ $data->id }} </td>
                       <td></td>
                       <td> {{ $data->sasaran_program }} </td>
                       <td> {{ $data->indikator_kinerja_kegiatan }} </td>
                       <td> {{ $data->rincian_kegiatan }} </td>
                        <td class="tanggapan" contenteditable="true" onchange="handleUpdate(this)"> {{ $data->tanggapan }}</td>
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
