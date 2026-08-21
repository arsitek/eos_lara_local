<script>
    $(document).ready(function () {
        const hasSelect2 = typeof $.fn.select2 === "function"
        let select2   = hasSelect2 ? $('.s').select2() : $('.s')
        const url          = new URL(window.location.href)
        const setSelectVal = (selector, value) => {
            if (!value) return
            const $el = $(selector)
            $el.val(value)
            if (hasSelect2) $el.trigger("change")
        }
        const updateUrlParams = (idunit, kodeSd, rpd) => {
            url.searchParams.set("idunit", idunit)
            url.searchParams.set("sd", kodeSd)
            url.searchParams.set("rpd", rpd)
            window.history.pushState({}, "", url)
        }
        $(".loading-div").hide()
        window.laporan = window.laporan || {}
        window.laporan.rpd = {
            elements: {
                bodyTbl: $('.body-tbl'),
                selectorUnit: "select.unit_kerja",
                selectorSumberDana: "select.sumberdana",
                selectorRpd: "select.rpd",
            },
            constants: {
                TAHUN: parseFloat("{{ $tahunAngka }}"),
                TATA_OPTIONS: { duration: 5000, animate: "slide" }
            },
            methods: {
                createOrUpdateMap: (node, key, createNode) => {
                    if ( !node.has(key) ) node.set(key, createNode())
                    return node.get(key)
                },
                getData: async (idunit, kodeSd, rpd) => {
                    const { createOrUpdateMap } = window.laporan.rpd.methods
                    try {
                        $(".loading-div").show()
                        const response = await $.get(`/laporan/rktunit/get/${idunit}/${kodeSd}?rpd=${rpd}`)
                        if ( response.success ) {
                            const { data } = response
                            if (data?.baseData){
                                const mapData = new Map()
                                mapData.total = 0
                                mapData.totalRealisasi = 0
                                mapData.totalAmprah = 0
                                data.baseData.forEach( item => {
                                    const kegMap = createOrUpdateMap( mapData, item.kode_keg, () => ({ nama: item.rincian_kegiatan, total: 0, sub: new Map(), totalRealisasi: 0, totalAmprah: 0 }) )
                                    const unitMap = createOrUpdateMap( kegMap.sub, item.unit_kerja, () => ({ nama: item.nama_unit, total: 0, sub: new Map(), totalRealisasi: 0, totalAmprah: 0 }) )
                                    const rekatMap = createOrUpdateMap( unitMap.sub, item.id_rekat, () => ({ nama: item.sub_judul, total: 0, sub: new Map(), totalRealisasi: 0, totalAmprah: 0 }) )
                                    const ppkMap = createOrUpdateMap( rekatMap.sub, item.nip_ppk, () => ({ namaPPK: item.nama_ppk, namaBPP: item.nama_bpp, total: 0, sub: new Map(), totalRealisasi: 0, totalAmprah: 0 }) )
                                    const coaMap = createOrUpdateMap( ppkMap.sub, item.id_jenis_belanja, () => ({ nama: item.jenis_belanja, kodeSd: item.kd_sumberdana, kodeIkv: item.kode_ikv, kodeSs: item.kode_ss, items: [], total: 0, sub: new Map(), totalRealisasi: 0, totalAmprah: 0 }) )
                                    coaMap.items.push(item);
                                    [ mapData, kegMap, unitMap, rekatMap, ppkMap, coaMap ].forEach( map => {
                                        map.total += Number(item.jumlah_biaya)
                                        map.totalRealisasi += Number(item.TOTAL_REALISASI)
                                        map.totalAmprah += Number(item.TOTAL_AMPRAH)
                                    })
                                })
                                return mapData
                            }
                            return null
                        } else {
                            tata.error("⛔ Error", "Gagal mendapatkan data laporan rpd", window.laporan.rpd.constants.TATA_OPTIONS)
                        }
                    } catch (error) {
                        console.error("Error fetching data: ", error)
                        tata.error("⛔ Error", "Terjadi kesalahan saat mengambil data laporan rpd", window.laporan.rpd.constants.TATA_OPTIONS)
                    } finally {
                        $(".loading-div").hide()
                    }
                },
                renderData: (mapData) => {
                    const { TAHUN } = window.laporan.rpd.constants
                    const tbody = window.laporan.rpd.elements.bodyTbl
                    tbody.empty()
                    const htmlRows = []
                    const totalRows = `<tr class="fw-bold">
                        <td colspan="2" class="text-center">TOTAL</td>
                        <td class="text-end">${rupiah(mapData.total)}</td>
                        <td class="text-end">${rupiah(mapData.totalRealisasi + mapData.totalAmprah)}</td>
                        <td class="text-end">${rupiah(mapData.total - ( mapData.totalRealisasi + mapData.totalAmprah ))}</td>
                    </tr>`
                    htmlRows.push(totalRows)
                    $(".total").text(rupiah(mapData.total))
                    $(".total_ptnbh").text(rupiah(mapData.total))
                    mapData.forEach( ( kegValue, kegKey ) => {
                        const tr = `<tr>
                            <td>${kegKey}</td>
                            <td>${kegValue.nama}</td>
                            <td class="text-end">${rupiah(kegValue.total)}</td>
                            <td class="text-end">${rupiah(kegValue.totalRealisasi + kegValue.totalAmprah)}</td>
                            <td class="text-end">${rupiah(kegValue.total - ( kegValue.totalRealisasi + kegValue.totalAmprah ))}</td>
                        </tr>`
                        htmlRows.push(tr)
                        kegValue.sub.forEach( ( unitValue, unitKey ) => {
                            const tr = `<tr>
                                <td>${unitKey}</td>
                                <td>${unitValue.nama}</td>
                                <td class="text-end">${rupiah(unitValue.total)}</td>
                                <td class="text-end">${rupiah(unitValue.totalRealisasi + unitValue.totalAmprah)}</td>
                                <td class="text-end">${rupiah(unitValue.total - ( unitValue.totalRealisasi + unitValue.totalAmprah ))}</td>
                            </tr>`
                            htmlRows.push(tr)
                            unitValue.sub.forEach( ( rekatValue, rekatKey ) => {
                                const tr = `<tr class="fw-bold">
                                    <td>${rekatKey}</td>
                                    <td>${rekatValue.nama}</td>
                                    <td class="text-end">${rupiah(rekatValue.total)}</td>
                                    <td class="text-end">${rupiah(rekatValue.totalRealisasi + rekatValue.totalAmprah)}</td>
                                    <td class="text-end">${rupiah(rekatValue.total - ( rekatValue.totalRealisasi + rekatValue.totalAmprah ))}</td>
                                </tr>`
                                htmlRows.push(tr)
                                rekatValue.sub.forEach( ( ppkValue, ppkKey ) => {
                                    const tr = `<tr>
                                        <td colspan="2">${ppkValue.namaPPK} <br> ${ppkValue.namaBPP}</td>
                                        <td class="text-end">${rupiah(ppkValue.total)}</td>
                                        <td class="text-end">${rupiah(ppkValue.totalRealisasi + ppkValue.totalAmprah)}</td>
                                        <td class="text-end">${rupiah(ppkValue.total - ( ppkValue.totalRealisasi + ppkValue.totalAmprah ))}</td>
                                    </tr>`
                                    htmlRows.push(tr)
                                    ppkValue.sub.forEach( ( coaValue, coaKey ) => {
                                        const kodeKegPartSafe = TAHUN >= 2026 ? `${coaValue.kodeIkv}.<br>${kegKey}` : (kegKey && kegKey.length >= 11) ? `${kegKey.substring(3, 11)}<br>` : '-'
                                        const mak             = `${coaValue.kodeSd}.${coaValue.kodeSs}.${kodeKegPartSafe}.${unitKey}.${rekatKey}.${coaKey}`
                                        const tr = `<tr class="coa-row">
                                            <td style="max-width: 330px; width:330px">${mak}</td>
                                            <td>${coaValue.nama}</td>
                                            <td class="text-end">${rupiah(coaValue.total)}</td>
                                            <td class="text-end">${rupiah(coaValue.totalRealisasi + coaValue.totalAmprah)}</td>
                                            <td class="text-end">${rupiah(coaValue.total - ( coaValue.totalRealisasi + coaValue.totalAmprah ))}</td>
                                        </tr>`
                                        htmlRows.push(tr)
                                        coaValue.items.forEach( item => {
                                            const totalRealisasi = Number(item.TOTAL_REALISASI) + Number(item.TOTAL_AMPRAH)
                                            const tr = `<tr class="coa-item-row">
                                                <td></td>
                                                <td style="max-width: 700px; width:700px">${item.itemCoa}</td>
                                                <td class="text-end">${rupiah(item.jumlah_biaya)}</td>
                                                <td class="text-end">${rupiah(totalRealisasi)}</td>
                                                <td class="text-end">${rupiah(item.jumlah_biaya - (totalRealisasi))}</td>
                                            </tr>`
                                            htmlRows.push(tr)
                                        })
                                    })
                                })
                            })
                        })
                    })
                    tbody.append(htmlRows.join(""))
                }
            }
        }
        $(document).on("click", ".cari", function(){
            const { selectorUnit, selectorSumberDana, selectorRpd } = window.laporan.rpd.elements
            const { TATA_OPTIONS } = window.laporan.rpd.constants
            const idunit  = $(selectorUnit).val()
            const kodeSd  =  $(selectorSumberDana).val()
            const rpd     = $(selectorRpd).val()

            if ( idunit == "" || kodeSd == "" || rpd == "")
                return tata.error("⛔ Error", "Harap memilih unit kerja, sumberdana, dan rpd", TATA_OPTIONS)

            updateUrlParams(idunit, kodeSd, rpd)
            window.laporan.rpd.methods.getData( idunit, kodeSd, rpd ).then( data => {
                window.laporan.rpd.methods.renderData(data)
            })

        })

        // 🔄 Auto-load based on URL params
        const idunitParam = url.searchParams.get("idunit")
        const sdParam     = url.searchParams.get("sd")
        const rpdParam    = url.searchParams.get("rpd")
        if ( idunitParam && sdParam && rpdParam ) {
            setSelectVal(window.laporan.rpd.elements.selectorUnit, idunitParam)
            setSelectVal(window.laporan.rpd.elements.selectorSumberDana, sdParam)
            setSelectVal(window.laporan.rpd.elements.selectorRpd, rpdParam)
            window.laporan.rpd.methods.getData( idunitParam, sdParam, rpdParam ).then( data => {
                if ( data ) window.laporan.rpd.methods.renderData(data)
            })
        }

        $(".btn-export-pdf").on("click", function(){
            const idunit = $("select.unit_kerja").val()
            const sd     = $("select.sumberdana").val()
            const rpd    = $("select.rpd").val()
            if ( select2[0].value == "" || select2[1].value == "" || select2[2].value == "") {
                return tata.error("⛔ Error", "Harap memilih unit kerja, sumberdana, dan rpd")
            }
            window.open("/laporan/per-rpd/pdf?idunit="+idunit+"&sd="+sd+"&rpd="+rpd, "_blank")
        })

        $(".btn-export-xlsx").on("click", function(){
            const idunit = $("select.unit_kerja").val()
            const sd     = $("select.sumberdana").val()
            const rpd    = $("select.rpd").val()
            if ( select2[0].value == "" || select2[1].value == "" || select2[2].value == "") {
                return tata.error("⛔ Error", "Harap memilih unit kerja, sumberdana, dan rpd")
            }
            exportExcel("tabel-rekat-unit", `${idunit}-LAPORAN RPD`)
        })

        const clearUnwantedRowsByClass = (className) => {
            $(`.${className}`).each(function () {
			if ( $(this).text() == "") {
				$(this).closest('tr').remove()
			}
		})
	}
    })
</script>
