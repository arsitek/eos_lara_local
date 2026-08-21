<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>Laporan | Analisis</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <h4 class="loading-msg"></h4>
            <div class="card-header d-flex">
                <table class="table">
                    <tr data-rka="true">
                        <td></td>
                        <td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahunAngka }}</td>
                    </tr>
                    <tr data-rka="true">
                        <td>KEMENTRIAN LEMBAGA</td>
                        <td>(023) KEMENTRIAN PENDIDIKAN DAN KEBUDAYAAN</td>
                    </tr>
                    <tr data-rka="true">
                        <td>UNIT ORGANISASI</td>
                        <td>(17) DITJEN PENDIDIKAN TINGGI</td>
                    </tr>
                    <tr data-rka="true">
                        <td>PTN/KOPERTIS</td>
                        <td>(690662) UNIVERSITAS SYIAH KUALA</td>
                    </tr>
                    <tr data-rka="true">
                        <td>UNIT KERJA</td>
                        <td> {{ $unitkerja->nama }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <table id="tabel-analisis" class="tabel-rekat table mb-0" style="border:2.5px solid black; table-layout: fixed">
                    <thead class="header">
                        <tr data-rka="true">
                            <th  style="width: 200px">codebase</th>
                            <th>SD.KRO.RO.KP.SK.DK</th>
                            <th>Tanggapan</th>
                        </tr>
                    </thead>
                    <tbody class="body-tbl-unit">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
@include("HELPERS.export")
@include('content.laporan.ANALISIS.script")
@include("HELPERS.report_function")
@include("COMPONENTS.scriptLoader")
<script>
    $(document).ready(function () {
        const currentUrl = new URL(window.location.href)
        const idunit     = currentUrl.searchParams.get("idunit")
        const sumberdana = currentUrl.searchParams.get("sumberdana")
        window.laporan          = window.laporan || {}
        window.laporan.analisis = window.laporan.analisis || {}
        // flag PDF mode so main script disables interactivity
        window.laporan.analisis.isPdf = true
        if ( idunit != null && sumberdana != null ){
            const tryInvoke = () => {
                if (window.laporan && window.laporan.analisis && window.laporan.analisis.methods && typeof window.laporan.analisis.methods.handleOnClickCari === 'function') {
                    window.laporan.analisis.methods.handleOnClickCari(idunit, sumberdana)
                    return true
                }
                return false
            }

            // immediate attempt
            if (!tryInvoke()) {
                // wait for up to ~3 seconds polling every 150ms
                let attempts = 0
                const maxAttempts = 20
                const timer = setInterval(() => {
                    attempts++
                    if ( tryInvoke() || attempts >= maxAttempts ) {
                        clearInterval(timer)
                        if (attempts >= maxAttempts) console.warn('handleOnClickCari not available after waiting')
                    }
                }, 150)
            }
        }
    })
</script>
