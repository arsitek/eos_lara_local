@php
    $role = session("role");
@endphp
<div class="modal fade" id="modal-tanggapan" style="display: none" key="">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title fw-bold">TANGGAPAN</h4>
            <button class="btn btn-danger px-3 py-1" id="close-modal-tanggapan">close</button>
        </div>
        <div class="modal-body">
            <table class="table mb-0" id="tabel-rekap-tanggapan" style="font-size: 16px">
                <thead class="header">
                    <tr class="fw-bold" style="background-color: #f0f0f0;">
                        <th>Verifikator</th>
                        <th>Tanggapan Verifikator</th>
                        <th>Tanggapan Operator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="bodyTblTanggapan">
                    @php
                        $isEditable = in_array($role, ['Pimpinan Unit', 'Verifikator RKAT', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Pimpinan Unit</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Pimpinan Unit"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Pimpinan Unit"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiPimpinanUnit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        $isEditable = in_array($role, ['Verifikator Keuangan', 'Verifikator RKAT', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Keuangan</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Verifikator Keuangan"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Verifikator Keuangan"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiKeu">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        $isEditable = in_array($role, ['Verifikator Aset', 'Verifikator RKAT', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Aset</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Verifikator Aset"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Verifikator Aset"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiAset">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        $isEditable = in_array($role, ['Verifikator RKAT', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>RKAT</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Verifikator RKAT"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Verifikator RKAT"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm mb-4 mt-3 btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiTim">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        $isEditable = in_array($role, ['Pimpinan USK','Verifikator RKAT', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Pimpinan USK</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Pimpinan USK"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Pimpinan USK"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiPimpinanUniv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        // Role SPI diberi ruang tanggapan terpisah dari verifikator lain.
                        $isEditable = in_array($role, ['Pengawasan Internal', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Pengawasan Internal</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Pengawasan Internal"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Pengawasan Internal"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Simpan
                                </button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiSpi">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    Setujui
                                </button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        // Auditor memakai field verifikasi SPI yang sama sesuai alur SPI.
                        $isEditable = in_array($role, ['Auditor', 'superadmin']) ? 'true' : 'false';
                        $isOpEditable = in_array($role, ['operator', 'superadmin']) ? 'true' : 'false';
                    @endphp
                    <tr>
                        <td>Auditor</td>
                        <td contenteditable="{{ $isEditable }}" class="tanggapan" jenis="Auditor"></td>
                        <td contenteditable="{{ $isOpEditable }}" class="tanggapan" jenis="Op Auditor"></td>
                        <td>
                            @if ( $isEditable )
                            <div class="btn-group">
                                <button class="btn btn-primary mb-4 mt-3 btn-sm btn_simpanTanggapan">Simpan</button>
                                <button class="mx-1 btn btn-info mb-4 mt-3 btn-sm btn_setujuiTanggapan" jenis="verifikasiSpi">Setujui</button>
                            </div>
                            @else
                                <span class="text-muted text-sm bg-danger text-white px-2 py-1">Tidak dapat memberi tanggapan</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

