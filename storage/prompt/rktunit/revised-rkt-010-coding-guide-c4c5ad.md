# REVISED CODING GUIDE — RKT-010 (Buat View statistik/rktunit.blade.php)

## 1. Tujuan

Membuat view Blade untuk halaman statistik RKT unit dengan pattern UI yang benar dari existing codebase.

## 2. Konteks & Logika

Task ini membuat view Blade yang akan menampilkan card-card statistik dan DataTables. View akan menggunakan layout dari `layouts/layoutMaster` dan mengikuti pattern yang benar dari `dayaserap.blade.php`.

**Pattern UI yang Benar (dari dayaserap.blade.php):**
- Card dengan action: `card card-action`
- Card header title: `card-action-title`
- Card action element: `card-action-element`
- Collapse button: `card-collapsible` dengan icon `tabler-chevron-up`
- Maximize button: `card-expand` dengan icon `tabler-arrows-maximize`
- Collapsible body: `collapse` (default collapsed) atau `collapse show` (default expanded)

## 3. Dependency

```text
Dependency: RKT-007, RKT-009
```

RKT-007 dan RKT-009 harus selesai karena kita perlu data dari controller dan JavaScript DataTables.

## 4. File yang Digunakan

### File existing yang harus diperiksa

```text
resources/views/statistik/dayaserap.blade.php
```

### File yang akan dibuat/diubah

```text
resources/views/statistik/rktunit.blade.php
```

## 5. Mapping Controller → Blade

| Blade Variable | Controller Source | Digunakan Untuk |
| -------------- | ----------------- | --------------- |
| `$totalSemua` | `$totalSemua` | Card 1 (KPI Utama) |
| `$statusStatistik` | `$statusStatistik` | Card 2 (Statistik Status) |
| `$unitTertinggi5` | `$unitTertinggi5` | Card 3 (5 Unit Tertinggi) |
| `$unitTerendah5` | `$unitTerendah5` | Card 4 (5 Unit Terendah) |
| `$distribusiJenisRab` | `$distribusiJenisRab` | Card 5 (Distribusi Jenis RAB) |
| `$distribusiSumberDana` | `$distribusiSumberDana` | Card 6 (Distribusi Sumber Dana) |
| `$unitDiatas100` | `$unitDiatas100` | Card 7 (Unit > 100%) |
| `$dataPerUnitArray` | `$dataPerUnitArray` | DataTables, window.dataRktUnit |
| `$backupKeterangan` | `$backupKeterangan` | Judul card, dokumentasi |
| `$backupTahun` | `$backupTahun` | Dokumentasi |

## 6. CODE SIAP COPY-PASTE

### Paste di:
```text
resources/views/statistik/rktunit.blade.php
```

### Code:

```blade
@extends('layouts/layoutMaster')

@section('title', 'Statistik | RKT Unit')

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
  @vite(['resources/assets/js/statistik-rktunit.js', 'resources/assets/js/cards-actions.js'])
@endsection

@section('content')
  <!-- Pass data to JavaScript -->
  <script>
    window.dataRktUnit = @json($dataPerUnitArray);
  </script>

  <!-- Total Semua Unit -->
  <div class="card bg-transparent shadow-none my-6 border-0">
    <div class="card-body row p-0 pb-6 g-6">
      <div class="col-12">
        <h5 class="mb-2">TOTAL RKT PER {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
        <div class="col-12 col-lg-8">
          <p>Ringkasan total jumlah biaya, realisasi, sisa, dan rata-rata persentase untuk seluruh unit.</p>
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
              <p class="mb-0 fw-medium">TOTAL JUMLAH BIAYA</p>
              <h4 class="text-primary mb-0">{{ number_format($totalSemua['total_jumlah_biaya'] ?? 0, 0, ',', '.') }}</h4>
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
              <p class="mb-0 fw-medium">TOTAL SISA</p>
              <h4 class="text-info mb-0">{{ number_format($totalSemua['total_sisa'] ?? 0, 0, ',', '.') }}</h4>
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
                        d="M11.875 11.875C13.6264 11.875 15.0469 13.2955 15.0469 15.0469C15.0469 16.7983 13.6264 18.2188 11.875 18.2188C10.1236 18.2188 8.70312 16.7983 8.70312 15.0469C8.70312 13.2955 10.1236 11.875 11.875 11.875Z"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      <path id="Vector_3"
                        d="M26.125 19.7812C27.8764 19.7812 29.2969 21.2017 29.2969 22.9531C29.2969 24.7045 27.8764 26.125 26.125 26.125C24.3736 26.125 22.9531 24.7045 22.9531 22.9531C22.9531 21.2017 24.3736 19.7812 26.125 19.7812Z"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                  </svg>
                </div>
              </div>
            </div>
            <div class="content-right">
              <p class="mb-0 fw-medium">RATA-RATA PERSENTASE</p>
              <h4 class="text-warning mb-0">{{ number_format($totalSemua['avg_persentase'] ?? 0, 2, ',', '.') }}%</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistik Berdasarkan Status Realisasi -->
  <div class="card mb-6">
    <div class="card-header">
      <h5 class="card-title mb-0">Statistik Berdasarkan Status Realisasi</h5>
    </div>
    <div class="card-body">
      <div class="row g-6">
        <!-- Sudah Realisasi -->
        <div class="col-md-4">
          <div class="card bg-label-success h-100">
            <div class="card-body">
              <h6 class="text-success mb-2">Sudah Realisasi</h6>
              <h4 class="text-success mb-1">{{ number_format($statusStatistik['sudah']['total_jumlah_biaya'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Biaya</p>
              <h4 class="text-success mb-1">{{ number_format($statusStatistik['sudah']['total_realisasi'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Realisasi</p>
              <h4 class="text-success mb-1">{{ number_format($statusStatistik['sudah']['total_sisa'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Sisa</p>
              <h4 class="text-success mb-1">{{ number_format($statusStatistik['sudah']['persentase'] ?? 0, 2, ',', '.') }}%</h4>
              <p class="mb-0">Persentase</p>
              <h4 class="text-success mb-0">{{ $statusStatistik['sudah']['count'] ?? 0 }}</h4>
              <p class="mb-0">Jumlah Item</p>
            </div>
          </div>
        </div>
        <!-- Belum Realisasi -->
        <div class="col-md-4">
          <div class="card bg-label-danger h-100">
            <div class="card-body">
              <h6 class="text-danger mb-2">Belum Realisasi</h6>
              <h4 class="text-danger mb-1">{{ number_format($statusStatistik['belum']['total_jumlah_biaya'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Biaya</p>
              <h4 class="text-danger mb-1">{{ number_format($statusStatistik['belum']['total_sisa'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Sisa</p>
              <h4 class="text-danger mb-1">0%</h4>
              <p class="mb-0">Persentase</p>
              <h4 class="text-danger mb-0">{{ $statusStatistik['belum']['count'] ?? 0 }}</h4>
              <p class="mb-0">Jumlah Item</p>
            </div>
          </div>
        </div>
        <!-- Draft -->
        <div class="col-md-4">
          <div class="card bg-label-warning h-100">
            <div class="card-body">
              <h6 class="text-warning mb-2">Draft</h6>
              <h4 class="text-warning mb-1">{{ number_format($statusStatistik['draft']['total_jumlah_biaya'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Biaya</p>
              <h4 class="text-warning mb-1">{{ number_format($statusStatistik['draft']['total_sisa'] ?? 0, 0, ',', '.') }}</h4>
              <p class="mb-0">Total Sisa</p>
              <h4 class="text-warning mb-1">0%</h4>
              <p class="mb-0">Persentase</p>
              <h4 class="text-warning mb-0">{{ $statusStatistik['draft']['count'] ?? 0 }}</h4>
              <p class="mb-0">Jumlah Item</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5 Unit dengan Total Biaya Tertinggi -->
  <div class="card card-action mb-4">
    <div class="card-header">
      <h5 class="card-action-title mb-0">5 Unit dengan Total Biaya Tertinggi</h5>
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
              @forelse($unitTertinggi5 ?? [] as $unit)
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
              <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($totalSemua['total_jumlah_biaya'] > 0) ? ($jenis['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100 : 0 }}%"></div>
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
              <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($totalSemua['total_jumlah_biaya'] > 0) ? ($sd['total_jumlah_biaya'] / $totalSemua['total_jumlah_biaya']) * 100 : 0 }}%"></div>
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
                  <td class="text-end text-danger fw-bold">{{ number_format($unit['avg_persentase'], 2, ',', '.') }}%</td>
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
          <li>Mengambil data RKT dari tabel <code>tb_backup_rkat</code> dengan join ke <code>tb_backup_rkat_detail</code></li>
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
          <li><code>tb_backup_rkat_detail</code> - Menyimpan detail backup RKAT (jumlah_biaya, jumlah_amprahan, jumlah_realisasi, terpakai_sisa)</li>
          <li><code>tb_sumberdana</code> - Menyimpan master data sumber dana (kd_sumberdana, sumberdana, is_show, is_deleted)</li>
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
          <li><strong>Statistik Status:</strong> Sudah (realisasi > 0), Belum (realisasi = 0 dan bukan draft), Draft (is_draft = 'true')</li>
          <li><strong>5 Unit Tertinggi/Terendah:</strong> Diurutkan berdasarkan total biaya</li>
          <li><strong>Unit > 100%:</strong> Unit dengan persentase realisasi > 100 (over-budget)</li>
          <li><strong>Distribusi:</strong> Progress bar menunjukkan persentase dari total biaya semua unit</li>
          <li><strong>Data ditampilkan dalam format Indonesia:</strong> Angka menggunakan pemisah ribuan dengan titik (.)</li>
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
```

## 7. PENJELASAN CODE

### Apa yang dilakukan?

Membuat view Blade lengkap untuk halaman statistik RKT unit dengan pattern UI yang benar dari existing codebase.

### Mengapa dilakukan?

View ini akan menampilkan data statistik dari controller ke user dengan UI yang konsisten dengan existing page.

### Bagaimana logikanya?

1. Setup layout dan sections (vendor-style, vendor-script, page-script)
2. Script window object untuk menyediakan data ke JavaScript (RKT-011)
3. Card 1: Total RKT dengan 4 panel (jumlah biaya, realisasi, sisa, persentase)
4. Card 2: Statistik status dengan 3 panel (sudah, belum, draft)
5. Card 3-7: Cards dengan collapse/expand menggunakan pattern `card-action`
6. Card 8: DataTables dengan dropdown filter
7. Card 9: Dokumentasi dengan query dan formula (collapsed)

### Business rule apa yang diterapkan?

- Total Sisa digunakan sebagai pengganti Total Revisi karena field jumlah_biaya_revisi tidak tersedia
- Status realisasi: Sudah (realisasi > 0), Belum (realisasi = 0 dan bukan draft), Draft (is_draft = 'true')
- Unit > 100% adalah unit dengan persentase realisasi > 100
- Progress bar distribusi menggunakan division by zero handling

### Data grain apa yang digunakan?

- Card 1-7: Aggregated data per unit, per status, per jenis RAB, per sumber dana
- Card 8: Detail data per item RKT (via DataTables)

## 8. EXPECTED RESULT

```text
View Blade dibuat dan diisi dengan code lengkap
Semua card menampilkan data dengan benar
Pattern UI konsisten dengan existing page
DataTables berfungsi dengan filter
Collapse/expand berfungsi dengan benar
```

## 9. VERIFICATION

- Buka file `resources/views/statistik/rktunit.blade.php`
- Pastikan code lengkap sudah di-copy-paste
- Buka browser dan akses `/statistik/rktunit` (setelah route ditambahkan)
- Pastikan semua card menampilkan data
- Test collapse/expand button
- Test DataTables filter
- Cek browser console untuk error

## 10. TROUBLESHOOTING

### Symptom

Card tidak muncul atau layout berantakan.

### Kemungkinan penyebab

Pattern UI salah atau class tidak sesuai.

### Cara memeriksa

Buka browser developer tools dan inspect element untuk cek class.

### Solusi

Pastikan menggunakan pattern yang benar dari dayaserap.blade.php.

### Division by Zero

Progress bar distribusi mungkin menyebabkan error jika total biaya = 0.

### Solusi

Code sudah menggunakan division by zero handling: `($totalSemua['total_jumlah_biaya'] > 0) ? ... : 0`

## 11. CHECKPOINT

```text
## RKT-010 — Buat View statistik/rktunit.blade.php

- [ ] Buka file rktunit.blade.php
- [ ] Copy-paste code lengkap dari Coding Guide
- [ ] Verifikasi pattern UI sesuai dengan dayaserap.blade.php
- [ ] Verifikasi script window object ditempatkan dengan benar
- [ ] Test di browser setelah route ditambahkan
- [ ] Pastikan semua card menampilkan data
- [ ] Test collapse/expand button
- [ ] Test DataTables filter
```

## 12. GIT COMMIT

### Commit Type

```text
feat
```

### Commit Scope

```text
statistik
```

### Commit Message

```text
feat(statistik): add RKT unit view Blade
```

### Alasan

Task ini menambahkan view Blade baru untuk halaman statistik RKT unit.

### Command

```bash
git add resources/views/statistik/rktunit.blade.php
git commit -m "feat(statistik): add RKT unit view Blade"
```

### Catatan

View ini sudah mengintegrasikan script window object (RKT-011) dalam satu commit.
