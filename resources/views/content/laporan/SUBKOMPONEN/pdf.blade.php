<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>Rekap | Subkomponen</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <input type="text" class="idunit" value="{{ $idunit }}" hidden>
            <h4 class="loading-msg"></h4>
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
                        <td>UNIT KERJA</td>
                        @if( @session()->get('role') == "superadmin" )
                        <td id="unitkerja">{{ $unitkerja->nama ?? 'Semua unitkerja'}}</td>
                        @else
                        <td> {{ session()->get('unitkerja_nama') }}</td>
                        @endif
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
                    <h5 class="pdf-status">Sedang memuat data ...</h5>
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" border="3">
                        <thead class="text-uppercase">
                            <tr>
                                <th>codebase</th>
                                <th>SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th>total biaya</th>
                                <th>proses</th>
                                <th>real</th>
                                <th>sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit" style="font-size: 11px;">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
@include('content.laporan.SUBKOMPONEN.script")
@include("COMPONENTS.scriptLoader")
<script>
    $(document).ready(function () {
        const url        = new URL(window.location.href)
        const idunit     = url.searchParams.get("idunit")
        const sumberdana = url.searchParams.get("sumberdana")
        const filter     = url.searchParams.get("filterdata")
        window.laporan = window.laporan || {};
        window.laporan.subkomponen = window.laporan.subkomponen || {};
        window.laporan.subkomponen.methods = window.laporan.subkomponen.methods || {};
        window.laporan.subkomponen.methods.buildData( idunit, sumberdana, filter )
    });
    rupiah = (number) => {
        const formattedValue = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number)
        return formattedValue.replace(/\./g, ',')
    }
    rupiahToNumber = (rupiahString) => {
        const numericString = rupiahString.replace(/[^\d.]/g, '')
        const numericValue = parseFloat(numericString.replace(/,/g, ''))
        return isNaN(numericValue) ? null : numericValue
    }
</script>
