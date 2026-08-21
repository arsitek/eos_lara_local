<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

<title>Laporan | Usulan Revisi</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
<div class="row mt-5" id="container-laporan">
    <div class="col-lg-12">
        <div class="card">
            <div class="alert alert-danger alert-dismissible mt-2 statusError" style="display: none">
                Data tidak ditemukan.
            </div>
            <div class="statusLoading" style="display: none">
                <h4>Data sedang dimuat ...</h4>
            </div>
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
                        <td>{{ $namaUnit ?? '-' }}</td>
                    </tr>
                    @foreach( $sumberdana as $itemSd )
                    <tr>
                        <td style="width: 400px">{{ $itemSd->sumberdana}}</td>
                        <td class="total-{{$itemSd->kd_sumberdana}}">Rp 0</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>Total</td>
                        <td class="totalSumberdana">0</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0" border="3" style="font-size:13px" id="tabel-usulan-revisi" style="font-size:13px; font-family: 'IBM Plex Sans'">
                        <thead>
                            <tr style="text-transform:uppercase; text-align: center">
                                <th class="align-middle text-center">codebase</th>
                                <th class="align-middle text-center">Nilai Existing</th>
                                <th class="align-middle text-center">Nilai Usulan</th>
                                <th class="align-middle text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script>
    /**
     * Set PDF mode flag before loading main script
     */
    const isPdfView = true; // This tells the script we're in PDF mode
    
    /**
     * Utility function: Membuat atau update Map node
     * @param {Map} map - Map object
     * @param {String} key - Key untuk node
     * @param {Function} createNode - Function untuk membuat node baru
     * @returns {Object} Node yang dibuat atau sudah ada
     */
    const createOrUpdateMap = (map, key, createNode) => {
        if (!map.has(key)) {
            map.set(key, createNode());
        }
        return map.get(key);
    };

    /**
     * Utility function: Format number ke format Rupiah Indonesia
     * @param {Number} number - Angka yang akan diformat
     * @returns {String} Format rupiah (contoh: Rp 1,000,000)
     */
    const rupiah = (number) => {
        const formattedValue = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number);
        
        // Ganti titik dengan koma untuk separator ribuan
        return formattedValue.replace(/\./g, ',');
    };
</script>
@include('content.laporan.USULANREVISI.script')