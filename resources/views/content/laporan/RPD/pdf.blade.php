<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<title>Laporan | RPD</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan" style="font-family: 'IBM Plex Sans', sans-serif;">
    <div class="col-lg-12">
        <div class="card">
            <input type="text" class="idunit" value="{{ $idunit->idunit }}" hidden>
            <div class="mb-5 loading-div">
                <h4 style="font-weight:bold; display: inline; margin-left:10px;" class="loading-msg">MEMUAT DATA RPD... MOHON MENUNGGU</h4>
            </div>
            <div class="card-header d-flex">
                <table class="table">
                    <tr>
                        <td></td>
                        <td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahunAngka }}</td>
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
                        <td id="unitkerja">{{ $idunit->nama }}</td>
                        @else
                        <td> {{ session()->get('unitkerja_nama') }}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Rencana Penarikan Dana</td>
                        <td class="rpd"></td>
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
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" border="3" style="font-size: 13px">
                        <thead>
                            <tr class="bg-dark">
                                <th class="text-light align-middle text-center">CODEBASE</th>
                                <th class="text-light align-middle text-center">URAIAN</th>
                                <th class="text-light align-middle text-center">TOTAL BIAYA</th>
                                <th class="text-light align-middle text-center">REALISASI</th>
                                <th class="text-light align-middle text-center">SISA</th>
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
@include("HELPERS.report_function")
@include("COMPONENTS.scriptLoader")
@include('content.laporan.RPD.script")
<script>
    $(document).ready(function () {
        // 📦 Init variable
        const bodyTbl    = $('.body-tbl')
        let searchParams = new URLSearchParams(window.location.search)
        const idunit     = searchParams.get("idunit")
        const sd         = searchParams.get("sd")
        const rpd        = searchParams.get("rpd")
        if ( idunit == null || sd == null || rpd == null )
            return tata.error("⛔ Error", "Unit kerja, sumberdana, dan rpd tidak valid")
        $(".rpd").text(rpd)

        const getData   = window.laporan?.rpd?.methods?.getData
        const renderData = window.laporan?.rpd?.methods?.renderData

        if ( !getData || !renderData ) {
            return tata.error("⛔ Error", "Fungsi getData/renderData tidak tersedia")
        }

        getData( idunit, sd, rpd ).then( data => {
            if ( data ) {
                renderData( data )
            }
        })
    })

</script>
