@extends('layout.layout')
@section('title', 'laporan rab gedung')
@section('content')
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">LAPORAN RAB GEDUNG</h3>
            </div>
            <div class="card-body">
            <div style="margin-bottom: 10px;">
                <a role="button" id="exportPDF" href="{{ route('gdgReport.pdf')}}" class="btn btn-secondary bg-info-gradient btn-pill"><i class="fa fa-print"></i> PRINT TO PDF</a>
                <a role="button" id="exportEXCEL" href="{{ route('gdgReport.excel')}}" class="btn btn-secondary bg-success-gradient btn-pill"><i class="bi bi-table"></i> SAVE TO EXCEL</a>
            </div>
                <div class="table-responsive">
                    <table class="tabel-rabgdg table table-bordered border mb-0">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Unit Kerja</th>
                                <th>Rincian Kegiatan</th>
                                <th>Rincian Komponen</th>
                                <th>Jenis Belanja</th>
                                <th>Kebutuhan Kegiatan </th>
                                <th>Alamat lokasi Gedung</th>
                                <th>Latitude/ Longitude</th>
                                <th>Luas Bangunan Gedung (dalam meter persegi)</th>
                                <th>Jumlah Gedung</th>
                                <th>Jumlah Lantai</th>
                                <th>Ruang Kuliah (dalam ruang)</th>
                                <th>Ruang Laboratorium/Workshop/Bengkel (dalam ruang)</th>
                                <th>Ruang Kantor/Management/Penunjang (dalam ruang)</th>
                                <th>lainnya (dalam ruang)</th>
                                <th>Kesesuaian Gedung dengan Master Plan Kawasan</th>
                                <th>Sertifikat</th>
                                <th>SIMAK BMN (url)</th>
                                <th>dokumen analisis kementerian PUPR (url)</th>
                                <th>dokumen IMB  (url)</th>
                                <th>Dokumen AMDAL/Ijin Lingkungan (url)</th>
                                <th>Dokumen RKS  (url)</th>
                                <th>DED AWAL</th>
                                <th>DED Review Terakhir</th>
                                <th>Nilai Perencanaan (Rp.)</th>
                                <th>Nilai Struktur (Rp.)</th>
                                <th>Nilai ME (Rp.)</th>
                                <th>Nilai Lanscape (Rp.)</th>
                                <th>Nilai Pengawasan (Rp.)</th>
                                <th>Proposal Project/KAK</th>
                                <th>RAB Detail (excel)</th>
                                <th>Perencanaan Gambar (pdf)</th>
                                <th>Jumlah Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rabgdg as $data)
                        <tr>
                            <td> {{ $data->id }}</td>
                            <td> {{ $data->unit->unitkerja}}</td>
                            <td> {{ $data->rincian_kegiatan }}</td>
                            <td> {{ $data->rincian_komponen }}</td>
                            <td> {{ $data->jenis_belanja }}</td>
                            <td> {{ $data->kebutuhan_kegiatan  }}</td>
                            <td> {{ $data->alamat}}</td>
                            <td> {{ $data->latlong }}</td>
                            <td> {{ $data->luas_bangunan }}</td>
                            <td> {{ $data->jumlah_gedung }}</td>
                            <td> {{ $data->jumlah_lantai }}</td>
                            <td> {{ $data->ruang_kuliah }}</td>
                            <td> {{ $data->ruang_lab }}</td>
                            <td> {{ $data->ruang_kantor }}</td>
                            <td> {{ $data->lainnya }}</td>
                            <td> {{ $data->kesesuaian_gedung }}</td>
                                {{-- ====> sertifikat --}}
                            <td> 
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/Sertifikat/'.$data->sertifikat)}}" >Download</a>
                            </td>
                            {{-- ====> simak bmn --}}
                            <td>
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/SIMAK_BMN/'.$data->simak_BMN) }}" >Download</a>
                            </td>
                                {{-- ====> pupr --}}
                            <td>
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/PUPR/'.$data->PUPR)}}">Download</a>
                            </td>
                                {{-- ====> imb --}}
                            <td> 
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/DOKUMEN_IMB/'.$data->dokumen_IMB) }}">Download</a>
                            </td>
                            {{-- ====> amdal --}}
                            <td>  
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/AMDAL/'.$data->dokumen_AMDAL)}}" >Download</a>
                            </td>
                            {{-- ====> rks --}}
                            <td>
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/RKS/'.$data->dokumen_RKS)}}">Download</a>
                            </td>
                            <td> {{ $data->DED_AWAL }}</td>
                            <td> {{ $data->DED_REVIEW}}</td>
                            <td> {{ $data->nilai_perencanaan}}</td>
                            <td> {{ $data->nilai_struktur }}</td>
                            <td> {{ $data->nilai_me  }}</td>
                            <td> {{ $data->nilai_landscape  }}</td>
                            <td> {{ $data->nilai_pengawasan  }}</td>
                            {{-- ====> proposal project --}}
                            <td> 
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/PROPOSAL_PROJECT/'.$data->proposal_project)}}">Download</a>
                            </td>
                            <td> 
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/RAB_DETAIL/'.$data->rab_detail)}}">Download</a>
                            </td>
                            <td> 
                                <a class="badge bg-success" href="{{ asset('uploads/Rab_Gedung/PERENCANAAN_GAMBAR/'.$data->perencanaan_gambar) }}">Download</a>
                            </td>
                            <td> {{ $data->jumlah_nilai }}</td>
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
@include('content.laporan.GEDUNG.script')
@endpush
