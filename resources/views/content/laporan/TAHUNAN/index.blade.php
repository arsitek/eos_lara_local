@extends('layouts/layoutMaster')
@section('title', 'Rekap | Tahunan')
@push('yss')
    @include('content.laporan.TAHUNAN.style')
    @include('COMPONENTS.multipleSelectCss')
@endpush
@push('yss')
    <style>
        .dt-buttons.btn-group {
            position: relative;
            left: 0%;
            margin: 10px;
        }
    </style>
@endpush
@section('content')
<div class="row mt-5">
    <div class="col-lg-12 mb-3 d-flex justify-content-between flex-column flex-lg-row rkaUnitHeader">
        <div class="rkaUnit">
            <div class="ios-select-multiple">
                <div class="select-trigger">
                  <span class="selected-text selected-text-unit">Pilih Unitkerja</span>
                  <span class="arrow"></span>
                </div>
                <div class="options-container unitkerja-container">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Ketik nama unitkerja..." />
                    </div>
                    <div class="no-results">Unitkerja tidak ditemukan.</div>
                    @if($role == "superadmin" || session()->get("id_user") == "196709261992031002" )
                    <div class="option-group level-1">
                        <div class="group-header level-1 collapsed">
                            <span>Semua Kategori</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="option unitkerjaOption level-1" single="false" data-text="Universitas Syiah Kuala" data-jenis="unitkerja" data-value="X">
                            <span class="checkmark">✓</span>
                            <span>Universitas Syiah Kuala</span>
                        </div>
                        <div class="option unitkerjaOption level-1" single="false" data-text="Semua Unit Kerja" data-jenis="unitkerja" data-value="semua">
                            <span class="checkmark">✓</span>
                            <span>Semua Unit Kerja</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="ios-select-multiple">
                <div class="select-trigger">
                  <span class="selected-text selected-text-sumberdana">Pilih Sumber dana</span>
                  <span class="arrow"></span>
                </div>
                <div class="options-container sumberdana-container">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Ketik nama sumber dana..." />
                    </div>
                    <div class="no-results">Sumber dana tidak ditemukan.</div>

                    <!-- Level 1: Root Categories -->
                    <div class="option-group level-1">
                        <div class="group-header level-1 collapsed">
                            <span>Semua Kategori</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        @if ( in_array( $role, ["superadmin", "admin", "Majelis Wali Amanat", "Pimpinan USK"] ) )
                        <div class="option sumberdanaOption level-1" single="false" data-text="Semua Sumber Dana" data-jenis="sumberdana" data-value="semua">
                            <span class="checkmark">✓</span>
                            <span>Semua sumber dana</span>
                        </div>
                        <div class="option sumberdanaOption level-1" single="false" data-text="Proyeksi Layanan Pendidikan Lainnya" data-jenis="sumberdana" data-value="41010301">
                            <span class="checkmark">✓</span>
                            <span>Proyeksi Layanan Pendidikan Lainnya</span>
                        </div>
                        @endif
                        @if( $tahunAngka == "2024" )
                        @foreach ($sumberdana as $sd)
                        @if( $sd->sumberdana )
                        <div class="option sumberdanaOption level-1" single="false" data-text="{{ $sd->sumberdana->sumberdana ?? '-' }}" data-jenis="sumberdana" data-value="{{ $sd->sd }}">
                            <span class="checkmark">✓</span>
                            <span>{{ $sd->sumberdana->sumberdana ?? '-' }}</span>
                        </div>
                        @endif
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Primary Button -->
        <button class="ios-button cari cari-smaller-screen" style="display: none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="7%" class="me-1 searchSVG">
                <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
            </svg>
            Submit
        </button>
    </div>
    <div class="col-lg-12">
        <!--Tabs-->
        <div class="tabs mx-3 mb-2">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link item-tab" id="SD-tab" data-toggle="tab" role="tab" aria-controls="SD">Sumberdana</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link item-tab" id="SS-tab" data-toggle="tab" role="tab" aria-controls="SS">Klasifikasi Rincian Output</a>
                </li>
                <li class="nav-item ro-item">
                    <a class="nav-link item-tab" id="RO-tab" data-toggle="tab" role="tab" aria-controls="OK">Rincian Output</a>
                </li>
                <li class="nav-item ikv-item">
                    <a class="nav-link item-tab" id="IKV-tab" data-toggle="tab" role="tab" aria-controls="IKV">Komponen</a>
                </li>
                <li class="nav-item ikv-item">
                    <a class="nav-link item-tab" id="KEG-tab" data-toggle="tab" role="tab" aria-controls="KEG">Subkomponen</a>
                </li>
                <li class="nav-item riwayat-item">
                    <a class="nav-link item-tab" id="RIWAYAT-tab" data-toggle="tab" role="tab" aria-controls="RIWAYAT">Kegiatan</a>
                </li>
                <li class="nav-item riwayat-item">
                    <a class="nav-link item-tab" id="COA-tab" data-toggle="tab" role="tab" aria-controls="COA">Jenis Belanja</a>
                </li>
                <li class="nav-item riwayat-item">
                    <a class="nav-link item-tab" id="DETAIL-tab" data-toggle="tab" role="tab" aria-controls="DETAIL">Detail Kegiatan</a>
                </li>
            </ul>
        </div>
        @include('content.laporan.TAHUNAN.sd")
        @include('content.laporan.TAHUNAN.kro")
        @include('content.laporan.TAHUNAN.ro")
        @include('content.laporan.TAHUNAN.ikv")
        @include('content.laporan.TAHUNAN.keg")
        @include('content.laporan.TAHUNAN.riwayat")
        @include('content.laporan.TAHUNAN.coa")
        @include('content.laporan.TAHUNAN.detail")
    </div>
</div>
@endsection
@push('scripts')
@include('content.laporan.TAHUNAN.script')
@include('COMPONENTS.scriptLoader')
@include('HELPERS.report_function')
@include("HELPERS.export")
@include('COMPONENTS.multipleSelectScript')
@endpush
