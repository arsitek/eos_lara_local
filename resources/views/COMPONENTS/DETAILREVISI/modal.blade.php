<style>
    /* Scoped professional modal styles */
    #modal-detail-revisi .modal-header {
        background: linear-gradient(90deg, #1f3a93 0%, #2e86de 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
        align-items: center;
    }
    #modal-detail-revisi .modal-title { font-weight: 600; font-size: 1.25rem; }
    #modal-detail-revisi .btn-danger { background: #e74c3c; border: none; }
    #modal-detail-revisi .modal-body { background: #f7f9fb; padding: 1.5rem; }
    #modal-detail-revisi .table-section { background: #ffffff; border-radius: 10px; padding: 1rem; box-shadow: 0 6px 18px rgba(31,58,147,0.07); margin-bottom: 1rem; }
    #modal-detail-revisi .table-section-title { font-weight: 600; margin-bottom: 0.75rem; color: #34495e; }
    #modal-detail-revisi .table { border-collapse: separate; border-spacing: 0; }
    #modal-detail-revisi .table thead th { background: #ecf2ff; color: #1f3a93; border: none; font-weight: 600; }
    #modal-detail-revisi .table tbody td { padding: 0.85rem; vertical-align: middle; border-top: 1px solid #eef3fb; color: #334e68; }
    #modal-detail-revisi .table tbody tr:hover { background: #fbfdff; }
    #modal-detail-revisi .metadata { color: #556b7a; font-size: 0.95rem; margin-top: 0.5rem; }
    @media (max-width: 768px) {
        #modal-detail-revisi .modal-dialog { max-width: 95%; }
        #modal-detail-revisi .modal-body { padding: 1rem; }
    }
</style>

<div class="modal fade" id="modal-detail-revisi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title fw-bold">Detail Revisi</h4>
            <button class="btn btn-danger px-3 py-1" style="width: 100px" id="close-modal-detail-revisi">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="30%" class="me-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Close
            </button>
        </div>
        <div class="modal-body">
            <div class="table-section semula">
                <div class="table-section-title">Detail Revisi Semula</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabel-detail-revisi-semula" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SPESIFIKASI</th>
                                <th>JUMLAH BIAYA</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="table-section menjadi">
                <div class="table-section-title">Detail Revisi Menjadi</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabel-detail-revisi-menjadi" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SPESIFIKASI</th>
                                <th>JUMLAH BIAYA</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <h4 class="mt-4" id="metadata"></h4>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

