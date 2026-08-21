@extends('layout.layout')
@section('title', 'Verifikasi | Belum Terverifikasi')
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Data Revisi / Validasi belum terverifikasi</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                    <div class="btn-group" role="group" aria-label="Jenis Revisi" style=" display: inline-flex; background: rgba(0, 0, 0, 0.05); border-radius: 10px; padding: 3px; gap: 4px;">
                        <button type="button" class="btn btn-revision-filter" data-jenis="SS" style="
                            border: none;
                            background: transparent;
                            color: #3C3C43;
                            font-weight: 400;
                            padding: 8px 16px;
                            border-radius: 7px;
                            transition: all 0.2s ease;
                            font-size: 14px;
                        ">
                            <i class="fas fa-exchange-alt" style="margin-right: 6px;"></i> SS
                            <span style="color: #8E8E93; font-size: 13px; margin-left: 4px;">(Sasaran)</span>
                        </button>
                        <button type="button" class="btn btn-revision-filter" data-jenis="RO" style="
                            border: none;
                            background: transparent;
                            color: #3C3C43;
                            font-weight: 400;
                            padding: 8px 16px;
                            border-radius: 7px;
                            transition: all 0.2s ease;
                            font-size: 14px;
                        ">
                            <i class="fas fa-sync-alt" style="margin-right: 6px;"></i> RO
                            <span style="color: #8E8E93; font-size: 13px; margin-left: 4px;">(Rincian Output)</span>
                        </button>
                        <button type="button" class="btn btn-revision-filter" data-jenis="OUTPUT" style="
                            border: none;
                            background: transparent;
                            color: #3C3C43;
                            font-weight: 400;
                            padding: 8px 16px;
                            border-radius: 7px;
                            transition: all 0.2s ease;
                            font-size: 14px;
                        ">
                            <i class="fas fa-box" style="margin-right: 6px;"></i> Validasi
                        </button>
                        <button type="button" class="btn btn-revision-filter" data-jenis="BREAKDOWN" style="
                            border: none;
                            background: transparent;
                            color: #3C3C43;
                            font-weight: 400;
                            padding: 8px 16px;
                            border-radius: 7px;
                            transition: all 0.2s ease;
                            font-size: 14px;
                        ">
                        Breakdown
                        </button>
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="belumVerifikasiTable" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Sumber Dana</th>
                                    <th>Unit Kerja</th>
                                    <th>Jenis</th>
                                    <th>COA</th>
                                    <th>Item COA</th>
                                    <th>Spesifikasi</th>
                                    <th>Jumlah Biaya</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('VERIFIKASI.BELUM_VERIFIKASI.script')
@endpush
