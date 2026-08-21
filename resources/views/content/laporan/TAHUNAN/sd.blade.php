<div class="card sd" style="display: none">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title sd-title">Lingkup Universitas Syiah Kuala</h3>
        <button class="btn btn-primary p-1 toggle-btn">−</button>
    </div>
    <div class="card-body">
        @include("COMPONENTS.loader")
        <div class="table-responsive">
            <table id="tabel-sd" class="table mb-0 table-bordered" style="font-size:13px">
                <thead class="header">
                    <tr>
                        <th class="align-middle">CODEBASE</th>
                        <th class="align-middle">URAIAN</th>
                        <th class="align-middle">PROYEKSI PENERIMAAN ANGGARAN</th>
                        <th class="align-middle">REALISASI PENERIMAAN</th>
                        <th class="align-middle">PAGU ALOKASI</th>
                        <th class="align-middle">ANGGARAN TERPETAKAN</th>
                        <th class="align-middle">REALISASI ANGGARAN</th>
                        <th class="align-middle">SISA ANGGARAN</th>
                    </tr>
                </thead>
                <tbody class="body-tbl-sd">
                </tbody>
            </table>
        </div>
    </div>
</div>
