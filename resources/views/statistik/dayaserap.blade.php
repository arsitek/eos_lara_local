@extends('layouts/layoutMaster')

@section('title', 'Statistik | Daya Serap')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notiflix/notiflix.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/statistik-dayaserap.js', 'resources/assets/js/cards-actions.js'])
@endsection

@section('content')
  <!-- Total Semua Unit -->
  <div class="card my-6">
    <div class="card-body">
      <!-- Header with Alert Badge -->
      <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
        <div>
          <h5 class="mb-1">Total daya serap per {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
          <p class="mb-0 text-muted" style="font-size: 13px;">Ringkasan total pagu alokasi, realisasi, daya serap, dan
            rata-rata persentase untuk seluruh unit.</p>
        </div>
        @if ($totalSemua['avg_persentase'] > 100)
          <div class="d-flex align-items-center gap-2 bg-label-danger text-danger"
            style="font-size: 12px; font-weight: 500; padding: 6px 12px; border-radius: 8px; white-space: nowrap;">
            <i class="icon-base ti tabler-alert-triangle" style="font-size: 14px;"></i>
            Melebihi pagu {{ number_format($totalSemua['avg_persentase'] - 100, 2, ',', '.') }}%
          </div>
        @endif
      </div>

      <!-- 4 Summary Cards -->
      <div class="row g-3 mb-4">
        <!-- Total Pagu Alokasi -->
        <div class="col-md-3">
          <div class="card h-100 shadow-none border-0">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div
                  style="width: 28px; height: 28px; border-radius: 7px; background: rgba(105, 108, 255, 0.12); display: flex; align-items: center; justify-content: center;">
                  <i class="icon-base ti tabler-credit-card" style="font-size: 14px; color: #696cff;"></i>
                </div>
                <p class="mb-0 text-muted" style="font-size: 12px;">Total pagu alokasi</p>
              </div>
              <h4 class="mb-0" style="font-size: 19px; font-weight: 500;">
                {{ number_format($totalSemua['total_pagu_alokasi'] ?? 0, 0, ',', '.') }}</h4>
            </div>
          </div>
        </div>
        <!-- Total Realisasi -->
        <div class="col-md-3">
          <div class="card h-100 shadow-none border-0">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div
                  style="width: 28px; height: 28px; border-radius: 7px; background: rgba(40, 199, 111, 0.12); display: flex; align-items: center; justify-content: center;">
                  <i class="icon-base ti tabler-trending-up" style="font-size: 14px; color: #28c76f;"></i>
                </div>
                <p class="mb-0 text-muted" style="font-size: 12px;">Total realisasi (Anggaran)</p>
                <i class="ti ti-help-circle text-muted" style="font-size: 14px; cursor: help;" data-bs-toggle="tooltip"
                  data-bs-placement="top"
                  data-bs-title="Realisasi berdasarkan pagu alokasi vs pengeluaran aktual per unit kerja dan sumber dana. Digunakan untuk pelaporan eksternal ke Kementerian/Dewan."></i>
              </div>
              <h4 class="mb-0 text-success" style="font-size: 19px; font-weight: 500;">
                {{ number_format($totalSemua['total_realisasi'] ?? 0, 0, ',', '.') }}</h4>
            </div>
          </div>
        </div>
        <!-- Total Daya Serap -->
        <div class="col-md-3">
          <div class="card h-100 shadow-none border-0">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div
                  style="width: 28px; height: 28px; border-radius: 7px; background: rgba(234, 84, 85, 0.12); display: flex; align-items: center; justify-content: center;">
                  <i class="icon-base ti tabler-arrow-down-right" style="font-size: 14px; color: #ea5455;"></i>
                </div>
                <p class="mb-0 text-muted" style="font-size: 12px;">Total daya serap</p>
              </div>
              <h4 class="mb-0 text-danger" style="font-size: 19px; font-weight: 500;">
                {{ number_format($totalSemua['total_daya_serap'] ?? 0, 0, ',', '.') }}</h4>
            </div>
          </div>
        </div>
        <!-- Rata-rata Persentase -->
        <div class="col-md-3">
          <div class="card h-100 shadow-none border-0">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div
                  style="width: 28px; height: 28px; border-radius: 7px; background: rgba(255, 159, 67, 0.12); display: flex; align-items: center; justify-content: center;">
                  <i class="icon-base ti tabler-percentage" style="font-size: 14px; color: #ff9f43;"></i>
                </div>
                <p class="mb-0 text-muted" style="font-size: 12px;">Rata-rata persentase</p>
              </div>
              <h4 class="mb-0 text-warning" style="font-size: 19px; font-weight: 500;">
                {{ number_format($totalSemua['avg_persentase'] ?? 0, 2, ',', '.') }}%</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- Comparison Progress Bars -->
      <div style="border-top: 1px solid #e9ecef; padding-top: 1rem;">
        <p class="mb-2 text-muted" style="font-size: 12px;">Pagu alokasi vs realisasi</p>
        <div class="d-flex align-items-center gap-3 mb-2">
          <span class="text-muted" style="font-size: 12px; width: 64px; flex-shrink: 0;">Pagu</span>
          <div class="flex-grow-1" style="height: 14px; background: #f5f5f5; border-radius: 4px; overflow: hidden;">
            @php
              $paguPercentage =
                  $totalSemua['total_realisasi'] > 0
                      ? ($totalSemua['total_pagu_alokasi'] / $totalSemua['total_realisasi']) * 100
                      : 0;
            @endphp
            <div style="width: {{ min(100, $paguPercentage) }}%; height: 100%; background: #378ADD;"></div>
          </div>
          <span class="text-muted"
            style="font-size: 12px; width: 110px; text-align: right; flex-shrink: 0;">{{ number_format($totalSemua['total_pagu_alokasi'] ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted" style="font-size: 12px; width: 64px; flex-shrink: 0;">Realisasi</span>
          <div class="flex-grow-1" style="height: 14px; background: #f5f5f5; border-radius: 4px; overflow: hidden;">
            <div style="width: 100%; height: 100%; background: #E24B4A;"></div>
          </div>
          <span class="text-danger"
            style="font-size: 12px; width: 110px; text-align: right; flex-shrink: 0; font-weight: 500;">{{ number_format($totalSemua['total_realisasi'] ?? 0, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
  </div>
  <!--/ Total Semua Unit -->

  <!-- 5 Unit dengan Daya Serap Terendah -->
  <div class="card mb-4">
    <h5 class="card-header pb-0">5 Unit dengan Daya Serap Terendah</h5>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
          <thead>
            <tr>
              <th>UNIT KERJA</th>
              <th class="text-end">TOTAL PAGU ALOKASI</th>
              <th class="text-end">TOTAL REALISASI</th>
              <th class="text-end">TOTAL DAYA SERAP</th>
              <th class="text-end">RATA-RATA PERSENTASE</th>
            </tr>
          </thead>
          <tbody>
            @forelse($unitTerendah5 ?? [] as $unit)
              <tr>
                <td>{{ $unit['unit_kerja'] }}</td>
                <td class="text-end">{{ number_format($unit['total_pagu_alokasi'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($unit['total_realisasi'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($unit['total_daya_serap'], 0, ',', '.') }}</td>
                <td class="text-end">{{ $unit['avg_persentase'] }}%</td>
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
  <!--/ 5 Unit dengan Daya Serap Terendah -->

  <!-- Daftar Unit dengan Persentase Daya Serap > 100% -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">Daftar Unit dengan Persentase Daya Serap > 100%</h5>
      <div class="card-action-element">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a href="javascript:void(0);" class="card-collapsible"><i
                class="icon-base ti tabler-chevron-up icon-sm"></i></a>
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
                <th class="text-end">TOTAL PAGU ALOKASI</th>
                <th class="text-end">TOTAL REALISASI</th>
                <th class="text-end">TOTAL DAYA SERAP</th>
                <th class="text-end">RATA-RATA PERSENTASE</th>
              </tr>
            </thead>
            <tbody>
              @forelse($unitDiatas100 ?? [] as $unit)
                <tr>
                  <td>{{ $unit['unit_kerja'] }}</td>
                  <td class="text-end">{{ number_format($unit['total_pagu_alokasi'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_realisasi'], 0, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($unit['total_daya_serap'], 0, ',', '.') }}</td>
                  <td class="text-end text-danger fw-bold">{{ $unit['avg_persentase'] }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada unit dengan persentase daya serap > 100%</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!--/ Daftar Unit dengan Persentase Daya Serap > 100% -->

  <!-- Daya Serap DataTable -->
  <div class="card mb-4">
    <h5 class="card-header pb-0 text-md-start text-center">RINCIAN DAYA SERAP -
      {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
    <div class="card-datatable">
      <table class="dt-dayaserap table table-bordered" id="dayaSerapTable">
        <thead>
          <tr>
            <th>UNIT KERJA</th>
            <th>SUMBER DANA</th>
            <th>PAGU ALOKASI</th>
            <th>REALISASI</th>
            <th>DAYA SERAP</th>
            <th>PERSENTASE</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
  <!--/ Daya Serap DataTable -->

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
          <li>Mengambil data alokasi dari tabel <code>tb_backup_alokasi</code> dengan join ke <code>tb_sumberdana</code>
            dan <code>tb_unit_api</code></li>
          <li>Mengambil data realisasi dari tabel <code>tb_backup_rkat</code> dengan join ke
            <code>tb_backup_rkat_detail</code>, <code>tb_sumberdana</code>, dan <code>tb_unit_api</code>
          </li>
          <li>Menggabungkan data alokasi dan realisasi berdasarkan kombinasi unit kerja dan sumber dana</li>
          <li>Menghitung daya serap dan persentase untuk setiap kombinasi unit-sumber dana</li>
          <li>Mengakumulasi data per unit untuk statistik agregat</li>
        </ol>

        <h6 class="mb-3">Tabel Database yang Digunakan</h6>
        <ul class="mb-4">
          <li><code>tb_duplikasi_rkat</code> - Menyimpan informasi backup RKAT (id, keterangan, tahun, created_at)</li>
          <li><code>tb_backup_alokasi</code> - Menyimpan data alokasi pagu per unit dan sumber dana</li>
          <li><code>tb_sumberdana</code> - Menyimpan master data sumber dana (kd_sumberdana, sumberdana, is_show,
            is_deleted, tahun)</li>
          <li><code>tb_unit_api</code> - Menyimpan master data unit kerja (idunit, nama)</li>
          <li><code>tb_backup_rkat</code> - Menyimpan header backup RKAT</li>
          <li><code>tb_backup_rkat_detail</code> - Menyimpan detail backup RKAT (jumlah_amprahan, jumlah_realisasi)</li>
        </ul>

        <h6 class="mb-3">Formula Matematika</h6>
        <div class="mb-4">
          <p class="mb-2"><strong>Daya Serap per Unit-Sumber Dana:</strong></p>
          <code class="d-block mb-3">Daya Serap = Pagu Alokasi - Realisasi</code>

          <p class="mb-2"><strong>Persentase per Unit-Sumber Dana:</strong></p>
          <code class="d-block mb-3">Persentase = (Realisasi / Pagu Alokasi) × 100</code>

          <p class="mb-2"><strong>Total Pagu Alokasi per Unit:</strong></p>
          <code class="d-block mb-3">Total Pagu Alokasi = Σ(Pagu Alokasi) untuk semua sumber dana unit tersebut</code>

          <p class="mb-2"><strong>Total Realisasi per Unit:</strong></p>
          <code class="d-block mb-3">Total Realisasi = Σ(Realisasi) untuk semua sumber dana unit tersebut</code>

          <p class="mb-2"><strong>Total Daya Serap per Unit:</strong></p>
          <code class="d-block mb-3">Total Daya Serap = Σ(Daya Serap) untuk semua sumber dana unit tersebut</code>

          <p class="mb-2"><strong>Rata-rata Persentase per Unit:</strong></p>
          <code class="d-block mb-3">Rata-rata Persentase = Σ(Persentase) / Jumlah Sumber Dana</code>

          <p class="mb-2"><strong>Total Semua Unit:</strong></p>
          <code class="d-block mb-3">Total = Σ(Total per Unit) untuk semua unit</code>

          <p class="mb-2"><strong>Rata-rata Persentase Semua Unit:</strong></p>
          <code class="d-block mb-3">Rata-rata = Σ(Persentase per Unit) / Jumlah Unit</code>
        </div>

        <h6 class="mb-3">Keterangan Statistik</h6>
        <ul class="mb-4">
          <li><strong>5 Unit dengan Daya Serap Terendah:</strong> Unit dengan rata-rata persentase daya serap terendah
            (diurutkan ascending)</li>
          <li><strong>Daftar Unit dengan Persentase > 100%:</strong> Unit yang realisasinya melebihi pagu alokasi
            (over-budget)</li>
          <li><strong>Data ditampilkan dalam format Indonesia:</strong> Angka menggunakan pemisah ribuan dengan titik
            (.)
            dan desimal dengan koma (,)</li>
        </ul>

        <h6 class="mb-3">Metrik Realisasi di Sistem</h6>
        <div class="alert alert-info mb-4">
          <p class="mb-2"><strong>Terdapat 3 metrik realisasi yang berbeda di sistem dengan tujuan dan scope yang
              berbeda:</strong></p>
        </div>
        <div class="mb-4">
          <p class="mb-2 fw-bold">1. Total Realisasi (Anggaran) - Halaman Daya Serap</p>
          <ul class="mb-3">
            <li><strong>Scope:</strong> Per unit kerja + sumber dana</li>
            <li><strong>Purpose:</strong> Budget Execution Monitoring (Pagu vs Realisasi)</li>
            <li><strong>Stakeholder:</strong> Eksternal (Kementerian/Dewan Pengawas)</li>
            <li><strong>Formula:</strong> SUM(jumlah_amprahan + jumlah_realisasi) GROUP BY unit.idunit, backupRkat.sd</li>
            <li><strong>Insight:</strong> Mengukur efektivitas eksekusi anggaran untuk pelaporan eksternal sesuai Standar
              Akuntansi Pemerintahan (SAP)</li>
          </ul>

          <p class="mb-2 fw-bold">2. Total Realisasi (Kegiatan) - Halaman RKT Unit</p>
          <ul class="mb-3">
            <li><strong>Scope:</strong> Per unit kerja + sumber dana + jenis RAB + kegiatan (id_mak)</li>
            <li><strong>Purpose:</strong> Activity-Based Performance Tracking</li>
            <li><strong>Stakeholder:</strong> Internal (Unit Kerja)</li>
            <li><strong>Formula:</strong> COALESCE(jumlah_amprahan, 0) + COALESCE(jumlah_realisasi, 0) GROUP BY
              unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak</li>
            <li><strong>Insight:</strong> Mengukur realisasi per kegiatan/program untuk monitoring internal dan efisiensi
              operasional (Activity-Based Budgeting)</li>
          </ul>

          <p class="mb-2 fw-bold">3. Sudah Realisasi (Status) - Halaman RKT Unit (Distribusi Anggaran)</p>
          <ul class="mb-0">
            <li><strong>Scope:</strong> Subset dari Total Realisasi (Kegiatan), hanya item dengan realisasi > 0</li>
            <li><strong>Purpose:</strong> Execution Status Tracking</li>
            <li><strong>Stakeholder:</strong> Manajemen Operasional</li>
            <li><strong>Filter:</strong> WHERE realisasi > 0</li>
            <li><strong>Insight:</strong> Mengukur progress pelaksanaan kegiatan untuk decision making dan prioritisasi
              sumber daya (Project Management)</li>
          </ul>
        </div>

        <h6 class="mb-3">Query yang Digunakan</h6>
        <div class="mb-0">
          <p class="mb-2"><strong>1. Query untuk mendapatkan backup terbaru:</strong></p>
          <pre class="mb-3"><code>SELECT id, keterangan, tahun
FROM tb_duplikasi_rkat
WHERE is_deleted = false
  AND duplikasi_ke = 0
  AND peruntukan = 'RKAT Awal'
ORDER BY created_at DESC
LIMIT 1</code></pre>

          <p class="mb-2"><strong>2. Query untuk data alokasi:</strong></p>
          <pre class="mb-3"><code>SELECT
    unit.nama AS unit_kerja,
    sd.kd_sumberdana,
    sd.sumberdana,
    ba.pagu AS pagu_alokasi
FROM tb_backup_alokasi ba
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = ba.kode_sd
    AND sd.is_show = 'true'
    AND sd.is_deleted = 'false'
INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit
WHERE ba.id_duplikasi = ?
ORDER BY sd.kd_sumberdana, ba.idunit</code></pre>

          <p class="mb-2"><strong>3. Query untuk data realisasi:</strong></p>
          <pre class="mb-0"><code>SELECT
    unit.nama AS unit_kerja,
    unit.idunit AS unit_kerja_rkt,
    sd.kd_sumberdana,
    sd.sumberdana,
    SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS realisasi
FROM tb_backup_rkat backupRkat
INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
WHERE backupRkat.id_duplikasi = ?
  AND backupRkatDet.id_duplikasi = ?
  AND backupRkat.tahun = ?
GROUP BY unit.idunit, backupRkat.sd</code></pre>
        </div>
      </div>
    </div>
  </div>
  <!--/ Dokumentasi -->

  <script>
    // Pass data to JavaScript
    window.dataDayaSerap = @json($dataDayaSerapArray);
  </script>
@endsection
