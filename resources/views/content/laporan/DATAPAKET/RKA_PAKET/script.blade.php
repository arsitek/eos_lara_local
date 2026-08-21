<script>
    $( document ).ready(function() {
        window.laporan = window.laporan || {}
        window.laporan.paket = {
            constants: {
                TIMEOUT: 5000,
                TATA_OPTIONS: { duration: 4000, animate: "slide"},
                ROUTES: {
                    GET_RKA_PAKET: "{{ route('laporan.datapaket.get.rka') }}",
                    EXPORT_PDF: "{{ route('laporan.datapaket.indexPaketPdf') }}",
                    GET_SUMBER_DANA_PPK: "{{ route('laporan.datapaket.getSumberDanaPPK') }}",
                },
                IS_PDF: {{ request()->is("*/pdf") ? "true" : "false" }},
            },
            elements: {
                table: $("table#tabel-rekat-unit"),
                tbody: $("tbody.body-tbl-unit"),
                ppk: $("select.ppk"),
                kodeSd: $("select.sumberdana"),
            },
            methods: {
                init: () => {
                    const { constants, methods, elements } = window.laporan.paket

                    methods.bindEvents()

                    if (constants.IS_PDF) {
                        const url = new URL(window.location.href)
                        const nip = url.searchParams.get("ppk") || url.searchParams.get("nip") || ""
                        const kodeSd = url.searchParams.get("kode_sd") || url.searchParams.get("kodeSd") || ""

                        if ( !nip || !kodeSd ) {
                            console.warn("Parameter ppk/nip atau kode_sd/kodeSd pada URL PDF tidak ditemukan")
                            return
                        }

                        methods.handleSubmit(nip, kodeSd)
                        return
                    }

                    const url = new URL(window.location.href)
                    const savedNip = url.searchParams.get("ppk") || ""
                    const savedKodeSd = url.searchParams.get("kode_sd") || ""
                    if ( !savedNip ) return

                    elements.ppk.val(savedNip).trigger("change")
                    methods.getSumberDanaPPK(savedNip, savedKodeSd)
                        .then( () => {
                            if ( savedKodeSd ) {
                                elements.kodeSd.val(savedKodeSd).trigger("change")
                            }
                            return methods.handleSubmit(savedNip, savedKodeSd)
                        })
                },
                bindEvents: () => {
                    const { elements, methods } = window.laporan.paket
                    $(elements.ppk).on("change", function() {
                        const selectedPpk = $(this).val()
                        methods.getSumberDanaPPK(selectedPpk)
                    })
                },
                getSumberDanaPPK: ( ppk, selectedKodeSd = "" ) => {
                    return new Promise( ( resolve ) => {
                        const { constants, elements } = window.laporan.paket
                        const sumberdanaEl = elements.kodeSd

                        sumberdanaEl.html('<option value="">Pilih Sumberdana</option>')
                        if ( !ppk ) {
                            sumberdanaEl.trigger("change")
                            return resolve([])
                        }

                        $.ajax({
                            url: constants.ROUTES.GET_SUMBER_DANA_PPK,
                            method: "GET",
                            data: { ppk },
                            timeout: constants.TIMEOUT,
                            success: ( res ) => {
                                const data = Array.isArray(res?.data) ? res.data : []

                                data.forEach( item => {
                                    sumberdanaEl.append(`<option value="${item.kd_sumberdana}">${item.sumberdana}</option>`)
                                })

                                if ( selectedKodeSd ) {
                                    sumberdanaEl.val(selectedKodeSd)
                                }
                                sumberdanaEl.trigger("change")
                                resolve(data)
                            },
                            error: ( err ) => {
                                tata.error("Error", err?.responseJSON?.message || "Gagal mendapatkan data sumberdana", constants.TATA_OPTIONS)
                                sumberdanaEl.trigger("change")
                                resolve([])
                            }
                        })
                    })
                },
                setSearchParams: ( nip, kodeSd ) => {
                    const url = new URL(window.location.href)
                    url.searchParams.set("ppk", nip)
                    url.searchParams.set("kode_sd", kodeSd)
                    window.history.replaceState({}, "", url)
                },
                rupiah: (number) => {
                    const formattedValue = new Intl.NumberFormat("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    }).format(number)
                    return formattedValue.replace(/\./g, ',');
                },
                createOrUpdateMap: ( map, key, createFn ) => {
                    if ( !map.has(key) ) map.set(key, createFn())
                    return map.get(key)
                },
                handleSubmit: async ( nip, kodeSd, el ) => {
                    const hasButton = !!el && typeof el.html === "function" && typeof el.prop === "function"
                    const currentBtnHtml = hasButton ? el.html() : null
                    if ( hasButton ) {
                        el.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat ...`)
                    }
                    const { constants, elements, methods } = window.laporan.paket
                    let res = null
                    try {
                        res = await $.ajax({
                            url: constants.ROUTES.GET_RKA_PAKET, method: "GET", data: { ppk: nip, kode_sd: kodeSd },
                            beforeSend: () => elements.tbody.empty()
                        })
                    } catch ( err ) {
                        if ( hasButton ) {
                            el.prop("disabled", false).html(currentBtnHtml)
                        }
                        return tata.error("Error", err?.responseJSON?.message || "Gagal mendapatkan data.")
                    }

                    if (!res.success) {
                        if ( hasButton ) {
                            el.prop("disabled", false).html(currentBtnHtml)
                        }
                        return tata.error("Error", res.message || "Gagal mendapatkan data.")
                    }
                    if (res.data.length === 0) {
                        if ( hasButton ) {
                            el.prop("disabled", false).html(currentBtnHtml)
                        }
                        return tata.error("Info", "Data tidak ditemukan", constants.TATA_OPTIONS)
                    }
                    // build data map
                    const dataMap = new Map()
                    res.data && res.data.forEach( item => {
                        const sdMap   = methods.createOrUpdateMap(dataMap, item.kd_sumberdana, () => ({ sub: new Map(), sd: item.sumberdana, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        const ssMap   = methods.createOrUpdateMap(sdMap.sub, item.kode_ss, () => ({ sub: new Map(), ss: item.ss, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        const ikuMap  = methods.createOrUpdateMap(ssMap.sub, item.kode_ikk, () => ({ sub: new Map(), ikk: item.ikk, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        const ikvMap  = methods.createOrUpdateMap(ikuMap.sub, item.kode_ikv, () => ({ sub: new Map(), ikv: item.ikv, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        const kegMap  = methods.createOrUpdateMap(ikvMap.sub, item.kode_keg, () => ({ sub: new Map(), keg: item.keg, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        const unitMap = methods.createOrUpdateMap(kegMap.sub, item.idunit, () => ({ sub: new Map(), namaUnit: item.nama_unit, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                        unitMap.sub.set(item.id_mak, { ...item });
                        [ sdMap, ssMap, ikuMap, ikvMap, kegMap, unitMap ].forEach( map => {
                            map.total += Number(item.jumlah_biaya)
                            map.totalRealisasi += Number(item.total_realisasi)
                            map.totalAmprah += Number(item.total_amprah)
                        })
                    })


                    const isPdf     = constants.IS_PDF
                    const sdColor   = isPdf ? "" : "background-color: rgba(0,255,255, 1); color: darkblue"
                    const ssColor   = isPdf ? "" : "background-color: rgba(0,255,255, .8); color: darkblue"
                    const ikuColor  = isPdf ? "" : "background-color: rgba(0,255,255, .7); color: darkblue"
                    const ikvColor  = isPdf ? "" : "background-color: rgba(0,255,255, .6); color: darkblue"
                    const kegColor  = isPdf ? "" : "background-color: rgba(0,255,255, .5); color: darkblue"
                    const unitColor = isPdf ? "" : "background-color: rgba(0,255,255, .4); color: darkblue"

                    // display data
                    dataMap.forEach( (sdValue, sdKey) => {
                        $(".total").text(methods.rupiah(sdValue.total))
                        const sisaSd = sdValue.total - ( sdValue.totalRealisasi + sdValue.totalAmprah )
                        const sdRow = `<tr style="${sdColor}" class="fw-bold">
                            <td>${sdKey}</td><td>${sdValue.sd}</td>
                            <td></td><td class="text-end ">${methods.rupiah(sdValue.total)}</td>
                            <td></td>
                            <td class="text-end ">${methods.rupiah(sdValue.totalAmprah)}</td>
                            <td class="text-end ">${methods.rupiah(sdValue.totalRealisasi)}</td>
                            <td class="text-end ">${methods.rupiah(sisaSd)}</td>
                        </tr>`
                        elements.tbody.append(sdRow)
                        sdValue.sub.forEach( (ssValue, ssKey) => {
                            const sisaSs = ssValue.total - ( ssValue.totalRealisasi + ssValue.totalAmprah )
                            const ssRow = `<tr style="${ssColor}" class="">
                                <td>${ssKey}</td><td>${ssValue.ss}</td>
                                <td></td><td class="text-end ">${methods.rupiah(ssValue.total)}</td>
                                <td></td>
                                <td class="text-end">${methods.rupiah(ssValue.totalAmprah)}</td>
                                <td class="text-end">${methods.rupiah(ssValue.totalRealisasi)}</td>
                                <td class="text-end">${methods.rupiah(sisaSs)}</td>
                            </tr>`
                            elements.tbody.append(ssRow)
                            ssValue.sub.forEach( (ikuValue, ikuKey) => {
                                const sisaIku = ikuValue.total - ( ikuValue.totalRealisasi + ikuValue.totalAmprah )
                                const ikuRow = `<tr style="${ikuColor}" class="">
                                    <td>${ikuKey}</td><td>${ikuValue.ikk}</td>
                                    <td></td><td class="text-end ">${methods.rupiah(ikuValue.total)}</td>
                                    <td></td>
                                    <td class="text-end ">${methods.rupiah(ikuValue.totalAmprah)}</td>
                                    <td class="text-end ">${methods.rupiah(ikuValue.totalRealisasi)}</td>
                                    <td class="text-end ">${methods.rupiah(sisaIku)}</td>
                                </tr>`
                                elements.tbody.append(ikuRow)
                                ikuValue.sub.forEach( (ikvValue, ikvKey) => {
                                    const sisaIkv = ikvValue.total - ( ikvValue.totalRealisasi + ikvValue.totalAmprah )
                                    const ikvRow = `<tr style="${ikvColor}" class="">
                                        <td>${ikvKey}</td><td>${ikvValue.ikv}</td>
                                        <td></td><td class="text-end ">${methods.rupiah(ikvValue.total)}</td>
                                        <td></td>
                                        <td class="text-end ">${methods.rupiah(ikvValue.totalAmprah)}</td>
                                        <td class="text-end ">${methods.rupiah(ikvValue.totalRealisasi)}</td>
                                        <td class="text-end ">${methods.rupiah(sisaIkv)}</td>
                                    </tr>`
                                    elements.tbody.append(ikvRow)
                                    ikvValue.sub.forEach( (kegValue, kegKey) => {
                                        const sisaKeg = kegValue.total - ( kegValue.totalRealisasi + kegValue.totalAmprah )
                                        const kegRow = `<tr style="${kegColor}" class="">
                                            <td>${kegKey}</td><td>${kegValue.keg}</td>
                                            <td></td><td class="text-end ">${methods.rupiah(kegValue.total)}</td>
                                            <td></td>
                                            <td class="text-end ">${methods.rupiah(kegValue.totalAmprah)}</td>
                                            <td class="text-end ">${methods.rupiah(kegValue.totalRealisasi)}</td>
                                            <td class="text-end ">${methods.rupiah(sisaKeg)}</td>
                                        </tr>`
                                        elements.tbody.append(kegRow)
                                        kegValue.sub.forEach( (unitValue, unitKey) => {
                                            const sisaUnit = unitValue.total - ( unitValue.totalRealisasi + unitValue.totalAmprah )
                                            const unitRow = `<tr style="${unitColor}" class="">
                                                <td>${unitKey}</td><td>${unitValue.namaUnit}</td>
                                                <td></td><td class="text-end ">${methods.rupiah(unitValue.total)}</td>
                                                <td></td>
                                                <td class="text-end ">${methods.rupiah(unitValue.totalAmprah)}</td>
                                                <td class="text-end ">${methods.rupiah(unitValue.totalRealisasi)}</td>
                                                <td class="text-end ">${methods.rupiah(sisaUnit)}</td>
                                            </tr>`
                                            elements.tbody.append(unitRow)
                                            unitValue.sub.forEach( (paketValue, paketKey) => {
                                                const spek      = `${paketValue.kuantitas} ${paketValue.satuan_kuantitas} x ${paketValue.durasi} ${paketValue.satuan_durasi} x ${paketValue.kegiatan} ${paketValue.satuan_kegiatan}`
                                                const sisaPaket = paketValue.jumlah_biaya ?? 0 - ( paketValue.total_realisasi ?? 0 + paketValue.total_amprah ?? 0 )
                                                const paketRow = `<tr class="fw-bold btn-detail" data-id="${paketKey}" style="cursor: pointer;">
                                                    <td>${paketKey}</td><td style="width:400px">${paketValue.sub_judul}</td>
                                                    <td>${spek}</td><td class="text-end ">${methods.rupiah(paketValue.jumlah_biaya ?? 0)}</td>
                                                    <td style="width: 30px">${paketValue.rpd}</td>
                                                    <td class="text-end ">${methods.rupiah(paketValue.total_amprah ?? 0)}</td>
                                                    <td class="text-end ">${methods.rupiah(paketValue.total_realisasi ?? 0)}</td>
                                                    <td class="text-end ">${methods.rupiah(sisaPaket)}</td>
                                                    </tr>`
                                                elements.tbody.append(paketRow)
                                                })
                                            })
                                    })
                                })
                            })
                        })
                    })
                    if ( hasButton ) {
                        el.prop("disabled", false).html(currentBtnHtml)
                    }
                }
            },
        }

        $("#btn-submit-filter-paket-ppk").on("click", function(){
            const { elements, methods, constants } = window.laporan.paket
            const nip          = elements.ppk.val()
            const kodeSd       = elements.kodeSd.val()
            if ( !nip || !kodeSd ) return tata.error("Error", "PPK dan Sumberdana harus dipilih", constants.TATA_OPTIONS)
            methods.setSearchParams(nip, kodeSd)
            methods.handleSubmit(nip, kodeSd, $(this))
        })
        $(document).on("click", "#exportPdfAnchor", function() {
            const { constants, elements } = window.laporan.paket
            const url                     = new URL(window.location.href)
            const nip                     = elements.ppk.val()
            const kodeSd                  = elements.kodeSd.val()
            if ( !nip || !kodeSd )
                return tata.error("Error", "PPK dan Sumberdana harus dipilih untuk mengunduh PDF", constants.TATA_OPTIONS)
            const pdfUrl = `${constants.ROUTES.EXPORT_PDF}?ppk=${nip}&kode_sd=${kodeSd}`
            window.open(pdfUrl, "_blank")
        })
        window.laporan.paket.methods.init()
    })
</script>
