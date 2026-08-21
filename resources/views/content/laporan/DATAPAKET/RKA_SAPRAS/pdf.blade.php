<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<title>LAPORAN | RKA Sapras</title>
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
                        <td>{{ $nama_ppk ?? '-'}}</td>
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
                    <table id="tabel-rekat-unit" class="table mb-0" border="3" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th class="align-middle text-center">CODEBASE</th>
                                <th class="align-middle text-center">SD/KRO/RO/KP/SK/PIC/DK/COA/SBM</th>
                                <th class="align-middle text-center">SPESIFIKASI</th>
                                <th class="align-middle text-center">TOTAL BIAYA</th>
                                <th class="align-middle text-center">RPD</th>
                                <th class="align-middle text-center">PROSES</th>
                                <th class="align-middle text-center">REAL</th>
                                <th class="align-middle text-center">REV</th>
                                <th class="align-middle text-center">SISA</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit">

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
@include('HELPERS.report_function')
@include("COMPONENTS.scriptLoader")
<script>
    let rupiah = (number) => {
        const formattedValue = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number)
        return formattedValue.replace(/\./g, ',')
    }
    let rupiahToNumber = (rupiahString) => {
        const numericString = rupiahString.replace(/[^\d.]/g, '')
        const numericValue = parseFloat(numericString.replace(/,/g, ''))

        return isNaN(numericValue) ? null : numericValue;
    }
    $(document).ready(function() {
        const url = new URL(window.location.href)
        const kodeSd = url.searchParams.get("sumberdana")
        const ppk = url.searchParams.get("ppk")
        const status = url.searchParams.get("status")
        $('.body-tbl-unit').children().remove()
        generateRKA( null, kodeSd, null, status, `/laporan/rktunit/get/null/${kodeSd}`, '#loader-div', '.body-tbl-unit', 'Memuat data RKA...Mohon menunggu', false, false, [], ppk )
    })
</script>
