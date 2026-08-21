@php
    $jenis = request()->get('jenis');
@endphp
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<title>Laporan | Tahunan</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan" style="font-size:13px; font-family: 'IBM Plex Sans'">
    <div class="col-lg-12">
        <div class="card">
            <input type="text" class="idunit" value="{{ e($idunit) }}" hidden>
            <h4 class="loading-msg px-2 py-3">Sedang memuat data ...</h4>
            @include('content.laporan.REKAT_UK.card24")
            <div class="card-body">
                <div class="table-responsive">
                    <!-- Sumberdana Table-->
                    @if ( $jenis === "sd" )
                    <table id="tabel-sd" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PROYEKSI PENERIMAAN ANGGARAN</th>
                                <th class="align-middle">REALISASI PENERIMAAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-sd">
                        </tbody>
                    </table>
                    <!-- KRO Table -->
                    @elseif ( $jenis === "ss" )
                    <table id="tabel-ss" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-ss"></tbody>
                    </table>
                    <!-- Rincian Output Table -->
                    @elseif ( $jenis === "ro" )
                    <table id="tabel-ro" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">TARGET KINERJA</th>
                                <th class="align-middle">CAPAIAN KINERJA</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-ro">
                        </tbody>
                    </table>
                    <!-- IKV -->
                    @elseif ( $jenis === "ikv" )
                    <table id="tabel-ikv" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-ikv">
                        </tbody>
                    </table>
                    <!-- KEG -->
                    @elseif ( $jenis === "keg" )
                    <table id="tabel-keg" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-keg">
                        </tbody>
                    </table>
                    <!-- RIWAYAT -->
                    @elseif ( $jenis === "riwayat" )
                    <table id="tabel-riwayat" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">REV 0</th>
                                <th class="align-middle revHeaderRiwayat">REV 1</th>
                                <th class="align-middle revHeaderRiwayat">REV 2</th>
                                <th class="align-middle revHeaderRiwayat">REV 3</th>
                                <th class="align-middle revHeaderRiwayat">REV 4</th>
                                <th class="align-middle revHeaderRiwayat">REV 5</th>
                                <th class="align-middle revHeaderRiwayat">REV 6</th>
                                <th class="align-middle revHeaderRiwayat">REV 7</th>
                                <th class="align-middle revHeaderRiwayat">REV 8</th>
                                <th class="align-middle revHeaderRiwayat">REV 9</th>
                                <th class="align-middle revHeaderRiwayat">REV 10</th>
                                <th class="align-middle revHeaderRiwayat">REV 11</th>
                                <th class="align-middle revHeaderRiwayat">REV 12</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-riwayat">
                        </tbody>
                    </table>
                    @elseif ( $jenis === "detail" )
                    <table id="tabel-detail" class="table mb-0" style="font-size:13px;">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle revHeaderDetail">REV 1</th>
                                <th class="align-middle revHeaderDetail">REV 2</th>
                                <th class="align-middle revHeaderDetail">REV 3</th>
                                <th class="align-middle revHeaderDetail">REV 4</th>
                                <th class="align-middle revHeaderDetail">REV 5</th>
                                <th class="align-middle revHeaderDetail">REV 6</th>
                                <th class="align-middle revHeaderDetail">REV 7</th>
                                <th class="align-middle revHeaderDetail">REV 8</th>
                                <th class="align-middle revHeaderDetail">REV 9</th>
                                <th class="align-middle revHeaderDetail">REV 10</th>
                                <th class="align-middle revHeaderDetail">REV 11</th>
                                <th class="align-middle revHeaderDetail">REV 12</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-detail">

                        </tbody>
                    </table>
                    <!-- COA Table-->
                    @elseif ( $jenis === "coa" )
                    <table id="tabel-coa" class="table mb-0 table-bordered" style="font-size:13px">
                        <thead class="header">
                            <tr>
                                <th class="align-middle">CODEBASE</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">PAGU ALOKASI</th>
                                <th class="align-middle">PAGU TERPAKAI</th>
                                <th class="align-middle">REALISASI ANGGARAN</th>
                                <th class="align-middle">SISA ANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-coa">

                        </tbody>
                    </table>
                    @endif
                </div>
                @if( !$pttd )
                *Data tanda tangan belum tersedia. Mohon untuk melengkapi data tersebut terlebih dahulu pada perjanjian kinerja.
                @endif
                <div class="d-flex justify-content-between px-3 mt-5 containerTtd" style="margin-inline: 130px;">
                    <div class="">
                        <span>{{ "Banda Aceh, $tahunAngka-$bulan-$tanggal" }}</span><br><br><br><br><br>
                        <span>{{ $pttd->PP_REKTOR ?? "Pimpinan universitas tidak ditemukan."}}</span><br>
                        <span>{{ $pttd->PP_NIP ?? "-"}}</span>
                    </div>
                    <div style="margin-right: 30px">
                        <span>{{ "Banda Aceh, $tahunAngka-$bulan-$tanggal" }}</span><br>
                        <span>{{ "Pimpinan KAI"}}</span><br><br><br><br>
                        <span>{{ "-"}}</span><br>
                        <span>{{ "-"}}</span>
                    </div>
                    <div style="margin-right: 30px">
                        <span>{{ "Banda Aceh,  $tahunAngka-$bulan-$tanggal" }}</span><br>
                        <span>{{ $pttd->PK_JBT ?? "-"}}</span><br><br><br><br>
                        <span>{{ $pttd->PK_NAMA ?? "Pimpinan unitkerja tidak ditemukan."}}</span><br>
                        <span>{{ $pttd->PK_NIP ?? "-"}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
@include('content.laporan.TAHUNAN.script")
@include("HELPERS.report_function")
<script>
$(document).ready(function () {
    const url = new URL(window.location.href);
    const idunit = url.searchParams.get("idunit");
    const kodeSd = url.searchParams.get("kodeSd");

    window.laporan = window.laporan || {};
    window.laporan.tahunan = window.laporan.tahunan || {};
    window.laporan.tahunan.methods = window.laporan.tahunan.methods || {};

    const jenis = url.searchParams.get("jenis")
    if ( jenis === "coa" )
        window.laporan.tahunan.methods.showCoaTab( idunit, kodeSd )
    if ( jenis === "ss" )
        window.laporan.tahunan.methods.showSsTab( idunit, kodeSd )
    if ( jenis === "sd" )
        window.laporan.tahunan.methods.showSdTab( idunit, kodeSd )
    if ( jenis === "ro" )
        window.laporan.tahunan.methods.showIkkTab( idunit, kodeSd )
    if ( jenis === "ikv" )
        window.laporan.tahunan.methods.showIkvTab( idunit, kodeSd )
    if ( jenis === "keg" )
        window.laporan.tahunan.methods.showKegTab( idunit, kodeSd )
    if ( jenis === "riwayat" )
        window.laporan.tahunan.methods.showRiwayatTab( idunit, kodeSd )
    if ( jenis === "detail" )
        window.laporan.tahunan.methods.showDetailTab( idunit, kodeSd )
})
</script>
