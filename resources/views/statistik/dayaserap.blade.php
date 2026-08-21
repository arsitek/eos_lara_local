<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statistik | Daya Serap</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">DAYA SERAP - {{ $backupKeterangan ?? 'Data Terbaru' }}</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
