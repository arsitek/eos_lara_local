@extends('layouts/layoutMaster')

@section('title', 'Statistik | Daya Serap')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/statistik-dayaserap.js'])
@endsection

@section('content')
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

  <!-- Daya Serap DataTable -->
  <div class="card">
    <h5 class="card-header pb-0 text-md-start text-center">DAYA SERAP - {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
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

  <script>
    // Pass data to JavaScript
    window.dataDayaSerap = @json($dataDayaSerapArray);
  </script>
@endsection
