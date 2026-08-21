@extends('layout.layout')
@section('title', 'Laporan | Program Kegiatan')
@section('content')
    @push('yss')
        <style>
            /* Make dropdowns responsive */
            .rkaUnit .ios-select-multiple {
                width: 100% !important;
                /* Full width on mobile */
                max-width: 400px;
                margin: 0 0 15px 0;
                /* Mulai dari kiri, bukan center dari style global. */
            }

            .rkaUnit button.cari {
                width: 100%;
                /* Button spans full width */
                margin-top: 10px;
            }

            .rkaUnit {
                display: flex;
                gap: 5px;
                align-items: stretch;
                flex-direction: column;
                /* Stack items vertically */
                flex-wrap: nowrap;
                width: 100%;
            }

            /* Tooltip iOS ringan untuk menjelaskan sumber hitung persentase. */
            .perkin-percentage-trigger {
                position: relative;
                display: inline-flex;
                justify-content: center;
                min-width: 48px;
                cursor: help;
                color: #0d6efd;
                font-weight: 600;
            }

            .perkin-percentage-tooltip {
                position: absolute;
                right: 50%;
                bottom: calc(100% + 10px);
                z-index: 1080;
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 220px;
                padding: 10px 12px;
                border-radius: 12px;
                background: rgba(255, 255, 255, .96);
                color: #1f2937;
                font-size: 12px;
                font-weight: 400;
                line-height: 1.35;
                text-align: left;
                box-shadow: 0 8px 24px rgba(15, 23, 42, .16);
                border: 1px solid rgba(226, 232, 240, .9);
                opacity: 0;
                visibility: hidden;
                transform: translate(50%, 6px);
                transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
                pointer-events: none;
                white-space: nowrap;
            }

            .perkin-percentage-trigger.tooltip-left .perkin-percentage-tooltip {
                right: 0;
                transform: translate(0, 6px);
            }

            .perkin-percentage-trigger:hover .perkin-percentage-tooltip,
            .perkin-percentage-trigger:focus .perkin-percentage-tooltip {
                opacity: 1;
                visibility: visible;
                transform: translate(50%, 0);
            }

            .perkin-percentage-trigger.tooltip-left:hover .perkin-percentage-tooltip,
            .perkin-percentage-trigger.tooltip-left:focus .perkin-percentage-tooltip {
                transform: translate(0, 0);
            }

        </style>
        @include('COMPONENTS.multipleSelectCss')
    @endpush
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="rkaUnit">
                <div class="ios-select-multiple" data-unitkerja-selection="single">
                    <div class="select-trigger">
                        <span class="selected-text">{{ session('unitkerja_nama') ?? 'Pilih Unitkerja' }}</span>
                        <span class="arrow"></span>
                    </div>
                    <div class="options-container unitkerja-container">
                        <div class="search-container">
                            <input type="text" class="search-input" placeholder="Ketik nama unitkerja..." />
                        </div>
                        <div class="no-results">Unitkerja tidak ditemukan.</div>
                        {{-- @if (in_array($role, ['superadmin', 'admin', 'Majelis Wali Amanat', 'Pengawasan Internal', 'Auditor']) || session()->get('id_user') == '196709261992031002')
                            <div class="option-group level-1">
                                <div class="group-header level-1 collapsed">
                                    <span>Semua Kategori</span>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="option unitkerjaOption level-1" single="true"
                                    data-text="Universitas Syiah Kuala" data-jenis="unitkerja" data-value="X">
                                    <span class="checkmark">✓</span>
                                    <span>Universitas Syiah Kuala</span>
                                </div>
                            </div>
                        @endif --}}
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-info-light mb-3" id="btn-cari">Cari</button>
                    <button type="button" class="btn btn-danger mb-3" id="btn-pdf">Tampilkan PDF</button>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Program Kegiatan</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabel-program-kegiatan" class="table table-bordered border mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px">Sasaran Program</th>
                                    <th>IKU-SEKJEN</th>
                                    <th>IKU-DIRJEN</th>
                                    <th>IKU/IKT USK</th>
                                    <th style="width: 300px">Indikator Kinerja Utama (USK)</th>
                                    <th>Satuan</th>
                                    <th>Baseline Project</th>
                                    <th>Target 2026</th>
                                    <th>Program Kegiatan</th>
                                    <th>Anggaran</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="fw-bold grand-total-row bg-light">
                                    <td>Total Anggaran Keseluruhan</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="jumlah-biaya-revisi total-anggaran-keseluruhan">-</td>
                                    <td class="persentase total-persentase-keseluruhan">-</td>
                                </tr>
                                <tr class="fw-bold parent-row" data-ikv="KU.01.01.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td>Angka Efisiensi Edukasi Perguruan Tinggi (AEE PT)</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr>
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">a. D1</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr>
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">b. D2</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.01.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">c. D3</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.01.02">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">d. D4 / S1</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.01.03">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">f. S2</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.01.04">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">g. S3</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr>
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">h. Profesi</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr>
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">k. Spesialis</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr>
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>IKU 1.1</td>
                                    <td>IKU 1.1</td>
                                    <td style="padding-left: 30px">l. Subspesialis</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="fw-bold parent-row" data-ikv="KU.01.02.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>-</td>
                                    <td>IKU 1.2</td>
                                    <td>Persentase Mahasiswa Pascasarjana terhadap total mahasiswa</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.02.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>-</td>
                                    <td>IKU 1.2</td>
                                    <td style="padding-left: 30px">a. Persentase mahasiswa magister terhadap total mahasiswa</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.02.02">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>-</td>
                                    <td>IKU 1.2</td>
                                    <td style="padding-left: 30px">b. Persentase mahasiswa Doktor terhadap total mahasiswa</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.01.03.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 1</td>
                                    <td>-</td>
                                    <td>IKU 1.3</td>
                                    <td>Persentase mahasiswa internasional</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.02.01.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 2</td>
                                    <td>IKU 1.2</td>
                                    <td>IKU 1.4</td>
                                    <td>Persentase lulusan pendidikan tinggi program diploma satu, diploma dua, diploma
                                        tiga, diploma empat/sarjana terapan, dan sarjana yang langsung bekerja,
                                        berwirausaha, atau melanjutkan studi dalam jangka waktu 1 (satu) tahun setelah
                                        kelulusan, serta sudah bekerja, atau berwirausaha sebelum lulus kuliah</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.01.03.01.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 3</td>
                                    <td>IKU 1.3</td>
                                    <td>IKU 1.5</td>
                                    <td>Persentase mahasiswa program Diploma dan Sarjana yang berkegiatan/meraih prestasi di luar program studi</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.04.01.02">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 4</td>
                                    <td>IKU 2.1</td>
                                    <td>IKU 1.6</td>
                                    <td>Persentase Dosen perguruan tinggi yang mendapatkan rekognisi internasional atau hasil penelitiannya diterapkan oleh masyarakat</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.04.01.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKU 4</td>
                                    <td>IKU 2.1</td>
                                    <td>IKU 1.7</td>
                                    <td>Persentase dosen berpendidikan S3</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KT.03.07.02.01">
                                    <td>S1 | Talenta</td>
                                    <td>IKT 1.1</td>
                                    <td>IKT 1.1</td>
                                    <td>IKT 1.1</td>
                                    <td>Persentase Prodi Terakreditasi Unggul</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.05.01.01">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 5</td>
                                    <td>IKU 2.2</td>
                                    <td>IKU 2.1</td>
                                    <td>Persentase luaran hasil kerja sama dan hilirisasi antara Perguruan Tinggi dengan start-up/industri/Lembaga</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 6</td>
                                    <td>IKU 2.3</td>
                                    <td>IKU 2.2</td>
                                    <td>Total Publikasi Bereputasi Internasional (Scopus/WoS)</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="parent-row fw-bold" data-ikv="KU.02.06.01">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 6</td>
                                    <td>-</td>
                                    <td>IKU 2.2</td>
                                    <td>Total Publikasi Internasional</td>
                                    <td>Artikel</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.06.01.01">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 6</td>
                                    <td>-</td>
                                    <td>IKU 2.2</td>
                                    <td style="padding-left: 30px">a. Persentase publikasi Top Tier</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.06.01.02">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 6</td>
                                    <td>-</td>
                                    <td>IKU 2.2</td>
                                    <td style="padding-left: 30px">b. Persentase publikasi Q1</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.02.06.02.01">
                                    <td> S2 | Inovasi</td>
                                    <td>IKU 6</td>
                                    <td>-</td>
                                    <td>IKU 2.3</td>
                                    <td>Persentase penelitian berkolaborasi internasional</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="parent-row fw-bold" data-ikv="">
                                    <td>S2 | Kontribusi/dedikasi pada masyarakat</td>
                                    <td>IKU 7</td>
                                    <td>IKU 3.1</td>
                                    <td>IKU 3.1</td>
                                    <td>Persentase keterlibatan perguruan tinggi dalam SDG 1 (tanpa kemiskinan), SDG 4 (pendidikan berkualitas), SDG 17 (kemitraan), dan 2 (dua) SDGs lain sesuai keunggulan</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.03.07.01.01">
                                    <td>S2 | Kontribusi/dedikasi pada masyarakat</td>
                                    <td>IKU 7</td>
                                    <td>IKU 3.1</td>
                                    <td>IKU 3.2</td>
                                    <td style="padding-left: 30px">a. Peringkat PT pada QS World University Ranking</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.03.07.01.02">
                                    <td>S2 | Kontribusi/dedikasi pada masyarakat</td>
                                    <td>IKU 7</td>
                                    <td>IKU 3.1</td>
                                    <td>IKU 3.3</td>
                                    <td style="padding-left: 30px">b. Peringkat PT pada THE Impact Ranking</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="fw-bold parent-row" data-ikv="KU.03.08.01">
                                    <td>S2 | Kontribusi/dedikasi pada masyarakat</td>
                                    <td>IKU 8</td>
                                    <td>IKU 3.2</td>
                                    <td>IKU 3.2</td>
                                    <td>Persentase Sumber Daya Manusia (SDM) perguruan tinggi yang terlibat langsung dalam penyusunan kebijakan (nasional/daerah/industri)</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.03.08.01.01">
                                    <td>S2 | Kontribusi/dedikasi pada masyarakat</td>
                                    <td>IKU 8</td>
                                    <td>IKU 3.2</td>
                                    <td>IKU 3.3</td>
                                    <td style="padding-left: 30px">a. Jumlah Pusat Unggulan yang terlibat dalam pengabdian kepada masyarakat</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="fw-bold parent-row" data-ikv="KU.04.09.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 9</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.2</td>
                                    <td>Persentase pendapatan/penghasilan dari bidang nonakademik (selain UKT/uang kuliah)</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.2</td>
                                    <td style="padding-left: 30px">a. Persentase pendapatan terhadap total aset</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.02">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.2</td>
                                    <td style="padding-left: 30px">b. Persentase Pendapatan industri terhadap total pendapatan</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.03">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.2</td>
                                    <td style="padding-left: 30px">c. Pendapatan Dana Abadi</td>
                                    <td>Rp</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="fw-bold parent-row" data-ikv="KU.04.09.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.3</td>
                                    <td>Alokasi pendapatan dana masyarakat untuk peningkatan:</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.04">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 15</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.3</td>
                                    <td style="padding-left: 30px">a. Riset</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.05">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 16</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.3</td>
                                    <td style="padding-left: 30px">b. Upskilling dan up-gradding dosen</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.06">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 17</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.3</td>
                                    <td style="padding-left: 30px">c. Update Laboratorium</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.12.01.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.1</td>
                                    <td>IKU 4.4</td>
                                    <td>Perencanaan strategis peningkatan kesejahteraan Dosen</td>
                                    <td>Dokumen</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.10.01.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 10</td>
                                    <td>IKU 4.2</td>
                                    <td>IKU 4.5</td>
                                    <td>Jumlah usulan Zona Integritas - WBK/WBBM</td>
                                    <td>Unit Kerja</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.11.01.01">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 19</td>
                                    <td>IKU 4.2</td>
                                    <td>IKU 4.5</td>
                                    <td>Hasil audit atas Laporan Keuangan perguruan tinggi</td>
                                    <td>Jumlah</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.11.01.02">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 19</td>
                                    <td>IKU 4.2</td>
                                    <td>IKU 4.5</td>
                                    <td>Predikat Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) Perguruan Tinggi</td>
                                    <td>Jumlah</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.11.01.03">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 19</td>
                                    <td>IKU 4.2</td>
                                    <td>IKU 4.5</td>
                                    <td>Jumlah laporan pelanggaran integritas akademik</td>
                                    <td>Jumlah</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.11.01.04">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKU 20</td>
                                    <td>IKU 4.2</td>
                                    <td>IKU 4.5</td>
                                    <td>Persentase Pencegahan dan penanganan anti kekerasan, anti narkoba, dan anti korupsi</td>
                                    <td>%</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.07">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKT-4.01</td>
                                    <td>IKT-4.01</td>
                                    <td>IKT-4.01</td>
                                    <td>Uang Kuliah Tunggal (UKT)</td>
                                    <td>Rp(Milyar)</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.08">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKT-4.02</td>
                                    <td>IKT-4.02</td>
                                    <td>IKT-4.02</td>
                                    <td>Iuran Pengembangan Institusi (IPI)</td>
                                    <td>Rp(Milyar)</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                                <tr class="ikv-row" data-ikv="KU.04.09.01.09">
                                    <td>S2 | Tata kelola berintegritas</td>
                                    <td>IKT-4.03</td>
                                    <td>IKT-4.03</td>
                                    <td>IKT-4.03</td>
                                    <td>Sumber Pendanaan lain yang bersumber dari BUMN dan/atau Swasta</td>
                                    <td>Rp(Milyar)</td>
                                    <td class="baseline-awal">-</td>
                                    <td class="target-akhir">-</td>
                                    <td class="subjudul">-</td>
                                    <td class="jumlah-biaya-revisi">-</td>
                                    <td class="persentase">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('COMPONENTS.multipleSelectScript')
    @include('content.laporan.PERKIN.script')
@endpush
