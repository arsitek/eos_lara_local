<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<title>Lampiran | RKAT</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex">
                <table class="table">
                    <tr>
                        <td></td>
                        <td class="fw-bold">RINCIAN KERTAS KERJA SATKER T.A. {{ $tahunAngka }}</td>
                    </tr>
                    <tr>
                        <td>KEMENTRIAN LEMBAGA</td>
                        <td>(023) KEMENTRIAN PENDIDIKAN TINGGI SAINS DAN TEKNOLOGI</td>
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
                        <td>{{ $unitkerja->nama ?? '-' }}</td>
                    </tr>
                    @foreach( $listKodeSd as $itemSd )
                    <tr>
                        <td style="width: 400px">{{ $itemSd->sumberdana}}</td>
                        <td class="totalPtnbh-{{$itemSd->kd_sumberdana}}">Rp 0</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>Total</td>
                        <td class="totalSumberdana">0</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <div class="alert alert-danger alert-dismissible mt-2 statusError" style="display: none">
                    Data tidak ditemukan.
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" border="3" style="font-size:13px" id="tabel-rkat" style="font-size:13px; font-family: 'IBM Plex Sans'">
                        <thead>
                            <tr style="text-transform:uppercase; text-align: center">
                                <th class="align-middle">KODEFIKASI</th>
                                <th class="align-middle">URAIAN</th>
                                <th class="align-middle">KUANTITAS</th>
                                <th class="align-middle">SATUAN</th>
                                <th class="align-middle">DURASI</th>
                                <th class="align-middle">SATUAN</th>
                                <th class="align-middle">VOLUME</th>
                                <th class="align-middle">SATUAN</th>
                                <th class="align-middle">ANGGARAN BIAYA</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                @if( !$pttd )
                *Data tanda tangan belum tersedia. Mohon untuk melengkapi data tersebut terlebih dahulu pada halaman perjanjian kinerja.
                @endif
                <div class="d-flex justify-content-between px-3 mt-5 containerTtd" style="margin-inline: 130px;">
                    <div class="">
                        <span>{{ $pttd->PP_JBT ?? "-"}}</span><br><br><br><br><br>
                        <span>{{ $pttd->PP_REKTOR ?? "Pimpinan universitas tidak ditemukan."}}</span><br>
                        <span>{{ $pttd->PP_NIP ?? "-"}}</span>
                    </div>
                    <div style="margin-right: 30px">
                        <span>{{ ( str_replace(",", "", $pttd->PP_TPT ?? "-") ) .", ". ( $pttd->PP_TGL ?? "-" ) }}</span><br>
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
@include('content.laporan.RKAT.LAMPIRAN.script")
@include("COMPONENTS.scriptLoader")
<script>
    $(document).ready(function () {

        const url        = new URL(window.location.href)
        const unitParams = url.searchParams.get("idunit") ?? ""
        const sdParams   = url.searchParams.get("sumberdana") ?? ""

        if ( unitParams && sdParams ) {
            window.laporan.rkat.methods.getData().finally( () => {
                window.laporan.rkat.methods.buildData( window.laporan.rkat.constants.DATA ).then( data => {
                    window.laporan.rkat.methods.generateData( data )
                }).catch( err => {
                    console.error( err )
                    tata.error("⛔ Error", "Terjadi kesalahan saat memproses data")
                })
                removeLoader()
            })
        }
    })
</script>
