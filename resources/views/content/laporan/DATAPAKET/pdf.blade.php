<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>LAPORAN | RKA Paket</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan"">
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
                        <td>PTNBH</td>
                        <td class="total_ptnbh">0</td>
                    </tr>
                    <tr>
                        <td>NonAPBN</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>RM</td>
                        <td>0</td>
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
                    <table id="tabel-laporan-paket-rka" class="table mb-0" border="3">
                        <thead>
                            <tr>
                                <th>codebase</th>
                                <th>SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>qt</th>
                                <th>unit</th>
                                <th>dr</th>
                                <th>ms</th>
                                <th>keg</th>
                                <th>item</th>
                                <th>total biaya</th>
                                <th>rpd</th>
                                <th>proses</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
<script src="{{ asset('assets/js/tata-master/dist/tata.js') }}"></script>
@include('content.laporan.DATAPAKET.script')
@include('HELPERS.export')
