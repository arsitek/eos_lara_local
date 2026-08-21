@include('COMPONENTS.multipleSelectCss')
@include('LAPORAN.REKAT_UK.css')
<div class="row rekapSubkomponen">
    <div class="col-lg-12">
        <div class="card mt-5 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-3">Rekap Subkomponen</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="rekap-shared-filter mb-3 rounded-3">
                    <div class="rkaUnit">
                        <div class="ios-select-multiple">
                            <div class="select-trigger">
                                <span class="selected-text">{{ session('unitkerja_nama') ?? 'Pilih Unitkerja' }}</span>
                                <span class="arrow"></span>
                            </div>
                            <div class="options-container unitkerja-container">
                                <div class="search-container">
                                    <input type="text" class="search-input" placeholder="Ketik nama unitkerja..." />
                                </div>
                                <div class="no-results">Unitkerja tidak ditemukan.</div>

                                @if(in_array(session()->get('role'), ['superadmin', 'admin', 'Majelis Wali Amanat', 'Pimpinan USK']))
                                <div class="option-group level-1">
                                    <div class="group-header level-1 collapsed">
                                        <span>Semua Kategori</span>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                    <div class="option unitkerjaOption level-1" single="false"
                                        data-text="Universitas Syiah Kuala" data-jenis="unitkerja" data-value="X">
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
                                    <input type="text" class="search-input"
                                        placeholder="Ketik nama sumber dana..." />
                                </div>
                                <div class="no-results">Sumber dana tidak ditemukan.</div>
                                <div class="option-group level-1">
                                    <div class="group-header level-1 collapsed">
                                        <span>Semua Kategori</span>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                    @if (in_array(session('role'), ['superadmin', 'admin', 'Majelis Wali Amanat']))
                                        <div class="option sumberdanaOption level-1" single="false"
                                            data-text="Semua Sumber Dana" data-jenis="sumberdana" data-value="semua">
                                            <span class="checkmark">✓</span>
                                            <span>Semua sumber dana</span>
                                        </div>
                                        <div class="option sumberdanaOption level-1" single="false"
                                            data-text="Proyeksi Layanan Pendidikan Lainnya" data-jenis="sumberdana"
                                            data-value="41010301">
                                            <span class="checkmark">✓</span>
                                            <span>Proyeksi Layanan Pendidikan Lainnya</span>
                                        </div>
                                    @endif
                                    @if ($tahunAngka == '2024')
                                        @foreach ($sumberdana as $sd)
                                            @if ($sd->sumberdana)
                                                <div class="option sumberdanaOption level-1" single="false"
                                                    data-text="{{ $sd->sumberdana->sumberdana ?? '-' }}"
                                                    data-jenis="sumberdana" data-value="{{ $sd->sd }}">
                                                    <span class="checkmark">✓</span>
                                                    <span>{{ $sd->sumberdana->sumberdana ?? '-' }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-3" style="display: block; width: 100%">
                            <button type="button" class="btn btn-primary w-100" id="btnSubmitRekapSubkomponen">
                                <i class="bi bi-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Klik tombol tampilkan setelah memilih unit kerja dan sumber
                        dana.</span>
                    <button type="button" class="btn btn-danger" id="btnExportRekapSubkomponenPdf">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
                    </button>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-hover align-middle"
                        id="tabel-rekap-subkomponen" style="width: 100%; font-size: 13px">
                        <thead class="table-light position-sticky top-0" style="z-index: 1; font-size: 15px">
                            <tr>
                                <th class="text-center" colspan="2">SEMULA</th>
                                <th class="text-center" colspan="2">PERUBAHAN</th>
                                <th class="text-center align-middle" rowspan="2">SELISIH</th>
                                <th class="text-center align-middle" rowspan="2">(%)</th>
                            </tr>
                            <tr>
                                <th>SUBKOMPONEN</th>
                                <th>JUMLAH BIAYA</th>
                                <th>SUBKOMPONEN</th>
                                <th>JUMLAH BIAYA</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rekapSubkomponen .table tbody tr td {
        vertical-align: middle;
    }

    .rekapSubkomponen .table-hover tbody tr:hover {
        background-color: #f8f9fb;
    }

    .rekapSubkomponen .badge.border {
        border: 1px solid #e5e7eb;
    }

    /* soften striped colors */
    .rekapSubkomponen .table-striped>tbody>tr:nth-of-type(odd)>* {
        --bs-table-bg-type: #fbfcfe;
    }
</style>
