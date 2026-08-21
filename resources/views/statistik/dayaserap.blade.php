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
  <!-- Daya Serap DataTable -->
  <div class="card">
    <h5 class="card-header pb-0 text-md-start text-center">DAYA SERAP - {{ $backupKeterangan ?? 'Data Terbaru' }}</h5>
    <div class="card-datatable text-nowrap">
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
    window.dataDayaSerap = @json($dataDayaSerap);
  </script>
@endsection
