<script>
    $( document ).ready( function () {
        const stringURL   = window.location.href || ''
        const url         = new URL( stringURL )
        let isPdf         = stringURL.includes("pdf")
        let isToggleUniv  = false

        const updateSelectedUnitCardTitles = () => {
            const selectedUnits = $(".unitkerjaOption.selected")
            let unitLabel = "Universitas Syiah Kuala"

            if ( selectedUnits.length > 0 ) {
                const labels = selectedUnits.map((_, el) => {
                    const dataText = $(el).data("text")
                    if ( dataText ) return String(dataText).trim()
                    return $(el).text().trim()
                }).get().filter(Boolean)

                if ( labels.length > 0 ) {
                    if ( labels.includes("Semua Unit Kerja") ) {
                        unitLabel = "Semua Unit Kerja"
                    } else if ( labels.includes("Universitas Syiah Kuala") && labels.length === 1 ) {
                        unitLabel = "Universitas Syiah Kuala"
                    } else if ( labels.length <= 2 ) {
                        unitLabel = labels.join(", ")
                    } else {
                        unitLabel = `${labels.slice(0, 2).join(", ")} +${labels.length - 2} lainnya`
                    }
                }
            }

            const titleText = `Lingkup ${unitLabel}`
            $(".sd-title, .kro-title, .ro-title, .ikv-title, .keg-title, .riwayat-title, .coa-title, .detail-title").text(titleText)
        }

        window.laporan = {}
        window.laporan.tahunan = {}
        updateSelectedUnitCardTitles()
        $(".kro, .ro, .ikv, .riwayat, .sd, .keg, .coa, .detail").hide()
        const defaultDatatableConfig = {
            "autoWidth": false,
            "responsive": true,
            "ordering": false,
            "language": {
                "emptyTable": "Tidak ada data"
            },
            dom: "Bfrltip",
            "pageLength": 100,
            // default entries is 50
            "lengthMenu": [100, 200],
        }
        // Init select2 & datatable
        let tableSs = null;
        let tableRo = null;
        let tableIkv = null;
        let tableRiwayat = null;
        let tableSd = null;
        let tableSubkomponen = null;
        let tableCoa = null;
        let tableDetail = null;
        if ( !isPdf ) {
            tableSs = $('#tabel-ss').DataTable({
                ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap Klasifikasi Rincian Output' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=ss&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ],
            })
            tableRo = $('#tabel-ro').DataTable({
                ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap Rincian Output' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=ro&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ],
            })
            tableIkv = $('#tabel-ikv').DataTable({
                ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap Komponen' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=ikv&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ],
            })
            tableRiwayat = $('#tabel-riwayat').DataTable({
                ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap Riwayat' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=riwayat&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ],
            })
            tableSd = $('#tabel-sd').DataTable({
                ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap Sumberdana'},
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=sd&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ],
            })
            tableSubkomponen = $('#tabel-keg').DataTable({ ...defaultDatatableConfig, "buttons": [
                { extend: 'excel', title: 'Rekap Subkomponen' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=keg&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ] })
            tableCoa = $('#tabel-coa').DataTable({ ...defaultDatatableConfig,
                "buttons": [{ extend: 'excel', title: 'Rekap COA' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=coa&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ]
            })
            tableDetail = $('#tabel-detail').DataTable({
                ...defaultDatatableConfig,
                "pageLength": 2000,
                "dom": "Brt",
                "buttons": [{ extend: 'excel', title: 'Rekap Detail' },
                // {
                //     text: 'Export to PDF',
                //     action: function ( e, dt, node, config ) {
                //         const unitkerjaSelected   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         const sumberdanaSelected  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                //         window.open("/laporan/tahunan/pdf?jenis=detail&kodeSd=" + sumberdanaSelected + "&idunit=" + unitkerjaSelected, "_blank" )
                //     }
                // }
            ]
            })
        }

        const navLink = $( ".item-tab" )
        navLink.on( 'click', function () {
            updateSelectedUnitCardTitles()
            const unitkerja   = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get()
            const sumberdana  = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
            if ( unitkerja.length === 0 )
                return tata.error( "⛔ Error", "Harap memilih unitkerja terlebih dahulu", { animate: "slide", duration: 5000} )
            if ( sumberdana.length === 0 )
                return tata.error( "⛔ Error", "Harap memilih sumberdana terlebih dahulu", { animate: "slide", duration: 5000} )

            const $this = $(this)
            $this.css({ 'color': 'blue'})
            // empty the baseMap
            window.laporan.tahunan.baseMap = null
            navLink.each( function ( index, item ) {
                if ( item !== $this[0] )
                    $(item).css({ 'color': 'black'})
                    $(item).removeClass( 'active' )
            })
            $this.addClass( 'active' )

            // Hide all elements first
            $(".kro, .ro, .ikv, .riwayat, .sd, .keg, .coa, .detail").hide()

            // Handle tab actions based on the ID
            switch ($this.attr('id')) {
                case 'SD-tab':
                    $(".sd").show();
                    window.laporan.tahunan.methods.showSdTab(unitkerja);
                    break;

                case 'SS-tab':
                    $(".kro").show();
                    window.laporan.tahunan.methods.showSsTab(unitkerja);
                    break;

                case 'RO-tab':
                    $(".ro").show();
                    window.laporan.tahunan.methods.showIkkTab(unitkerja);
                    break;

                case 'IKV-tab':
                    $(".ikv").show();
                    window.laporan.tahunan.methods.showIkvTab(unitkerja);
                    break;

                case 'KEG-tab':
                    $(".keg").show();
                    window.laporan.tahunan.methods.showKegTab(unitkerja);
                    break;

                case 'RIWAYAT-tab':
                    $(".riwayat").show();
                    window.laporan.tahunan.methods.showRiwayatTab(unitkerja);
                    break;
                case 'COA-tab':
                    $(".coa").show();
                    window.laporan.tahunan.methods.showCoaTab(unitkerja);
                    break;
                case 'DETAIL-tab':
                    $(".detail").show();
                    window.laporan.tahunan.methods.showDetailTab( unitkerja);
                    break;
            }

            // Prevent default action
            return false;

        })

        $(document).on("click", ".unitkerjaOption", function () {
            setTimeout(updateSelectedUnitCardTitles, 0)
        })

        function updateURLandTab(param, value) {
            url.searchParams.set(param, value)
            window.history.pushState({}, '', url)

            const itemTab = $(".item-tab.active")
            if ( checkSelectNotNull() ) {
                if (itemTab.attr('id') === 'SD-tab') {
                    window.laporan.tahunan.methods.showSdTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'SS-tab') {
                    window.laporan.tahunan.methods.showSsTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'RO-tab') {
                    window.laporan.tahunan.methods.showRoTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'IKV-tab') {
                    window.laporan.tahunan.methods.showIkvTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'RIWAYAT-tab') {
                    window.laporan.tahunan.methods.showRiwayatTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'KEG-tab') {
                    window.laporan.tahunan.methods.showKegTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'COA-tab') {
                    window.laporan.tahunan.methods.showCoaTab(unitkerja.val())
                } else if (itemTab.attr('id') === 'DETAIL-tab') {
                    window.laporan.tahunan.methods.showDetailTab(unitkerja.val())
                }
            }
        }

        $( ".toggle-btn" ).on( 'click', function () {
            const $this        = $(this)
            const cardBody     = $this.closest('.card').find('.card-body')

            // Toggle card body
            cardBody.slideToggle()
            isToggleUniv = !isToggleUniv
            if ( isToggleUniv )
                return $this.text( '+' )
            return $this.text( '−' )
        })

        if ( !isPdf ) {
            $( document ).on("input", ".cakin", debounce( (e) => {
                const $target = $(e.target)
                const text    = $target.text()
                const key     = $target.attr("key")
                const idunit  = unitkerja.val()
                const loader  = $target.closest("tr").find(".loaderSave")
                $.ajax({
                    url: `/laporan/tahunan/storeRO`,
                    type: "POST",
                    data: { "_token": "{{ csrf_token() }}", key, text, idunit },
                    beforeSend: () => {
                        loader.attr('style','display:inline-block !important');
                    },
                    success: ( res ) => {
                        const { data } = res
                        loader.attr('style','display:none !important');
                        return
                    },
                    error: ( err ) => {
                        loader.attr('style','display:none !important');
                        const message = err.responseJSON.message || "Gagal mengambil data"
                        return tata.error( "⛔ Error", message )
                    }
                })
            }, 300))
            $( document ).on("input", ".proyeksi", debounce( (e) => {
                return
                const $target = $(e.target)
                const text    = $target.text()
                const key     = $target.attr("key")
                const idunit  = unitkerja.val()
                const loader  = $target.closest("tr").find(".loaderSave")
                $.ajax({
                    url: `/laporan/tahunan/storeProyeksiPenerimaan`,
                    type: "POST",
                    data: { "_token": "{{ csrf_token() }}", key, idunit, text },
                    beforeSend: () => {
                        loader.attr('style','display:inline-block !important');
                    },
                    success: ( res ) => {
                        const { data } = res
                        loader.attr('style','display:none !important');
                        return
                    },
                    error: ( err ) => {
                        loader.attr('style','display:none !important');
                        const message = err.responseJSON.message || "Gagal mengambil data"
                        return tata.error( "⛔ Error", message )
                    }
                })
            }, 300))
        }


        const checkSelectNotNull = () => {
            if ( unitkerja.val() === "" )
                return false
            if ( sumberdana.val() === "" )
                return false
            return true
        }
        $("button.export-pdf").on("click", function () {
            if ( !checkSelectNotNull() ) {
                return tata.error( "⛔ Error", "Harap memilih unitkerja dan sumberdana terlebih dahulu" )
            }
            const jenis = $(this).data("jenis")
            window.open(`/laporan/tahunan/pdf?jenis=${jenis}&kodeSd=${sumberdana.val()}&idunit=${unitkerja.val()}`, "_blank")
        })
        $("button.export-excel").on("click", function () {
            const currentHtml = $(this).html()
            const jenis = $(this).data("jenis")
            const tableName = $(this).data("tablename")
            const idunit = unitkerja.val()
            const kodeSd = sumberdana.val()
            if ( !checkSelectNotNull() ) {
                return tata.error( "⛔ Error", "Harap memilih unitkerja dan sumberdana terlebih dahulu" )
            }
            try {
                $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengunduh file...`)
                const tabel = document.getElementById(tableName)
                const rows = tabel.rows
                const wb = XLSX.utils.table_to_book(tabel, { sheet: "Sheet1" })
                $(this).html(currentHtml)
                return XLSX.writeFile(wb, `Laporan_Tahunan_${jenis}_${kodeSd}_${idunit}.xlsx`)
            } catch ( e ) {
                $(this).html(currentHtml)
                console.error("Gagal mengexport ke Excel:", e)
            }

        })

        const smallLoadingSpin = `<span class="loaderSave px-2 py-1 bg-warning d-inline-block" style="border-radius: 5px; display: none !important">
            <small class="mx-2 align-items-center spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></small>
        Menyimpan data...</span>`
        window.laporan.tahunan.methods = {
            createOrUpdateMap: ( map, key, createNode ) => {
                if ( !map.has(key) ) map.set(key, createNode() )
                return map.get(key)
            },
            createMap: ( name, value, dataObject = []) => new Map([
                ["sub", new Map()],
                [ name, value ],
                ["total", 0],
                ["totalAmprah", 0],
                ["totalRealisasi", 0],
                ...Object.entries(dataObject)
            ]),
            updateTotals: (maps, jumlahBiaya, totalAmprah, totalRealisasi) => {
                const amounts = {
                    total: Number(jumlahBiaya),
                    totalAmprah: Number(totalAmprah),
                    totalRealisasi: Number(totalRealisasi)
                }

                maps.forEach(map => {
                    Object.entries( amounts ).forEach( ( [key, value] ) => {
                        map.set(key, (map.get(key) || 0) + value)
                    })
                })
            },
            /**
             * Fungsi untuk mengambil data dasar berdasarkan idunit dan kode sumberdana
             * @param {string|number} idunit - Idunit yang dipilih
             * @param {string|number} kodeSd - Kode sumberdana yang dipilih
             * @returns {Promise} Promise yang berisi data dasar
             */
            getBaseData: ( idunit, kodeSd, isAllSumberdana = false )  => {
                return new Promise( ( resolve, reject ) => {
                    if ( kodeSd == "semua" )
                        isAllSumberdana = true
                    $.ajax({
                        type: "GET",
                        url: `/laporan/tahunan/getBaseData?kodesd=${kodeSd}&idunit=${idunit}&semuaSumberdana=${isAllSumberdana}`,
                        success: ( res ) => {
                            // console.log( res )
                            const { baseData, pagu } = res.data
                            window.laporan.tahunan.alokasiRaw = Array.isArray(pagu) ? pagu : []
                            window.laporan.tahunan.baseDataRaw = Array.isArray(baseData) ? baseData : []
                            window.laporan.tahunan.methods.generateBaseMap( baseData ).then( ( baseMap ) => {
                                window.laporan.tahunan.baseMap = baseMap
                            }).catch( err => {
                                reject( err )
                            })
                            resolve("berhasil mengambil data")
                        },
                        error: ( err ) => {
                            const message = err.responseJSON.message || "Gagal mengambil data"
                            reject( message )
                        }
                    })
                })
            },
            generateBaseMap: ( data ) => {
                return new Promise( ( resolve, reject ) => {
                    if ( !data || data.length === 0 ) {
                        return reject("Data tidak ditemukan")
                    }
                    const baseMap = new Map([
                        ["sub", new Map()], ["total", 0],
                        ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ])
                    data.forEach( item => {
                        const { kd_sumberdana: kodeSd, sumberdana, kode_ss: kodeSs, ss, kode_ikk: kodeIkk, ikk, kode_ikv: kodeIkv, ikv,
                            kode_keg: kodeKeg, rincian_kegiatan: rincianKeg, id_rekat: idRekat, sub_judul: subJudul,
                            unit_kerja: unitKerja, nama_unit: namaUnit, id_jenis_belanja: idJenisBelanja, jenis_belanja: jenisBelanja,
                            kuantitas, sKuantitas, durasi, sDurasi, kegiatan, sKegiatan, rpd, id, itemCoa, biaya_satuan: biayaSatuan,
                            jumlah_biaya: jumlahBiaya, TOTAL_AMPRAH: totalAmprah, TOTAL_REALISASI: totalRealisasi, total_pagu: totalPagu
                        } = item

                        // Create hierarchy using helper functions
                        const methods       = window.laporan.tahunan.methods
                        const jenisAnggaran = kodeSd.startsWith("41") ? "Non APBN" : "APBN"
                        const revMap        = methods.createOrUpdateMap(baseMap.get("sub"), jenisAnggaran, () => methods.createMap("jenisAnggaran", jenisAnggaran))
                        const sdMap         = methods.createOrUpdateMap(revMap.get("sub"), kodeSd, () => methods.createMap("sumberdana", sumberdana, { totalPagu }))
                        const ssMap         = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => methods.createMap("ss", ss ))
                        const ikkMap        = methods.createOrUpdateMap(ssMap.get("sub"), kodeIkk, () => methods.createMap("ikk", ikk ))
                        const ikvMap        = methods.createOrUpdateMap(ikkMap.get("sub"), kodeIkv, () => methods.createMap("ikv", ikv ))
                        const kegMap        = methods.createOrUpdateMap(ikvMap.get("sub"), kodeKeg, () => methods.createMap("rincianKeg", rincianKeg ))
                        const unitMap       = methods.createOrUpdateMap(kegMap.get("sub"), unitKerja, () => methods.createMap("namaUnit", namaUnit ))
                        const rekatMap      = methods.createOrUpdateMap(unitMap.get("sub"), idRekat, () => methods.createMap("subJudul", subJudul ))
                        const coaMap        = methods.createOrUpdateMap(rekatMap.get("sub"), idJenisBelanja, () => methods.createMap("jenisBelanja", jenisBelanja ))
                        coaMap.get("sub").set(id, new Map([
                            ["biayaSatuan", biayaSatuan], ["jumlahBiaya", jumlahBiaya],
                            ["kuantitas", kuantitas], ["sKuantitas", sKuantitas],
                            ["durasi", durasi], ["sDurasi", sDurasi], ["kegiatan", kegiatan], ["sKegiatan", sKegiatan],
                            ["itemCoa", itemCoa], ["rpd", rpd],
                        ]))

                        // Update totals for all levels efficiently
                        const allMaps = [baseMap, revMap, sdMap, ssMap, ikkMap, ikvMap, kegMap, unitMap, rekatMap, coaMap]
                        methods.updateTotals(allMaps, jumlahBiaya, totalAmprah, totalRealisasi)
                    })
                    resolve( baseMap )
                } )
            },
            generateSdByUnitMap: ( data = [] ) => {
                const methods      = window.laporan.tahunan.methods
                const unitMap      = new Map();
                unitMap.set("-", new Map([
                    ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0],
                    ["totalAmprah", 0], ["totalPagu", 0], ["totalPaguTambahan", 0],
                ]))
                // variabel penampung di luar perulangan
                let grandTotalPagu         = 0;
                let grandTotalPaguTambahan = 0;
                let grandTotalReal         = 0;
                let grandTotal             = 0;
                let grandTotalAmprah       = 0;

                data.forEach((item) => {
                    const unitKey           = item?.unit_kerja || item?.idunit || "-"
                    const unitName          = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd            = item?.kd_sumberdana || "-"
                    const namaSd            = item?.sumberdana || "Sumber dana tidak diketahui"
                    const total             = Number(item?.jumlah_biaya || 0)
                    const totalAmprah       = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi    = Number(item?.TOTAL_REALISASI || 0)
                    const jenisAnggaran     = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"
                    const totalPagu         = Number(item?.total_pagu || 0)
                    const totalPaguTambahan = Number(item?.total_pagu_tambahan || 0)

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName],
                        ["sub", new Map()],
                        ["total", 0],
                        ["totalAmprah", 0],
                        ["totalRealisasi", 0],
                        ["totalPagu", 0],
                        ["totalPaguTambahan", 0],
                    ]))

                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()],
                        ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0],
                        ["totalPagu", 0], ["totalPaguTambahan", 0],
                    ]))

                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0],
                        ["totalRealisasi", 0], ["totalPagu", totalPagu], ["totalPaguTambahan", totalPaguTambahan],
                    ]));
                    methods.updateTotals([perUnitMap, jenisMap, sdMap], total, totalAmprah, totalRealisasi)
                })

                // special sum for total pagu at unit level, since total pagu is only available at sumberdana level, we need to sum it up manually for each unit
                unitMap.forEach((unitData, key) => {
                    // Lewati (skip) key "-" jika itu hanya template atau header
                    if (key === "-") return;
                    let totalPaguUnit   = 0;
                    let totalPaguTambahanUnit = 0;
                    let totalRealUnit   = 0;
                    let totalUnit       = 0;
                    let totalAmprahUnit = 0;
                    if (unitData.has("sub")) {
                        unitData.get("sub").forEach((jenisData) => {
                            // Menghitung total per JENIS menggunakan reduce (sudah benar)
                            const totalPaguJenis = Array.from(jenisData.get("sub").values()).reduce((sum, sdData) => sum + (sdData.get("totalPagu") || 0), 0);
                            const totalPaguTambahanJenis = Array.from(jenisData.get("sub").values()).reduce((sum, sdData) => sum + (sdData.get("totalPaguTambahan") || 0), 0);
                            jenisData.set("totalPagu", totalPaguJenis);
                            jenisData.set("totalPaguTambahan", totalPaguTambahanJenis);
                            totalPaguUnit += totalPaguJenis;
                            totalPaguTambahanUnit += totalPaguTambahanJenis;
                            // Menghitung total realisasi per JENIS
                            const totalRealJenis = Array.from(jenisData.get("sub").values()).reduce((sum, sdData) => sum + (sdData.get("totalRealisasi") || 0), 0);
                            jenisData.set("totalRealisasi", totalRealJenis);
                            totalRealUnit += totalRealJenis;
                            // Menghitung total jumlah biaya per JENIS
                            const totalJenis = Array.from(jenisData.get("sub").values()).reduce((sum, sdData) => sum + (sdData.get("total") || 0), 0);
                            jenisData.set("total", totalJenis);
                            totalUnit += totalJenis;
                            // Menghitung total amprah per JENIS
                            const totalAmprahJenis = Array.from(jenisData.get("sub").values()).reduce((sum, sdData) => sum + (sdData.get("totalAmprah") || 0), 0);
                            jenisData.set("totalAmprah", totalAmprahJenis);
                            totalAmprahUnit += totalAmprahJenis;
                        });
                    }
                    unitData.set("totalPagu", totalPaguUnit);
                    unitData.set("totalRealisasi", totalRealUnit);
                    unitData.set("total", totalUnit);
                    unitData.set("totalAmprah", totalAmprahUnit);
                    unitData.set("totalPaguTambahan", totalPaguTambahanUnit);
                    grandTotalPagu += totalPaguUnit;
                    grandTotalPaguTambahan += totalPaguTambahanUnit;
                    grandTotalReal += totalRealUnit;
                    grandTotal += totalUnit;
                    grandTotalAmprah += totalAmprahUnit;
                });
                if (unitMap.has("-")) {
                    unitMap.get("-").set("totalPaguTambahan", grandTotalPaguTambahan);
                    unitMap.get("-").set("totalPagu", grandTotalPagu);
                    unitMap.get("-").set("totalRealisasi", grandTotalReal);
                    unitMap.get("-").set("total", grandTotal);
                    unitMap.get("-").set("totalAmprah", grandTotalAmprah);
                }
                return unitMap
            },
            generateSsByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const kodeSs         = item?.kode_ss || "-"
                    const namaSs         = item?.ss || "Data sasaran tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName],
                        ["sub", new Map()],
                        ["total", 0],
                        ["totalAmprah", 0],
                        ["totalRealisasi", 0],
                        ["totalPagu", 0]
                    ]))

                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran],
                        ["sub", new Map()],
                        ["total", 0],
                        ["totalAmprah", 0],
                        ["totalRealisasi", 0],
                        ["totalPagu", 0]
                    ]))

                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd],
                        ["sub", new Map()],
                        ["total", 0],
                        ["totalAmprah", 0],
                        ["totalRealisasi", 0],
                        ["totalPagu", totalPagu]
                    ]))

                    const ssMap = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => new Map([
                        ["ss", namaSs],
                        ["sub", new Map()],
                        ["total", 0],
                        ["totalAmprah", 0],
                        ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, ssMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateIkkByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const kodeSs         = item?.kode_ss || "-"
                    const namaSs         = item?.ss || "Data sasaran tidak ditemukan"
                    const kodeIkk        = item?.kode_ikk || "-"
                    const namaIkk        = item?.ikk || "Data IKU tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", totalPagu]
                    ]))
                    const ssMap = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => new Map([
                        ["ss", namaSs], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const ikkMap = methods.createOrUpdateMap(ssMap.get("sub"), kodeIkk, () => new Map([
                        ["ikk", namaIkk], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, ssMap, ikkMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateIkvByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const kodeSs         = item?.kode_ss || "-"
                    const namaSs         = item?.ss || "Data sasaran tidak ditemukan"
                    const kodeIkk        = item?.kode_ikk || "-"
                    const namaIkk        = item?.ikk || "Data IKU tidak ditemukan"
                    const kodeIkv        = item?.kode_ikv || "-"
                    const namaIkv        = item?.ikv || "Data IKV tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", totalPagu]
                    ]))
                    const ssMap = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => new Map([
                        ["ss", namaSs], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const ikkMap = methods.createOrUpdateMap(ssMap.get("sub"), kodeIkk, () => new Map([
                        ["ikk", namaIkk], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const ikvMap = methods.createOrUpdateMap(ikkMap.get("sub"), kodeIkv, () => new Map([
                        ["ikv", namaIkv], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, ssMap, ikkMap, ikvMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateKegByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const kodeSs         = item?.kode_ss || "-"
                    const namaSs         = item?.ss || "Data sasaran tidak ditemukan"
                    const kodeIkk        = item?.kode_ikk || "-"
                    const namaIkk        = item?.ikk || "Data IKU tidak ditemukan"
                    const kodeIkv        = item?.kode_ikv || "-"
                    const namaIkv        = item?.ikv || "Data IKV tidak ditemukan"
                    const kodeKeg        = item?.kode_keg || "-"
                    const namaKeg        = item?.rincian_kegiatan || "Data kegiatan tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", totalPagu]
                    ]))
                    const ssMap = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => new Map([
                        ["ss", namaSs], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const ikkMap = methods.createOrUpdateMap(ssMap.get("sub"), kodeIkk, () => new Map([
                        ["ikk", namaIkk], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const ikvMap = methods.createOrUpdateMap(ikkMap.get("sub"), kodeIkv, () => new Map([
                        ["ikv", namaIkv], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))
                    const kegMap = methods.createOrUpdateMap(ikvMap.get("sub"), kodeKeg, () => new Map([
                        ["rincianKeg", namaKeg], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, ssMap, ikkMap, ikvMap, kegMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateRekatByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const idRekat        = item?.id_rekat || "-"
                    const subJudul       = item?.sub_judul || "Data sub judul tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", totalPagu]
                    ]))
                    const rekatMap = methods.createOrUpdateMap(sdMap.get("sub"), idRekat, () => new Map([
                        ["subJudul", subJudul], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, rekatMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateCoaByUnitMap: ( data = [] ) => {
                const methods = window.laporan.tahunan.methods
                const unitMap = new Map()

                data.forEach((item) => {
                    const unitKey        = item?.unit_kerja || item?.idunit || "-"
                    const unitName       = item?.nama_unit || "Unit tidak diketahui"
                    const kodeSd         = item?.kd_sumberdana || "-"
                    const namaSd         = item?.sumberdana || "Sumber dana tidak diketahui"
                    const idJenisBelanja = item?.id_jenis_belanja || "-"
                    const coaKey         = String(idJenisBelanja).trim() || "-"
                    const jenisBelanja   = item?.jenis_belanja || "Data COA tidak ditemukan"
                    const total          = Number(item?.jumlah_biaya || 0)
                    const totalAmprah    = Number(item?.TOTAL_AMPRAH || 0)
                    const totalRealisasi = Number(item?.TOTAL_REALISASI || 0)
                    const totalPagu      = Number(item?.total_pagu || 0)
                    const jenisAnggaran  = String(kodeSd).startsWith("41") ? "Non APBN" : "APBN"

                    const perUnitMap = methods.createOrUpdateMap(unitMap, unitKey, () => new Map([
                        ["namaUnit", unitName], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const jenisMap = methods.createOrUpdateMap(perUnitMap.get("sub"), jenisAnggaran, () => new Map([
                        ["jenisAnggaran", jenisAnggaran], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", 0]
                    ]))
                    const sdMap = methods.createOrUpdateMap(jenisMap.get("sub"), kodeSd, () => new Map([
                        ["sumberdana", namaSd], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0], ["totalPagu", totalPagu]
                    ]))
                    const coaMap = methods.createOrUpdateMap(sdMap.get("sub"), coaKey, () => new Map([
                        ["jenisBelanja", jenisBelanja], ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0]
                    ]))

                    methods.updateTotals([perUnitMap, jenisMap, sdMap, coaMap], total, totalAmprah, totalRealisasi)
                })

                // total pagu tersedia di level sumberdana, lalu diturunkan ke jenis dan unit
                unitMap.forEach((perUnitMap) => {
                    let totalPaguUnit = 0
                    if ( !perUnitMap.has("sub") ) {
                        perUnitMap.set("totalPagu", 0)
                        return
                    }

                    perUnitMap.get("sub").forEach((jenisMap) => {
                        let totalPaguJenis = 0
                        if ( jenisMap.has("sub") ) {
                            jenisMap.get("sub").forEach((sdMap) => {
                                totalPaguJenis += Number(sdMap.get("totalPagu") || 0)
                            })
                        }
                        jenisMap.set("totalPagu", totalPaguJenis)
                        totalPaguUnit += totalPaguJenis
                    })

                    perUnitMap.set("totalPagu", totalPaguUnit)
                })

                return unitMap
            },
            generateBaseBackupMap: ( idunit, kodeSd ) => {
                return new Promise( ( resolve, reject ) => {
                    $.ajax({
                        type: "GET",
                        url: `/laporan/tahunan/getBaseDataBackup?kodesd=${kodeSd}&idunit=${idunit}`,
                        success: ( res ) => {
                            const { baseDataBackup } = res.data
                            if ( !baseDataBackup || baseDataBackup.length === 0 ) {
                                const emptyMap = new Map([ ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0] ])
                                window.laporan.tahunan.baseBackupMap = emptyMap
                                return resolve( emptyMap )
                            }

                            const baseMap = new Map([ ["sub", new Map()], ["total", 0], ["totalAmprah", 0], ["totalRealisasi", 0] ])
                            baseDataBackup.forEach( item => {
                                const { kd_sumberdana: kodeSd, sumberdana, kode_ss: kodeSs, ss, kode_ikk: kodeIkk, ikk, kode_ikv: kodeIkv, ikv,
                                    kode_keg: kodeKeg, rincian_kegiatan: rincianKeg, id_rekat: idRekat, sub_judul: subJudul,
                                    idunit, nama_unit: namaUnit, id_jenis_belanja: idJenisBelanja, jenis_belanja: jenisBelanja,
                                    kuantitas, sKuantitas, durasi, sDurasi, kegiatan, sKegiatan, rpd, id, itemCoa, biaya_satuan: biayaSatuan,
                                    jumlah_biaya: jumlahBiaya, TOTAL_AMPRAH: totalAmprah, TOTAL_REALISASI: totalRealisasi,
                                    id_revisi:idRevisi, nama_revisi: namaRevisi
                                } = item
                                const methods = window.laporan.tahunan.methods

                                const jenisAnggaran = kodeSd.startsWith("41") ? "Non APBN" : "APBN"
                                const jenisAnggaranMap = methods.createOrUpdateMap(baseMap.get("sub"), jenisAnggaran, () => methods.createMap("jenisAnggaran", jenisAnggaran ))
                                const revMap = methods.createOrUpdateMap(jenisAnggaranMap.get("sub"), namaRevisi, () => methods.createMap("idRevisi", idRevisi ))
                                const sdMap = methods.createOrUpdateMap(revMap.get("sub"), kodeSd, () => methods.createMap("sumberdana", sumberdana ))
                                const ssMap = methods.createOrUpdateMap(sdMap.get("sub"), kodeSs, () => methods.createMap("ss", ss))
                                const ikkMap = methods.createOrUpdateMap(ssMap.get("sub"), kodeIkk, () => methods.createMap("ikk", ikk, { "namaRevisi": namaRevisi }))
                                const ikvMap = methods.createOrUpdateMap(ikkMap.get("sub"), kodeIkv, () => methods.createMap("ikv", ikv))
                                const kegMap = methods.createOrUpdateMap(ikvMap.get("sub"), kodeKeg, () => methods.createMap("rincianKeg", rincianKeg))
                                const unitMap = methods.createOrUpdateMap(kegMap.get("sub"), idunit, () => methods.createMap("namaUnit", namaUnit))
                                const rekatMap = methods.createOrUpdateMap(unitMap.get("sub"), idRekat, () => methods.createMap("subJudul", subJudul, { "namaRevisi": namaRevisi } ))
                                const coaMap = methods.createOrUpdateMap(rekatMap.get("sub"), idJenisBelanja, () => methods.createMap("jenisBelanja", jenisBelanja))
                                coaMap.get("sub").set(id, new Map([
                                    ["biayaSatuan", biayaSatuan], ["jumlahBiaya", jumlahBiaya],
                                    ["kuantitas", kuantitas], ["sKuantitas", sKuantitas],
                                    ["durasi", durasi], ["sDurasi", sDurasi], ["kegiatan", kegiatan], ["sKegiatan", sKegiatan],
                                    ["itemCoa", itemCoa], ["rpd", rpd], ["namaRevisi", namaRevisi]
                                ]))
                                const allMaps = [baseMap, revMap, sdMap, ssMap, ikkMap, ikvMap, kegMap, unitMap, rekatMap, coaMap]
                                methods.updateTotals(allMaps, jumlahBiaya, totalAmprah, totalRealisasi)
                            })
                            window.laporan.tahunan.baseBackupMap = baseMap
                            resolve( baseMap )
                        },
                    })
                })
            },
            showCoaTab: (idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data ...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd )
                    .then( baseMap => {
                        baseMap = window.laporan.tahunan.baseMap
                        const tableBody = $(".body-tbl-coa")
                        tableBody.html("")
                        const fragment = document.createDocumentFragment()
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text()

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja" ) return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);
                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;
                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });
                        baseMap.set("totalPagu", 0);
                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;
                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;
                                sdMap.set("totalPagu", paguSd);
                                totalPaguJenis += paguSd;
                            });
                            jenisMap.set("totalPagu", totalPaguJenis);
                            baseMap.set( "totalPagu", (baseMap.get("totalPagu") || 0) + totalPaguJenis);
                        });

                        const createTotalRow = () => {
                            const total = baseMap.get("total") || 0
                            const realisasi = (baseMap.get("totalAmprah") || 0) + (baseMap.get("totalRealisasi") || 0)
                            const sisa = total - realisasi
                            const totalPagu = baseMap.get("totalPagu") || 0
                            const totalRow = document.createElement("tr")
                            totalRow.classList.add("fw-bold", "total-row")
                            totalRow.innerHTML = `
                                <td>Total</td>
                                <td>-</td>
                                <td>${rupiah(totalPagu)}</td>
                                <td>${rupiah(total)}</td>
                                <td>${rupiah(realisasi)}</td>
                                <td>${rupiah(sisa)}</td>
                            `
                            return totalRow
                        }

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateCoaByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa = unitTotal - unitRealisasi
                                const unitPagu = perUnitMap.get("totalPagu") || 0
                                const unitRow = document.createElement("tr")
                                unitRow.classList.add("ro-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa = jenisTotal - jenisRealisasi
                                    const jenisPagu = jenisMap.get("totalPagu") || 0
                                    const groupId = `coa-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "coa-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="coa-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sdTotal = sdMap.get("total") || 0
                                        const sdRealisasi = (sdMap.get("totalAmprah") || 0) + (sdMap.get("totalRealisasi") || 0)
                                        const sdSisa = sdTotal - sdRealisasi
                                        const sdPagu = sdMap.get("totalPagu") || 0
                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("coa-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td style="padding-left: 10px">${kodeSd ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>${rupiah(sdPagu)}</td>
                                            <td>${rupiah(sdTotal)}</td>
                                            <td>${rupiah(sdRealisasi)}</td>
                                            <td>${rupiah(sdSisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)

                                        sdMap.get("sub").forEach((coaMap, idJenisBelanja) => {
                                            const coaTotal = coaMap.get("total") || 0
                                            const coaRealisasi = (coaMap.get("totalAmprah") || 0) + (coaMap.get("totalRealisasi") || 0)
                                            const coaSisa = coaTotal - coaRealisasi
                                            const coaRow = document.createElement("tr")
                                            coaRow.classList.add("coa-detail-row")
                                            coaRow.setAttribute("data-parent-group-id", groupId)
                                            coaRow.style.display = "none"
                                            coaRow.innerHTML = `
                                                <td style="padding-left: 20px">${idJenisBelanja ?? '-'}</td>
                                                <td>${coaMap.get("jenisBelanja") || '-'}</td>
                                                <td>-</td>
                                                <td>${rupiah(coaTotal)}</td>
                                                <td>${rupiah(coaRealisasi)}</td>
                                                <td>${rupiah(coaSisa)}</td>
                                            `
                                            fragment.appendChild(coaRow)
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            tableBody.off("click", ".coa-jenis-row").on("click", ".coa-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".coa-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.coa-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableCoa ) {
                                removeLoader()
                                tableCoa.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals ) => {
                            const tr = document.createElement("tr")
                            tr.classList.add("ro-group-header")
                            tr.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.total) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return tr
                        }

                        fragment.appendChild(createTotalRow())
                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                            const jenisTotals = {
                                total: jenisMap.get("total") || 0,
                                totalPagu: jenisMap.get("totalPagu") || 0,
                                realisasi: (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0),
                                sisa: (jenisMap.get("total") || 0) - ((jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0))
                            }
                            fragment.appendChild( createGroupHeader( jenisLabel, jenisTotals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const coaGroupedMap = new Map()
                                const { sumberdana, total, totalAmprah, totalRealisasi, totalPagu } = {
                                    sumberdana: sdMap.get("sumberdana"),
                                    total: sdMap.get("total"),
                                    totalAmprah: sdMap.get("totalAmprah"),
                                    totalRealisasi: sdMap.get("totalRealisasi"),
                                    totalPagu: sdMap.get("totalPagu")
                                }
                                const realisasi = (totalAmprah || 0) + (totalRealisasi || 0)
                                const sisa = (total || 0) - realisasi
                                const sdHeader = document.createElement("tr")
                                sdHeader.classList.add("fw-bold")
                                sdHeader.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sumberdana ?? '-'}</td>
                                    <td>${rupiah(totalPagu || 0)}</td>
                                    <td>${rupiah(total || 0)}</td>
                                    <td>${rupiah(realisasi)}</td>
                                    <td>${rupiah(sisa)}</td>
                                `
                                fragment.appendChild( sdHeader )

                                sdMap.get("sub").forEach( ( ssMap ) => {
                                    ssMap.get("sub").forEach( ( ikkMap ) => {
                                        ikkMap.get("sub").forEach( ( ikvMap ) => {
                                            ikvMap.get("sub").forEach( ( kegMap ) => {
                                                kegMap.get("sub").forEach( ( unitMap ) => {
                                                    unitMap.get("sub").forEach( ( rekatMap ) => {
                                                        rekatMap.get("sub").forEach( ( coaMap, idJenisBelanja ) => {
                                                            const coaKey         = String(idJenisBelanja).trim() || "-"
                                                            const jenisBelanja   = coaMap.get("jenisBelanja")
                                                            const total          = Number( coaMap.get("total") || 0 )
                                                            const totalAmprah    = Number( coaMap.get("totalAmprah") || 0 )
                                                            const totalRealisasi = Number( coaMap.get("totalRealisasi") || 0 )

                                                            if ( !coaGroupedMap.has(coaKey) ) {
                                                                coaGroupedMap.set(coaKey, {
                                                                    jenisBelanja,
                                                                    total: 0,
                                                                    totalAmprah: 0,
                                                                    totalRealisasi: 0
                                                                })
                                                            }

                                                            const coaGrouped = coaGroupedMap.get(coaKey)
                                                            coaGrouped.total += total
                                                            coaGrouped.totalAmprah += totalAmprah
                                                            coaGrouped.totalRealisasi += totalRealisasi
                                                        })
                                                    })
                                                })
                                            })
                                        })
                                    })
                                })

                                coaGroupedMap.forEach( ( { jenisBelanja, total, totalAmprah, totalRealisasi }, coaKey ) => {
                                    const realisasi = totalAmprah + totalRealisasi
                                    const sisa = total - realisasi
                                    const coaRow = document.createElement("tr")
                                    coaRow.innerHTML = `
                                        <td style="padding-left: 20px">${ ( !coaKey || coaKey == "null" ) ? '-' : coaKey}</td>
                                        <td>${jenisBelanja ?? 'Coa tidak ditemukan'}</td>
                                        <td>-</td>
                                        <td>${rupiah(total)}</td>
                                        <td>${rupiah(realisasi)}</td>
                                        <td>${rupiah(sisa)}</td>
                                    `
                                    fragment.appendChild(coaRow)
                                })
                            })
                        })
                        tableBody.append(fragment)
                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableCoa ) {
                            removeLoader()
                            tableCoa.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }
                    })
                    .catch( err => {
                        console.error("Error loading COA data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showSdTab: ( idunit, kodeSdParam = null ) => {
                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data COA...")
                }
                const kodeSd = kodeSdParam || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                window.laporan.tahunan.methods.getBaseData( idunit, kodeSd, false )
                .then( baseMap => {
                        baseMap = window.laporan.tahunan.baseMap
                        if ( baseMap === null ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Data tidak ditemukan" )
                        }
                        const tableBody = $(".body-tbl-sd")
                        tableBody.html("")
                        const fragment = document.createDocumentFragment()
                        const isSemuaUnit = (Array.isArray(idunit) && (idunit.includes("semua"))) || idunit === "semua"

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateSdByUnitMap(window.laporan.tahunan.baseDataRaw || [])

                            unitMap.forEach((perUnitMap, unitKey) => {
                                if (unitKey == "-") {
                                    // show header
                                    const unitTotal = perUnitMap.get("total") || 0
                                    const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                    const unitSisa = unitTotal - unitRealisasi
                                    const unitPagu = perUnitMap.get("totalPagu") || 0
                                    const unitPaguTambahan = perUnitMap.get("totalPaguTambahan") || 0
                                    const unitRow = document.createElement("tr")
                                    unitRow.classList.add("sd-group-header")
                                    unitRow.innerHTML = `
                                        <td>TOTAL</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>${rupiah(0)}</td>
                                        <td>${rupiah(unitPagu)}</td>
                                        <td>${rupiah(unitTotal)}</td>
                                        <td>${rupiah(unitRealisasi)}</td>
                                        <td>${rupiah(unitSisa)}</td>
                                    `
                                    fragment.appendChild(unitRow)
                                    return
                                }
                                if ( !perUnitMap.get("sub") || perUnitMap.get("sub").size === 0 ) return
                                const unitTotal     = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa      = unitTotal - unitRealisasi
                                const unitPagu      = perUnitMap.get("totalPagu") || 0
                                const unitPaguTambahan = perUnitMap.get("totalPaguTambahan") || 0
                                const unitRow       = document.createElement("tr")
                                unitRow.classList.add("sd-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>-</td>
                                    <td>${rupiah(0)}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal     = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa      = jenisTotal - jenisRealisasi
                                    const jenisPagu      = jenisMap.get("totalPagu") || 0
                                    const jenisPaguTambahan = jenisMap.get("totalPaguTambahan") || 0
                                    const groupId = `sd-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow       = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "sd-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td></td>
                                        <td><span class="sd-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(0)}</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSdItem) => {
                                        const total             = sdMap.get("total") || 0
                                        const totalAmprah       = sdMap.get("totalAmprah") || 0
                                        const totalRealisasi    = sdMap.get("totalRealisasi") || 0
                                        const realisasiAnggaran = totalAmprah + totalRealisasi
                                        const sisa              = total - realisasiAnggaran
                                        const sdPagu            = sdMap.get("totalPagu") || 0
                                        const sdPaguTambahan    = sdMap.get("totalPaguTambahan") || 0

                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("sd-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td style="padding-left: 14px">${kodeSdItem ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>-</td>
                                            <td>${rupiah(0)}</td>
                                            <td class="alokasi">${rupiah(sdPagu)}</td>
                                            <td class="alokasi">${rupiah(total)}</td>
                                            <td class="realisasi">${rupiah(realisasiAnggaran)}</td>
                                            <td class="sisa">${rupiah(sisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)
                                    })
                                })
                            })

                            tableBody.append(fragment)

                            tableBody.off("click", ".sd-jenis-row").on("click", ".sd-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".sd-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.sd-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })

                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableSd ) {
                                removeLoader()
                                tableSd.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals ) => {
                            const header = document.createElement("tr")
                            header.classList.add("sd-group-header")
                            header.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.realisasiPenerimaan) : '-'}</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.alokasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasiAnggaran) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return header
                        }

                        const alokasiRaw = window.laporan.tahunan.alokasiRaw
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];

                        // 1. Kumpulkan total pagu per sumber dana
                        const alokasiBySd = new Map();
                        alokasiRaw.forEach(item => {
                            const unitSekarang = Number(item.unit_kerja);

                            if (!selectedUnits.includes(unitSekarang)) {
                                return;
                            }

                            const kodeSd = String(item?.kd_sumberdana || '-');
                            const pagu = Number(item.total_pagu) || 0;
                            const paguTambahan = Number(item.total_pagu_tambahan) || 0;

                            const current = alokasiBySd.get(kodeSd) || {
                                totalPagu: 0,
                                totalPaguTambahan: 0,
                            };

                            current.totalPagu += pagu;
                            current.totalPaguTambahan += paguTambahan;

                            alokasiBySd.set(kodeSd, current);
                        });
                        // 2. Reset total dulu agar tidak dobel kalau function dijalankan ulang
                        baseMap.set("totalPagu", 0);
                        baseMap.set("totalPaguTambahan", 0);

                        baseMap.get("sub").forEach(jenisMap => {
                            jenisMap.set("totalPagu", 0);
                            jenisMap.set("totalPaguTambahan", 0);

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                sdMap.set("totalPagu", 0);
                                sdMap.set("totalPaguTambahan", 0);
                            });
                        });
                        // 3. Masukkan total alokasi ke sdMap
                        baseMap.get("sub").forEach(jenisMap => {
                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const kodeSd = String(kodeSdKey);

                                const alokasi = alokasiBySd.get(kodeSd);

                                if (!alokasi) {
                                    return;
                                }

                                sdMap.set("totalPagu", alokasi.totalPagu);
                                sdMap.set("totalPaguTambahan", alokasi.totalPaguTambahan);

                                jenisMap.set(
                                    "totalPagu",
                                    (jenisMap.get("totalPagu") || 0) + alokasi.totalPagu
                                );

                                jenisMap.set(
                                    "totalPaguTambahan",
                                    (jenisMap.get("totalPaguTambahan") || 0) + alokasi.totalPaguTambahan
                                );

                                baseMap.set(
                                    "totalPagu",
                                    (baseMap.get("totalPagu") || 0) + alokasi.totalPagu
                                );

                                baseMap.set(
                                    "totalPaguTambahan",
                                    (baseMap.get("totalPaguTambahan") || 0) + alokasi.totalPaguTambahan
                                );
                            });
                        });
                        // display total
                        const totalRow = document.createElement("tr")
                        totalRow.classList.add("fw-bold", "total-row")
                        totalRow.innerHTML = `
                            <td>Total</td>
                            <td>-</td><td>-</td><td>-</td>
                            <td>${rupiah( baseMap.get("totalPagu") || 0 )}</td>
                            <td class="alokasi">${rupiah( baseMap.get("total") || 0 )}</td>
                            <td class="realisasi">${rupiah( baseMap.get("totalRealisasi") || 0 )}</td>
                            <td class="sisa">${rupiah( (baseMap.get("total") || 0) - (baseMap.get("totalRealisasi") || 0) )}</td>
                        `
                        fragment.appendChild(totalRow)
                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel     = jenisMap.get("jenisAnggaran") || jenisKey
                            const jenisTotal     = jenisMap.get("total") || 0
                            const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                            const paguJenis      = jenisMap.get("totalPagu") || 0
                            const paguTambahanJenis = jenisMap.get("totalPaguTambahan") || 0
                            const jenisSisa      = jenisTotal - jenisRealisasi
                            const jenisTotals    = { realisasiPenerimaan: 0, alokasi: jenisTotal, realisasiAnggaran: jenisRealisasi,
                                sisa: jenisSisa, totalPagu: paguJenis, totalPaguTambahan: paguTambahanJenis
                            }

                            fragment.appendChild( createGroupHeader( jenisLabel, jenisTotals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const sumberdana          = sdMap.get("sumberdana") || '-'
                                const total               = sdMap.get("total") || 0
                                const totalAmprah         = sdMap.get("totalAmprah") || 0
                                const totalRealisasi      = sdMap.get("totalRealisasi") || 0
                                const totalPagu           = sdMap.get("totalPagu") || 0
                                const totalPaguTambahan   = sdMap.get("totalPaguTambahan") || 0
                                const realisasiPenerimaan = 0
                                const realisasiAnggaran   = totalAmprah + totalRealisasi
                                const sisa                = total - realisasiAnggaran

                                $(`.total-${kodeSd}`).text( rupiah( sdMap.get("total") || 0 ) )

                                const tr = document.createElement("tr")
                                tr.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sumberdana}</td>
                                    <td class="proyeksi" key="${kodeSd}">${sdMap.get("proyeksi") || ''}</td>
                                    <td>${rupiah(realisasiPenerimaan)}</td>
                                    <td>${rupiah(totalPagu)}</td>
                                    <td class="alokasi">${rupiah(total)}</td>
                                    <td class="realisasi">${rupiah(realisasiAnggaran)}</td>
                                    <td class="sisa">${rupiah(sisa)}</td>
                                `
                                fragment.appendChild(tr)
                            })
                        })
                        tableBody.append(fragment)

                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableSd ) {
                            removeLoader()
                            tableSd.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }

                        // sync data proyeksi
                        window.laporan.tahunan.methods.getDataProyeksi( idunit ).then( data => {
                            data.forEach( item => {
                                $(`td.proyeksi[key="${item.kd_sumberdana}"]`).text( item.proyeksi_penerimaan || '' )
                            })
                        }).catch( err => {
                            console.error("Error loading proyeksi data:", err)
                            if ( !isPdf ) {
                                removeLoader()
                                return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                            }
                        })
                    })
                    .catch( err => {
                        console.error("Error loading sumberdana data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showSsTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data ...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd, false )
                    .then( baseMap => {
                        baseMap = window.laporan.tahunan.baseMap
                        if ( baseMap == null ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Data tidak ditemukan" )
                        }
                        const tableBody = $(".body-tbl-ss")
                        tableBody.html("")
                        const fragment    = document.createDocumentFragment()
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text();

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);

                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });

                        baseMap.set("totalPagu", 0);

                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                sdMap.set("totalPagu", paguSd);

                                totalPaguJenis += paguSd;
                            });

                            jenisMap.set("totalPagu", totalPaguJenis);

                            baseMap.set(
                                "totalPagu",
                                (baseMap.get("totalPagu") || 0) + totalPaguJenis
                            );
                        });

                        const createTotalRow = () => {
                            const total = baseMap.get("total") || 0
                            const realisasi = (baseMap.get("totalAmprah") || 0) + (baseMap.get("totalRealisasi") || 0)
                            const sisa = total - realisasi
                            const totalPagu = baseMap.get("totalPagu") || 0
                            const totalRow = document.createElement("tr")
                            totalRow.classList.add("fw-bold", "total-row")
                            totalRow.innerHTML = `
                                <td>Total</td>
                                <td>-</td>
                                <td>${rupiah(totalPagu)}</td>
                                <td>${rupiah(total)}</td>
                                <td>${rupiah(realisasi)}</td>
                                <td>${rupiah(sisa)}</td>
                            `
                            return totalRow
                        }

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateSsByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa = unitTotal - unitRealisasi
                                const unitPagu = perUnitMap.get("totalPagu") || 0
                                const unitRow = document.createElement("tr")
                                unitRow.classList.add("ss-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa = jenisTotal - jenisRealisasi
                                    const jenisPagu = jenisMap.get("totalPagu") || 0
                                    const groupId = `ss-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "ss-subgroup-header", "ss-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="ss-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sumberdana = sdMap.get("sumberdana") || '-'
                                        const total = sdMap.get("total") || 0
                                        const totalAmprah = sdMap.get("totalAmprah") || 0
                                        const totalRealisasi = sdMap.get("totalRealisasi") || 0
                                        const totalPagu = sdMap.get("totalPagu") || 0
                                        const realisasi = totalAmprah + totalRealisasi
                                        const sisa = total - realisasi

                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("ss-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td>${kodeSd ?? '-'}</td>
                                            <td>${sumberdana}</td>
                                            <td>${rupiah(totalPagu)}</td>
                                            <td>${rupiah(total)}</td>
                                            <td>${rupiah(realisasi)}</td>
                                            <td>${rupiah(sisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)
                                        sdMap.get("sub").forEach((ssMap, kodeSs) => {
                                            const ss = ssMap.get("ss") || '-'
                                            const total = ssMap.get("total") || 0
                                            const totalAmprah = ssMap.get("totalAmprah") || 0
                                            const totalRealisasi = ssMap.get("totalRealisasi") || 0
                                            const realisasi = totalAmprah + totalRealisasi
                                            const sisa = total - realisasi

                                            const ssRow = document.createElement("tr")
                                            ssRow.classList.add("ss-detail-row")
                                            ssRow.setAttribute("data-parent-group-id", groupId)
                                            ssRow.style.display = "none"
                                            ssRow.innerHTML = `
                                                <td style="padding-left: 40px">${kodeSs ?? '-'}</td>
                                                <td>${ss}</td>
                                                <td>-</td>
                                                <td>${rupiah(total)}</td>
                                                <td>${rupiah(realisasi)}</td>
                                                <td>${rupiah(sisa)}</td>
                                            `
                                            fragment.appendChild(ssRow)
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            tableBody.off("click", ".ss-jenis-row").on("click", ".ss-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".ss-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.ss-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableSs ) {
                                removeLoader()
                                tableSs.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals, level = 0 ) => {
                            const tr = document.createElement("tr")
                            tr.classList.add(level === 0 ? "ss-group-header" : "fw-bold")
                            tr.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.total) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return tr
                        }

                        fragment.appendChild(createTotalRow())

                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                            const totals = {
                                total: jenisMap.get("total") || 0,
                                totalPagu: jenisMap.get("totalPagu") || 0,
                                realisasi: (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0),
                                sisa: (jenisMap.get("total") || 0) - ((jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0))
                            }
                            fragment.appendChild( createGroupHeader( jenisLabel, totals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const { total, totalAmprah, totalRealisasi, totalPagu, sd } = {
                                    total: sdMap.get("total"),
                                    totalAmprah: sdMap.get("totalAmprah"),
                                    totalRealisasi: sdMap.get("totalRealisasi"),
                                    totalPagu: sdMap.get("totalPagu"),
                                    sd: sdMap.get("sumberdana")
                                }
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold")
                                const realisasi = totalAmprah + totalRealisasi
                                const sisa = total - realisasi
                                tr.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sd ?? 'Data Sumber Dana tidak ditemukan.'}</td>
                                    <td>${ rupiah( totalPagu || 0 ) }</td>
                                    <td>${ rupiah( total ) }</td>
                                    <td>${ rupiah( realisasi ) }</td>
                                    <td>${ rupiah( sisa ) }</td>
                                `
                                fragment.appendChild(tr)
                                sdMap.get("sub").forEach( ( ssMap, kodeSs ) => {
                                    const { ss, total, totalAmprah, totalRealisasi } = {
                                        ss: ssMap.get("ss"),
                                        total: ssMap.get("total"),
                                        totalAmprah: ssMap.get("totalAmprah"),
                                        totalRealisasi: ssMap.get("totalRealisasi")
                                    }
                                    const realisasi = totalAmprah + totalRealisasi
                                    const sisa = total - realisasi

                                    const tr = document.createElement("tr")
                                    const styleTR = !kodeSs ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                    tr.setAttribute("style", styleTR)
                                    tr.innerHTML = `
                                        <td>${kodeSs ?? '-'}</td>
                                        <td>${ss ?? 'Data IKU tidak ditemukan.'}</td>
                                        <td>-</td>
                                        <td>${ rupiah( total ) }</td>
                                        <td>${ rupiah( realisasi ) }</td>
                                        <td>${ rupiah( sisa ) }</td>
                                    `
                                    fragment.appendChild(tr)
                                })
                            })
                        })

                        tableBody.append(fragment)
                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableSs ) {
                            removeLoader()
                            tableSs.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }
                    })
                    .catch( err => {
                        console.error("Error loading sumberdana data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showIkkTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data COA...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd, false )
                    .then( baseMap => {
                        baseMap = window.laporan.tahunan.baseMap
                        if ( baseMap == null ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Data tidak ditemukan" )
                        }
                        const tableBody = $(".body-tbl-ro")
                        tableBody.html("")
                        const fragment = document.createDocumentFragment()
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text();

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);

                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });

                        baseMap.set("totalPagu", 0);

                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                sdMap.set("totalPagu", paguSd);
                                totalPaguJenis += paguSd;
                            });

                            jenisMap.set("totalPagu", totalPaguJenis);

                            baseMap.set(
                                "totalPagu",
                                (baseMap.get("totalPagu") || 0) + totalPaguJenis
                            );
                        });

                        const createTotalRow = () => {
                            const total = baseMap.get("total") || 0
                            const realisasi = (baseMap.get("totalAmprah") || 0) + (baseMap.get("totalRealisasi") || 0)
                            const sisa = total - realisasi
                            const totalPagu = baseMap.get("totalPagu") || 0
                            const totalRow = document.createElement("tr")
                            totalRow.classList.add("fw-bold", "total-row")
                            totalRow.innerHTML = `
                                <td>Total</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>${rupiah(totalPagu)}</td>
                                <td>${rupiah(total)}</td>
                                <td>${rupiah(realisasi)}</td>
                                <td>${rupiah(sisa)}</td>
                            `
                            return totalRow
                        }

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateIkkByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa = unitTotal - unitRealisasi
                                const unitPagu = perUnitMap.get("totalPagu") || 0
                                const unitRow = document.createElement("tr")
                                unitRow.classList.add("ro-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa = jenisTotal - jenisRealisasi
                                    const jenisPagu = jenisMap.get("totalPagu") || 0
                                    const groupId = `ro-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "ro-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="ro-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sdTotal = sdMap.get("total") || 0
                                        const sdRealisasi = (sdMap.get("totalAmprah") || 0) + (sdMap.get("totalRealisasi") || 0)
                                        const sdSisa = sdTotal - sdRealisasi
                                        const sdPagu = sdMap.get("totalPagu") || 0
                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("ro-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td style="padding-left: 10px">${kodeSd ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>${rupiah(sdPagu)}</td>
                                            <td>${rupiah(sdTotal)}</td>
                                            <td>${rupiah(sdRealisasi)}</td>
                                            <td>${rupiah(sdSisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)

                                        sdMap.get("sub").forEach((ssMap, kodeSs) => {
                                            const ssTotal = ssMap.get("total") || 0
                                            const ssRealisasi = (ssMap.get("totalAmprah") || 0) + (ssMap.get("totalRealisasi") || 0)
                                            const ssSisa = ssTotal - ssRealisasi
                                            const ssRow = document.createElement("tr")
                                            ssRow.classList.add("ro-detail-row")
                                            ssRow.setAttribute("data-parent-group-id", groupId)
                                            ssRow.style.display = "none"
                                            ssRow.innerHTML = `
                                                <td style="padding-left: 20px">${kodeSs ?? '-'}</td>
                                                <td>${ssMap.get("ss") || 'Data IKU tidak ditemukan.'}</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>${rupiah(ssTotal)}</td>
                                                <td>${rupiah(ssRealisasi)}</td>
                                                <td>${rupiah(ssSisa)}</td>
                                            `
                                            fragment.appendChild(ssRow)

                                            ssMap.get("sub").forEach((ikkMap, kodeIkk) => {
                                                const ikkTotal = ikkMap.get("total") || 0
                                                const ikkRealisasi = (ikkMap.get("totalAmprah") || 0) + (ikkMap.get("totalRealisasi") || 0)
                                                const ikkSisa = ikkTotal - ikkRealisasi
                                                const ikkRow = document.createElement("tr")
                                                ikkRow.classList.add("ro-detail-row")
                                                ikkRow.setAttribute("data-parent-group-id", groupId)
                                                ikkRow.style.display = "none"
                                                ikkRow.innerHTML = `
                                                    <td style="padding-left: 30px">${kodeIkk ?? '-'}</td>
                                                    <td>${ikkMap.get("ikk") || 'Data IKU tidak ditemukan.'}</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>${rupiah(ikkTotal)}</td>
                                                    <td>${rupiah(ikkRealisasi)}</td>
                                                    <td>${rupiah(ikkSisa)}</td>
                                                `
                                                fragment.appendChild(ikkRow)
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            tableBody.off("click", ".ro-jenis-row").on("click", ".ro-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".ro-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.ro-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableRo ) {
                                removeLoader()
                                tableRo.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals ) => {
                            const tr = document.createElement("tr")
                            tr.classList.add("ro-group-header")
                            tr.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.total) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return tr
                        }

                        fragment.appendChild(createTotalRow())

                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                            const totals = {
                                total: jenisMap.get("total") || 0,
                                totalPagu: jenisMap.get("totalPagu") || 0,
                                realisasi: (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0),
                                sisa: (jenisMap.get("total") || 0) - ((jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0))
                            }
                            fragment.appendChild( createGroupHeader( jenisLabel, totals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const { sd, total, totalAmprah, totalRealisasi, totalPagu } = {
                                    sd: sdMap.get("sumberdana"),
                                    total: sdMap.get("total"),
                                    totalAmprah: sdMap.get("totalAmprah"),
                                    totalRealisasi: sdMap.get("totalRealisasi"),
                                    totalPagu: sdMap.get("totalPagu")
                                }
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold")
                                const realisasi = totalAmprah + totalRealisasi
                                const sisa = total - realisasi
                                tr.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sd ?? 'Data Sumber Dana tidak ditemukan.'}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>${ rupiah( totalPagu || 0 ) }</td>
                                    <td>${ rupiah( total ) }</td>
                                    <td>${ rupiah( realisasi ) }</td>
                                    <td>${ rupiah( sisa ) }</td>
                                `
                                fragment.appendChild(tr)
                                sdMap.get("sub").forEach(( ssMap, kodeSs ) => {
                                    const { ss, total: ssTotal, totalAmprah: ssAmprah, totalRealisasi: ssRealisasi } = {
                                        ss: ssMap.get("ss"),
                                        total: ssMap.get("total"),
                                        totalAmprah: ssMap.get("totalAmprah"),
                                        totalRealisasi: ssMap.get("totalRealisasi")
                                    }
                                    const ssRealisasiVal = (ssAmprah || 0) + (ssRealisasi || 0)
                                    const ssSisa = (ssTotal || 0) - ssRealisasiVal
                                    const ssRow = document.createElement("tr")
                                    const ssStyle = !kodeSs ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                    ssRow.setAttribute("style", ssStyle)
                                    ssRow.innerHTML = `
                                        <td style="padding-left: 10px;">${kodeSs ?? '-'}</td>
                                        <td class="ss">${ss ?? 'Data IKU tidak ditemukan.'}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>${ rupiah( ssTotal || 0 ) }</td>
                                        <td>${ rupiah( ssRealisasiVal ) }</td>
                                        <td>${ rupiah( ssSisa ) }</td>
                                    `
                                    fragment.appendChild(ssRow)

                                    ssMap.get("sub").forEach( ( ikkMap, kodeIkk ) => {
                                        const { ikk, total, totalAmprah, totalRealisasi } = {
                                            ikk: ikkMap.get("ikk"),
                                            total: ikkMap.get("total"),
                                            totalAmprah: ikkMap.get("totalAmprah"),
                                            totalRealisasi: ikkMap.get("totalRealisasi")
                                        }
                                        const realisasi = totalAmprah + totalRealisasi
                                        const sisa = total - realisasi

                                        const tr = document.createElement("tr")
                                        const styleTR = !kodeIkk ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                        tr.setAttribute("style", styleTR)
                                        tr.innerHTML = `
                                            <td style="padding-left: 20px;">${!kodeIkk ? '-' : kodeIkk}</td>
                                            <td class="ikk">${ikk ?? 'Data tidak ditemukan'} ${smallLoadingSpin}</td>
                                            <td>-</td>
                                            <td class="cakin" key="${kodeIkk}">-</td>
                                            <td>-</td>
                                            <td>${ rupiah( total ) }</td>
                                            <td>${ rupiah( realisasi ) }</td>
                                            <td>${ rupiah( sisa ) }</td>
                                        `
                                        fragment.appendChild(tr)
                                    })
                                })
                            })
                        })

                        tableBody.append(fragment)

                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableRo ) {
                            removeLoader()
                            tableRo.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }
                    }).catch( err => {
                        console.error("Error loading sumberdana data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showIkvTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data ...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd, false )
                    .then( baseMap => {
                        const tableBody = $(".body-tbl-ikv")
                        tableBody.html("")
                        const fragment = document.createDocumentFragment()
                        baseMap = window.laporan.tahunan.baseMap
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text();

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);

                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });

                        baseMap.set("totalPagu", 0);

                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                sdMap.set("totalPagu", paguSd);
                                totalPaguJenis += paguSd;
                            });

                            jenisMap.set("totalPagu", totalPaguJenis);

                            baseMap.set(
                                "totalPagu",
                                (baseMap.get("totalPagu") || 0) + totalPaguJenis
                            );
                        });

                        const createTotalRow = () => {
                            const total = baseMap.get("total") || 0
                            const realisasi = (baseMap.get("totalAmprah") || 0) + (baseMap.get("totalRealisasi") || 0)
                            const sisa = total - realisasi
                            const totalPagu = baseMap.get("totalPagu") || 0
                            const totalRow = document.createElement("tr")
                            totalRow.classList.add("fw-bold", "total-row")
                            totalRow.innerHTML = `
                                <td>Total</td>
                                <td>-</td>
                                <td>${rupiah(totalPagu)}</td>
                                <td>${rupiah(total)}</td>
                                <td>${rupiah(realisasi)}</td>
                                <td>${rupiah(sisa)}</td>
                            `
                            return totalRow
                        }

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateIkvByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal     = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa      = unitTotal - unitRealisasi
                                const unitPagu      = perUnitMap.get("totalPagu") || 0
                                const unitRow       = document.createElement("tr")
                                unitRow.classList.add("ikv-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal     = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa      = jenisTotal - jenisRealisasi
                                    const jenisPagu      = jenisMap.get("totalPagu") || 0
                                    const groupId = `ikv-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow       = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "ikv-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="ikv-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sdTotal     = sdMap.get("total") || 0
                                        const sdRealisasi = (sdMap.get("totalAmprah") || 0) + (sdMap.get("totalRealisasi") || 0)
                                        const sdSisa      = sdTotal - sdRealisasi
                                        const sdPagu      = sdMap.get("totalPagu") || 0
                                        const sdRow       = document.createElement("tr")
                                        sdRow.classList.add("ikv-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td style="padding-left: 10px">${kodeSd ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>${rupiah(sdPagu)}</td>
                                            <td>${rupiah(sdTotal)}</td>
                                            <td>${rupiah(sdRealisasi)}</td>
                                            <td>${rupiah(sdSisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)

                                        sdMap.get("sub").forEach((ssMap, kodeSs) => {
                                            const ssTotal     = ssMap.get("total") || 0
                                            const ssRealisasi = (ssMap.get("totalAmprah") || 0) + (ssMap.get("totalRealisasi") || 0)
                                            const ssSisa      = ssTotal - ssRealisasi
                                            const ssRow       = document.createElement("tr")
                                            ssRow.classList.add("ikv-detail-row")
                                            ssRow.setAttribute("data-parent-group-id", groupId)
                                            ssRow.style.display = "none"
                                            ssRow.innerHTML = `
                                                <td style="padding-left: 20px">${kodeSs ?? '-'}</td>
                                                <td>${ssMap.get("ss") || 'Data IKU tidak ditemukan.'}</td>
                                                <td>-</td>
                                                <td>${rupiah(ssTotal)}</td>
                                                <td>${rupiah(ssRealisasi)}</td>
                                                <td>${rupiah(ssSisa)}</td>
                                            `
                                            fragment.appendChild(ssRow)

                                            ssMap.get("sub").forEach((ikkMap, kodeIkk) => {
                                                const ikkTotal     = ikkMap.get("total") || 0
                                                const ikkRealisasi = (ikkMap.get("totalAmprah") || 0) + (ikkMap.get("totalRealisasi") || 0)
                                                const ikkSisa      = ikkTotal - ikkRealisasi
                                                const ikkRow       = document.createElement("tr")
                                                ikkRow.classList.add("ikv-detail-row")
                                                ikkRow.setAttribute("data-parent-group-id", groupId)
                                                ikkRow.style.display = "none"
                                                ikkRow.innerHTML = `
                                                    <td style="padding-left: 30px">${kodeIkk ?? '-'}</td>
                                                    <td>${ikkMap.get("ikk") || 'Data IKU tidak ditemukan.'}</td>
                                                    <td>-</td>
                                                    <td>${rupiah(ikkTotal)}</td>
                                                    <td>${rupiah(ikkRealisasi)}</td>
                                                    <td>${rupiah(ikkSisa)}</td>
                                                `
                                                fragment.appendChild(ikkRow)

                                                ikkMap.get("sub").forEach((ikvMap, kodeIkv) => {
                                                    const ikvTotal     = ikvMap.get("total") || 0
                                                    const ikvRealisasi = (ikvMap.get("totalAmprah") || 0) + (ikvMap.get("totalRealisasi") || 0)
                                                    const ikvSisa      = ikvTotal - ikvRealisasi
                                                    const ikvRow       = document.createElement("tr")
                                                    ikvRow.classList.add("ikv-detail-row")
                                                    ikvRow.setAttribute("data-parent-group-id", groupId)
                                                    ikvRow.style.display = "none"
                                                    ikvRow.innerHTML = `
                                                        <td style="padding-left: 40px">${kodeIkv ?? '-'}</td>
                                                        <td>${ikvMap.get("ikv") || 'Data IKV tidak ditemukan.'}</td>
                                                        <td>-</td>
                                                        <td>${rupiah(ikvTotal)}</td>
                                                        <td>${rupiah(ikvRealisasi)}</td>
                                                        <td>${rupiah(ikvSisa)}</td>
                                                    `
                                                    fragment.appendChild(ikvRow)
                                                })
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            tableBody.off("click", ".ikv-jenis-row").on("click", ".ikv-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".ikv-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.ikv-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableIkv ) {
                                removeLoader()
                                tableIkv.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals ) => {
                            const tr = document.createElement("tr")
                            tr.classList.add("ikv-group-header")
                            tr.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.total) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return tr
                        }

                        fragment.appendChild(createTotalRow())

                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                            const totals = {
                                total: jenisMap.get("total") || 0,
                                totalPagu: jenisMap.get("totalPagu") || 0,
                                realisasi: (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0),
                                sisa: (jenisMap.get("total") || 0) - ((jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0))
                            }
                            fragment.appendChild( createGroupHeader( jenisLabel, totals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const { sd, total, totalAmprah, totalRealisasi, totalPagu } = {
                                    sd: sdMap.get("sumberdana"),
                                    total: sdMap.get("total"),
                                    totalAmprah: sdMap.get("totalAmprah"),
                                    totalRealisasi: sdMap.get("totalRealisasi"),
                                    totalPagu: sdMap.get("totalPagu")
                                }
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold")
                                const realisasi = totalAmprah + totalRealisasi
                                const sisa      = total - realisasi
                                tr.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sd ?? 'Data Sumber Dana tidak ditemukan.'}</td>
                                    <td>${ rupiah( totalPagu || 0 ) }</td>
                                    <td>${ rupiah( total ) }</td>
                                    <td>${ rupiah( realisasi ) }</td>
                                    <td>${ rupiah( sisa ) }</td>
                                `
                                fragment.appendChild(tr)
                                sdMap.get("sub").forEach( ( ssMap, kodeSs ) => {
                                    const { ss, total: ssTotal, totalAmprah: ssAmprah, totalRealisasi: ssRealisasi } = {
                                        ss: ssMap.get("ss"),
                                        total: ssMap.get("total"),
                                        totalAmprah: ssMap.get("totalAmprah"),
                                        totalRealisasi: ssMap.get("totalRealisasi")
                                    }
                                    const ssRealisasiVal = (ssAmprah || 0) + (ssRealisasi || 0)
                                    const ssSisa = (ssTotal || 0) - ssRealisasiVal
                                    const ssRow = document.createElement("tr")
                                    const ssStyle = !kodeSs ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                    ssRow.setAttribute("style", ssStyle)
                                    ssRow.innerHTML = `
                                        <td style="padding-left: 10px;">${kodeSs ?? '-'}</td>
                                        <td class="ss">${ss ?? 'Data IKU tidak ditemukan.'}</td>
                                        <td>-</td>
                                        <td>${ rupiah( ssTotal || 0 ) }</td>
                                        <td>${ rupiah( ssRealisasiVal ) }</td>
                                        <td>${ rupiah( ssSisa ) }</td>
                                    `
                                    fragment.appendChild(ssRow)

                                    ssMap.get("sub").forEach( ( ikkMap, kodeIkk ) => {
                                        const { ikk, total: ikkTotal, totalAmprah: ikkAmprah, totalRealisasi: ikkRealisasi } = {
                                            ikk: ikkMap.get("ikk"),
                                            total: ikkMap.get("total"),
                                            totalAmprah: ikkMap.get("totalAmprah"),
                                            totalRealisasi: ikkMap.get("totalRealisasi")
                                        }
                                        const ikkRealisasiVal = (ikkAmprah || 0) + (ikkRealisasi || 0)
                                        const ikkSisa = (ikkTotal || 0) - ikkRealisasiVal
                                        const ikkRow = document.createElement("tr")
                                        const ikkStyle = !kodeIkk ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                        ikkRow.setAttribute("style", ikkStyle)
                                        ikkRow.innerHTML = `
                                            <td style="padding-left: 20px;">${kodeIkk ?? '-'}</td>
                                            <td class="ikk">${ikk ?? 'Data tidak ditemukan'}</td>
                                            <td>-</td>
                                            <td>${ rupiah( ikkTotal || 0 ) }</td>
                                            <td>${ rupiah( ikkRealisasiVal ) }</td>
                                            <td>${ rupiah( ikkSisa ) }</td>
                                        `
                                        fragment.appendChild(ikkRow)

                                        ikkMap.get("sub").forEach( ( ikvMap, kodeIkv ) => {
                                            const { ikv, total: ikvTotal, totalAmprah: ikvAmprah, totalRealisasi: ikvRealisasi } = {
                                                ikv: ikvMap.get("ikv"),
                                                total: ikvMap.get("total"),
                                                totalAmprah: ikvMap.get("totalAmprah"),
                                                totalRealisasi: ikvMap.get("totalRealisasi")
                                            }
                                            const ikvRealisasiVal = (ikvAmprah || 0) + (ikvRealisasi || 0)
                                            const ikvSisa = (ikvTotal || 0) - ikvRealisasiVal
                                            const ikvRow = document.createElement("tr")
                                            const ikvStyle = !kodeIkv ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                            ikvRow.setAttribute("style", ikvStyle)
                                            ikvRow.innerHTML = `
                                                <td style="padding-left: 30px;">${kodeIkv ?? '-'}</td>
                                                <td class="ikv">${ikv ?? 'Data tidak ditemukan'}</td>
                                                <td>-</td>
                                                <td>${ rupiah( ikvTotal || 0 ) }</td>
                                                <td>${ rupiah( ikvRealisasiVal ) }</td>
                                                <td>${ rupiah( ikvSisa ) }</td>
                                            `
                                            fragment.appendChild(ikvRow)
                                        })
                                    })
                                })
                            })
                        })

                        tableBody.append(fragment)

                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableIkv ) {
                            removeLoader()
                            tableIkv.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }
                    }).catch( err => {
                        console.error("Error loading sumberdana data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showKegTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data ...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd, false )
                    .then( baseMap => {
                        const tableBody = $(".body-tbl-keg")
                        tableBody.html("")
                        const fragment = document.createDocumentFragment()
                        baseMap = window.laporan.tahunan.baseMap
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text();

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);

                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });

                        baseMap.set("totalPagu", 0);

                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                sdMap.set("totalPagu", paguSd);
                                totalPaguJenis += paguSd;
                            });

                            jenisMap.set("totalPagu", totalPaguJenis);

                            baseMap.set(
                                "totalPagu",
                                (baseMap.get("totalPagu") || 0) + totalPaguJenis
                            );
                        });

                        const createTotalRow = () => {
                            const total = baseMap.get("total") || 0
                            const realisasi = (baseMap.get("totalAmprah") || 0) + (baseMap.get("totalRealisasi") || 0)
                            const sisa = total - realisasi
                            const totalPagu = baseMap.get("totalPagu") || 0
                            const totalRow = document.createElement("tr")
                            totalRow.classList.add("fw-bold", "total-row")
                            totalRow.innerHTML = `
                                <td>Total</td>
                                <td>-</td>
                                <td>${rupiah(totalPagu)}</td>
                                <td>${rupiah(total)}</td>
                                <td>${rupiah(realisasi)}</td>
                                <td>${rupiah(sisa)}</td>
                            `
                            return totalRow
                        }

                        if (isSemuaUnit) {
                            const unitMap = window.laporan.tahunan.methods.generateKegByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal = perUnitMap.get("total") || 0
                                const unitRealisasi = (perUnitMap.get("totalAmprah") || 0) + (perUnitMap.get("totalRealisasi") || 0)
                                const unitSisa = unitTotal - unitRealisasi
                                const unitPagu = perUnitMap.get("totalPagu") || 0
                                const unitRow = document.createElement("tr")
                                unitRow.classList.add("keg-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    <td>${rupiah(unitTotal)}</td>
                                    <td>${rupiah(unitRealisasi)}</td>
                                    <td>${rupiah(unitSisa)}</td>
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal = jenisMap.get("total") || 0
                                    const jenisRealisasi = (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0)
                                    const jenisSisa = jenisTotal - jenisRealisasi
                                    const jenisPagu = jenisMap.get("totalPagu") || 0
                                    const groupId = `keg-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "keg-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="keg-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        <td>${rupiah(jenisTotal)}</td>
                                        <td>${rupiah(jenisRealisasi)}</td>
                                        <td>${rupiah(jenisSisa)}</td>
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sdTotal = sdMap.get("total") || 0
                                        const sdRealisasi = (sdMap.get("totalAmprah") || 0) + (sdMap.get("totalRealisasi") || 0)
                                        const sdSisa = sdTotal - sdRealisasi
                                        const sdPagu = sdMap.get("totalPagu") || 0
                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("keg-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td style="padding-left: 10px">${kodeSd ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>${rupiah(sdPagu)}</td>
                                            <td>${rupiah(sdTotal)}</td>
                                            <td>${rupiah(sdRealisasi)}</td>
                                            <td>${rupiah(sdSisa)}</td>
                                        `
                                        fragment.appendChild(sdRow)

                                        sdMap.get("sub").forEach((ssMap, kodeSs) => {
                                            const ssTotal = ssMap.get("total") || 0
                                            const ssRealisasi = (ssMap.get("totalAmprah") || 0) + (ssMap.get("totalRealisasi") || 0)
                                            const ssSisa = ssTotal - ssRealisasi
                                            const ssRow = document.createElement("tr")
                                            ssRow.classList.add("keg-detail-row")
                                            ssRow.setAttribute("data-parent-group-id", groupId)
                                            ssRow.style.display = "none"
                                            ssRow.innerHTML = `
                                                <td style="padding-left: 20px">${kodeSs ?? '-'}</td>
                                                <td>${ssMap.get("ss") || 'Data IKU tidak ditemukan.'}</td>
                                                <td>-</td>
                                                <td>${rupiah(ssTotal)}</td>
                                                <td>${rupiah(ssRealisasi)}</td>
                                                <td>${rupiah(ssSisa)}</td>
                                            `
                                            fragment.appendChild(ssRow)

                                            ssMap.get("sub").forEach((ikkMap, kodeIkk) => {
                                                const ikkTotal = ikkMap.get("total") || 0
                                                const ikkRealisasi = (ikkMap.get("totalAmprah") || 0) + (ikkMap.get("totalRealisasi") || 0)
                                                const ikkSisa = ikkTotal - ikkRealisasi
                                                const ikkRow = document.createElement("tr")
                                                ikkRow.classList.add("keg-detail-row")
                                                ikkRow.setAttribute("data-parent-group-id", groupId)
                                                ikkRow.style.display = "none"
                                                ikkRow.innerHTML = `
                                                    <td style="padding-left: 30px">${kodeIkk ?? '-'}</td>
                                                    <td>${ikkMap.get("ikk") || 'Data IKU tidak ditemukan.'}</td>
                                                    <td>-</td>
                                                    <td>${rupiah(ikkTotal)}</td>
                                                    <td>${rupiah(ikkRealisasi)}</td>
                                                    <td>${rupiah(ikkSisa)}</td>
                                                `
                                                fragment.appendChild(ikkRow)

                                                ikkMap.get("sub").forEach((ikvMap, kodeIkv) => {
                                                    const ikvTotal = ikvMap.get("total") || 0
                                                    const ikvRealisasi = (ikvMap.get("totalAmprah") || 0) + (ikvMap.get("totalRealisasi") || 0)
                                                    const ikvSisa = ikvTotal - ikvRealisasi
                                                    const ikvRow = document.createElement("tr")
                                                    ikvRow.classList.add("keg-detail-row")
                                                    ikvRow.setAttribute("data-parent-group-id", groupId)
                                                    ikvRow.style.display = "none"
                                                    ikvRow.innerHTML = `
                                                        <td style="padding-left: 40px">${kodeIkv ?? '-'}</td>
                                                        <td>${ikvMap.get("ikv") || 'Data IKV tidak ditemukan.'}</td>
                                                        <td>-</td>
                                                        <td>${rupiah(ikvTotal)}</td>
                                                        <td>${rupiah(ikvRealisasi)}</td>
                                                        <td>${rupiah(ikvSisa)}</td>
                                                    `
                                                    fragment.appendChild(ikvRow)

                                                    ikvMap.get("sub").forEach((kegMap, kodeKeg) => {
                                                        const kegTotal = kegMap.get("total") || 0
                                                        const kegRealisasi = (kegMap.get("totalAmprah") || 0) + (kegMap.get("totalRealisasi") || 0)
                                                        const kegSisa = kegTotal - kegRealisasi
                                                        const kegRow = document.createElement("tr")
                                                        kegRow.classList.add("keg-detail-row")
                                                        kegRow.setAttribute("data-parent-group-id", groupId)
                                                        kegRow.style.display = "none"
                                                        kegRow.innerHTML = `
                                                            <td style="padding-left: 50px">${kodeKeg ?? '-'}</td>
                                                            <td>${kegMap.get("rincianKeg") || 'Data kegiatan tidak ditemukan.'}</td>
                                                            <td>-</td>
                                                            <td>${rupiah(kegTotal)}</td>
                                                            <td>${rupiah(kegRealisasi)}</td>
                                                            <td>${rupiah(kegSisa)}</td>
                                                        `
                                                        fragment.appendChild(kegRow)
                                                    })
                                                })
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            tableBody.off("click", ".keg-jenis-row").on("click", ".keg-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".keg-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.keg-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            if ( !isPdf && tableSubkomponen ) {
                                removeLoader()
                                tableSubkomponen.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                            return
                        }

                        const createGroupHeader = ( label, totals ) => {
                            const tr = document.createElement("tr")
                            tr.classList.add("keg-group-header")
                            tr.innerHTML = `
                                <td>${label}</td>
                                <td>-</td>
                                <td>${totals ? rupiah(totals.totalPagu) : '-'}</td>
                                <td>${totals ? rupiah(totals.total) : '-'}</td>
                                <td>${totals ? rupiah(totals.realisasi) : '-'}</td>
                                <td>${totals ? rupiah(totals.sisa) : '-'}</td>
                            `
                            return tr
                        }

                        fragment.appendChild(createTotalRow())

                        baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                            const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                            const totals = {
                                total: jenisMap.get("total") || 0,
                                totalPagu: jenisMap.get("totalPagu") || 0,
                                realisasi: (jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0),
                                sisa: (jenisMap.get("total") || 0) - ((jenisMap.get("totalAmprah") || 0) + (jenisMap.get("totalRealisasi") || 0))
                            }
                            fragment.appendChild( createGroupHeader( jenisLabel, totals ) )

                            jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                const { sd, total, totalAmprah, totalRealisasi, totalPagu } = {
                                    sd: sdMap.get("sumberdana"),
                                    total: sdMap.get("total"),
                                    totalAmprah: sdMap.get("totalAmprah"),
                                    totalRealisasi: sdMap.get("totalRealisasi"),
                                    totalPagu: sdMap.get("totalPagu")
                                }
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold")
                                const realisasi = totalAmprah + totalRealisasi
                                const sisa = total - realisasi
                                tr.innerHTML = `
                                    <td>${kodeSd ?? '-'}</td>
                                    <td>${sd ?? 'Data Sumber Dana tidak ditemukan.'}</td>
                                    <td>${ rupiah( totalPagu || 0 ) }</td>
                                    <td>${ rupiah( total ) }</td>
                                    <td>${ rupiah( realisasi ) }</td>
                                    <td>${ rupiah( sisa ) }</td>
                                `
                                fragment.appendChild(tr)
                                sdMap.get("sub").forEach( ( ssMap, kodeSs ) => {
                                    const { ss, total: ssTotal, totalAmprah: ssAmprah, totalRealisasi: ssRealisasi } = {
                                        ss: ssMap.get("ss"),
                                        total: ssMap.get("total"),
                                        totalAmprah: ssMap.get("totalAmprah"),
                                        totalRealisasi: ssMap.get("totalRealisasi")
                                    }
                                    const ssRealisasiVal = (ssAmprah || 0) + (ssRealisasi || 0)
                                    const ssSisa = (ssTotal || 0) - ssRealisasiVal
                                    const ssRow = document.createElement("tr")
                                    const ssStyle = !kodeSs ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                    ssRow.setAttribute("style", ssStyle)
                                    ssRow.innerHTML = `
                                        <td style="padding-left: 10px;">${kodeSs ?? '-'}</td>
                                        <td class="ss">${ss ?? 'Data IKU tidak ditemukan.'}</td>
                                        <td>-</td>
                                        <td>${ rupiah( ssTotal || 0 ) }</td>
                                        <td>${ rupiah( ssRealisasiVal ) }</td>
                                        <td>${ rupiah( ssSisa ) }</td>
                                    `
                                    fragment.appendChild(ssRow)

                                    ssMap.get("sub").forEach( ( ikkMap, kodeIkk ) => {
                                        const { ikk, total: ikkTotal, totalAmprah: ikkAmprah, totalRealisasi: ikkRealisasi } = {
                                            ikk: ikkMap.get("ikk"),
                                            total: ikkMap.get("total"),
                                            totalAmprah: ikkMap.get("totalAmprah"),
                                            totalRealisasi: ikkMap.get("totalRealisasi")
                                        }
                                        const ikkRealisasiVal = (ikkAmprah || 0) + (ikkRealisasi || 0)
                                        const ikkSisa = (ikkTotal || 0) - ikkRealisasiVal
                                        const ikkRow = document.createElement("tr")
                                        const ikkStyle = !kodeIkk ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                        ikkRow.setAttribute("style", ikkStyle)
                                        ikkRow.innerHTML = `
                                            <td style="padding-left: 20px;">${kodeIkk ?? '-'}</td>
                                            <td class="ikk">${ikk ?? 'Data tidak ditemukan'}</td>
                                            <td>-</td>
                                            <td>${ rupiah( ikkTotal || 0 ) }</td>
                                            <td>${ rupiah( ikkRealisasiVal ) }</td>
                                            <td>${ rupiah( ikkSisa ) }</td>
                                        `
                                        fragment.appendChild(ikkRow)

                                        ikkMap.get("sub").forEach( ( ikvMap, kodeIkv ) => {
                                            const { ikv, total: ikvTotal, totalAmprah: ikvAmprah, totalRealisasi: ikvRealisasi } = {
                                                ikv: ikvMap.get("ikv"),
                                                total: ikvMap.get("total"),
                                                totalAmprah: ikvMap.get("totalAmprah"),
                                                totalRealisasi: ikvMap.get("totalRealisasi")
                                            }
                                            const ikvRealisasiVal = (ikvAmprah || 0) + (ikvRealisasi || 0)
                                            const ikvSisa = (ikvTotal || 0) - ikvRealisasiVal
                                            const ikvRow = document.createElement("tr")
                                            const ikvStyle = !kodeIkv ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                            ikvRow.setAttribute("style", ikvStyle)
                                            ikvRow.innerHTML = `
                                                <td style="padding-left: 30px;">${kodeIkv ?? '-'}</td>
                                                <td class="ikv">${ikv ?? 'Data tidak ditemukan'}</td>
                                                <td>-</td>
                                                <td>${ rupiah( ikvTotal || 0 ) }</td>
                                                <td>${ rupiah( ikvRealisasiVal ) }</td>
                                                <td>${ rupiah( ikvSisa ) }</td>
                                            `
                                            fragment.appendChild(ikvRow)

                                            ikvMap.get("sub").forEach( ( kegMap, kodeKeg ) => {
                                                const { rincianKeg, total, totalAmprah, totalRealisasi } = {
                                                    rincianKeg: kegMap.get("rincianKeg"),
                                                    total: kegMap.get("total"),
                                                    totalAmprah: kegMap.get("totalAmprah"),
                                                    totalRealisasi: kegMap.get("totalRealisasi")
                                                }
                                                const realisasi = totalAmprah + totalRealisasi
                                                const sisa = total - realisasi
                                                const styleTR = !kodeKeg ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                                const tr = document.createElement("tr")
                                                tr.setAttribute("style", styleTR)
                                                tr.innerHTML = `
                                                    <td style="padding-left: 40px;">${kodeKeg ?? '-'}</td>
                                                    <td class="keg">${rincianKeg ?? 'Data tidak ditemukan'}</td>
                                                    <td>-</td>
                                                    <td>${ rupiah( total ) }</td>
                                                    <td>${ rupiah( realisasi ) }</td>
                                                    <td>${ rupiah( sisa ) }</td>
                                                `
                                                fragment.appendChild(tr)
                                            })
                                        })
                                    })
                                })
                            })
                        })

                        tableBody.append(fragment)
                        $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                        // Update DataTable only if not in PDF mode
                        if ( !isPdf && tableSubkomponen ) {
                            removeLoader()
                            tableSubkomponen.clear().rows.add(tableBody.find("tr")).draw()
                        } else {
                            $(".loading-msg").hide()
                        }
                    }).catch( err => {
                        console.error("Error loading sumberdana data:", err)
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                        }
                    })
            },
            showRiwayatTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data ...")
                }
                window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd, false )
                    .then( baseMap => {
                        baseMap = window.laporan.tahunan.baseMap
                        const isSemuaUnit = (Array.isArray(idunit) && idunit.includes("semua")) || idunit === "semua"
                        const alokasiRaw  = window.laporan.tahunan.alokasiRaw || []
                        const paguByKodeSd = new Map();
                        const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                        const selectedUnitsText = $(".selected-text-unit").text();

                        alokasiRaw.forEach((item) => {
                            const unitSekarang = Number(item.unit_kerja);
                            if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                            const kodeSd = String(item?.kd_sumberdana || "-");
                            const pagu = Number(item?.total_pagu || 0);

                            const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                            paguByKodeSd.set(kodeSd, currentPagu + pagu);
                        });

                        baseMap.set("totalPagu", 0);

                        baseMap.get("sub").forEach((jenisMap) => {
                            let totalPaguJenis = 0;

                            jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                sdMap.set("totalPagu", paguSd);
                                totalPaguJenis += paguSd;
                            });

                            jenisMap.set("totalPagu", totalPaguJenis);

                            baseMap.set(
                                "totalPagu",
                                (baseMap.get("totalPagu") || 0) + totalPaguJenis
                            );
                        });

                        if (isSemuaUnit) {
                            const tableBody = $(".body-tbl-riwayat")
                            tableBody.html("")
                            const fragment = document.createDocumentFragment()
                            const unitMap = window.laporan.tahunan.methods.generateRekatByUnitMap(window.laporan.tahunan.baseDataRaw || [])
                            const createRev0Cell = (kode, total) => `<td class="REV0-RIWAYAT" data-kode="${kode}">${rupiah(total || 0)}</td>`
                            const createRevCells = (kode) => Array.from({length: 12}, (_, i) => `<td class="REV${i+1}-RIWAYAT" data-kode="${kode}">-</td>`).join('')
                            const createTotalRow = () => {
                                const total = baseMap.get("total") || 0
                                const totalPagu = baseMap.get("totalPagu") || 0
                                const totalRow = document.createElement("tr")
                                totalRow.classList.add("fw-bold", "total-row")
                                totalRow.innerHTML = `
                                    <td>Total</td>
                                    <td>-</td>
                                    <td>${rupiah(totalPagu)}</td>
                                    ${createRev0Cell("TOTAL", total)}
                                    ${createRevCells("TOTAL")}
                                `
                                return totalRow
                            }

                            fragment.appendChild(createTotalRow())

                            unitMap.forEach((perUnitMap, unitKey) => {
                                const unitTotal = perUnitMap.get("total") || 0
                                const unitPagu = perUnitMap.get("totalPagu") || 0
                                const unitRow = document.createElement("tr")
                                unitRow.classList.add("ro-group-header")
                                unitRow.innerHTML = `
                                    <td>${unitKey}</td>
                                    <td>${perUnitMap.get("namaUnit") || '-'}</td>
                                    <td>${rupiah(unitPagu)}</td>
                                    ${createRev0Cell(unitKey, unitTotal)}
                                    ${createRevCells(unitKey)}
                                `
                                fragment.appendChild(unitRow)

                                perUnitMap.get("sub").forEach((jenisMap, jenisKey) => {
                                    const jenisTotal = jenisMap.get("total") || 0
                                    const jenisPagu = jenisMap.get("totalPagu") || 0
                                    const groupId = `riwayat-group-${String(unitKey).replace(/[^a-zA-Z0-9]/g, "_")}-${String(jenisKey).replace(/[^a-zA-Z0-9]/g, "_")}`
                                    const jenisRow = document.createElement("tr")
                                    jenisRow.classList.add("fw-bold", "riwayat-jenis-row")
                                    jenisRow.setAttribute("data-group-id", groupId)
                                    jenisRow.setAttribute("data-expanded", "false")
                                    jenisRow.style.cursor = "pointer"
                                    jenisRow.innerHTML = `
                                        <td><span class="riwayat-toggle-indicator me-1">▸</span>${jenisKey}</td>
                                        <td>-</td>
                                        <td>${rupiah(jenisPagu)}</td>
                                        ${createRev0Cell(jenisKey, jenisTotal)}
                                        ${createRevCells(jenisKey)}
                                    `
                                    fragment.appendChild(jenisRow)

                                    jenisMap.get("sub").forEach((sdMap, kodeSd) => {
                                        const sdTotal = sdMap.get("total") || 0
                                        const sdPagu = sdMap.get("totalPagu") || 0
                                        const sdRow = document.createElement("tr")
                                        sdRow.classList.add("riwayat-detail-row")
                                        sdRow.setAttribute("data-parent-group-id", groupId)
                                        sdRow.style.display = "none"
                                        sdRow.innerHTML = `
                                            <td>${kodeSd ?? '-'}</td>
                                            <td>${sdMap.get("sumberdana") || '-'}</td>
                                            <td>${rupiah(sdPagu)}</td>
                                            ${createRev0Cell(kodeSd, sdTotal)}
                                            ${createRevCells(kodeSd)}
                                        `
                                        fragment.appendChild(sdRow)

                                        sdMap.get("sub").forEach((rekatMap, idRekat) => {
                                            const rekatTotal = rekatMap.get("total") || 0
                                            const rekatRow = document.createElement("tr")
                                            rekatRow.classList.add("riwayat-detail-row")
                                            rekatRow.setAttribute("data-parent-group-id", groupId)
                                            rekatRow.style.display = "none"
                                            rekatRow.innerHTML = `
                                                <td>${idRekat ?? '-'}</td>
                                                <td>${rekatMap.get("subJudul") || '-'}</td>
                                                <td>-</td>
                                                ${createRev0Cell(idRekat, rekatTotal)}
                                                ${createRevCells(idRekat)}
                                            `
                                            fragment.appendChild(rekatRow)
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)
                            const finalizeRiwayatSemuaUnit = () => {
                                $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                                if ( !isPdf && tableRiwayat ) {
                                    removeLoader()
                                    tableRiwayat.clear().rows.add(tableBody.find("tr")).draw()
                                } else {
                                    $(".loading-msg").hide()
                                }
                            }

                            tableBody.off("click", ".riwayat-jenis-row").on("click", ".riwayat-jenis-row", function () {
                                const groupId = $(this).attr("data-group-id")
                                const expanded = $(this).attr("data-expanded") === "true"
                                const nextState = !expanded
                                $(this).attr("data-expanded", String(nextState))
                                $(this).find(".riwayat-toggle-indicator").text(nextState ? "▾" : "▸")
                                tableBody
                                    .find(`.riwayat-detail-row[data-parent-group-id="${groupId}"]`)
                                    .toggle(nextState)
                            })
                            window.laporan.tahunan.methods.generateBaseBackupMap( idunit, targetKodeSd )
                                .then((backupMap) => {
                                    const revHeaders = $(".revHeaderRiwayat").get()
                                        .map(el => $(el).text().trim().replace(/\s+/g, ''))
                                        .filter(name => name)

                                    revHeaders.forEach((revKey) => {
                                        const totalByJenis = new Map()
                                        const totalBySd = new Map()
                                        const totalByUnit = new Map()
                                        const totalByRekat = new Map()
                                        let totalRev = 0
                                        backupMap.get("sub").forEach((jenisBackupMap, jenisKey) => {
                                            const selectedRevMap = jenisBackupMap.get("sub")?.get(revKey)
                                            if ( !selectedRevMap ) return

                                            const jenisTotal = Number(selectedRevMap.get("total") || 0)
                                            totalRev += jenisTotal
                                            totalByJenis.set(jenisKey, (totalByJenis.get(jenisKey) || 0) + jenisTotal)

                                            traverseToMap(selectedRevMap, (sdMap, keys) => {
                                                const [kodeSd] = keys
                                                const total = Number(sdMap.get("total") || 0)
                                                totalBySd.set(kodeSd, (totalBySd.get(kodeSd) || 0) + total)
                                            }, 1)

                                            traverseToMap(selectedRevMap, (unitMap, keys) => {
                                                const [, , , , , idunitKey] = keys
                                                const total = Number(unitMap.get("total") || 0)
                                                totalByUnit.set(idunitKey, (totalByUnit.get(idunitKey) || 0) + total)
                                            }, 6)

                                            traverseToMap(selectedRevMap, (rekatMap, keys) => {
                                                const [, , , , , , idRekat] = keys
                                                const total = Number(rekatMap.get("total") || 0)
                                                totalByRekat.set(idRekat, (totalByRekat.get(idRekat) || 0) + total)
                                            }, 7)
                                        })

                                        totalByJenis.forEach((value, key) => {
                                            $(`.${revKey}-RIWAYAT[data-kode="${key}"]`).text(rupiah(value))
                                        })
                                        totalBySd.forEach((value, key) => {
                                            $(`.${revKey}-RIWAYAT[data-kode="${key}"]`).text(rupiah(value))
                                        })
                                        totalByUnit.forEach((value, key) => {
                                            $(`.${revKey}-RIWAYAT[data-kode="${key}"]`).text(rupiah(value))
                                        })
                                        totalByRekat.forEach((value, key) => {
                                            $(`.${revKey}-RIWAYAT[data-kode="${key}"]`).text(rupiah(value))
                                        })

                                        $(`.${revKey}-RIWAYAT[data-kode="TOTAL"]`).text(rupiah(totalRev))
                                    })
                                    finalizeRiwayatSemuaUnit()
                                })
                                .catch(() => {
                                    finalizeRiwayatSemuaUnit()
                                })
                            return
                        }

                        // todo: tampilkan data basemap pada kolom
                        window.laporan.tahunan.methods.generateBaseBackupMap( idunit, targetKodeSd )
                        .then( baseBackupMap => {
                            const tableBody = $(".body-tbl-riwayat")
                            tableBody.html("")
                            baseBackupMap = window.laporan.tahunan.baseBackupMap
                            const baseMap = window.laporan.tahunan.baseMap

                            const revHeaders = $(".revHeaderRiwayat").get()
                                .map(el => $(el).text().trim().replace(/\s+/g, ''))
                                .filter(name => name)

                            const fragment = document.createDocumentFragment()
                            const createRev0Cell = ( kode, total ) => `<td class="REV0-RIWAYAT" data-kode="${kode}">${rupiah(total || 0)}</td>`
                            const createRevCells = ( kode ) => Array.from({length: 12}, (_, i) => `<td class="REV${i+1}-RIWAYAT" data-kode="${kode}">-</td>`).join('')

                            const createGroupHeader = ( label, jenisBackupMap, jenisBaseMap ) => {
                                const safeLabel = label.replace(/\s+/g, '-')
                                const rev0Total = jenisBaseMap?.get("total") || 0
                                const totalPagu = jenisBaseMap?.get("totalPagu") || 0
                                const revCells = revHeaders.map((revKey) => {
                                    const revMap = jenisBackupMap?.get("sub")?.get(revKey)
                                    const total = revMap ? (revMap.get("total") || 0) : null
                                    return `<td class="rev-total" data-jenis="${safeLabel}" data-rev="${revKey}">${total ? rupiah(total) : '-'}</td>`
                                }).join('')
                                const tr = document.createElement("tr")
                                tr.classList.add("ro-group-header")
                                tr.innerHTML = `
                                    <td>${label}</td>
                                    <td>-</td>
                                    <td>${rupiah(totalPagu)}</td>
                                    ${createRev0Cell(safeLabel, rev0Total)}
                                    ${revCells}
                                `
                                return tr
                            }

                            const createTotalRow = () => {
                                const total = baseMap.get("total") || 0
                                const totalPagu = baseMap.get("totalPagu") || 0
                                const revCells = createRevCells("TOTAL")
                                const totalRow = document.createElement("tr")
                                totalRow.classList.add("fw-bold", "total-row")
                                totalRow.innerHTML = `
                                    <td>Total</td>
                                    <td>-</td>
                                    <td>${rupiah(totalPagu)}</td>
                                    ${createRev0Cell("TOTAL", total)}
                                    ${revCells}
                                `
                                return totalRow
                            }

                            const createIkkRow = ( kodeIkk, ikk, total = 0 ) => {
                                const tr = document.createElement("tr")
                                const trStyle = !kodeIkk ? 'background-color: #f0f0f0; font-weight: bold; color: red' : ''
                                tr.setAttribute("style", trStyle)
                                tr.innerHTML = `
                                    <th>${kodeIkk ?? '-'}</th>
                                    <th class="ikk">${ikk ?? 'Data tidak ditemukan'}</th>
                                    <th>-</th>
                                    ${createRev0Cell(kodeIkk, total)}
                                    ${createRevCells(kodeIkk)}
                                `
                                return tr
                            }

                            const createRekatRow = ( idRekat, subJudul, total = 0 ) => {
                                const tr = document.createElement("tr")
                                tr.innerHTML = `
                                    <td>${idRekat}</td>
                                    <td>${subJudul}</td>
                                    <td>-</td>
                                    ${createRev0Cell(idRekat, total)}
                                    ${createRevCells(idRekat)}
                                `
                                return tr
                            }

                            fragment.appendChild(createTotalRow())

                            baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                                const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey

                                // append group header for Non APBN / APBN with per-rev totals
                                fragment.appendChild( createGroupHeader( jenisLabel, baseBackupMap.get("sub").get(jenisKey), jenisMap ) )

                                jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                    const { sd, total, totalAmprah, totalRealisasi, totalPagu } = {
                                        sd: sdMap.get("sumberdana"),
                                        total: sdMap.get("total"),
                                        totalAmprah: sdMap.get("totalAmprah"),
                                        totalRealisasi: sdMap.get("totalRealisasi"),
                                        totalPagu: sdMap.get("totalPagu")
                                    }
                                    const tr = document.createElement("tr")
                                    tr.classList.add("fw-bold")
                                    const realisasi = totalAmprah + totalRealisasi
                                    const sisa = total - realisasi
                                    tr.innerHTML = `
                                        <td>${kodeSd ?? '-'}</td>
                                        <td>${sd ?? 'Data Sumber Dana tidak ditemukan.'}</td>
                                        <td>${rupiah(totalPagu || 0)}</td>
                                        ${createRev0Cell(kodeSd, total)}
                                        ${createRevCells(kodeSd)}
                                    `
                                    fragment.appendChild(tr)
                                    sdMap.get("sub").forEach( ( ssMap ) => {
                                        ssMap.get("sub").forEach( ( ikkMap, kodeIkk ) => {
                                            const ikkRow = createIkkRow( kodeIkk, ikkMap.get("ikk"), ikkMap.get("total") )
                                            fragment.appendChild( ikkRow )
                                            // collect rekap (subJudul) rows under this IKK
                                            ikkMap.get("sub").forEach( ikvMap => {
                                                ikvMap.get("sub").forEach( kegMap => {
                                                    kegMap.get("sub").forEach( unitMap => {
                                                        unitMap.get("sub").forEach( ( rekatMap, idRekat ) => {
                                                            const subJudul = rekatMap.get("subJudul")
                                                            const rekatRow = createRekatRow( idRekat, subJudul, rekatMap.get("total") )
                                                            fragment.appendChild( rekatRow )
                                                        })
                                                    })
                                                })
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)

                            $(".revHeaderRiwayat").each( ( idx, el ) => {
                                const $el = $(el)
                                const baseLabel = $el.data("baseLabel") || $el.text().trim()
                                $el.data("baseLabel", baseLabel)

                                const selectedRevName = baseLabel.replace(/\s+/g, '')

                                // Skip if selectedRevName is undefined, null, or empty
                                if (!selectedRevName || selectedRevName === '')
                                    return true

                                let totalRev = 0
                                baseBackupMap.get("sub").forEach( ( jenisBackupMap ) => {
                                    const selectedRevMap = jenisBackupMap.get("sub").get(selectedRevName)
                                    if ( !selectedRevMap ) return true

                                    totalRev += Number(selectedRevMap.get("total") || 0)

                                    traverseToMap( selectedRevMap, ( riwayatMap, keys) => {
                                        const [ kodeSd, kodeSs, kodeIkk, kodeIkv, kodeKeg, idunit, idRekat, idCoa, idItem ] = keys
                                        // some intermediate nodes may not have namaRevisi stored; fall back to the header name
                                        const namaRevisi = riwayatMap.get("namaRevisi") || selectedRevName
                                        const total = riwayatMap.get("total") || 0
                                        $(`.${namaRevisi}-RIWAYAT[data-kode="${kodeSd}"]`).text( rupiah(total) )
                                    }, 1)
                                    traverseToMap( selectedRevMap, ( riwayatMap, keys) => {
                                        const [ kodeSd, kodeSs, kodeIkk, kodeIkv, kodeKeg, idunit, idRekat, idCoa, idItem ] = keys
                                        const namaRevisi = riwayatMap.get("namaRevisi") || selectedRevName
                                        const total = riwayatMap.get("total") || 0
                                        $(`.${namaRevisi}-RIWAYAT[data-kode="${kodeIkk}"]`).text( rupiah(total) )
                                    }, 3)
                                    traverseToMap( selectedRevMap, ( rekatMap, keys) => {
                                        const [ kodeSd, kodeSs, kodeIkk, kodeIkv, kodeKeg, idunit, idRekat ] = keys
                                        const namaRevisi = rekatMap.get("namaRevisi") || selectedRevName
                                        const total = rekatMap.get("total") || 0
                                        $(`.${namaRevisi}-RIWAYAT[data-kode="${idRekat}"]`).text( rupiah(total) )
                                    }, 7)
                                })
                                $(`.${selectedRevName}-RIWAYAT[data-kode="TOTAL"]`).text( rupiah(totalRev) )
                            })
                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            // Update DataTable only if not in PDF mode
                            if ( !isPdf && tableRiwayat ) {
                                removeLoader()
                                tableRiwayat.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                        }).catch( err => {
                            console.error("Error loading riwayat data:", err)
                            if ( !isPdf ) {
                                removeLoader()
                                return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 3000, animate: "slide" } )
                            }
                        })
                    })
            },
            showDetailTab: ( idunit, kodeSd = null ) => {
                const targetKodeSd = kodeSd || $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
                if ( idunit == "X" || ( Array.isArray(idunit) && idunit.includes("X") ) || ( Array.isArray(idunit) && idunit.includes("semua") ) ) {
                    if ( isPdf )
                        $(".loading-msg").html("<p class='px-3 py-2 d-inline' style='border-radius: 5px;color: #dc3545; background: #fdecea;'>Unit kerja tidak tersedia</p>")
                    return tata.error( "⛔ Error", "Mohon maaf, Unit kerja tidak tersedia", { duration: 3000, animate: "slide" } )
                }

                if ( !isPdf ) {
                    showLoader()
                    setLoaderText("Sedang memuat data...")
                }

                // Dapatkan backup map (berisi per-revisi) dan base map (struktur utama)
                window.laporan.tahunan.methods.generateBaseBackupMap( idunit, targetKodeSd )
                    .then( () => {
                        window.laporan.tahunan.methods.getBaseData( idunit, targetKodeSd )
                        .then( () => {
                            const tableBody = $(".body-tbl-detail")
                            tableBody.html("")
                            const fragment = document.createDocumentFragment()

                            const baseMap       = window.laporan.tahunan.baseMap
                            const baseBackupMap = window.laporan.tahunan.baseBackupMap
                            const alokasiRaw    = window.laporan.tahunan.alokasiRaw || []

                            if (!baseMap || baseMap.get("sub").size === 0) {
                                if ( !isPdf ) {
                                    removeLoader()
                                    return tata.error( "⛔ Error", "Data tidak ditemukan" )
                                } else {
                                    $(".loading-msg").html("<p class='px-3 py-2 d-inline' style='border-radius: 5px;color: #dc3545; background: #fdecea;'>Data tidak ditemukan</p>")
                                    return
                                }
                            }
                            const paguByKodeSd = new Map();
                            const selectedUnits = Array.isArray(idunit) ? idunit.map(Number) : [Number(idunit)];
                            const selectedUnitsText = $(".selected-text-unit").text();

                            alokasiRaw.forEach((item) => {
                                const unitSekarang = Number(item.unit_kerja);
                                if (!selectedUnits.includes(unitSekarang) && selectedUnitsText != "Semua Unit Kerja") return;
                                const kodeSd = String(item?.kd_sumberdana || "-");
                                const pagu = Number(item?.total_pagu || 0);

                                const currentPagu = paguByKodeSd.get(kodeSd) || 0;

                                paguByKodeSd.set(kodeSd, currentPagu + pagu);
                            });

                            baseMap.set("totalPagu", 0);

                            baseMap.get("sub").forEach((jenisMap) => {
                                let totalPaguJenis = 0;

                                jenisMap.get("sub").forEach((sdMap, kodeSdKey) => {
                                    const paguSd = paguByKodeSd.get(String(kodeSdKey)) || 0;

                                    sdMap.set("totalPagu", paguSd);
                                    totalPaguJenis += paguSd;
                                });

                                jenisMap.set("totalPagu", totalPaguJenis);

                                baseMap.set(
                                    "totalPagu",
                                    (baseMap.get("totalPagu") || 0) + totalPaguJenis
                                );
                            });

                            // Header grup Non APBN / APBN dengan kolom REV total
                            const revHeaders = $(".revHeaderDetail").get().map(el => $(el).text().trim().replace(/\s+/g, '')).filter(name => name)

                            const createTotalRow = () => {
                                const total = baseMap.get("total") || 0
                                const totalPagu = baseMap.get("totalPagu") || 0
                                const revCells = createRevColumns("TOTAL")
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold", "total-row")
                                tr.innerHTML = `
                                    <td>Total</td>
                                    <td>-</td>
                                    <td>${rupiah(totalPagu)}</td>
                                    <td>${rupiah(total)}</td>
                                    ${revCells}
                                `
                                return tr
                            }

                            const createGroupHeader = ( label, jenisMap, jenisBackupMap ) => {
                                const safeLabel = label ?? '-'
                                const jenisTotal = jenisMap?.get("total") ?? 0
                                const jenisPagu = jenisMap?.get("totalPagu") ?? 0
                                const revCells = revHeaders.map(revKey => {
                                    const revMap = jenisBackupMap?.get("sub")?.get(revKey)
                                    const total = revMap ? (revMap.get("total") || 0) : null
                                    return `<td class="rev-total-detail" data-jenis="${safeLabel}" data-rev="${revKey}">${total ? rupiah(total) : '-'}</td>`
                                }).join('')
                                const tr = document.createElement("tr")
                                tr.setAttribute("style", "font-size:16px; font-weight:bold; background-color:#e9ecef")
                                tr.classList.add("sd-group-header")
                                tr.innerHTML = `
                                    <td>${safeLabel}</td>
                                    <td>-</td>
                                    <td>${rupiah(jenisPagu)}</td>
                                    <td>${rupiah(jenisTotal)}</td>
                                    ${revCells}
                                `
                                return tr
                            }

                            // Baris helper untuk REV columns
                            const createRevColumns = (kode, count = 12) => Array.from({length: count}, (_, i) => `<td class="REV${i+1}-DETAIL" data-kode="${kode}">-</td>`).join('')

                            fragment.appendChild(createTotalRow())

                            // Build rows per jenis anggaran
                            baseMap.get("sub").forEach( ( jenisMap, jenisKey ) => {
                                const jenisLabel = jenisMap.get("jenisAnggaran") || jenisKey
                                fragment.appendChild( createGroupHeader( jenisLabel, jenisMap, baseBackupMap.get("sub").get(jenisKey) ) )

                                jenisMap.get("sub").forEach( ( sdMap, kodeSd ) => {
                                    const sumberdana = sdMap.get("sumberdana") ?? '-'
                                    const totalPagu = sdMap.get("totalPagu") ?? 0
                                    const total = sdMap.get("total") ?? 0
                                    const totalAmprah = sdMap.get("totalAmprah") ?? 0
                                    const totalRealisasi = sdMap.get("totalRealisasi") ?? 0
                                    const realisasi = totalAmprah + totalRealisasi
                                    const revSdColumns = createRevColumns(kodeSd)
                                    const safeKodeSd = kodeSd ?? '-'

                                    const row = document.createElement("tr")
                                    row.classList.add("fw-bold")
                                    row.innerHTML = `
                                        <td style="width:100px">${safeKodeSd}</td><td style="width:200px">${sumberdana}</td>
                                        <td>${rupiah(totalPagu)}</td><td>${rupiah(total)}</td>${revSdColumns}
                                    `
                                    fragment.appendChild(row)

                                    sdMap.get("sub").forEach( ( ssMap, kodeSs ) => {
                                        const ss = ssMap.get("ss") ?? '-'
                                        const total = ssMap.get("total") ?? 0
                                        const totalAmprah = ssMap.get("totalAmprah") ?? 0
                                        const totalRealisasi = ssMap.get("totalRealisasi") ?? 0
                                        const realisasi = totalAmprah + totalRealisasi
                                        const revSsColumns = createRevColumns(kodeSs)
                                        const safeKodeSs = kodeSs ?? '-'

                                        const subRow = document.createElement("tr")
                                        subRow.innerHTML = `
                                            <td>${safeKodeSs}</td>
                                            <td>${ss}</td>
                                            <td>-</td><td>${rupiah(total)}</td>
                                        ${revSsColumns}`
                                        fragment.appendChild(subRow)

                                        ssMap.get("sub").forEach( ( ikkMap, kodeIkk ) => {
                                            const ikk = ikkMap.get("ikk") ?? '-'
                                            const total = ikkMap.get("total") ?? 0
                                            const totalAmprah = ikkMap.get("totalAmprah") ?? 0
                                            const totalRealisasi = ikkMap.get("totalRealisasi") ?? 0
                                            const realisasi = totalAmprah + totalRealisasi
                                            const revIkkColumns = createRevColumns(kodeIkk)
                                            const safeKodeIkk = kodeIkk ?? '-'

                                            const subSubRow = document.createElement("tr")
                                            subSubRow.innerHTML = `
                                                <td>${safeKodeIkk}</td><td>${ikk}</td>
                                                <td>-</td><td>${rupiah(total)}</td>${revIkkColumns}`
                                            fragment.appendChild(subSubRow)

                                            ikkMap.get("sub").forEach( ( ikvMap, kodeIkv ) => {
                                                const ikv = ikvMap.get("ikv") ?? '-'
                                                const total = ikvMap.get("total") ?? 0
                                                const totalAmprah = ikvMap.get("totalAmprah") ?? 0
                                                const totalRealisasi = ikvMap.get("totalRealisasi") ?? 0
                                                const revIkvColumns = createRevColumns(kodeIkv)
                                                const safeKodeIkv = kodeIkv ?? '-'

                                                const subRow = document.createElement("tr")
                                                subRow.innerHTML = `
                                                    <td>${safeKodeIkv}</td><td>${ikv}</td>
                                                    <td>-</td><td>${rupiah(total)}</td>${revIkvColumns}`
                                                fragment.appendChild(subRow)

                                                ikvMap.get("sub").forEach( ( kegMap, kodeKeg ) => {
                                                    const rincianKeg = kegMap.get("rincianKeg") ?? '-'
                                                    const total = kegMap.get("total") ?? 0
                                                    const totalAmprah = kegMap.get("totalAmprah") ?? 0
                                                    const totalRealisasi = kegMap.get("totalRealisasi") ?? 0
                                                    const revKegColumns = createRevColumns(kodeKeg)
                                                    const safeKodeKeg = kodeKeg ? kodeKeg : '--'
                                                    const subRow = document.createElement("tr")
                                                    subRow.innerHTML = `
                                                        <td>${safeKodeKeg}</td><td>${safeKodeKeg ? rincianKeg : '--'}</td>
                                                        <td>-</td><td>${rupiah(total)}</td>${revKegColumns}`
                                                    fragment.appendChild(subRow)

                                                    kegMap.get("sub").forEach( ( unitMap, unitKerja ) => {
                                                        const namaUnit = unitMap.get("namaUnit") ?? '-'
                                                        const total = unitMap.get("total") ?? 0
                                                        const totalAmprah = unitMap.get("totalAmprah") ?? 0
                                                        const totalRealisasi = unitMap.get("totalRealisasi") ?? 0
                                                        const revUnitColumns = createRevColumns(`${kodeKeg}-${idunit}`)
                                                        const safeUnitKerja = unitKerja ?? '-'

                                                        const subRow = document.createElement("tr")
                                                        subRow.innerHTML = `
                                                            <td>${safeUnitKerja}</td><td>${namaUnit}</td>
                                                            <td>-</td><td>${rupiah(total)}</td>${revUnitColumns}`
                                                        fragment.appendChild(subRow)

                                                        unitMap.get("sub").forEach( ( rekatMap, idRekat ) => {
                                                            const subJudul = rekatMap.get("subJudul") ?? '-'
                                                            const total = rekatMap.get("total") ?? 0
                                                            const totalAmprah = rekatMap.get("totalAmprah") ?? 0
                                                            const totalRealisasi = rekatMap.get("totalRealisasi") ?? 0
                                                            const revRekatColumns = createRevColumns(`${kodeKeg}-${idunit}-${idRekat}`)
                                                            const safeIdRekat = idRekat ?? '-'

                                                            const subRow = document.createElement("tr")
                                                            subRow.innerHTML = `
                                                                <td>${safeIdRekat}</td><td>${subJudul}</td>
                                                                <td>-</td><td>${rupiah(total)}</td>${revRekatColumns}`
                                                            fragment.appendChild(subRow)

                                                            const coaSub = rekatMap.get("sub")
                                                            if ( !coaSub || typeof coaSub.forEach !== "function" ) return

                                                            coaSub.forEach( ( coaMap, idJenisBelanja ) => {
                                                                const jenisBelanja = coaMap.get("jenisBelanja") ?? '-'
                                                                const total = coaMap.get("total") ?? 0
                                                                const totalAmprah = coaMap.get("totalAmprah") ?? 0
                                                                const totalRealisasi = coaMap.get("totalRealisasi") ?? 0
                                                                const safePartKodeKeg = kodeKeg ? kodeKeg.substring(3, 11) : '--'
                                                                const revCoaColumns = createRevColumns(`${kodeKeg}-${idRekat}-${idJenisBelanja}`)
                                                                const safeKodeSd = kodeSd ?? '-'
                                                                const safeKodeSs = kodeSs ?? '-'
                                                                const safeIdRekatMak = idRekat ?? '-'
                                                                const safeIdJenisBelanja = idJenisBelanja ?? '-'
                                                                const mak = `${safeKodeSd}.${safeKodeSs}.${safePartKodeKeg}.<br>${idunit ?? '-'}.${safeIdRekatMak}.${safeIdJenisBelanja}`

                                                                const subRow = document.createElement("tr")
                                                                subRow.setAttribute("data-mak", mak.replace("<br>", "") )
                                                                subRow.innerHTML = `
                                                                    <td>${mak}</td><td>${jenisBelanja}</td>
                                                                    <td>-</td><td>${ rupiah(total) }</td>${revCoaColumns}`
                                                                fragment.appendChild(subRow)

                                                                const detailSub = coaMap.get("sub")
                                                                if ( !detailSub || typeof detailSub.forEach !== "function" ) return

                                                                detailSub.forEach( ( detailMap, idDetail ) => {
                                                                    const itemCoa = detailMap.get("itemCoa") ?? '-'
                                                                    const jumlahBiaya = detailMap.get("jumlahBiaya") ?? 0
                                                                    const rpd = detailMap.get("rpd") ?? '-'
                                                                    const revItemColumns = createRevColumns(`${kodeKeg}-${idRekat}-${idJenisBelanja}-${idDetail}`)

                                                                    const subRow = document.createElement("tr")
                                                                    subRow.innerHTML = `
                                                                        <td></td>
                                                                        <td>${itemCoa} (${rpd})</td>
                                                                        <td>-</td><td>${rupiah(jumlahBiaya)}</td>${revItemColumns}`
                                                                    fragment.appendChild(subRow)
                                                                })
                                                            })
                                                        })
                                                    })
                                                })
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(fragment)

                            // Generate REV columns dynamically (fill values) using baseBackupMap grouped by jenis
                            const createRowIfNotExists = (selectedRevName, kode, content, revColumns, parentKeys = null) => {
                                const foundClass = $(`.${selectedRevName}-DETAIL[data-kode="${kode}"]`)
                                if (parentKeys) {
                                    const parentRow = $(`.${selectedRevName}-DETAIL[data-kode="${parentKeys}"]`)
                                    if (parentRow.length > 0) {
                                        const newRow = document.createElement("tr")
                                            newRow.innerHTML = `${content}<td>-</td><td>-</td>${revColumns}`
                                        parentRow.closest("tr").after(newRow)
                                        return $(`.${selectedRevName}-DETAIL[data-kode="${kode}"]`)
                                    }
                                }
                                if (foundClass.length === 0) {
                                    const row = document.createElement("tr")
                                        row.innerHTML = `${content}<td>-</td><td>-</td>${revColumns}`
                                    tableBody.append(row)
                                    return $(`.${selectedRevName}-DETAIL[data-kode="${kode}"]`)
                                }
                            }

                            const revHeadersFill = $(".revHeaderDetail").get().map(el => $(el).text().trim().replace(/\s+/g, '')).filter(name => name)

                            revHeadersFill.forEach(selectedRevName => {
                                let totalRev = 0
                                // iterate per jenis anggaran in backup map
                                baseBackupMap.get("sub").forEach( ( jenisBackupMap ) => {
                                    const selectedBackup = jenisBackupMap.get("sub")?.get(selectedRevName)
                                    if (!selectedBackup) return true

                                    totalRev += Number(selectedBackup.get("total") || 0)

                                    const levelConfigs = [
                                        {
                                            level: 1,
                                            data: ( currentMap ) => ({ total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[0], // kode sumber dana
                                            getSelectorKey: (keys) => keys[0], // kode sumber dana
                                            updateOnly: true
                                        },
                                        {
                                            level: 2,
                                            data: ( currentMap ) => ({ desc: currentMap.get("ss"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[1], // kode sasaran
                                            parentKeys: (keys) => keys[0],
                                            getSelectorKey: (keys) => keys[1],
                                            createContent: (keys, data) => `<td>${keys[1] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 3,
                                            data: ( currentMap ) => ({ desc: currentMap.get("ikk"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[2],
                                            parentKeys: (keys) => keys[1],
                                            getSelectorKey: (keys) => keys[2],
                                            createContent: (keys, data) => `<td>${keys[2] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 4,
                                            data: ( currentMap ) => ({ desc: currentMap.get("ikv"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[3],
                                            parentKeys: (keys) => keys[2],
                                            getSelectorKey: (keys) => keys[3],
                                            createContent: (keys, data) => `<td>${keys[3] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 5,
                                            data: ( currentMap ) => ({ desc: currentMap.get("rincianKeg"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[4],
                                            parentKeys: (keys) => keys[3],
                                            getSelectorKey: (keys) => keys[4],
                                            createContent: (keys, data) => `<td>${keys[4] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 6,
                                            data: ( currentMap ) => ({ desc: currentMap.get("namaUnit"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[5],
                                            parentKeys: (keys) => keys[4],
                                            getSelectorKey: (keys) => `${keys[4]}-${keys[5]}`,
                                            createContent: (keys, data) => `<td>${keys[5] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 7,
                                            data: ( currentMap ) => ({ desc: currentMap.get("subJudul"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[6],
                                            parentKeys: (keys) => `${keys[4]}-${keys[5]}`,
                                            getSelectorKey: (keys) => `${keys[4]}-${keys[5]}-${keys[6]}`,
                                            createContent: (keys, data) => `<td>${keys[6] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`,
                                        },
                                        {
                                            level: 8,
                                            data: ( currentMap ) => ({ desc: currentMap.get("jenisBelanja"), total: currentMap.get("total") }),
                                            getKeys: (keys) => keys[7],
                                            parentKeys: (keys) => `${keys[4]}-${keys[5]}-${keys[6]}`,
                                            getSelectorKey: (keys) => `${keys[4]}-${keys[6]}-${keys[7]}`,
                                            createContent: (keys, data) => `<td>${keys[7] ?? '-'}</td><td>${data.desc ?? 'Data tidak ditemukan'}</td>`
                                        },
                                        {
                                            level: 9,
                                            data: ( currentMap ) => ({ desc: currentMap.get("itemCoa"), total: currentMap.get("jumlahBiaya"), rpd: currentMap.get("rpd") }),
                                            getKeys: (keys) => keys[8],
                                            parentKeys: (keys) => `${keys[4]}-${keys[6]}-${keys[7]}`,
                                            getSelectorKey: (keys) => `${keys[4]}-${keys[6]}-${keys[7]}-${keys[8]}`,
                                            createContent: (keys, data) => `<td></td><td>${data.desc ?? 'Data tidak ditemukan'} (${data.rpd ?? '-'})</td>`,
                                        }
                                    ]

                                    levelConfigs.forEach(config => {
                                        traverseToMap(selectedBackup, (detailMap, keys) => {
                                            const data        = config.data(detailMap)
                                            const selectorKey = config.getSelectorKey(keys)
                                            const parentKeys  = config.parentKeys ? config.parentKeys(keys) : null

                                            const isFound = $(`.${selectedRevName}-DETAIL[data-kode="${selectorKey}"]`).length > 0
                                            if (config.updateOnly || isFound) {
                                                $(`.${selectedRevName}-DETAIL[data-kode="${selectorKey}"]`).text(rupiah(data.total || 0))
                                            } else {
                                                const revColumns = createRevColumns(selectorKey)
                                                const element = createRowIfNotExists(selectedRevName, selectorKey, config.createContent(keys, data), revColumns, parentKeys)
                                                element.text(rupiah(data.total || 0))
                                            }
                                        }, config.level)
                                    })
                                })
                                $(`.${selectedRevName}-DETAIL[data-kode="TOTAL"]`).text(rupiah(totalRev))
                            })

                            $(".total").text( rupiah( baseMap.get("total") || 0 ) )
                            // Update DataTable only if not in PDF mode
                            if ( !isPdf ) {
                                removeLoader()
                                tableDetail.clear().rows.add(tableBody.find("tr")).draw()
                            } else {
                                $(".loading-msg").hide()
                            }
                        }).catch( err => {
                            console.error("Error loading detail data:", err)
                            if ( !isPdf ) {
                                removeLoader()
                                return tata.error( "⛔ Error", "Terjadi kesalahan saat memuat data.", { duration: 3000, animate: "slide" } )
                            }
                        })
                    })
            },
            getDataProyeksi: ( idunit ) => {
                return new Promise( ( resolve, reject ) => {
                    $.ajax({
                        url: "/laporan/tahunan/getDataProyeksi",
                        type: "GET",
                        data: { idunit },
                        success: ( res ) => {
                            const { data } = res
                            resolve( data )
                        },
                        error: ( err ) => {
                            const message = err.responseJSON?.message || "Terjadi kesalahan saat memuat data proyeksi."
                            reject(message)
                        }
                    })
                })
            }
        }

        // recursive function to traverse the nested Map structure
        function traverseToMap(map, callback, selectedLevel = 0, keys = []) {
            // Check if map exists and is a Map object
            if (!map || typeof map.has !== 'function')
                return;

            const level = keys.length ;

            if ( selectedLevel === level ) {
                callback(map, keys);
                return
            }

            if (map.has("sub")) {
                map.get("sub").forEach((subMap, key) => {
                    traverseToMap(subMap, callback, selectedLevel, [...keys, key]);
                });
            }
        }
    })
</script>
