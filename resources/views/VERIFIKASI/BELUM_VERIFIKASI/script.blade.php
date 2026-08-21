<script>
    $(document).ready(function() {
        window.belumVerifikasi = {
            elements: {
                table: $('table#belumVerifikasiTable'),
                revisionFilterBtns: $('.btn-revision-filter'),
            },
            constants: {
                TATA_OPTIONS: {
                    duration: 5000,
                    animate: 'slide'
                },
                table: null,
                CURRENT_TYPE: 'SS', // Default revision type
                ROUTES: {
                    GET_DATA_BELUM_VERIFIKASI: "{{ route('v.getBelumVerifikasi') }}",
                    PROSES_REVISI_SASARAN_PENAMBAHAN: "{{ route('revisi.sasaran.verifikasi.penambahan') }}",
                    PROSES_REVISI_SASARAN_PENGURANGAN: "{{ route('revisi.sasaran.verifikasi.pengurangan') }}",
                    PROSES_REVISI_OUTPUT_PENAMBAHAN: "{{ route('revisi.output.verifikasi.penambahan') }}",
                    PROSES_REVISI_OUTPUT_PENGURANGAN: "{{ route('revisi.output.verifikasi.pengurangan') }}",
                    PROSES_VALIDASI_KEGIATAN_PENAMBAHAN: "{{ route('validasi.kegiatan.verifikasi.penambahan') }}",
                    PROSES_VALIDASI_KEGIATAN_PENGURANGAN: "{{ route('validasi.kegiatan.verifikasi.pengurangan') }}",
                    PROSES_BREAKDOWN_VERIFIKASI: "/revisi/breakdown/verifikasi",
                },
                MAPPING_STATUS : {
                    "Setuju": "menyetujui revisi ini",
                    "Tolak": "menolak revisi ini",
                },
                TIMEOUT: 10000,
                CSRF_TOKEN: "{{ csrf_token() }}",
                isLoading: false, // Prevent duplicate requests
            },
            methods: {
                bindFunctions: () => {
                    const { revisionFilterBtns } = window.belumVerifikasi.elements;
                    const { TATA_OPTIONS } = window.belumVerifikasi.constants;

                    // Handle revision type filter buttons
                    revisionFilterBtns.on('click', function() {
                        const btn = $(this);
                        const jenis = btn.data('jenis');

                        if (window.belumVerifikasi.constants.isLoading) return; // Prevent multiple clicks during loading

                        // Map OUTPUT to KK for backend compatibility
                        const jenisBackend = jenis === "OUTPUT" ? "KK" : jenis;

                        if ( jenis != "SS" && jenis != "RO" && jenis != "OUTPUT" && jenis != "BREAKDOWN" )
                            return tata.info('ℹ Info', 'Mohon maaf, fitur sedang dalam pengembangan.', TATA_OPTIONS);

                        // Update active state immediately untuk better UX
                        revisionFilterBtns.removeClass('active');
                        btn.addClass('active');
                        window.belumVerifikasi.constants.CURRENT_TYPE = jenisBackend;

                        // Load data with the selected type
                        window.belumVerifikasi.methods.getDataBelumVerifikasi(jenisBackend);
                    });

                    // Handle approve/reject buttons - event delegation untuk better performance
                    $(document).on("click", "button.btn-approve, button.btn-reject", handleOnApprove);
                },
                initializeDataTable: () => {
                    const { table } = window.belumVerifikasi.elements;
                    window.belumVerifikasi.constants.table = table.DataTable({
                        responsive: true,
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                        language: {
                            search: "Pencarian:",
                            lengthMenu: "Tampilkan _MENU_ data per halaman",
                            zeroRecords: "Data tidak ditemukan",
                            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                            infoEmpty: "Tidak ada data yang tersedia",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            }
                        },
                        order: [[0, 'asc']],
                        deferRender: true, // Render hanya baris yang terlihat
                        processing: true, // Show processing indicator
                        scroller: false, // Disable scroller untuk performa lebih baik
                        stateSave: false, // Disable state saving untuk load lebih cepat
                    });
                },
                getDataBelumVerifikasi: async ( jenis ) => {
                    const { ROUTES, TIMEOUT } = window.belumVerifikasi.constants;
                    const { table: dataTable } = window.belumVerifikasi.constants;
                    const { revisionFilterBtns } = window.belumVerifikasi.elements;

                    // Validate jenis parameter is provided
                    if (!jenis) {
                        console.warn('⚠️ No jenis parameter provided, skipping data fetch');
                        return;
                    }

                    // Auto-sync active tab with jenis parameter
                    revisionFilterBtns.removeClass('active');
                    const jenisDisplay = jenis === "KK" ? "OUTPUT" : jenis; // Map KK back to OUTPUT for display
                    revisionFilterBtns.filter(`[data-jenis="${jenisDisplay}"]`).addClass('active');
                    
                    // Update current type
                    window.belumVerifikasi.constants.CURRENT_TYPE = jenis;

                    // Performance: Start timer
                    const fetchStart = performance.now();

                    // Show loading state dengan optimasi DOM minimal
                    if (dataTable) {
                        // Performance: Suspend drawing untuk operasi lebih cepat
                        dataTable.clear();

                        // Lightweight loading indicator
                        const tbody = $('#belumVerifikasiTable tbody');
                        tbody.html(`
                            <tr class="loading-row">
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-inline-block">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="text-muted">Memuat data ${jenisDisplay}...</span>
                                    </div>
                                </td>
                            </tr>
                        `);
                    }

                    try {
                        const response = await $.ajax({
                            url: ROUTES.GET_DATA_BELUM_VERIFIKASI,
                            method: 'GET',
                            data: { jenis: jenis }, // Use the passed jenis parameter
                            dataType: 'JSON',
                            timeout: TIMEOUT,
                            cache: false, // Disable cache untuk data terbaru
                        });

                        const fetchEnd = performance.now();
                        console.log(`✅ Data fetched for ${jenis} in ${(fetchEnd - fetchStart).toFixed(2)}ms`);
                        
                        if ( response.success ) {
                            const { data } = response;
                            // Defer display
                            requestAnimationFrame(() => {
                                displayDataToTable( data );
                            });
                        } else {
                            console.log('⛔ Gagal mengambil data', response);
                            tata.error('⛔ Error', 'Gagal mengambil data', { duration: 5000, animate: 'slide' });
                        }
                    } catch (error) {
                        console.error('⛔ Error fetching data:', error);
                        return tata.error('⛔ Error','Terjadi kesalahan saat mengambil data', { duration: 5000, animate: 'slide' });
                    }
                },
                getMappingRoutes: () => {
                    const { ROUTES } = window.belumVerifikasi.constants;
                    return {
                        "SS": {
                            "Penambahan": ROUTES.PROSES_REVISI_SASARAN_PENAMBAHAN,
                            "Penambahan Item Coa": ROUTES.PROSES_REVISI_SASARAN_PENAMBAHAN,
                            "Pengurangan": ROUTES.PROSES_REVISI_SASARAN_PENGURANGAN,
                        },
                        "RO": {
                            "Penambahan": ROUTES.PROSES_REVISI_OUTPUT_PENAMBAHAN,
                            "Penambahan Item Coa": ROUTES.PROSES_REVISI_OUTPUT_PENAMBAHAN,
                            "Pengurangan": ROUTES.PROSES_REVISI_OUTPUT_PENGURANGAN,
                        },
                        "KK": {
                            "Penambahan": ROUTES.PROSES_VALIDASI_KEGIATAN_PENAMBAHAN,
                            "Penambahan Item Coa": ROUTES.PROSES_VALIDASI_KEGIATAN_PENAMBAHAN,
                            "Pengurangan": ROUTES.PROSES_VALIDASI_KEGIATAN_PENGURANGAN,
                        },
                        "BREAKDOWN": {
                            "Pergeseran RPD" : ROUTES.PROSES_BREAKDOWN_VERIFIKASI,
                            "Penambahan": ROUTES.PROSES_BREAKDOWN_VERIFIKASI,
                            "Penambahan Item Coa": ROUTES.PROSES_BREAKDOWN_VERIFIKASI,
                            "Pengurangan": ROUTES.PROSES_BREAKDOWN_VERIFIKASI,
                        },
                    };
                },
            }
        }
        const handleOnApprove = async (el) => {
            const target  = $(el.target).closest('button');
            const idSm     = target.data("id");
            const isDraft  = target.data("draft")
            const kodeSd   = target.data("sd")
            const idunit   = target.data("idunit")
            const jenis    = target.data("jenis")
            const status   = target.data("title")
            const jenisRab = target.data("jenisrab")

            const { methods } = window.belumVerifikasi;
            const { CSRF_TOKEN } = window.belumVerifikasi.constants;
            const { CURRENT_TYPE, TATA_OPTIONS, MAPPING_STATUS } = window.belumVerifikasi.constants;

            // Get mapping routes dynamically
            const MAPPING_ROUTES = methods.getMappingRoutes();

            const routes = MAPPING_ROUTES[CURRENT_TYPE] ? MAPPING_ROUTES[CURRENT_TYPE][jenis] : null;

            if ( !routes )
                return tata.error('⛔ Error', 'Terjadi kesalahan saat mencoba memproses data.', TATA_OPTIONS);

            const statusText = MAPPING_STATUS[status] || "melakukan aksi ini";
            Swal.fire({
                title: 'Konfirmasi Persetujuan',
                text: `Apakah Anda yakin ${statusText}? Anda tidak dapat membatalkan aksi ini.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setuju',
                cancelButtonText: 'Batal',
            }).then( async ( result ) => {
                if ( result.isConfirmed ) {
                    const $tr = target.closest('tr');
                    // Show loading state on button
                    const originalHtml = target.html();
                    target.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

                    try {
                        const response = await $.ajax({
                            url: routes,
                            method: 'POST',
                            data: { _token: CSRF_TOKEN, idunit, kd_sumberdana: kodeSd, id: idSm, idSm, kodeSd, isDraft, status, jenisRab },
                            dataType: 'JSON',
                        });
                        if ( response.success ) {
                            tata.success('✅ Sukses', response.message, TATA_OPTIONS);
                            target.prop('disabled', false).html(originalHtml);
                            $tr.remove();
                            // window.belumVerifikasi.methods.getDataBelumVerifikasi();
                        } else {
                            const message = response.message || 'Gagal memproses persetujuan';
                            console.log('⛔ Gagal processing approval:', response);
                            tata.error('⛔ Gagal', message, TATA_OPTIONS);
                            target.prop('disabled', false).html(originalHtml);
                        }
                    } catch (error) {
                        const message = error.responseJSON && error.responseJSON.message ? error.responseJSON.message : 'Terjadi kesalahan saat memproses persetujuan';
                        console.error('⛔ Error processing approval:', error);
                        target.prop('disabled', false).html(originalHtml);
                        return tata.error('⛔ Error',message, TATA_OPTIONS);
                    }
                }
            });
        }
        const displayDataToTable = ( data ) => {
            const { table: dataTable } = window.belumVerifikasi.constants;
            const startTime = performance.now();
            // Clear existing data
            dataTable.clear();

            // Batch process rows
            const rowsData = [];
            const dataLength = data.length;

            // Pre-compile template strings di luar loop untuk performa lebih baik
            for (let idx = 0; idx < dataLength; idx++) {
                const item = data[idx];
                const { id_sm, unit_kerja, jenis_validasi, jumlah_semula, jumlah_menjadi, jenis_rab, jenis_revisi,
                        itemCoaSemula, itemCoaMenjadi, spekSemula, spekMenjadi, rpdSemula, rpdMenjadi,
                        coaMenjadi, coaSemula, namaUnit, sd, sumberdana, is_draft } = item;
                
                const num          = idx + 1;
                const coaSem       = coaSemula ? coaSemula.replaceAll('~~~', ' | ') : '-';
                const coaMenj      = coaMenjadi ? coaMenjadi.replaceAll('~~~', ' | ') : '-';
                const spekSem      = spekSemula ? spekSemula.replaceAll('~~~', ' ') : '-';
                const spekMenj     = spekMenjadi ? spekMenjadi.replaceAll('~~~', ' ') : '-';
                const jmlSemula    = jumlah_semula ? rupiah(jumlah_semula) : '-';
                const jmlMenjadi   = jumlah_menjadi ? rupiah(jumlah_menjadi) : '-';
                const jenisValid   = jenis_validasi || '-';
                const namaUnitSafe = namaUnit || '-';
                const jenisRabSafe = jenis_rab || '-';

                rowsData.push([
                    num,
                    `${sd} | ${sumberdana}`,
                    namaUnitSafe,
                    `<div>${jenisValid}</div>`,
                    `<div class="text-muted small">Semula:</div><div class="mb-1">${coaSem}</div><div class="text-muted small">Menjadi:</div><div class="text-primary font-weight-bold">${coaMenj}</div>`,
                    `<div class="text-muted small">Semula:</div><div class="mb-1">${itemCoaSemula || '-'} (${rpdSemula || '-'})</div><div class="text-muted small">Menjadi:</div><div class="text-primary font-weight-bold">${itemCoaMenjadi || '-'} (${rpdMenjadi || '-'})</div>`,
                    `<div class="text-muted small">Semula:</div><div class="mb-1">${spekSem}</div><div class="text-muted small">Menjadi:</div><div class="text-primary font-weight-bold">${spekMenj}</div>`,
                    `<div class="text-muted small">Semula:</div><div class="mb-1">${jmlSemula}</div><div class="text-muted small">Menjadi:</div><div class="text-primary font-weight-bold">${jmlMenjadi}</div>`,
                    `<div class="btn-group mx-2">
                        <button class="btn btn-primary btn-sm btn-approve" data-id="${id_sm}" data-jenisrab="${jenisRabSafe}" data-sd="${sd}" data-idunit="${unit_kerja}" data-draft="${is_draft}" data-jenis="${jenisValid}" data-title="Setuju">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5em" height="1.5em">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            Setuju
                        </button>
                        <button class="btn btn-danger btn-sm btn-reject" data-id="${id_sm}" data-jenisrab="${jenisRabSafe}" data-sd="${sd}" data-idunit="${unit_kerja}" data-draft="${is_draft}" data-jenis="${jenisValid}" data-title="Tolak">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2" width="1.5em" height="1.5em">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Tolak
                        </button>
                    </div>`
                ]);
            }
            dataTable.rows.add(rowsData);
            dataTable.draw(false); // false = tidak reset paging
            const endTime = performance.now();
        }
        // Initialize
        window.belumVerifikasi.methods.bindFunctions();
        window.belumVerifikasi.methods.initializeDataTable();
        
        // Check URL parameters for jenis
        const urlParams = new URLSearchParams(window.location.search);
        const jenisParam = urlParams.get('jenis');
        
        if (jenisParam) {
            // If jenis parameter exists, fetch data for that type
            const jenisBackend = jenisParam.toUpperCase() === "OUTPUT" ? "KK" : jenisParam.toUpperCase();
            
            // Validate jenis is supported
            const validTypes = ['SS', 'RO', 'KK', 'BREAKDOWN'];
            if (validTypes.includes(jenisBackend)) {
                console.log(`🔄 Auto-loading data for type: ${jenisBackend}`);
                window.belumVerifikasi.methods.getDataBelumVerifikasi(jenisBackend);
            } else {
                console.warn(`⚠️ Invalid jenis parameter: ${jenisParam}`);
                // Show empty state with message
                const tbody = $('#belumVerifikasiTable tbody');
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fe fe-filter" style="font-size: 48px; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0">Silakan pilih jenis revisi untuk menampilkan data</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            // No jenis parameter - show empty state
            console.log('ℹ️ No jenis parameter - waiting for user selection');
            const tbody = $('#belumVerifikasiTable tbody');
            tbody.html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fe fe-filter" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0">Silakan pilih jenis revisi untuk menampilkan data</p>
                        </div>
                    </td>
                </tr>
            `);
        }
    });
</script>
