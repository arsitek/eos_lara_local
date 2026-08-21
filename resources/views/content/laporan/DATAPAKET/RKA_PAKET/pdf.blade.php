@include("COMPONENTS.loader")
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>LAPORAN | RKA Paket</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan" style="font-family: 'IBM Plex Sans'">
    <div class="col-lg-12">
        <div class="card">
            <h4 id="loading-msg" style="display: none;"></h4>
            <div class="card-header d-flex">
                <table class="table">
                    <tr>
                        <td></td>
                        <td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahun }}</td>
                    </tr>
                    <tr>
                        <td>KEMENTRIAN LEMBAGA</td>
                        <td>(023) KEMENTRIAN PENDIDIKAN DAN KEBUDAYAAN</td>
                    </tr>
                    <tr>
                        <td>UNIT ORGANISASI</td>
                        <td>(17) DITJEN PENDIDIKAN TINGGI</td>
                    </tr>
                    <tr>
                        <td>PTN/KOPERTIS</td>
                        <td>(690662) UNIVERSITAS SYIAH KUALA</td>
                    </tr>
                    <tr>
                        <td>PPK</td>
                        <td>{{ $nama_ppk }}</td>
                    </tr>
                    <tr>
                        <td>SUMBER DANA</td>
                        <td>{{ $sumberdana->sumberdana ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td class="total">0</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <!-- <button class="btn-export-pdf">Unduh pdf</button> -->
                    <table id="tabel-rekat-unit" class="table mb-0" border="3">
                        <thead>
                            <tr class="text-uppercase">
                                <th class="align-middle text-center">codebase</th>
                                <th class="align-middle text-center">SD/KRO/RO/KP/SK/PIC/DK/COA/SBM</th>
                                <th class="align-middle text-center">spesifikasi</th>
                                <th class="align-middle text-center">total biaya</th>
                                <th class="align-middle text-center">rpd</th>
                                <th class="align-middle text-center">proses</th>
                                <th class="align-middle text-center">real</th>
                                <th class="align-middle text-center">sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
@include('content.laporan.DATAPAKET.RKA_PAKET.script")
