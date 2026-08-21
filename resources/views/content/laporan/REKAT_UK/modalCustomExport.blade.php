<div class="modal fade" id="modal-custom-export" style="display: none">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-light text-dark">
            <h4 class="modal-title fw-bold">Custom Export</span></h4>
            <button class="btn btn-danger px-3 py-1" id="close-modal-info">close</button>
        </div>
        <div class="modal-body">
            <div class="container mt-5">
                <div>
                    <select id="jenisCustom" class="select2">
                        <option value="">Pilih jenis export</option>
                        <option value="idrekat">Berdasarkan ID Rekat</option>
                    </select>
                </div>
                <div class="mt-4" id="containerCustomExport">
                    <div class="alert alert-info" id="alertCustomExport" style="display: none">
                        Jenis export tidak dikenali
                    </div>
                    <div class="basedOnIdRekat" style="display: none">
                        <select class="select2" multiple="multiple" id="selectIdRekat" style="width: 100%">
                            <option>Pilih ID Rekat</option>
                        </select>
                    </div>
                    <button class="btn btn-primary mt-4 w-100" id="btnFilterExport">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1.5em" height="1.5em" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                        </svg>
                        Filter Export
                    </button>
                </div>
            </div>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

