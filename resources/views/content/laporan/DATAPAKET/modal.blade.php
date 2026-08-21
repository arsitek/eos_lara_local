<div class="modal fade" id="modal-paket" style="display: none">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Detail Paket</h4>
            <button class="close-modal btn btn-danger px-3 py-1">close</button>
        </div>
        <div class="modal-body">
            <div class="card-body">
                <!-- Row untuk promis-->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat">Provinsi/Kabupaten/Kota</label><br>
                            <input type="text" class="form-control" id="alamat" name="alamat">
                        </div>
                        <!-- /. end Provinsi/Kabupaten/Kota -->
                        <div class="form-group">
                            <label for="detailLokasi">Detail Lokasi</label><br>
                            <input type="text" class="form-control" id="detailLokasi" name="detailLokasi">
                        </div>
                        <!-- /.end Detail Lokasi-->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahunAnggaran">Tahun Anggaran</label><br>
                            <input type="number" class="form-control" id="tahunAnggaran" name="tahunAnggaran">
                        </div>
                        <!-- /. end Tahun Anggaran -->
                        <div class="form-group">
                            <label for="uraianPekerjaan">Uraian Pekerjaan</label><br>
                            <input type="text" class="form-control" id="uraianPekerjaan" name="uraianPekerjaan">
                        </div>
                        <!-- /.end Uraian Pekerjaan-->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="spekPekerjaan">Spesifikasi Pekerjaan</label><br>
                            <input type="text" class="form-control" id="spekPekerjaan" name="spekPekerjaan">
                        </div>
                        <!-- /. end Spesifikasi Pekerjaan -->
                        <div class="form-group">
                            <label for="volumePekerjaan">Volume Pekerjaan</label><br>
                            <input type="text" class="form-control" id="volumePekerjaan" name="volumePekerjaan">
                        </div>
                        <!-- /.end Volume Pekerjaan-->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="satuan">Satuan</label><br>
                            <input type="text" class="form-control" id="satuan" name="satuan">
                        </div>
                        <!-- /. end Satuan -->
                        <div class="form-group">
                            <label for="produkDalamNegeri">Produk dalam Negeri</label><br>
                            <select name="produkDalamNegeri" id="produkDalamNegeri" class="form-control">
                                <option value="" selected>Pilih Produk dalam Negeri</option>
                                <option value="YA">YA</option>
                                <option value="TIDAK">TIDAK</option>
                            </select>
                        </div>
                        <!-- /.end Produk dalam Negeri-->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="praDIPA">Pra DIPA</label><br>
                            <select name="praDIPA" id="praDIPA" class="form-control">
                                <option value="YA">YA</option>
                                <option value="TIDAK">TIDAK</option>
                            </select>
                        </div>
                        <!-- /.end Pra dipa-->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="multiYears">Izin Tahun Jamak</label><br>
                            <input type="text" name="multiYears" id="multiYears" class="form-control">
                        </div>
                        <!-- /. end Izin Tahun Jamak -->
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="metodePengadaan">Metode Pengadaan</label><br>
                                <select name="metodePengadaan" id="metodePengadaan" class="form-control">
                                    <option value="">Pilih Metode Pengadaan</option>
                                    <option value="Pembelian Langsung">Pembelian Langsung</option>
                                    <option value="Pengadaan Langsung">Pengadaan Langsung</option>
                                    <option value="Penunjukan Langsung">Penunjukan Langsung</option>
                                    <option value="Quotation">Quotation</option>
                                    <option value="Tender">Tender</option>
                                </select>
                            </div>
                        </div>
                        <!-- /.end Metode Pengadaan-->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jenisPengadaan">Jenis Pengadaan</label><br>
                                <select name="jenisPengadaan" id="jenisPengadaan" class="form-control">
                                    <option value="">Pilih Jenis Pengadaan</option>
                                    <option value="Barang Langsung">Barang Langsung</option>
                                    <option value="Konstruksi Langsung">Konstruksi Langsung</option>
                                    <option value="Jasa Konsultasi">Jasa Konsultasi</option>
                                    <option value="Jasa Lainnya">Jasa Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <!-- /.end Jenis Pengadaan-->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="usahaKecil">Usaha Kecil/Non Kecil</label><br>
                                <select name="usahaKecil" id="usahaKecil" class="form-control">
                                    <option value="">Pilih Usaha Kecil/Non Kecil</option>
                                    <option value="kecil">Kecil</option>
                                    <option value="non kecil">Non Kecil</option>
                                </select>
                            </div>
                        </div>
                        <!-- /. end usaha kecil/non kecil -->
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rencanaPemilihanPenyediaStart">Rencana Pemilihan Penyedia*</label><br>
                            <input type="date" id="rencanaPemilihanPenyediaStart" name="rencanaPemilihanPenyediaStart" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="rencanaPelaksanaanKontrakStart">Rencana Pelaksanaan Kontrak*</label><br>
                            <input type="date" id="rencanaPelaksanaanKontrakStart" name="rencanaPelaksanaanKontrakStart" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rencanaPemilihanPenyediaEnd">Sampai*</label>
                            <input type="date" id="rencanaPemilihanPenyediaEnd" name="rencanaPemilihanPenyediaEnd" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="rencanaPelaksanaanKontrakEnd">Sampai*</label>
                            <input type="date" id="rencanaPelaksanaanKontrakEnd" name="rencanaPelaksanaanKontrakEnd" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rencanaPemanfaatanBarangStart">Rencana Pemanfaatan Barang*</label>
                            <input type="date" id="rencanaPemanfaatanBarangStart" name="rencanaPemanfaatanBarangStart" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rencanaPemanfaatanBarangEnd">Sampai</label>
                            <input type="date" id="rencanaPemanfaatanBarangEnd" name="rencanaPemanfaatanBarangEnd" class="form-control">
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
