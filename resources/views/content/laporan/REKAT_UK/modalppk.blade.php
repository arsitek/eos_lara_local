<div class="modal fade" id="modal-ppk" style="display: none">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-light text-dark">
            <h4 class="modal-title fw-bold">Detail Pejabat Pembuat Komitmen</span></h4>
            <span id="idRekat"></span>
            <button class="btn btn-danger px-3 py-1" id="close-modal-ppk">close</button>
        </div>
        <div class="modal-body">
            <div class="container mt-5">
                <div id="divPpk">
                    <div class="container mt-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-center text-primary mb-4">Form Pilihan PPK & BPP</h5>

                                <p class="text-muted" id="item-coa"></p>
                                <p class="d-none" id="jenisRab"></p>
                                <p class="d-none" id="idRab"></p>
                                <p class="d-none" id="jumlahBiaya"></p>
                                <p class="d-none" id="idJenisBelanja"></p>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label fw-bold">PPK</label>
                                    <div class="col-sm-9">
                                        <select style="width: 100%;" name="ppk" class="form-select select2 ppk">
                                            <option value="">Silahkan Pilih</option>
                                            @foreach ($ppk as $item)
                                                @if($item->jenis == "PPK")
                                                    <option value="{{ $item->nip }}">{{ $item->nama_pejabat }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label class="col-sm-3 col-form-label fw-bold">BPP</label>
                                    <div class="col-sm-9">
                                        <select style="width: 100%;" name="bpp" class="form-select select2 bpp">
                                            <option value="">Silahkan Pilih</option>
                                            @foreach ($ppk as $item)
                                                @if($item->jenis == "BPP")
                                                    <option value="{{ $item->nip }}">{{ $item->nama_pejabat }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary" id="simpanPpk">SIMPAN</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

