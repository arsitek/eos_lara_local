@extends('layouts/layoutMaster')

@section('title', 'Statistik | Daya Serap')

@section('page-style')
  @vite(['resources/assets/vendor/fonts/fontawesome.scss'])
@endsection

@section('content')
  <style>
    .card {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table {
      font-size: 14px;
    }

    .table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
  </style>
  <div class="row mt-5">
    <div class="col-lg-12 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <h3 class="card-title">DAYA SERAP - {{ $backupKeterangan ?? 'Data Terbaru' }}</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
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
              <tbody>
                @forelse ($dataDayaSerap as $item)
                  <tr>
                    <td>{{ $item['unit_kerja'] }}</td>
                    <td>{{ $item['sumberdana'] }}</td>
                    <td>{{ number_format($item['pagu_alokasi'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['realisasi'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['daya_serap'], 0, ',', '.') }}</td>
                    <td>{{ $item['persentase'] }}%</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center">Tidak ada data untuk ditampilkan</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
