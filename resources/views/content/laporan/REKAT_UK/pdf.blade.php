<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<title>Laporan | RKA</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <input type="text" class="idunit" value="{{ $idunit }}" hidden>
            <h4 class="loading-msg"></h4>
            @if( $tahunAngka === "2024" )
            @include('content.laporan.REKAT_UK.card24")
            @elseif( in_array( $tahunAngka, ["2025", "2026"]) )
            @include('content.laporan.REKAT_UK.card25")
            @endif
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabel-rekat" class="tabel-rekat table mb-0" border="3" style="font-size:13px; font-family: 'IBM Plex Sans'">
                        <thead>
                            <tr style="text-transform:uppercase; text-align: center">
                                <th class="align-middle">codebase</th>
                                <th class="align-middle" style="width: 350px">SD.KRO.RO.KP.SK.PIC.DK.COA.SBM</th>
                                <th class="align-middle" style="width: 150px">Spesifikasi</th>
                                <th class="align-middle" style="width: 200px">total biaya</th>
                                <th class="align-middle">rpd</th>
                                <th class="align-middle">proses</th>
                                <th class="align-middle">real</th>
                                <th class="align-middle">rev</th>
                                <th class="align-middle">sisa</th>
                            </tr>
                        </thead>
                        <tbody class="body-tbl-unit">
                        </tbody>
                    </table>
                </div>
                @if( !$pttd )
                *Data tanda tangan belum tersedia. Mohon untuk melengkapi data tersebut terlebih dahulu pada perjanjian kinerja.
                @endif
                <div class="d-flex justify-content-between px-3 mt-5 containerTtd" style="margin-inline: 130px;">
                    <div class="">
                        <span>{{ $pttd->PP_JBT ?? "-"}}</span><br><br><br><br><br>
                        <span>{{ $pttd->PP_REKTOR ?? "Pimpinan universitas tidak ditemukan."}}</span><br>
                        <span>{{ $pttd->PP_NIP ?? "-"}}</span>
                    </div>
                    <div style="margin-right: 30px">
                        <span>{{ ( str_replace(",", "", $pttd->PP_TPT ?? "-") ) .", " . ($pttd->PP_TGL ?? "-")  }}</span><br>
                        <span>{{ $pttd->PK_JBT ?? "-"}}</span><br><br><br><br>
                        <span>{{ $pttd->PK_NAMA ?? "Pimpinan unitkerja tidak ditemukan."}}</span><br>
                        <span>{{ $pttd->PK_NIP ?? "-"}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
@include("HELPERS.export")
@include('content.laporan.REKAT_UK.script_final")
@include("HELPERS.report_function")
@include("COMPONENTS.scriptLoader")
<script>
    $(document).ready(function () {
        window.laporan = window.laporan || {}
        window.laporan.rka = window.laporan.rka || {}
        window.laporan.rka.methods = window.laporan.rka.methods || {}

        const urlSplit   = window.location.href.split("/")
        const idunit     = urlSplit[6]
        let sumberdana   = urlSplit[7]
        // end sumberdana string when it found ? char
        if ( sumberdana.includes("?") )
            sumberdana = sumberdana.split("?")[0]

        const url        = new URL(window.location.href)
        const filter     = url.searchParams.get("filterdata")
        const backup     = url.searchParams.get("backup")
        const idRekats   = url.searchParams.get("idrekats") ?? ""
        const tahun      = "{{$tahunAngka}}"

        const allowedFilters = ["realisasi", "!realisasi", "", "final", "!verifikasi", "draft"];
        if (!allowedFilters.includes(filter)) {
            setLoaderText("Kategori filter tidak sesuai");
            showLoader();
            return;
        }

        if ( "final" === filter ) {
            showLoader()
            setLoaderText("Memproses data RKA ... Mohon menunggu")
            window.laporan.rka.methods.getBaseData( idunit, sumberdana, filter, backup )
                .then( data => {
                    window.laporan.rka.methods.generateRkaFinal( data.data, true )
                }).catch( err => {
                    setLoaderText("Terjadi kesalahan.")
                    showLoader()
                } )
            return
        }
        generateRKA(idunit, sumberdana, backup, filter, `/laporan/rktunit/get/${idunit}/${sumberdana}`,'.loading-msg', '.body-tbl-unit', 'Sedang Memuat Data...', false, false, idRekats)

    })


</script>
