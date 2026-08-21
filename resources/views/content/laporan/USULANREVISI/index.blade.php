@extends('layouts/layoutMaster')
@section('title', 'Laporan | RKA')
@push('yss')
    @include('content.laporan.REKAT_UK.css')
    @include('COMPONENTS.multipleSelectCss')
    @include('content.laporan.USULANREVISI.css')
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
    $allowedRoles = ["superadmin", "admin", "Majelis Wali Amanat", "Pimpinan USK", "Pengawasan Internal"];
@endphp
<div class="row mt-5">
    @if( session()->has('error') )
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session()->get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

                    <!-- Level 1: Root Categories -->
                    <div class="option-group level-1">
                        <div class="group-header level-1 collapsed">
                            <span>Semua Kategori</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        @if ( in_array( $role, ["superadmin", "admin", "Majelis Wali Amanat"] ) )
                        <div class="option unitkerjaOption level-1" single="true" data-text="Universitas Syiah Kuala" data-jenis="unitkerja" data-value="X">
                            <span class="checkmark">✓</span>
                            <span>Universitas Syiah Kuala</span>
                        </div>
                        @endif
                    </div>
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
                        <div class="option sumberdanaOption level-1" single="false" data-text="Proyeksi Layanan Pendidikan Lainnya" data-jenis="sumberdana" data-value="41010301">
                            <span class="checkmark">✓</span>
                            <span>Proyeksi Layanan Pendidikan Lainnya</span>
                        </div>
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
            <button class="ios-button cari-larger-screen cari" id="cari-usulan-revisi">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.5rem" height="1.5rem" class="me-1 searchSVG">
                    <path d="M8.25 10.875a2.625 2.625 0 1 1 5.25 0 2.625 2.625 0 0 1-5.25 0Z" />
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.125 4.5a4.125 4.125 0 1 0 2.338 7.524l2.007 2.006a.75.75 0 1 0 1.06-1.06l-2.006-2.007a4.125 4.125 0 0 0-3.399-6.463Z" clip-rule="evenodd" />
                </svg>
                Submit
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
                <h3 class="card-title">LAPORAN USULAN REVISI</h3>
                <div>
                    <span class="me-3 fw-bold">Tampilkan data berdasarkan: </span>
                    <select class="form-select" id="filter-tampilan-usulan-revisi" style="width: 200px;">
                        <option value="">Pilih data</option>
                        <option value="postur">Postur Anggaran</option>
                        <option value="coa">Jenis Belanja</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
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
                </div>
                @include("COMPONENTS.loader")
                <div class="table-responsive tableContainer" id="wrap">
                    <h4 id="loading-msg" style="display: none;"></h4>
                    <table id="tabel-usulan-revisi" class="table-bordered table mb-0" style="border:2.5px solid black; font-size:13px;">
                        <thead class="header">
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">codebase</th>
                                <th class="text-light align-middle text-center">Pagu Existing</th>
                                <th class="text-light align-middle text-center">Pagu Usulan Revisi</th>
                                <th class="text-light align-middle text-center">Pergerakan Anggaran (%)</th>
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
    <script>
        /**
         * Export table ke PDF
         * Menggunakan method yang sama dengan REKAT_UK
         */
        $(document).on("click", ".btn-export-pdf", function(){
            let parentSd         = window.laporan.usulanRevisi.business.getHighestSumberdanaParent();
            const idunit         = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get();
            const sumberdana     = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get();
            const idBackup       = $(".riwayatOption.selected").map((_, el) => $(el).data("value") ).get();
            const isShowDetail   = window.laporan.usulanRevisi.cache.showDetailedSD;
            const filterTampilan = $("#filter-tampilan-usulan-revisi").val();

            // Validasi input
            if ( sumberdana.length === 0 ) {
                return tata.warn('Perhatian', 'Silahkan memilih sumber dana terlebih dahulu');
            }
            if ( idunit.length === 0 ){
                return tata.warn('Perhatian', 'Silahkan memilih unit kerja terlebih dahulu');
            }
            if ( idBackup.length === 0 ){
                return tata.warn('Perhatian', 'Silahkan memilih riwayat usulan revisi terlebih dahulu');
            }
            if ( !filterTampilan ){
                return tata.warn('Perhatian', 'Silahkan pilih filter tampilan terlebih dahulu');
            }
            
            // Open new tab and navigate to the URL
            const pdfTab = window.open('', '_blank');
            if ( isShowDetail === true )
                parentSd = '' 
            pdfTab.location.href = `/laporan/usulanrevisi/pdf/${idunit}/${sumberdana}?backup=${idBackup}&filter=${filterTampilan}&parentSd=${parentSd}`;
        });
        
        /**
         * Export table ke Excel
         * Menggunakan library XLSX (SheetJS)
         */
        function ExportToExcel(type, fn, dl) {
            const bodyTable = $("#tabel-usulan-revisi tbody");
            const unitkerja = $(".unitkerjaOption.selected").data("text") || "Unit-Kerja";
            const buttonDom = $("#btn_exportXlsx").html();

            // Validasi: Cek apakah unit kerja sudah dipilih
            if ( $(".unitkerjaOption.selected").length === 0 ) {
                return tata.warn("Perhatian", "Silahkan memilih unit kerja");
            }
            
            // Validasi: Cek apakah sumber dana sudah dipilih
            if ( $(".sumberdanaOption.selected").length === 0 ) {
                return tata.warn("Perhatian", "Silahkan memilih sumber dana");
            }

            // Tampilkan loading indicator
            $("#btn_exportXlsx").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengunduh file...`);
            
            // Tunggu sebentar untuk UI update, lalu export
            setTimeout(() => {
                try {
                    // Ambil tabel dan convert ke workbook
                    const tabel = document.getElementById('tabel-usulan-revisi');
                    const wb = XLSX.utils.table_to_book(tabel, { sheet: "Usulan Revisi" });
                    
                    // Kembalikan button ke kondisi semula
                    $("#btn_exportXlsx").html(buttonDom);
                    
                    // Download file
                    return dl ?
                        XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
                        XLSX.writeFile(wb, fn || (`Laporan-Usulan-Revisi-${unitkerja}.` + (type || 'xlsx')));
                        
                } catch (e) {
                    // Jika terjadi error, kembalikan button dan tampilkan error
                    $("#btn_exportXlsx").html(buttonDom);
                    console.error("Error saat export:", e);
                    return tata.error("Error", "Terjadi kesalahan saat export data");
                }
            }, 1000);
        }
    </script>
    @include("COMPONENTS.scriptLoader")
    @include("COMPONENTS.multipleSelectScript")
    @include('content.laporan.USULANREVISI.script")
    @include('content.laporan.REKAT_UK.script")
@endpush
