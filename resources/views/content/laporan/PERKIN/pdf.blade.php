<html>
    <head>
        <title>Laporan | Program Kegiatan</title>
        <link href="http://fonts.cdnfonts.com/css/times-new-roman" rel="stylesheet">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
        @include('content.laporan.PERKIN.css")
        <style>
            body {
                margin: 0;
                color: #111827;
                background: #fff;
                font-family: Arial, Helvetica, sans-serif;
            }

            .perkin-pdf-container {
                margin: 36px;
            }

            .perkin-report-header {
                margin-bottom: 18px;
            }

            .perkin-report-header table {
                width: 100%;
                margin: 0;
                border: 2px solid #111;
                border-collapse: collapse;
            }

            .perkin-report-header td {
                padding: 7px 10px;
                border: 1px solid #111;
                background: #fff;
                color: #111;
                font-size: 12px;
                line-height: 1.35;
                vertical-align: middle;
            }

            .perkin-report-header td:first-child {
                width: 31%;
                font-weight: 700;
            }

            .perkin-report-header__title td {
                background: #e5e7eb;
                font-size: 15px;
                font-weight: 800;
                text-align: center;
            }

            .perkin-report-header__total td {
                background: #f3f4f6;
                font-weight: 800;
            }

            /* Halaman PDF hanya menampilkan nilai persentase tanpa tooltip interaktif. */
            .perkin-percentage-tooltip {
                display: none;
            }

            #tabel-program-kegiatan {
                width: 100%;
                border: 2px solid #000 !important;
                border-collapse: collapse;
            }

            #tabel-program-kegiatan > :not(caption) > * > * {
                border: 1px solid #000 !important;
            }

            @page {
                size: A3 landscape;
                margin: 10mm;
            }

            @media print {
                .perkin-pdf-container {
                    margin: 0;
                }
            }
        </style>
    </head>
    <body data-perkin-auto-load="true" data-idunit="{{ $idunit }}">
        <main class="perkin-pdf-container">
            <header class="perkin-report-header">
                <table aria-label="Identitas laporan PERKIN">
                    <tbody>
                        <tr class="perkin-report-header__title">
                            <td colspan="2">PROGRAM KEGIATAN T.A. {{ $tahunAngka }}</td>
                        </tr>
                        <tr>
                            <td>KEMENTERIAN/LEMBAGA</td>
                            <td>(023) KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</td>
                        </tr>
                        <tr>
                            <td>UNIT ORGANISASI</td>
                            <td>(17) DIREKTORAT JENDERAL PENDIDIKAN TINGGI</td>
                        </tr>
                        <tr>
                            <td>PTN/KOPERTIS</td>
                            <td>(690662) UNIVERSITAS SYIAH KUALA</td>
                        </tr>
                        <tr>
                            <td>UNIT KERJA</td>
                            <td>{{ $namaUnit }}</td>
                        </tr>
                        <tr class="perkin-report-header__total">
                            <td>TOTAL ANGGARAN</td>
                            <td class="perkin-header-total-anggaran">-</td>
                        </tr>
                    </tbody>
                </table>
            </header>

            <div class="table-responsive">
                        <table id="tabel-program-kegiatan" class="table mb-0" style="width: 100%; border: 2px solid black;">
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
        </main>
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.rowspanizer.min.js') }}"></script>
        @include('content.laporan.PERKIN.script')
    </body>
</html>
