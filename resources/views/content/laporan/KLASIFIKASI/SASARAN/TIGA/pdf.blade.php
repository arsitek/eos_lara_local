<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>Klasifikasi Sasaran | SKK</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card">
            <input type="text" class="idunit" value="{{ $idunit }}" hidden>
            <div class="card-header d-flex">
                <table class="table">
                  <tr><td></td><td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahun }}</td></tr>
                  <tr><td>KEMENTRIAN LEMBAGA</td><td>(023) KEMENTRIAN PENDIDIKAN DAN KEBUDAYAAN</td></tr>
                  <tr><td>UNIT ORGANISASI</td><td>(17) DITJEN PENDIDIKAN TINGGI</td></tr>
                  <tr><td>PTN/KOPERTIS</td><td>(690662) UNIVERSITAS SYIAH KUALA</td></tr>
                  <tr><td>UNIT KERJA</td><td> {{ session()->get('unitkerja_nama') }}</td></tr>
                  <tr><td>BPPTNBH</td><td>0</td></tr>
                  <tr><td>PTNBH</td><td class="nilai_ptnbh">0</td></tr>
                  <tr><td>Hibah/Kerja Sama</td><td>0</td></tr>
                  <tr><td>Total</td><td>0</td></tr>
                </table>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <h4 id="loading-msg">Sedang memuat data ...</h4>
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" style="border:2.5px solid black;">
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
                                <th>biaya satuan</th>
                                <th>total biaya</th>
                                <th>rpd</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl">
                            <tr>
                                <td>41</td>
                                <td class="non-apbn" colspan="8">Non APBN</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
@include('content.laporan.KLASIFIKASI.SASARAN.TIGA.scripttiga")
