<div class="modal fade" id="modal-status" style="display: none">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title fw-bold">STATUS KEGIATAN</h4>
            <button class="btn btn-danger px-3 py-1" id="close-modal-status">close</button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0 fs-6 fw-semibold judulKegiatan"></h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="list-group list-group-flush">
                                <span class="d-inline-block px-3 py-1 text-white bg-primary fs-7 " style="border-radius: 15px; width: fit-content;">Kelengkapan Data</span>
                                 {{-- Verifikator Pimpinan Unit --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupTOR">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z" clip-rule="evenodd" />
                                            <path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" />
                                        </svg>
                                        <span class="fs-6">Kelengkapan TOR</span>
                                    </div>
                                </div>
                                <span class="d-inline-block px-3 py-1 text-white bg-primary fs-7" style="border-radius: 15px; width: fit-content;">Verifikator</span>
                                {{-- Verifikator Pimpinan Unit --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupPimpinanUnit">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                                          </svg>
                                        <span class="fs-6">Verifikasi Pimpinan Unit</span>
                                        <div class="w-100 text-wrap fs-7 mt-1 d-none text-muted containerTanggapanPimpinanUnit">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanPimpinanUnit">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Verifikator RKAT --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupRKAT">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                                            <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                                        </svg>
                                        <span class="fs-6">Verifikasi RKAT</span>
                                        <div class="w-100 text-wrap d-none fs-7 mt-1 text-muted containerTanggapanRKAT">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanRKAT">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Verifikator Keuangan --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupKeu">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="fs-6">Verifikasi Keuangan</span>
                                        <div class="w-100 text-wrap d-none fs-7 mt-1 text-muted containerTanggapanKeu">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanKeu">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Verifikator Aset --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupAset">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M3.375 3C2.339 3 1.5 3.84 1.5 4.875v.75c0 1.036.84 1.875 1.875 1.875h17.25c1.035 0 1.875-.84 1.875-1.875v-.75C22.5 3.839 21.66 3 20.625 3H3.375Z" />
                                            <path fill-rule="evenodd" d="m3.087 9 .54 9.176A3 3 0 0 0 6.62 21h10.757a3 3 0 0 0 2.995-2.824L20.913 9H3.087Zm6.163 3.75A.75.75 0 0 1 10 12h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="fs-6">Verifikasi Aset</span>
                                        <div class="w-100 text-wrap d-none fs-7 mt-1 text-muted containerTanggapanAset">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanAset">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Verifikator Pimpinan Univ --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupPimpinanUniv">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M11.584 2.376a.75.75 0 0 1 .832 0l9 6a.75.75 0 1 1-.832 1.248L12 3.901 3.416 9.624a.75.75 0 0 1-.832-1.248l9-6Z" />
                                            <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h.75v-9.918a.75.75 0 0 1 .634-.74A49.109 49.109 0 0 1 12 9c2.59 0 5.134.202 7.616.592a.75.75 0 0 1 .634.74Zm-7.5 2.418a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Zm3-.75a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0v-6.75a.75.75 0 0 1 .75-.75ZM9 12.75a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Z" clip-rule="evenodd" />
                                            <path d="M12 7.875a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" />
                                        </svg>
                                        <span class="fs-6">Verifikasi Pimpinan Univ</span>
                                        <div class="w-100 text-wrap d-none fs-6 mt-1 text-muted containerTanggapanPimpinanUniv">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanPimpinanUniv">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Verifikator KAI --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupKAI">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                        </svg>
                                        <span class="fs-6">Reviu SPI</span>
                                        <div class="w-100 text-wrap d-none fs-6 mt-1 text-muted containerTanggapanKAI">
                                            <div style="width: 450px">
                                                <span class="fw-bold">*Tanggapan: </span> <span class="tanggapanKAI">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <span class="mt-2 d-inline-block px-3 py-1 text-white bg-primary fs-7" style="border-radius: 15px; width: fit-content;">Revisi & Validasi</span>
                                {{-- Semula Menjadi --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupSemulaMenjadi d-none">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="fs-6">Revisi/Validasi</span>
                                    </div>
                                </div>

                                <span class="mt-2 d-inline-block px-3 py-1 text-white bg-primary fs-7" style="border-radius: 15px; width: fit-content;">Status</span>
                                {{-- Terpaketkan --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupPaket">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M2.273 5.625A4.483 4.483 0 0 1 5.25 4.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0 0 18.75 3H5.25a3 3 0 0 0-2.977 2.625ZM2.273 8.625A4.483 4.483 0 0 1 5.25 7.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0 0 18.75 6H5.25a3 3 0 0 0-2.977 2.625ZM5.25 9a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3v-6a3 3 0 0 0-3-3H15a.75.75 0 0 0-.75.75 2.25 2.25 0 0 1-4.5 0A.75.75 0 0 0 9 9H5.25Z" />
                                        </svg>
                                        <span class="fs-6">Terpaketkan</span>
                                    </div>
                                </div>

                                {{-- Ter RUP --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupRUP">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path fill-rule="evenodd" d="M2.25 2.25a.75.75 0 0 0 0 1.5H3v10.5a3 3 0 0 0 3 3h1.21l-1.172 3.513a.75.75 0 0 0 1.424.474l.329-.987h8.418l.33.987a.75.75 0 0 0 1.422-.474l-1.17-3.513H18a3 3 0 0 0 3-3V3.75h.75a.75.75 0 0 0 0-1.5H2.25Zm6.54 15h6.42l.5 1.5H8.29l.5-1.5Zm8.085-8.995a.75.75 0 1 0-.75-1.299 12.81 12.81 0 0 0-3.558 3.05L11.03 8.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l2.47-2.47 1.617 1.618a.75.75 0 0 0 1.146-.102 11.312 11.312 0 0 1 3.612-3.321Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="fs-6">RUP</span>
                                    </div>
                                </div>

                                {{-- Terposting API --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupPosting">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                                        </svg>
                                        <span class="fs-6">Terposting ke SIMKEU</span>
                                    </div>
                                </div>

                                {{-- Ter Amprah --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupAmprah">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                            <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" />
                                            <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                                        </svg>
                                        <span class="fs-6">Amprah</span>
                                    </div>
                                </div>

                                {{-- Terrealisasi --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupRealisasi">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-3" width="2rem" height="2rem">
                                            <path d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 0 1-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004ZM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 0 1-.921.42Z" />
                                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v.816a3.836 3.836 0 0 0-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 0 1-.921-.421l-.879-.66a.75.75 0 0 0-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 0 0 1.5 0v-.81a4.124 4.124 0 0 0 1.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 0 0-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 0 0 .933-1.175l-.415-.33a3.836 3.836 0 0 0-1.719-.755V6Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="fs-6">Realisasi</span>
                                    </div>
                                </div>
                                {{-- Tanggal Realisasi --}}
                                <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3 groupTglBayar">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="2rem" height="2rem">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                                        </svg>
                                        <span class="fs-6">Tanggal Bayar</span>
                                    </div>
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
