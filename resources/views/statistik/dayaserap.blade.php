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
  <div class="card bg-transparent shadow-none my-6 border-0">
    <div class="card-body row p-0 pb-6 g-6">
      <div class="col-12">
        <h5 class="mb-2">TOTAL DAYA SERAP PER {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
        <div class="col-12 col-lg-8">
          <p>Ringkasan total pagu alokasi, realisasi, daya serap, dan rata-rata persentase untuk seluruh unit.</p>
        </div>
        <div class="d-flex justify-content-between flex-wrap gap-4 me-12">
          <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
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
              <p class="mb-0 fw-medium">TOTAL PAGU ALOKASI</p>
              <h4 class="text-primary mb-0">{{ number_format($totalSemua['total_pagu_alokasi'] ?? 0, 0, ',', '.') }}</h4>
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
              <p class="mb-0 fw-medium">TOTAL REALISASI</p>
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
              <p class="mb-0 fw-medium">TOTAL DAYA SERAP</p>
              <h4 class="text-info mb-0">{{ number_format($totalSemua['total_daya_serap'] ?? 0, 0, ',', '.') }}</h4>
            </div>
          </div>
          <div class="d-flex align-items-center gap-4">
            <div class="avatar avatar-lg">
              <div class="avatar-initial bg-label-warning rounded">
                <div class="text-warning">
                  <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="Percent">
                      <path id="Vector" opacity="0.2" d="M11.875 26.125L26.125 11.875" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path id="Vector_2"
                        d="M11.875 11.875H14.25M11.875 11.875V14.25M26.125 26.125H23.75M26.125 26.125V23.75"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                  </svg>
                </div>
              </div>
            </div>
            <div class="content-right">
              <p class="mb-0 fw-medium">RATA-RATA PERSENTASE</p>
              <h4 class="text-warning mb-0">{{ $totalSemua['avg_persentase'] ?? 0 }}%</h4>
            </div>
          </div>
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
    <!--/ Daftar Unit dengan Persentase Daya Serap > 100% -->

    <!-- Daya Serap DataTable -->
    <div class="card">
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
