<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<title>Laporan | Proporsi Anggaran</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <h4 class="loading-msg"></h4>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabel-proporsi" class="table mb-0" style="border:2.5px solid black; font-family: 'IBM Plex Sans'">
                        <thead class="header">
                            <tr class="">
                                <th class="align-middle text-center">URAIAN</th>
                                <th class="align-middle text-center">PROPORSI BIAYA</th>
                                <th class="align-middle text-center">JUMLAH BIAYA</th>
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
@include("COMPONENTS.scriptLoader")
@include('content.laporan.PROPORSI.script")
<script>
    $( document ).ready(function () {

    })
</script>
