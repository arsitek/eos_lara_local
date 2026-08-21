@include("COMPONENTS.multipleSelectScript")
<script>
    $(document).ready( function() {
        // Initialize window.revisi if not exists, otherwise just add alokasi property
        if (!window.revisi) window.revisi = {}
        window.revisi.rekapSubkomponen = {
            elements: {
                root: $(document),
                filterContainer: $(".rekap-shared-filter"),
                rekapSasaranFilterSlot: $(".rekap-sasaran-filter-slot"),
                btnSubmitRekapSubkomponen: $("button#btnSubmitRekapSubkomponen"),
                btnExportRekapSubkomponenPdf: $("button#btnExportRekapSubkomponenPdf"),
                tableRekapSubkomponen: $("table#tabel-rekap-subkomponen"),
            },
            constants: {
                ROUTES: {
                    GET_DATA: "/laporan/rktunit/get/",
                },
                TATA_OPTIONS: {
                    duration: 4000,
                    animate: 'slide',
                }
            },
            methods: {
                /**
                 * Reusable loading indicator utility
                 * Shows or hides the loading spinner with optional button state management
                 * @param {boolean} show - true to show loading, false to hide
                 * @param {jQuery} $button - Optional button element to disable/enable
                 */
                toggleLoading: (show, $button = null) => {
                    if (show) {
                        if ($button) {
                            $button.prop('disabled', true)
                                .data('original-html', $button.html())
                                .html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...')
                        }
                    } else {
                        if ($button) {
                            const originalHtml = $button.data('original-html')
                            $button.prop('disabled', false)
                                .html(originalHtml || $button.html())
                        }
                    }
                },
                bindFunctions: () => {
                    const { btnSubmitRekapSubkomponen, btnExportRekapSubkomponenPdf } = window.revisi.rekapSubkomponen.elements
                    const { mountSharedFilter, reloadTables, exportPdf } = window.revisi.rekapSubkomponen.methods
                    mountSharedFilter()
                    btnSubmitRekapSubkomponen.on("click", reloadTables)
                    btnExportRekapSubkomponenPdf.on("click", exportPdf)
                },
                mountSharedFilter: () => {
                    const { filterContainer, rekapSasaranFilterSlot } = window.revisi.rekapSubkomponen.elements
                    if (!filterContainer.length || !rekapSasaranFilterSlot.length || filterContainer.data("mounted-in-rekap-sasaran")) return

                    rekapSasaranFilterSlot.append(filterContainer)
                    filterContainer.data("mounted-in-rekap-sasaran", true)
                },
                setSelectedFilterValue: (jenis, value) => {
                    const { root } = window.revisi.rekapSubkomponen.elements
                    const $options = root.find(`.${jenis}Option`)
                    const $target = $options.filter((_, el) => String($(el).data("value")) === String(value))
                    if (!$target.length) return

                    $options.removeClass("selected")
                    $target.addClass("selected")
                    root.find(`.${jenis}-container`).closest(".ios-select-multiple").find(".selected-text").text($target.data("text") || $target.text().trim())
                },
                getSelectedFilterValues: () => {
                    const { root } = window.revisi.rekapSubkomponen.elements
                    const uniqueValues = (selector, ignoredValues = []) => {
                        const ignored = new Set(ignoredValues)
                        return [...new Set(root.find(selector).map((_, el) => $(el).data("value")).get()
                            .filter(value => value !== undefined && value !== null && value !== "" && !ignored.has(String(value))))]
                    }

                    return {
                        unitkerja: uniqueValues(".unitkerja-container .unitkerjaOption.selected", ["X", "semua"]),
                        sumberdana: uniqueValues(".sumberdana-container .sumberdanaOption.selected", ["semua"])
                    }
                },
                getSelectedFilterLabels: () => {
                    const { root } = window.revisi.rekapSubkomponen.elements
                    const uniqueLabels = (selector, ignoredValues = []) => {
                        const ignored = new Set(ignoredValues)
                        return [...new Set(root.find(selector).map((_, el) => {
                            const $el = $(el)
                            const value = $el.data("value")
                            if (value === undefined || value === null || value === "" || ignored.has(String(value))) return null
                            return $el.data("text") || $el.text().trim()
                        }).get().filter(Boolean))]
                    }

                    return {
                        unitkerja: uniqueLabels(".unitkerja-container .unitkerjaOption.selected", ["X", "semua"]),
                        sumberdana: uniqueLabels(".sumberdana-container .sumberdanaOption.selected", ["semua"])
                    }
                },
                exportPdf: () => {
                    const { tableRekapSubkomponen } = window.revisi.rekapSubkomponen.elements
                    const { getSelectedFilterLabels } = window.revisi.rekapSubkomponen.methods
                    const { TATA_OPTIONS } = window.revisi.rekapSubkomponen.constants
                    const table = tableRekapSubkomponen.get(0)
                    const tbodyRows = Array.from(table?.querySelectorAll("tbody tr") || [])

                    if (!table) return
                    if (typeof pdfMake === "undefined") {
                        return tata.error("Error", "Library export PDF belum tersedia", TATA_OPTIONS)
                    }
                    if (tbodyRows.length === 0) {
                        return tata.warn("Perhatian", "Data rekap subkomponen belum tersedia untuk diexport", TATA_OPTIONS)
                    }

                    const selectedLabels = getSelectedFilterLabels()
                    const unitLabel = selectedLabels.unitkerja.length ? selectedLabels.unitkerja.join(", ") : "Semua Unit Kerja"
                    const sumberDanaLabel = selectedLabels.sumberdana.length ? selectedLabels.sumberdana.join(", ") : "Semua Sumber Dana"
                    const exportedAt = new Date().toLocaleString("id-ID")
                    const headerStyle = { bold: true, fillColor: "#eeeeee", alignment: "center", margin: [2, 3, 2, 3] }

                    const buildBodyCell = (cell) => {
                        const text = cell.innerText.trim() || " "
                        const computedStyle = window.getComputedStyle(cell)
                        const isNumeric = /^Rp\s|^-?[\d.,]+$|^\(-\)$/.test(text)
                        const isContainRp = text.includes("Rp")
                        const isDanger = cell.classList.contains("text-danger") || computedStyle.color === "rgb(220, 53, 69)" || computedStyle.color === "rgb(255, 0, 0)"
                        const isSuccess = cell.classList.contains("text-success") || computedStyle.color === "rgb(25, 135, 84)" || computedStyle.color === "rgb(0, 128, 0)"

                        return {
                            text,
                            bold: computedStyle.fontWeight === "bold" || Number(computedStyle.fontWeight) >= 600,
                            fontSize: parseFloat(computedStyle.fontSize) <= 12 ? 8 : 9,
                            alignment: isContainRp || text.includes("%") ? "right" : "left",
                            color: isDanger ? "#dc3545" : isSuccess ? "#198754" : "black",
                            margin: [2, 3, 2, 3]
                        }
                    }

                    const body = [
                        [
                            { text: "SEMULA", colSpan: 2, ...headerStyle }, {},
                            { text: "PERUBAHAN", colSpan: 2, ...headerStyle }, {},
                            { text: "SELISIH", ...headerStyle },
                            { text: "PERSENTASE (%)", ...headerStyle }
                        ],
                        [
                            { text: "SUBKOMPONEN", ...headerStyle },
                            { text: "JUMLAH BIAYA", ...headerStyle },
                            { text: "SUBKOMPONEN", ...headerStyle },
                            { text: "JUMLAH BIAYA", ...headerStyle },
                            { text: "SELISIH", ...headerStyle },
                            { text: "PERSENTASE (%)", ...headerStyle }
                        ],
                        ...tbodyRows.map((row) => {
                            const cells = Array.from(row.querySelectorAll("td")).map(buildBodyCell)
                            while (cells.length < 6) cells.push({ text: " " })
                            return cells.slice(0, 6)
                        })
                    ]

                    const docDefinition = {
                        pageOrientation: "landscape",
                        pageSize: "A4",
                        content: [
                            { text: "Rekap Subkomponen", style: "title" },
                            {
                                stack: [
                                    { text: `Unit Kerja: ${unitLabel}`, style: "meta" },
                                    { text: `Sumber Dana: ${sumberDanaLabel}`, style: "meta" },
                                    { text: `Tanggal Export: ${exportedAt}`, style: "meta", alignment: "right" }
                                ],
                                margin: [0, 0, 0, 10]
                            },
                            {
                                table: {
                                    headerRows: 2,
                                    widths: ["28%", "12%", "28%", "12%", "10%", "10%"],
                                    body
                                }
                            }
                        ],
                        styles: {
                            title: { fontSize: 14, bold: true, margin: [0, 0, 0, 8] },
                            meta: { fontSize: 9, margin: [0, 0, 0, 3] }
                        },
                        defaultStyle: {
                            fontSize: 8
                        }
                    }

                    const filenameDate = new Date().toISOString().slice(0, 10)
                    pdfMake.createPdf(docDefinition).download(`rekap-subkomponen-${filenameDate}.pdf`)
                },
                generateCurrentDataMap: ( data ) => {
                    const map = new Map()
                    data.forEach( item => {
                        const { kd_sumberdana, sumberdana, kode_ss, ss, ikk, kode_ikk, rab_type, kode_ikv, ikv, rincian_kegiatan, kode_keg, kd_rk, jumlah_biaya_revisi } = item
                        const sdMap = createOrUpdateMap( map, kd_sumberdana, () => ({ sumberdana, total: 0, sub: new Map() }) )
                        const kroMap = createOrUpdateMap( sdMap.sub ||= new Map(), kode_ss, () => ({ ss, total: 0, sub: new Map() }) )
                        const roMap = createOrUpdateMap( kroMap.sub ||= new Map(), kode_ikk, () => ({ ikk, total: 0, sub: new Map() }) )
                        const komponenMap = createOrUpdateMap( roMap.sub ||= new Map(), kode_ikv, () => ({ ikv, total: 0, sub: new Map() }) )
                        const subkomponenMap = createOrUpdateMap( komponenMap.sub ||= new Map(), kd_rk, () => ({ rincian_kegiatan, total: 0, items: [] }) )
                        subkomponenMap.items.push({ ...item });
                        // Update totals
                        [ sdMap, kroMap, roMap, komponenMap, subkomponenMap ].forEach( m => {
                            m.total += Number(jumlah_biaya_revisi)
                        })
                    })
                    return map
                },
                /**
                 * Reusable function to merge all keys from both maps
                 * Returns a Set containing all unique keys
                 */
                getAllKeys: (map1, map2) => {
                    const keys = new Set()
                    if (map1) map1.forEach((_, key) => keys.add(key))
                    if (map2) map2.forEach((_, key) => keys.add(key))
                    return keys
                },

                /**
                 * Reusable function to render comparison table with both backup and current data
                 * Shows differences and calculates selisih (difference)
                 */
                renderComparisonTable: (backupData, currentData) => {
                    return new Promise((resolve, reject) => {
                        try {
                            const { tableRekapSubkomponen } = window.revisi.rekapSubkomponen.elements
                            const { getAllKeys } = window.revisi.rekapSubkomponen.methods
                            const tableBody = tableRekapSubkomponen.find("tbody")
                            tableBody.empty()
                            const html = []
                            const formatKey = (key) => (key === null || key === undefined || key === '' ? 'data tidak ditemukan' : key)
                            const formatLabel = (label) => label || 'data tidak ditemukan'
                            const escapeAttr = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                            const calculatePercentage = (selisih, semula) => Number(semula) === 0 ? 0 : (Number(selisih) / Number(semula)) * 100
                            const formatPercentage = (value) => `${Number.isFinite(Number(value)) ? Number(value).toFixed(2) : '0.00'}%`
                            const comparisonToneClass = (value) => Number(value) > 0 ? 'text-success' : Number(value) < 0 ? 'text-danger' : ''

                            // Get all unique keys at sumberdana level
                            const allSdKeys = getAllKeys(backupData, currentData)

                            allSdKeys.forEach(sdKey => {
                                const backupSd = backupData?.get(sdKey)
                                const currentSd = currentData?.get(sdKey)
                                const sdLabel = formatLabel(backupSd?.sumberdana || currentSd?.sumberdana)
                                const sdKeyDisplay = formatKey(sdKey)
                                const backupSdTotal = backupSd?.total || 0
                                const currentSdTotal = currentSd?.total || 0
                                const selisihSd = currentSdTotal - backupSdTotal
                                const persentaseSd = calculatePercentage(selisihSd, backupSdTotal)

                                html.push(`<tr class="fw-bold rekap-subkomponen-sumberdana-row" data-sumberdana="${escapeAttr(sdKey)}" style="font-size: 15px">
                                    <td style="width: 300px; min-width: 500px; max-width: 500px;">${sdKeyDisplay} | ${sdLabel}</td>
                                    <td>${backupSd ? rupiah(backupSdTotal) : '(-)'}</td>
                                    <td style="width: 300px; min-width: 500px; max-width: 500px;">${currentSd ? `${sdKeyDisplay} | ${sdLabel}` : '(-)'}</td>
                                    <td>${currentSd ? rupiah(currentSdTotal) : '(-)'}</td>
                                    <td class="${comparisonToneClass(selisihSd)}">${rupiah(selisihSd)}</td>
                                    <td class="${comparisonToneClass(persentaseSd)}">${formatPercentage(persentaseSd)}</td>
                                </tr>`)

                                // Get all unique keys at KRO level
                                const allKroKeys = getAllKeys(backupSd?.sub, currentSd?.sub)

                                allKroKeys.forEach(kroKey => {
                                    const backupKro = backupSd?.sub?.get(kroKey)
                                    const currentKro = currentSd?.sub?.get(kroKey)

                                    const kroLabel = formatLabel(backupKro?.ss || currentKro?.ss)
                                    const kroKeyDisplay = formatKey(kroKey)
                                    const backupKroTotal = backupKro?.total || 0
                                    const currentKroTotal = currentKro?.total || 0
                                    const selisihKro = currentKroTotal - backupKroTotal
                                    const persentaseKro = calculatePercentage(selisihKro, backupKroTotal)

                                    html.push(`<tr class="fw-bold rekap-subkomponen-sasaran-row" data-sumberdana="${escapeAttr(sdKey)}" data-kode-ss="${escapeAttr(kroKey)}" style="font-size: 14px">
                                        <td style="padding-left: 20px;">${kroKeyDisplay} | ${kroLabel}</td>
                                        <td>${backupKro ? rupiah(backupKroTotal) : '(-)'}</td>
                                        <td style="padding-left: 20px;">${currentKro ? `${kroKeyDisplay} | ${kroLabel}` : '(-)'}</td>
                                        <td>${currentKro ? rupiah(currentKroTotal) : '(-)'}</td>
                                        <td class="${comparisonToneClass(selisihKro)}">${rupiah(selisihKro)}</td>
                                        <td class="${comparisonToneClass(persentaseKro)}">${formatPercentage(persentaseKro)}</td>
                                    </tr>`)

                                    // Get all unique keys at RO level
                                    const allRoKeys = getAllKeys(backupKro?.sub, currentKro?.sub)

                                    allRoKeys.forEach(roKey => {
                                        const backupRo = backupKro?.sub?.get(roKey)
                                        const currentRo = currentKro?.sub?.get(roKey)

                                        const roLabel = formatLabel(backupRo?.ikk || currentRo?.ikk)
                                        const roKeyDisplay = formatKey(roKey)
                                        const backupRoTotal = backupRo?.total || 0
                                        const currentRoTotal = currentRo?.total || 0
                                        const selisihRo = currentRoTotal - backupRoTotal
                                        const persentaseRo = calculatePercentage(selisihRo, backupRoTotal)

                                        html.push(`<tr>
                                            <td style="padding-left: 40px;">${roKeyDisplay} | ${roLabel}</td>
                                            <td>${backupRo ? rupiah(backupRoTotal) : '(-)'}</td>
                                            <td style="padding-left: 40px;">${currentRo ? `${roKeyDisplay} | ${roLabel}` : '(-)'}</td>
                                            <td>${currentRo ? rupiah(currentRoTotal) : '(-)'}</td>
                                            <td class="${comparisonToneClass(selisihRo)}">${rupiah(selisihRo)}</td>
                                            <td class="${comparisonToneClass(persentaseRo)}">${formatPercentage(persentaseRo)}</td>
                                        </tr>`)

                                        // Get all unique keys at Komponen level
                                        const allKomponenKeys = getAllKeys(backupRo?.sub, currentRo?.sub)

                                        allKomponenKeys.forEach(komponenKey => {
                                            const backupKomponen = backupRo?.sub?.get(komponenKey)
                                            const currentKomponen = currentRo?.sub?.get(komponenKey)

                                            const komponenLabel = formatLabel(backupKomponen?.ikv || currentKomponen?.ikv)
                                            const komponenKeyDisplay = formatKey(komponenKey)
                                            const backupKomponenTotal = backupKomponen?.total || 0
                                            const currentKomponenTotal = currentKomponen?.total || 0
                                            const selisihKomponen = currentKomponenTotal - backupKomponenTotal
                                            const persentaseKomponen = calculatePercentage(selisihKomponen, backupKomponenTotal)

                                            html.push(`<tr style="font-size: 12px">
                                                <td style="padding-left: 60px;">${komponenKeyDisplay} | ${komponenLabel}</td>
                                                <td>${backupKomponen ? rupiah(backupKomponenTotal) : '(-)'}</td>
                                                <td style="padding-left: 60px;">${currentKomponen ? `${komponenKeyDisplay} | ${komponenLabel}` : '(-)'}</td>
                                                <td>${currentKomponen ? rupiah(currentKomponenTotal) : '(-)'}</td>
                                                <td class="${comparisonToneClass(selisihKomponen)}">${rupiah(selisihKomponen)}</td>
                                                <td class="${comparisonToneClass(persentaseKomponen)}">${formatPercentage(persentaseKomponen)}</td>
                                            </tr>`)

                                            // Get all unique keys at Subkomponen level
                                            const allSubkomponenKeys = getAllKeys(backupKomponen?.sub, currentKomponen?.sub)
                                            allSubkomponenKeys.forEach(subkomponenKey => {
                                                const backupSubkomponen = backupKomponen?.sub?.get(subkomponenKey)
                                                const currentSubkomponen = currentKomponen?.sub?.get(subkomponenKey)

                                                const subkomponenLabel       = formatLabel(backupSubkomponen?.rincian_kegiatan || currentSubkomponen?.rincian_kegiatan)
                                                const subkomponenKeyDisplay  = formatKey(subkomponenKey)
                                                const backupSubkomponenTotal = backupSubkomponen?.total || 0
                                                const currentSubkomponenTotal = currentSubkomponen?.total || 0
                                                const selisihSubkomponen = currentSubkomponenTotal - backupSubkomponenTotal
                                                const persentaseSubkomponen = calculatePercentage(selisihSubkomponen, backupSubkomponenTotal)
                                                html.push(`<tr style="font-size: 11px">
                                                    <td style="padding-left: 80px;">${subkomponenKeyDisplay} | ${subkomponenLabel}</td>
                                                    <td>${backupSubkomponen ? rupiah(backupSubkomponenTotal) : '(-)'}</td>
                                                    <td style="padding-left: 80px;">${currentSubkomponen ? `${subkomponenKeyDisplay} | ${subkomponenLabel}` : '(-)'}</td>
                                                    <td>${currentSubkomponen ? rupiah(currentSubkomponenTotal) : '(-)'}</td>
                                                    <td class="${comparisonToneClass(selisihSubkomponen)}">${rupiah(selisihSubkomponen)}</td>
                                                    <td class="${comparisonToneClass(persentaseSubkomponen)}">${formatPercentage(persentaseSubkomponen)}</td>
                                                 </tr>`)
                                            })
                                        })
                                    })
                                })
                            })

                            tableBody.append(html.join(""))
                            resolve()
                        } catch (error) {
                            reject(error)
                        }
                    })
                },
                showDataToTable: () => {
                    const { currentData, backupData } = window.revisi.rekapSubkomponen
                    const { renderComparisonTable } = window.revisi.rekapSubkomponen.methods
                    const { TATA_OPTIONS } = window.revisi.rekapSubkomponen.constants
                    console.log(backupData)
                    if ( backupData.size === 0 || currentData.size === 0 )
                        return tata.warn("Perhatian ⚠️", "Data tidak lengkap untuk ditampilkan", TATA_OPTIONS)

                    renderComparisonTable(backupData, currentData).then(() => {
                        console.log("Table rendered successfully with comparison data")
                    }).catch(error => {
                        console.error("Error rendering comparison table:", error)
                        return tata.error("⛔ Error", "Terjadi kesalahan pada saat memuat data", TATA_OPTIONS)
                    })
                },
                reloadTables: async () => {
                    const { getData, getSelectedFilterValues } = window.revisi.rekapSubkomponen.methods
                    const { unitkerja, sumberdana } = getSelectedFilterValues()
                    if (unitkerja.length === 0 && sumberdana.length === 0) {
                        return tata.warn("Perhatian", "Silakan pilih setidaknya satu filter untuk memuat data", window.revisi.rekapSubkomponen.constants.TATA_OPTIONS)
                    }
                    const tasks = [getData(unitkerja, sumberdana)]

                    if (window.revisi?.methods?.muatRekapSasaran) {
                        tasks.push(window.revisi.methods.muatRekapSasaran(unitkerja, sumberdana))
                    }

                    await Promise.all(tasks)
                },
                getData: async (idunit = [], kodeSd = []) => {
                    const { ROUTES } = window.revisi.rekapSubkomponen.constants
                    const { generateCurrentDataMap, showDataToTable, toggleLoading } = window.revisi.rekapSubkomponen.methods
                    const { btnSubmitRekapSubkomponen } = window.revisi.rekapSubkomponen.elements
                    // Show loading indicator
                    toggleLoading(true, btnSubmitRekapSubkomponen)

                    const fetchData = async (url, data = {}) => {
                        try {
                            const res = await $.ajax({
                                url,
                                type: "GET",
                                data,
                                dataType: "json"
                            })
                            if (res.success) {
                                return res?.data?.baseData ?? []
                            } else {
                                console.error("Error in response:", res.message)
                            }
                        } catch (error) {
                            console.error("Error fetching data:", error)
                            throw error
                        }
                    }

                    try {
                        const payload = { unitkerja: idunit, kodeSd, verifikasi: "semua" }
                        await fetchData(`${ROUTES.GET_DATA}null/null`, payload).then( data => {
                            const dataMap = generateCurrentDataMap( data )
                            window.revisi.rekapSubkomponen.currentData = dataMap
                        })
                        await fetchData(`${ROUTES.GET_DATA}null/null`, { ...payload, backup: 69 }).then( data => {
                            const dataMap = generateCurrentDataMap( data )
                            window.revisi.rekapSubkomponen.backupData = dataMap
                        })
                        showDataToTable()
                    } catch (error) {
                        console.error("Failed to load data:", error)
                        alert("Gagal memuat data. Silakan coba lagi.")
                    } finally {
                        // Hide loading indicator
                        toggleLoading(false, btnSubmitRekapSubkomponen)
                    }
                }
            }
        }
        window.revisi.rekapSubkomponen.methods.bindFunctions()
    })
</script>
