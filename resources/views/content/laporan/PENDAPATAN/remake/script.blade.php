<script>
    $( document ).ready( function(){
        window.laporan = window.laporan || {}
        window.laporan.pendapatan = {
            state: {
                dataMaster: [],
                dataPendapatan: [],
                dataAlokasi: [],
            },
            elements: {
                selectUnitKerja: $("select.unit_kerja"),
                btnCari: $("button.cari"),
                tbodyPendapatan: $("#tabelPendapatan tbody"),
            },
            methods: {
                init: () => {
                    const { elements, methods } = window.laporan.pendapatan
                    elements.selectUnitKerja.select2()
                    methods.bindEvents()
                },
                bindEvents: () => {
                    const { methods, elements } = window.laporan.pendapatan
                    elements.btnCari.on("click", methods.handleOnClickCari )
                    $("button.btnExportPendapatanPdf").on("click", methods.exportPdf)
                },
                getDataPendapatan: async ( idunit ) => {
                    const { elements } = window.laporan.pendapatan
                    const idunitkerja = idunit || elements.selectUnitKerja.val()

                    try {
                        const response =  await $.ajax({
                            type: "GET",
                            url: "{{ route('laporan.pendapatan.getDataRemake') }}",
                            data: { idunit },
                            timeout: 10000
                        })
                        if ( !response.success )
                            console.error("Gagal")

                        const { dataAlokasi, dataMaster, dataPendapatan } = response.data
                        window.laporan.pendapatan.state.dataMaster = Array.isArray( dataMaster ) ? dataMaster : []
                        window.laporan.pendapatan.state.dataPendapatan = Array.isArray( dataPendapatan ) ? dataPendapatan : []
                        window.laporan.pendapatan.state.dataAlokasi = Array.isArray( dataAlokasi ) ? dataAlokasi : []
                    } catch ( error ) {
                        console.error( error )
                    }
                },
                handleOnClickCari: () => {
                    const { elements, methods } = window.laporan.pendapatan
                    
                    showLoader()
                    setLoaderText("Sedang memproses data...")
                    methods.getDataPendapatan( elements.selectUnitKerja.val() ).then( () => {
                        methods.renderTabelPendapatan()
                    }).finally( () => {
                        removeLoader()
                    }).catch( ( error ) => {
                        console.error( error )
                    })
                },
                normalizeKey: (key) => key !== undefined && key !== null ? String(key) : key,
                createOrUpdateMap: ( obj, key, createNode ) => {
                    if ( !obj.has(key) ) obj.set(key, createNode())
                    return obj.get(key)
                },
                accumulateByYear: ( mapYearToValue, tahun, nilai ) => {
                    const year = String(tahun)
                    const current = mapYearToValue.get(year) || 0
                    mapYearToValue.set(year, current + (Number(nilai) || 0))
                },
                computePersentaseDanSelisih: ( target, real ) => {
                    const targetNum = Number(target) || 0
                    const realNum   = Number(real) || 0
                    const selisih   = targetNum - realNum
                    const rataRata  = ( realNum + targetNum ) / 2
                    
                    if (target === 0 && real === 0)
                        return { selisih, persentase: 0 }
                    if ( rataRata === 0 )
                        // Jika keduanya nol, anggap 0%; jika hanya salah satu nol, biarkan persentase null agar tidak salah 100%
                        return { selisih, persentase: ( targetNum === 0 && realNum === 0 ) ? 0 : null }

                    const persenRaw = Math.abs( realNum / targetNum ) * 100
                    // const persenRaw = Math.abs( selisih / targetNum ) * 100 
                    const persentase = Math.min( persenRaw, 100 ) // batas akhir 100%
                    return { selisih, persentase }
                },
                formatPersentase: ( value ) => {
                    if ( value === null || value === undefined || isNaN(value) ) return "-"
                    return `${value.toFixed(1)}%`
                },

                exportPdf: () => {
                    const table = document.getElementById("tabelPendapatan")

                    if (!table) return

                    const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
                        .filter((row) => row.offsetParent !== null)

                    if (typeof pdfMake === "undefined") {
                        return tata.error("Error", "Library export PDF belum tersedia", {
                            duration: 5000,
                            animate: "slide"
                        })
                    }

                    if (visibleRows.length === 0) {
                        return tata.warn("Perhatian", "Data laporan pendapatan belum tersedia untuk diexport", {
                            duration: 5000,
                            animate: "slide"
                        })
                    }

                    const unitLabel =
                        $("select.unit_kerja option:selected").text().trim() || "Semua unitkerja"

                    const exportedAt = new Date().toLocaleString("id-ID")
                    const title = $(".card-title").first().text().trim() || "Laporan Pendapatan"

                    const pxToPt = (px) => {
                        const value = parseFloat(px || 0)
                        return Number.isNaN(value) ? 0 : value * 0.75
                    }

                    const buildCell = (cell, isHeader = false) => {
                        const text = cell.innerText.trim() || " "
                        const computedStyle = window.getComputedStyle(cell)

                        const firstChild = cell.firstElementChild
                        const childStyle = firstChild ? window.getComputedStyle(firstChild) : null

                        const color = computedStyle.color

                        const isDanger =
                            cell.classList.contains("text-danger") ||
                            ["rgb(220, 53, 69)", "rgb(255, 0, 0)"].includes(color)

                        const isSuccess =
                            cell.classList.contains("text-success") ||
                            ["rgb(25, 135, 84)", "rgb(0, 128, 0)"].includes(color)

                        const isNumeric = /^(Rp\s*)?-?[\d.,]+%?$|^-$/.test(text) || text.includes('Rp')

                        const paddingTop = pxToPt(computedStyle.paddingTop)
                        const paddingRight = pxToPt(computedStyle.paddingRight)
                        const paddingBottom = pxToPt(computedStyle.paddingBottom)
                        const paddingLeft = pxToPt(computedStyle.paddingLeft)

                        const childPaddingLeft = childStyle ? pxToPt(childStyle.paddingLeft) : 0
                        const childMarginLeft = childStyle ? pxToPt(childStyle.marginLeft) : 0

                        const pdfCell = {
                            text,
                            bold:
                                isHeader ||
                                computedStyle.fontWeight === "bold" ||
                                Number(computedStyle.fontWeight) >= 600,

                            alignment: isHeader ? "center" : isNumeric ? "right" : "left",
                            color: isDanger ? "#dc3545" : isSuccess ? "#198754" : "black",

                            margin: [
                                Math.max(2, paddingLeft + childPaddingLeft + childMarginLeft),
                                Math.max(2),
                                Math.max(3),
                                Math.max(2)
                            ],

                            fontSize: isHeader ? 7 : 6,
                            noWrap: isHeader,
                        }

                        const colspan = Number(cell.getAttribute("colspan") || 1)
                        const rowspan = Number(cell.getAttribute("rowspan") || 1)

                        if (colspan > 1) pdfCell.colSpan = colspan
                        if (rowspan > 1) pdfCell.rowSpan = rowspan
                        if (isHeader) pdfCell.fillColor = "#eeeeee"

                        return { pdfCell, colspan, rowspan }
                    }

                    const buildRows = (rows, isHeader = false) => {
                        const matrix = []
                        const spans = []

                        rows.forEach((row, rowIndex) => {
                            const output = matrix[rowIndex] || []
                            let colIndex = 0

                            while (spans[colIndex] > 0) {
                                output[colIndex] = {}
                                spans[colIndex] -= 1
                                colIndex += 1
                            }

                            row.querySelectorAll("th, td").forEach((cell) => {
                                while (spans[colIndex] > 0 || output[colIndex]) {
                                    if (spans[colIndex] > 0) spans[colIndex] -= 1
                                    output[colIndex] = output[colIndex] || {}
                                    colIndex += 1
                                }

                                const { pdfCell, colspan, rowspan } = buildCell(cell, isHeader)

                                output[colIndex] = pdfCell

                                for (let i = 1; i < colspan; i++) {
                                    output[colIndex + i] = {}
                                }

                                if (rowspan > 1) {
                                    for (let i = 0; i < colspan; i++) {
                                        spans[colIndex + i] = rowspan - 1
                                    }
                                }

                                colIndex += colspan
                            })

                            matrix[rowIndex] = output
                        })

                        const colCount = Math.max(...matrix.map((row) => row.length), 0)

                        return matrix.map((row) => {
                            while (row.length < colCount) row.push({})
                            return row
                        })
                    }

                    const headerRows = buildRows(Array.from(table.querySelectorAll("thead tr")), true)
                    const bodyRows = buildRows(visibleRows, false)

                    const colCount = Math.max(
                        headerRows[0]?.length || 0,
                        bodyRows[0]?.length || 0
                    )

                    // const widths = Array.from({ length: colCount }, (_, index) =>
                    //     index === 0 ? "22%" : "auto"
                    // )
                    const widths = Array.from({ length: colCount }, (_, index) => {
                        if (index === 0) return 90 // kolom nama/keterangan
                        return "*" // sisa kolom otomatis melebar rata
                    })

                    const body = [...headerRows, ...bodyRows]

                    pdfMake.createPdf({
                        pageOrientation: "potrait",
                        pageSize: "A3",

                        content: [
                            { text: title, style: "title" },
                            { text: `Unit Kerja: ${unitLabel}`, style: "meta" },
                            {
                                text: `Tanggal Export: ${exportedAt}`,
                                style: "meta",
                                margin: [0, 0, 0, 8]
                            },
                            {
                                table: {
                                    headerRows: headerRows.length,
                                    widths,
                                    body
                                },
                                layout: {
                                    paddingLeft: () => 0,
                                    paddingRight: () => 0,
                                    paddingTop: () => 1,
                                    paddingBottom: () => 1
                                }
                            }
                        ],

                        styles: {
                            title: {
                                fontSize: 12,
                                bold: true,
                                margin: [0, 0, 0, 6]
                            },
                            meta: {
                                fontSize: 8,
                                margin: [0, 0, 0, 2]
                            }
                        },

                        defaultStyle: {
                            fontSize: 6
                        }
                    }).download(`laporan-pendapatan-${new Date().toISOString().slice(0, 10)}.pdf`)
                },
                renderTabelPendapatan: () => {
                    return new Promise( ( resolve, reject ) => {
                        const { elements, state, methods } = window.laporan.pendapatan
                        const dataMaster = Array.isArray(state.dataMaster) ? state.dataMaster : []
                        const dataPendapatan = Array.isArray(state.dataPendapatan) ? state.dataPendapatan : []
                        const dataAlokasi = Array.isArray(state.dataAlokasi) ? state.dataAlokasi : []
                        elements.tbodyPendapatan.empty()

                        const baseDataMaster = new Map()
                        dataMaster.forEach( ( master ) => {
                            const sd2 = methods.createOrUpdateMap( baseDataMaster, master.sd2_kd, () => ({ sumberdana: master.sd2_sumberdana, sub: new Map(), totalAlokasi: new Map(), totalPendapatan: new Map() }) )
                            const sd4 = methods.createOrUpdateMap( sd2.sub, master.sd4_kd, () => ({ sumberdana: master.sd4_sumberdana, sub: new Map(), totalAlokasi: new Map(), totalPendapatan: new Map() }) )
                            const sd6 = methods.createOrUpdateMap( sd4.sub, master.sd6_kd, () => ({ sumberdana: master.sd6_sumberdana, sub: new Map(), totalAlokasi: new Map(), totalPendapatan: new Map() }) )
                            const sd8 = methods.createOrUpdateMap( sd6.sub, master.sd8_kd, () => ({ sumberdana: master.sd8_sumberdana, sub: new Map(), totalAlokasi: new Map(), totalPendapatan: new Map() }) )
                            const sd10 = methods.createOrUpdateMap( sd8.sub, master.sd10_kd, () => ({ sumberdana: master.sd10_sumberdana , items: new Map(), totalAlokasi: new Map(), totalPendapatan: new Map() }) )
                            const alokasiMatches = dataAlokasi.filter( ( alok ) =>  methods.normalizeKey(alok.sd10_kd) === methods.normalizeKey(master.sd10_kd) )
                            const pendapatanMatches = dataPendapatan.filter( ( pend ) =>  methods.normalizeKey(pend.coa) === methods.normalizeKey(master.sd10_kd) )
                            alokasiMatches.forEach( (alokasi) => { 
                                methods.accumulateByYear( sd10.items, alokasi.tahun_data, alokasi.total_pagu ?? alokasi.pagu ); // simpan detail alokasi per tahun di sd10
                                // roll-up per tahun ke parent
                                [ sd2, sd4, sd6, sd8, sd10 ].forEach( ( node ) => {
                                    methods.accumulateByYear( node.totalAlokasi, alokasi.tahun_data, alokasi.total_pagu ?? alokasi.pagu )
                                })
                            })
                            pendapatanMatches.forEach( (pendapatan) => {
                                // roll-up pendapatan per tahun ke parent
                                [ sd2, sd4, sd6, sd8, sd10 ].forEach( ( node ) => {
                                    methods.accumulateByYear( node.totalPendapatan, pendapatan.tahun, pendapatan.nominal )
                                })
                            })
                        })

                        // generate rows
                        const dataMasterRows = []
                        baseDataMaster.forEach( ( sd2Value, sd2Key ) => {
                            const prevYear = $(`th[data-tahun]:first`).data("tahun")
                            const currYear = $(`th[data-tahun]:last`).data("tahun")
                            let currTotalAlokasi = sd2Value.totalAlokasi.get(String(currYear)) || 0
                            let prevTotalAlokasi = sd2Value.totalAlokasi.get(String(prevYear)) || 0
                            let currTotalPendapatan = sd2Value.totalPendapatan.get(String(currYear)) || 0
                            let prevTotalPendapatan = sd2Value.totalPendapatan.get(String(prevYear)) || 0
                            const prevMetrics = methods.computePersentaseDanSelisih(prevTotalAlokasi, prevTotalPendapatan)
                            const currMetrics = methods.computePersentaseDanSelisih(currTotalAlokasi, currTotalPendapatan)
                            // Sumber Dana 2
                            const rowSd2 = $(`
                                <tr style="font-weight: bold;">
                                    <td style="max-width: 300px; width: 300px">${sd2Value.sumberdana}</td>
                                    <td data-tahun="${prevYear}" class="">${rupiah(prevTotalAlokasi)}</td><td data-tahun="${currYear}" class="">${rupiah(currTotalAlokasi)}</td>
                                    <td data-tahun="${prevYear}">${rupiah(prevTotalPendapatan)}</td><td data-tahun="${currYear}">${rupiah(currTotalPendapatan)}</td>
                                    <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(prevMetrics.persentase)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(currMetrics.persentase)}</td>
                                    <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(prevMetrics.selisih)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(currMetrics.selisih)}</td>
                                </tr>
                            `)
                            dataMasterRows.push( rowSd2 )
                            // Sumber Dana 4
                            sd2Value.sub.forEach( ( sd4Value, sd4Key ) => {
                                let currTotalAlokasi = sd4Value.totalAlokasi.get(String(currYear)) || 0
                                let prevTotalAlokasi = sd4Value.totalAlokasi.get(String(prevYear)) || 0
                                let currTotalPendapatan = sd4Value.totalPendapatan.get(String(currYear)) || 0
                                let prevTotalPendapatan = sd4Value.totalPendapatan.get(String(prevYear)) || 0
                                const prevMetrics = methods.computePersentaseDanSelisih(prevTotalAlokasi, prevTotalPendapatan)
                                const currMetrics = methods.computePersentaseDanSelisih(currTotalAlokasi, currTotalPendapatan)
                                const rowSd4 = $(`
                                    <tr>
                                        <td style="padding-left:10px">${sd4Value.sumberdana}</td>
                                        <td data-tahun="${prevYear}">${rupiah(prevTotalAlokasi)}</td><td data-tahun="${currYear}">${rupiah(currTotalAlokasi)}</td>
                                        <td data-tahun="${prevYear}">${rupiah(prevTotalPendapatan)}</td><td data-tahun="${currYear}">${rupiah(currTotalPendapatan)}</td>
                                        <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(prevMetrics.persentase)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(currMetrics.persentase)}</td>
                                        <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(prevMetrics.selisih)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(currMetrics.selisih)}</td>
                                    </tr>
                                `)
                                dataMasterRows.push( rowSd4 )
                                // Sumber Dana 6
                                sd4Value.sub.forEach( ( sd6Value, sd6Key ) => {
                                    let currTotalAlokasi = sd6Value.totalAlokasi.get(String(currYear)) || 0
                                    let prevTotalAlokasi = sd6Value.totalAlokasi.get(String(prevYear)) || 0
                                    let currTotalPendapatan = sd6Value.totalPendapatan.get(String(currYear)) || 0
                                    let prevTotalPendapatan = sd6Value.totalPendapatan.get(String(prevYear)) || 0
                                    const prevMetrics = methods.computePersentaseDanSelisih(prevTotalAlokasi, prevTotalPendapatan)
                                    const currMetrics = methods.computePersentaseDanSelisih(currTotalAlokasi, currTotalPendapatan)
                                    const rowSd6 = $(`
                                        <tr>
                                            <td style="padding-left:20px">${sd6Value.sumberdana}</td>
                                            <td data-tahun="${prevYear}">${rupiah(prevTotalAlokasi)}</td><td data-tahun="${currYear}">${rupiah(currTotalAlokasi)}</td>
                                            <td data-tahun="${prevYear}">${rupiah(prevTotalPendapatan)}</td><td data-tahun="${currYear}">${rupiah(currTotalPendapatan)}</td>
                                            <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(prevMetrics.persentase)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(currMetrics.persentase)}</td>
                                            <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(prevMetrics.selisih)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(currMetrics.selisih)}</td>
                                        </tr>
                                    `)
                                    dataMasterRows.push( rowSd6 )
                                    // Sumber Dana 8
                                    sd6Value.sub.forEach( ( sd8Value, sd8Key ) => {
                                        let currTotalAlokasi = sd8Value.totalAlokasi.get(String(currYear)) || 0
                                        let prevTotalAlokasi = sd8Value.totalAlokasi.get(String(prevYear)) || 0
                                        let currTotalPendapatan = sd8Value.totalPendapatan.get(String(currYear)) || 0
                                        let prevTotalPendapatan = sd8Value.totalPendapatan.get(String(prevYear)) || 0
                                        const prevMetrics = methods.computePersentaseDanSelisih(prevTotalAlokasi, prevTotalPendapatan)
                                        const currMetrics = methods.computePersentaseDanSelisih(currTotalAlokasi, currTotalPendapatan)
                                        const rowSd8 = $(`
                                            <tr>
                                                <td style="padding-left:30px">${sd8Value.sumberdana}</td>
                                                <td data-tahun="${prevYear}">${rupiah(prevTotalAlokasi)}</td><td data-tahun="${currYear}">${rupiah(currTotalAlokasi)}</td>
                                                <td data-tahun="${prevYear}">${rupiah(prevTotalPendapatan)}</td><td data-tahun="${currYear}">${rupiah(currTotalPendapatan)}</td>
                                                <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(prevMetrics.persentase)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(currMetrics.persentase)}</td>
                                                <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(prevMetrics.selisih)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(currMetrics.selisih)}</td>
                                            </tr>
                                        `)
                                        dataMasterRows.push( rowSd8 )
                                        // Sumber Dana 10
                                        sd8Value.sub.forEach( ( sd10Value, sd10Key ) => {
                                            let currTotalAlokasi = sd10Value.totalAlokasi.get(String(currYear)) || 0
                                            let prevTotalAlokasi = sd10Value.totalAlokasi.get(String(prevYear)) || 0
                                            let currTotalPendapatan = sd10Value.totalPendapatan.get(String(currYear)) || 0
                                            let prevTotalPendapatan = sd10Value.totalPendapatan.get(String(prevYear)) || 0
                                            const prevMetrics = methods.computePersentaseDanSelisih(prevTotalAlokasi, prevTotalPendapatan)
                                            const currMetrics = methods.computePersentaseDanSelisih(currTotalAlokasi, currTotalPendapatan)
                                            const rowSd10 = $(`
                                                <tr>
                                                    <td style="padding-left:40px">${sd10Value.sumberdana}</td>
                                                    <td data-tahun="${prevYear}">${rupiah(prevTotalAlokasi)}</td><td data-tahun="${currYear}">${rupiah(currTotalAlokasi)}</td>
                                                    <td data-tahun="${prevYear}">${rupiah(prevTotalPendapatan)}</td><td data-tahun="${currYear}">${rupiah(currTotalPendapatan)}</td>
                                                    <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(prevMetrics.persentase)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${methods.formatPersentase(currMetrics.persentase)}</td>
                                                    <td data-tahun="${prevYear}" class="${prevMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(prevMetrics.selisih)}</td><td data-tahun="${currYear}" class="${currMetrics.selisih < 0 ? 'text-danger' : ''}">${rupiah(currMetrics.selisih)}</td>
                                                </tr>
                                            `)
                                            dataMasterRows.push( rowSd10 )
                                        })
                                    })
                                })
                            })
                        })
                        elements.tbodyPendapatan.append( dataMasterRows )
                        resolve()
                    })
                },
            }
        }
        window.laporan.pendapatan.methods.init()
    })
</script>
