@extends('layouts/layoutMaster')

@section('title', 'Statistik | RKT Unit')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notiflix/notiflix.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/statistik-rktunit.js', 'resources/assets/js/cards-actions.js'])
@endsection

@section('content')
  <!-- Pass data to JavaScript -->
  <script>
    window.dataRktUnit = @json($dataRktDetailArray);
    window.realizationRate = {{ $totalSemua['avg_persentase'] ?? 0 }};
  </script>

  <!-- Hero Performance -->
  <div class="card mb-6">
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-12">
          <h5 class="mb-2">Realisasi Anggaran</h5>
        </div>
      </div>

      <div class="row align-items-center">
        <!-- Radial Bar Chart -->
        <div class="col-md-5">
          <div id="realizationRadialChart"></div>
        </div>

        <!-- Supporting Metrics -->
        <div class="col-md-7">
          <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-center gap-4">
              <div class="avatar avatar-lg">
                <div class="avatar-initial bg-label-primary rounded">
                  <div class="text-primary">
                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="Wallet">
                        <path id="Vector" opacity="0.2" d="M5.9375 11.875V26.125H32.0625V11.875H5.9375Z"
                          fill="currentColor" />
                        <path id="Vector_2"
                          d="M5.9375 11.875V26.125H32.0625V11.875M5.9375 11.875H32.0625M5.9375 11.875L8.3125 8.3125H29.6875L32.0625 11.875M5.9375 26.125L8.3125 29.6875H29.6875L32.0625 26.125"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </g>
                    </svg>
                  </div>
                </div>
              </div>
              <div class="content-right">
                <p class="mb-0 fw-medium text-body">Total Pagu</p>
                <h4 class="text-primary mb-0">{{ number_format($totalSemua['total_jumlah_biaya'] ?? 0, 0, ',', '.') }}
                </h4>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="avatar avatar-lg">
                <div class="avatar-initial bg-label-success rounded">
                  <div class="text-success">
                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="TrendingUp">
                        <path id="Vector" opacity="0.2" d="M5.9375 26.125L14.25 17.8125L19 22.5625L32.0625 9.5"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path id="Vector_2" d="M32.0625 9.5H23.75M32.0625 9.5V17.8125" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </g>
                    </svg>
                  </div>
                </div>
              </div>
              <div class="content-right">
                <p class="mb-0 fw-medium text-body">Total Realisasi</p>
                <h4 class="text-success mb-0">{{ number_format($totalSemua['total_realisasi'] ?? 0, 0, ',', '.') }}</h4>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="avatar avatar-lg">
                <div class="avatar-initial bg-label-info rounded">
                  <div class="text-info">
                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="Activity">
                        <path id="Vector" opacity="0.2"
                          d="M5.9375 19L14.25 19L19 11.875L23.75 26.125L28.5 19L32.0625 19" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </g>
                    </svg>
                  </div>
                </div>
              </div>
              <div class="content-right">
                <p class="mb-0 fw-medium text-body">Sisa Pagu</p>
                <h4 class="text-info mb-0">{{ number_format($totalSemua['total_sisa'] ?? 0, 0, ',', '.') }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.statusDistributionData = {
      sudah: {
        total: {{ $statusStatistik['sudah']['total_jumlah_biaya'] ?? 0 }},
        count: {{ $statusStatistik['sudah']['count'] ?? 0 }}
      },
      belum: {
        total: {{ $statusStatistik['belum']['total_jumlah_biaya'] ?? 0 }},
        count: {{ $statusStatistik['belum']['count'] ?? 0 }}
      },
      draft: {
        total: {{ $statusStatistik['draft']['total_jumlah_biaya'] ?? 0 }},
        count: {{ $statusStatistik['draft']['count'] ?? 0 }}
      },
      totalSemua: {{ $totalSemua['total_jumlah_biaya'] ?? 0 }},
      totalItemCount: {{ $statusStatistik['sudah']['count'] + $statusStatistik['belum']['count'] + $statusStatistik['draft']['count'] }}
    };
  </script>

  <!-- Status Distribution -->
  <div class="card mb-6">
    <div class="card-header">
      <h5 class="card-title mb-0">Distribusi Anggaran</h5>
    </div>
    <div class="card-body">
      <!-- 3 Kartu Horizontal dengan Progress Bar -->
      <div class="row g-4 mb-6">
        <!-- Sudah -->
        <div class="col-md-4">
          <div class="card h-100">
            <div class="card-body">
              <p class="mb-2 text-muted" style="font-size: 13px;">Sudah Realisasi</p>
              <h4 class="text-info mb-1" id="sudahTotal">Rp0</h4>
              <p class="mb-2 text-muted" style="font-size: 12px;" id="sudahCount">0 item</p>
              <div class="progress" style="height: 4px;">
                <div class="progress-bar bg-info" id="sudahProgress" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Belum -->
        <div class="col-md-4">
          <div class="card h-100">
            <div class="card-body">
              <p class="mb-2 text-muted" style="font-size: 13px;">Belum Realisasi</p>
              <h4 class="text-danger mb-1" id="belumTotal">Rp0</h4>
              <p class="mb-2 text-muted" style="font-size: 12px;" id="belumCount">0 item</p>
              <div class="progress" style="height: 4px;">
                <div class="progress-bar bg-danger" id="belumProgress" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Draft -->
        <div class="col-md-4">
          <div class="card h-100">
            <div class="card-body">
              <p class="mb-2 text-muted" style="font-size: 13px;">Draft</p>
              <h4 class="text-warning mb-1" id="draftTotal">Rp0</h4>
              <p class="mb-2 text-muted" style="font-size: 12px;" id="draftCount">0 item</p>
              <div class="progress" style="height: 4px;">
                <div class="progress-bar bg-warning" id="draftProgress" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2 Donut Charts -->
      <div class="row g-6">
        <!-- Financial Distribution Donut -->
        <div class="col-md-6">
          <div class="d-flex flex-wrap gap-3 mb-2 justify-content-center" style="font-size: 12px; color: #6c757d;">
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #00bad1;"></span>
              Sudah <span id="sudahFinPersentase">0%</span>
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #ea5455;"></span>
              Belum <span id="belumFinPersentase">0%</span>
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #ff9f43;"></span>
              Draft <span id="draftFinPersentase">0%</span>
            </span>
          </div>
          <div id="financialDonutChart" style="height: 220px;"></div>
          <p class="text-center mb-0 mt-2 text-muted" style="font-size: 12px;">Financial distribution</p>
        </div>
        <!-- Item Count Distribution Donut -->
        <div class="col-md-6">
          <div class="d-flex flex-wrap gap-3 mb-2 justify-content-center" style="font-size: 12px; color: #6c757d;">
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #00bad1;"></span>
              Sudah <span id="sudahCountPersentase">0%</span>
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #ea5455;"></span>
              Belum <span id="belumCountPersentase">0%</span>
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
              <span style="width: 10px; height: 10px; border-radius: 2px; background: #ff9f43;"></span>
              Draft <span id="draftCountPersentase">0%</span>
            </span>
          </div>
          <div id="itemCountDonutChart" style="height: 220px;"></div>
          <p class="text-center mb-0 mt-2 text-muted" style="font-size: 12px;">Item count distribution</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 5 Unit dengan Total Biaya Terendah -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">5 Unit dengan Total Biaya Terendah</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
          </li>
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-expand"><i
                class="icon-base ti tabler-arrows-maximize icon-sm"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="collapse show">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0">
            <thead>
              <tr>
                <th>UNIT KERJA</th>
                <th class="text-end">TOTAL BIAYA</th>
                <th class="text-end">TOTAL REALISASI</th>
                <th class="text-end">TOTAL SISA</th>
                <th class="text-end">PERSENTASE</th>
              </tr>
            </thead>
            <tbody>
              @forelse($unitTerendah5 ?? [] as $unit)
                <tr>
                  <td>{{ $unit['unit_kerja'] }}</td>
                  <td class="text-end">{{ number_format($unit['total_jumlah_biaya'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_realisasi'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_sisa'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['avg_persentase'], 2, ',', '.') }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Distribusi per Jenis RAB -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">Distribusi per Jenis RAB</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
          </li>
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-expand"><i
                class="icon-base ti tabler-arrows-maximize icon-sm"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="collapse show">
      <div class="card-body">
        @forelse($distribusiJenisRab ?? [] as $jenis)
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>{{ $jenis['jenis'] }}</span>
              <span>{{ number_format($jenis['total_jumlah_biaya'], 0, ',', '.') }} ({{ $jenis['count'] }} item)</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-primary" role="progressbar"
                style="width: {{ $totalSemua['total_jumlah_biaya'] > 0 ? ($jenis['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100 : 0 }}%">
              </div>
            </div>
          </div>
        @empty
          <p class="mb-0">Tidak ada data untuk ditampilkan</p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Distribusi per Sumber Dana -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">Distribusi per Sumber Dana</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
          </li>
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-expand"><i
                class="icon-base ti tabler-arrows-maximize icon-sm"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="collapse show">
      <div class="card-body">
        @forelse($distribusiSumberDana ?? [] as $sd)
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>{{ $sd['sumberdana'] }}</span>
              <span>{{ number_format($sd['total_jumlah_biaya'], 0, ',', '.') }} ({{ $sd['count'] }} item)</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-success" role="progressbar"
                style="width: {{ $totalSemua['total_jumlah_biaya'] > 0 ? ($sd['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100 : 0 }}%">
              </div>
            </div>
          </div>
        @empty
          <p class="mb-0">Tidak ada data untuk ditampilkan</p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Daftar Unit dengan Realisasi > 100% -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">Daftar Unit dengan Realisasi > 100%</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
          </li>
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-expand"><i
                class="icon-base ti tabler-arrows-maximize icon-sm"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="collapse">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0">
            <thead>
              <tr>
                <th>UNIT KERJA</th>
                <th class="text-end">TOTAL BIAYA</th>
                <th class="text-end">TOTAL REALISASI</th>
                <th class="text-end">TOTAL SISA</th>
                <th class="text-end">PERSENTASE</th>
              </tr>
            </thead>
            <tbody>
              @forelse($unitDiatas100 ?? [] as $unit)
                <tr>
                  <td>{{ $unit['unit_kerja'] }}</td>
                  <td class="text-end">{{ number_format($unit['total_jumlah_biaya'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_realisasi'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_sisa'], 0, ',', '.') }}</td>
                  <td class="text-end text-danger fw-bold">{{ number_format($unit['avg_persentase'], 2, ',', '.') }}%
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada unit dengan realisasi > 100%</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Rincian RKT Unit dengan DataTables -->
  <div class="card mb-4">
    <h5 class="card-header pb-0 text-md-start text-center">RINCIAN RKT UNIT -
      {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
    <div class="card-datatable">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" id="table-title">Rincian RKT Unit - Semua Data</h5>
          <select class="form-select form-select-sm" id="filter-status" style="width: 200px;">
            <option value="semua">Semua Data</option>
            <option value="realisasi">Sudah Realisasi</option>
            <option value="!realisasi">Belum Realisasi</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>
      <table class="dt-rktunit table table-bordered" id="rktUnitTable">
        <thead>
          <tr>
            <th>UNIT KERJA</th>
            <th>SUMBER DANA</th>
            <th>JENIS RAB</th>
            <th>KODE KEGIATAN</th>
            <th>RINCIAN KEGIATAN</th>
            <th class="text-end">JUMLAH BIAYA</th>
            <th class="text-end">REALISASI</th>
            <th class="text-end">SISA</th>
            <th class="text-end">PERSENTASE</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Dokumentasi -->
  <div class="card card-action mt-6 mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">Dokumentasi</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
          </li>
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-expand"><i
                class="icon-base ti tabler-arrows-maximize icon-sm"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="collapse">
      <div class="card-body">
        <h6 class="mb-3">Proses Pengolahan Data</h6>
        <ol class="mb-4">
          <li>Mengambil data backup terbaru dari tabel <code>tb_duplikasi_rkat</code> dengan kondisi <code>is_deleted =
              false</code>, <code>duplikasi_ke = 0</code>, dan <code>peruntukan = 'RKAT Awal'</code></li>
          <li>Mengambil data RKT dari tabel <code>tb_backup_rkat</code> dengan join ke <code>tb_backup_rkat_detail</code>
          </li>
          <li>JOIN ke <code>tb_sumberdana</code> dan <code>tb_unit_api</code> untuk mendapatkan data master</li>
          <li>Menghitung statistik agregat di PHP (total biaya, realisasi, sisa, persentase)</li>
          <li>Menghitung statistik berdasarkan status realisasi (sudah, belum, draft)</li>
          <li>Mengambil 5 unit dengan biaya tertinggi dan terendah</li>
          <li>Menghitung distribusi per jenis RAB dan sumber dana</li>
          <li>Filter unit dengan persentase realisasi > 100%</li>
        </ol>

        <h6 class="mb-3">Tabel Database yang Digunakan</h6>
        <ul class="mb-4">
          <li><code>tb_duplikasi_rkat</code> - Menyimpan informasi backup RKAT (id, keterangan, tahun, created_at)</li>
          <li><code>tb_backup_rkat</code> - Menyimpan header backup RKAT</li>
          <li><code>tb_backup_rkat_detail</code> - Menyimpan detail backup RKAT (jumlah_biaya, jumlah_amprahan,
            jumlah_realisasi, terpakai_sisa)</li>
          <li><code>tb_sumberdana</code> - Menyimpan master data sumber dana (kd_sumberdana, sumberdana, is_show,
            is_deleted)</li>
          <li><code>tb_unit_api</code> - Menyimpan master data unit kerja (idunit, nama)</li>
        </ul>

        <h6 class="mb-3">Formula Matematika</h6>
        <div class="mb-4">
          <p class="mb-2"><strong>Total Realisasi per Item:</strong></p>
          <code class="d-block mb-3">Realisasi = COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0)</code>

          <p class="mb-2"><strong>Total Sisa per Item:</strong></p>
          <code class="d-block mb-3">Sisa = jumlah_biaya - realisasi</code>

          <p class="mb-2"><strong>Persentase Realisasi per Unit:</strong></p>
          <code class="d-block mb-3">Persentase = (Total Realisasi / Total Biaya) × 100</code>

          <p class="mb-2"><strong>Total Biaya per Unit:</strong></p>
          <code class="d-block mb-3">Total Biaya = Σ(jumlah_biaya) untuk semua item unit tersebut</code>

          <p class="mb-2"><strong>Total Realisasi per Unit:</strong></p>
          <code class="d-block mb-3">Total Realisasi = Σ(realisasi) untuk semua item unit tersebut</code>

          <p class="mb-2"><strong>Total Sisa per Unit:</strong></p>
          <code class="d-block mb-3">Total Sisa = Σ(sisa) untuk semua item unit tersebut</code>
        </div>

        <h6 class="mb-3">Keterangan Statistik</h6>
        <ul class="mb-4">
          <li><strong>Statistik Status:</strong> Sudah (realisasi > 0), Belum (realisasi = 0 dan bukan draft), Draft
            (is_draft = 'true')</li>
          <li><strong>5 Unit Tertinggi/Terendah:</strong> Diurutkan berdasarkan total biaya</li>
          <li><strong>Unit > 100%:</strong> Unit dengan persentase realisasi > 100 (over-budget)</li>
          <li><strong>Distribusi:</strong> Progress bar menunjukkan persentase dari total biaya semua unit</li>
          <li><strong>Data ditampilkan dalam format Indonesia:</strong> Angka menggunakan pemisah ribuan dengan titik (.)
          </li>
        </ul>

        <h6 class="mb-3">Query yang Digunakan</h6>
        <div class="mb-0">
          <p class="mb-2"><strong>Query untuk data RKT:</strong></p>
          <pre class="mb-3"><code>SELECT
    unit.nama AS unit_kerja,
    unit.idunit AS unit_kerja_rkt,
    sd.kd_sumberdana,
    sd.sumberdana,
    backupRkatDet.jenis AS rab_type,
    backupRkatDet.jumlah_biaya,
    COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) AS realisasi,
    ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL)
         AND backupRkatDet.terpakai_sisa IS NOT NULL
        THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)
             + backupRkatDet.terpakai_sisa
        ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya_revisi,
    backupRkatDet.is_draft
FROM tb_backup_rkat backupRkat
INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
    AND sd.is_show = 'true'
    AND sd.is_deleted = 'false'
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
WHERE backupRkat.id_duplikasi = ?
  AND backupRkatDet.id_duplikasi = ?
  AND backupRkat.tahun = ?
  AND backupRkatDet.is_deleted = 'false'
GROUP BY unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak</code></pre>
        </div>
      </div>
    </div>
  </div>
@endsection
