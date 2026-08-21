@extends('layouts/layoutMaster')
@section('title', 'Laporan | RKA')
@push('yss')
    @include('content.laporan.REKAT_UK.css')
    @include('COMPONENTS.multipleSelectCss')
    @include("COMPONENTS.rightClickCss")
@endpush
@section('content')
@foreach( $sumberdana as $sd )
<div class="sumberdanaHeader" key="{{$sd->kd_sumberdana}}" style="width: 400px" hidden>{{$sd->sumberdana}}</div>
@endforeach
@php
    $role      = session("role");
    $nama_unit = session("unitkerja_nama") ?? "-";

    $sumberdanaList  = [];
    foreach ($sumberdana as $value) {
        if ( $value->sumberdana ) {
            if ( $value->sumberdana->jenis == "bptnbh" ) {
                $sumberdanaList[] = [ "kodeSd" => $value->sumberdana->kd_sumberdana, "namaSd" => $value->sumberdana->sumberdana, "jenis" => "APBN" ];
            } else {
                $sumberdanaList[] = [ "kodeSd" => $value->sumberdana->kd_sumberdana, "namaSd" => $value->sumberdana->sumberdana, "jenis" => "Non APBN" ];
            }
        }
    }
    $allowedRoles = ["superadmin", "admin", "Majelis Wali Amanat", "Pimpinan USK", "Pengawasan Internal", "Auditor", "Direktur Keuangan", "Analis Resiko"];
@endphp
<div class="row mt-5">
    @if( session()->has('error') )
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session()->get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="import-notice" id="tor-import-container" style="max-width: 100%; overflow: auto;">
        <button class="badge btn-primary" style="float: right; margin-left: 10px;" id="close-tor-notice">Tutup</button>
        <p class="pt-2">
            Untuk melihat dokumen TOR/KAK yang sudah diunggah, silakan lakukan klik kanan pada baris judul kegiatan (yang memuat ID rekat) kemudian pilih opsi <b>Lihat TOR</b> pada menu yang muncul.
        </p>
    </div>
    <div class="col-lg-12 mb-3 d-flex justify-content-between flex-column flex-lg-row rkaUnitHeader">
        <div class="rkaUnit">
            <div class="ios-select-multiple">
                <div class="select-trigger">
                  <span class="selected-text">{{ session("unitkerja_nama") ?? 'Pilih Unitkerja'}}</span>
                  <span class="arrow"></span>
                </div>
                <div class="options-container unitkerja-container">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Ketik nama unitkerja..." />
                    </div>
                    <div class="no-results">Unitkerja tidak ditemukan.</div>
                    @if(in_array($role, ["superadmin", "admin", "Majelis Wali Amanat", "Pengawasan Internal", "Auditor", "Analis Resiko"]) || session()->get('id_user') == "196709261992031002")
                    <div class="option-group level-1">
                        <div class="group-header level-1 collapsed">
                            <span>Semua Kategori</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="option unitkerjaOption level-1" single="false" data-text="Universitas Syiah Kuala" data-jenis="unitkerja" data-value="X">
                            <span class="checkmark">✓</span>
                            <span>Universitas Syiah Kuala</span>
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
                        @if ( in_array( $role, ["superadmin", "admin", "Majelis Wali Amanat"] ) )
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
            <div class="filterBulan">
                <div class="ios-select-multiple selectRiwayat">
                    <div class="select-trigger">
                        <span class="selected-text">Pilih Riwayat</span>
                        <span class="arrow"></span>
                    </div>
                    <div class="options-container">
                        <div class="search-container">
                            <input type="text" class="search-input" placeholder="Ketik riwayat..." />
                        </div>
                        <div class="no-results">Riwayat tidak ditemukan.</div>
                        <div class="option-group">
                            @if( count($dataBackup) != 0 )
                            <div class="group-header level-1 collapsed">
                                <span>Riwayat</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="group-description text-center text-danger fs-6 clear-option" role="button">Clear</div>
                            @endif
                            @foreach ($dataBackup as $backup)
                                <div class="option riwayatOption" single="true" data-text="{{$backup->keterangan}}" data-jenis="riwayat" data-value="{{$backup->id}}" data-group="Riwayat">
                                    <span class="checkmark">✓</span>
                                    <span>{{$backup->keterangan}}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- Primary Button -->
            <button class="ios-button cari cari-larger-screen">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.5rem" height="1.5rem" class="me-1 searchSVG">
                    <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
                </svg>
                Submit
            </button>
            <button class="ios-button btn-clear-dropdowns" style="margin-top: 10px;">
                Clear Option
            </button>
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
    <div class="col-lg-12 rkaUnit">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">LAPORAN RKT SAKTI UNIT KERJA</h3>
                {{-- filter --}}
                <select class="s filter-data" style="width: 170px">
                    <option value="">Filter Data</option>
                    <option value="!verifikasi">Semua Data</option>
                    <option value="realisasi">Sudah Realisasi</option>
                    <option value="!realisasi">Belum Realisasi</option>
                    <option value="draft">Draft</option>
                    @if( in_array( $role, ["superadmin", "admin"] ))
                    <option value="final">Data Final</option>
                    @endif
                </select>
            </div>
            <div class="context-menu">
                <div class="context-menu-section">
                    <div class="context-menu-item" data-action="tor">
                        <div class="menu-icon">📜</div>
                        Lihat TOR
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div>
                    <h5 class="bg-success px-3 py-1 mb-2 text-white" id="successFilter" style="border-radius: 10%; display: none">Berhasil Menerapkan filter</h5>
                </div>
                <div class="mt-3">
                    <button onclick="ExportToExcel('xlsx')" id="btn_exportXlsx" class="btn btn-success px-2 py-1 mb-2" style="width: 150px">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="15%" class="me-1 searchSVG">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M9.5 12.5l5 5M14.5 12.5l-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save to Excel
                    </button>
                    <button class="btn-export-pdf btn btn-primary px-2 py-1 mb-2" style="width: 150px">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="15%" class="me-1 searchSVG">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save to PDF
                    </button>
                    @if( in_array( $role, ["superadmin", "admin"] ) )
                    <button class="btn-custom-export btn btn-primary px-2 py-1 mb-2" style="width: 170px">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="15%" class="me-1 searchSVG">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                        </svg>
                        Custom Export
                    </button>
                    @endif
                </div>
                @include("COMPONENTS.loader")
                <div class="table-responsive tableContainer" id="wrap">
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabel-rekat-unit" class="tabel-rekat table mb-0" style="border:2.5px solid black; font-size:13px;">
                        <thead class="header">
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">codebase</th>
                                <th class="text-light align-middle text-center">SD/KRO/RO/KP/SK/PIC/DK/COA/SBM</th>
                                <th class="text-light align-middle text-center">spesifikasi</th>
                                <th class="text-light align-middle text-center">total biaya</th>
                                <th class="text-light align-middle text-center">rpd</th>
                                <th class="text-light align-middle text-center">proses</th>
                                <th class="text-light align-middle text-center">real</th>
                                <th class="text-light align-middle text-center">rev</th>
                                <th class="text-light align-middle text-center">sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit"></tbody>
                    </table>
                </div>
                @include('content.laporan.REKAT_UK.modalinfo")
                @include('content.laporan.REKAT_UK.modalppk")
                @include('content.laporan.REKAT_UK.modalStatus")
                @include('content.laporan.REKAT_UK.modalPpkNull")
                @include('content.laporan.REKAT_UK.modalCustomExport")
                @include("VERIFIKASI.COMPONENTS.modalTor")
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        const $select = $(".ios-select-multiple")
        // ubah href setiap kali event dropdown onchange ke trigger
        $(document).on("click", ".btn-export-pdf", function(){
            const idunit     = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get().filter(v => v !== "X")
            const sumberdana = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
            const idRekats   = $("#selectIdRekat").val() ? $("#selectIdRekat").val().join(",") : ""
            let filter       = $("select.filter-data").val()
            let backup       = $(".riwayatOption.selected").map((_, el) => $(el).data("value") ).get()

            if ( sumberdana.length === 0 ) {
                return tata.warn('Perhatian', 'Silahkan memilih sumber dana terlebih dahulu')
            }
            if ( idunit.length === 0 ){
                return tata.warn('Perhatian', 'Silahkan memilih unit kerja terlebih dahulu')
            }
            // Open new tab and navigate to the URL
            const rkaTab = window.open('', '_blank')
            rkaTab.location.href = `/laporan/rktunit/pdf/${idunit}/${sumberdana}?filterdata=${filter}&backup=${backup}&idrekats=${idRekats}`
        })
        function ExportToExcel(type, fn, dl) {
            const bodyTable = document.getElementsByClassName("body-tbl-unit")
            const unitkerjaList = $select.find(".unitkerjaOption.selected").map((_, el) => $(el).data("text")).get()
            const unitkerja = unitkerjaList.length === 1 ? unitkerjaList[0] : (unitkerjaList.length > 1 ? `${unitkerjaList.length} Unit` : 'Unitkerja')
            const buttonDom = $("#btn_exportXlsx").html()

            // Cek data
            if ( $("select.unit_kerja").val() == "" )
                return tata.warn("Perhatian", "Silahkan memilih unit kerja")
            if ( $("select.sumberdana").val() == "" )
                return tata.warn("Perhatian", "Silahkan memilih sumber dana")
            if (bodyTable[0].rows.length === 0)
                return tata.warn("Perhatian", "Tidak terdapat data")

            $("#btn_exportXlsx").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengunduh file...`)
            setTimeout(() => {
                try {
                const tabel = document.getElementById('tabel-rekat-unit')
                const rows  = tabel.rows
                const wb = XLSX.utils.table_to_book(tabel, { sheet: "sheet1" })
                $("#btn_exportXlsx").html(buttonDom)
                return dl ?
                    XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
                    XLSX.writeFile(wb, fn || (`RKA-${unitkerja}.` + (type || 'xlsx')))
            } catch (e) {
                $("#btn_exportXlsx").html(buttonDom)
                return tata.error("Error", "Terjadi kesalahan saat export data")
            }
            }, 1000)
        }

        $(document).ready(function() {
            const STORAGE_KEY   = 'torNoticeHidden'
            const SHOW_INTERVAL = 60 * 60 * 1000 // 1 hour in milliseconds

            const hiddenData = localStorage.getItem(STORAGE_KEY)
            if ( hiddenData ) {
                const { timestamp } = JSON.parse(hiddenData)
                const currentTime   = new Date().getTime()
                const timeDiff      = currentTime - timestamp

                if (timeDiff < SHOW_INTERVAL) {
                    $('#tor-import-container').hide()
                } else {
                    localStorage.removeItem(STORAGE_KEY)
                }
            }

            $('#close-tor-notice').on('click', function() {
                $(this).parent('.import-notice').hide()
                const timestamp = new Date().getTime()
                localStorage.setItem(STORAGE_KEY, JSON.stringify({ timestamp: timestamp }))
            })
        })
    </script>
    @include('HELPERS.report_function')
    @include('content.laporan.REKAT_UK.script_final')
    @include('content.laporan.REKAT_UK.script_filter')
    @include('content.laporan.REKAT_UK.script")
    @include("COMPONENTS.scriptLoader")
    @include("COMPONENTS.multipleSelectScript")
    @include('COMPONENTS.rightClickScript')
@endpush
