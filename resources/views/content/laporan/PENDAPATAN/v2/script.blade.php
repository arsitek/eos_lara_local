<script>
    $( document ).ready( function(){
        $("select.unit_kerja").select2()
        const tabel      = $("table#tabelPendapatan")
        window.laporan   = {}
        window.laporan.pendapatan = {}

        // Get dynamic years from session
        const currentYear = {{ $tahunAngka }}
        const pastYear = currentYear - 1
        const years = [currentYear, pastYear]

        const baseData = { data: new Map(), nominal: 0 }

        window.laporan.pendapatan.methods = {
            resetData: () => {
                baseData.data.clear();
                baseData.nominal = 0;
            },

            exportPdf: () => {
                const table = tabel.get(0)
                const visibleRows = Array.from(table?.querySelectorAll("tbody tr") || []).filter((row) => row.offsetParent !== null)
                if (!table) return
                if (typeof pdfMake === "undefined") {
                    return tata.error("Error", "Library export PDF belum tersedia", { duration: 5000, animate: "slide" })
                }
                if (visibleRows.length === 0) {
                    return tata.warn("Perhatian", "Data laporan pendapatan belum tersedia untuk diexport", { duration: 5000, animate: "slide" })
                }

                const unitLabel = $("select.unit_kerja option:selected").text().trim() || "Semua unitkerja"
                const exportedAt = new Date().toLocaleString("id-ID")
                const title = $(".card-title").first().text().trim() || "Laporan Pendapatan"
                const buildCell = (cell, isHeader = false) => {
                    const text = cell.innerText.trim() || " "
                    const computedStyle = window.getComputedStyle(cell)
                    const color = computedStyle.color
                    const isDanger = cell.classList.contains("text-danger") || ["rgb(220, 53, 69)", "rgb(255, 0, 0)"].includes(color)
                    const isSuccess = cell.classList.contains("text-success") || ["rgb(25, 135, 84)", "rgb(0, 128, 0)"].includes(color)
                    const isNumeric = /^Rp\s|^-?[\d.,]+%?$|^-?$/.test(text)
                    const pdfCell = {
                        text,
                        bold: isHeader || computedStyle.fontWeight === "bold" || Number(computedStyle.fontWeight) >= 600,
                        alignment: isHeader ? "center" : (isNumeric ? "right" : "left"),
                        color: isDanger ? "#dc3545" : (isSuccess ? "#198754" : "black"),
                        margin: [2, 3, 2, 3],
                        fontSize: isHeader ? 7 : 6
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
                            for (let i = 1; i < colspan; i++) output[colIndex + i] = {}
                            if (rowspan > 1) {
                                for (let i = 0; i < colspan; i++) spans[colIndex + i] = rowspan - 1
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
                const colCount = Math.max(headerRows[0]?.length || 0, bodyRows[0]?.length || 0)
                const widths = Array.from({ length: colCount }, (_, index) => index < 2 ? "12%" : "auto")
                const body = [...headerRows, ...bodyRows]

                pdfMake.createPdf({
                    pageOrientation: "landscape",
                    pageSize: "A4",
                    content: [
                        { text: title, style: "title" },
                        { text: `Unit Kerja: ${unitLabel}`, style: "meta" },
                        { text: `Tanggal Export: ${exportedAt}`, style: "meta", margin: [0, 0, 0, 8] },
                        { table: { headerRows: headerRows.length, widths, body } }
                    ],
                    styles: {
                        title: { fontSize: 12, bold: true, margin: [0, 0, 0, 6] },
                        meta: { fontSize: 8, margin: [0, 0, 0, 2] }
                    },
                    defaultStyle: { fontSize: 6 }
                }).download(`laporan-pendapatan-${new Date().toISOString().slice(0, 10)}.pdf`)
            },
            getDataRealtime : () => {
                // Reset data before fetching new data
                methods.resetData();
                $.ajax({
                    url : "{{ route('laporan.pendapatan.getDataRealtime') }}",
                    success : (res) => {

                        if (!res.data || !res.data) {
                            console.error('Invalid response structure - data is missing')
                            return
                        }

                        methods.generateData( res.data ).then( data => {
                            methods.generateTable( data )
                        }).catch( error => {
                            console.error('Error in generateData:', error)
                        })
                    }, error: ( err ) => {
                        return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data", { duration: 5000, animate: "slide" })
                    }
                })
            },
            generateData : ( data ) => {
                return new Promise( ( resolve, reject ) => {
                    try {
                        // Check if data has any properties
                        const dataKeys = Object.keys(data)
                        if (dataKeys.length === 0) {
                            console.warn('Data object is empty')
                            resolve(baseData)
                            return
                        }

                        // Iterate through each year in the data object
                        Object.entries(data).forEach(([year, yearData]) => {

                            yearData.data.forEach( ( item, index ) => {
                                const { coa: kodeCoa, nama_coa: namaCoa, kode_unit: kodeUnit, nama_unit: namaUnit, nominal } = item
                                const tahunBuilder = createOrUpdateMap( baseData.data, year, () => ({ data: new Map(), nominal: 0 }) )
                                const coaBuilder = createOrUpdateMap( tahunBuilder.data, kodeCoa, () => ({ kodeCoa, namaCoa, unit: new Map(), nominal: 0 }) )
                                const unitBuilder = createOrUpdateMap( coaBuilder.unit, kodeUnit, () => ({ kodeUnit, namaUnit, nominal: 0 }) )
                                unitBuilder.nominal += parseFloat( nominal )
                                coaBuilder.nominal += parseFloat( nominal )
                                tahunBuilder.nominal += parseFloat( nominal )
                                baseData.nominal += parseFloat( nominal )
                            })
                        })
                        resolve(baseData)
                    } catch (error) {
                        console.error('Error processing data:', error)
                        reject(error)
                    }
                })
            },
            generateTable : ( data ) => {
                return new Promise( ( resolve, reject ) => {
                    const kodeUnit = $("select.unit_kerja").val()
                    // Clear existing table content
                    tabel.find('tbody').empty()
                    tabel.find('tbody').append(`<tr class="fw-bold" style="font-size: 14px">
                        <td colspan="2">TOTAL</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="total" key="${pastYear}"></td>
                        <td class="total" key="${currentYear}"></td>
                        <td class="persentase" key="${pastYear}"></td>
                        <td class="persentase" key="${currentYear}"></td>
                        <td class="selisih" key="${pastYear}"></td>
                        <td class="selisih" key="${currentYear}"></td>
                    </tr>`)
                    // Create a structure to organize data by COA across years
                    const coaMap = new Map()
                    const totalByYear = new Map()
                    // First pass: organize data by COA
                    data.data.forEach((yearData, year) => {
                        yearData.data.forEach((coaData, coaCode) => {
                            if (!coaMap.has(coaCode)) {
                                coaMap.set(coaCode, {
                                    kodeCoa: coaData.kodeCoa,
                                    namaCoa: coaData.namaCoa,
                                    unit: new Map(),
                                    years: new Map()
                                })
                            }
                            coaData.unit.forEach((unitData, unitCode) => {
                                if (!coaMap.get(coaCode).unit.has(unitCode)) {
                                    coaMap.get(coaCode).unit.set(unitCode, {
                                        kodeUnit: unitData.kodeUnit,
                                        namaUnit: unitData.namaUnit,
                                        beforeYear: { nominal: 0, realisasi: 0, percent: 0, selisih: 0 },
                                        currentYear: { nominal: 0, realisasi: 0, percent: 0, selisih: 0 },
                                    })
                                }
                                // Update unit data for the specific year
                                const unitYearData = coaMap.get(coaCode).unit.get(unitCode)
                                if (year == currentYear) {
                                    unitYearData.currentYear.realisasi += unitData.nominal
                                } else if (year == pastYear) {
                                    unitYearData.beforeYear.realisasi += unitData.nominal
                                }
                            })
                            // Store year data for this COA
                            coaMap.get(coaCode).years.set(year, {
                                target: 0, // You might need to adjust this based on your data structure
                                realisasi: coaData.nominal,
                            })
                        })
                        totalByYear.set(year, {
                            nominal: yearData.nominal,
                        })
                    })
                    // Second pass: generate table rows
                    let filteredTotalCurrent = 0, filteredTotalPast = 0;

                    Array.from(coaMap.keys()).sort().forEach(coaCode => {
                        const coaInfo = coaMap.get(coaCode);
                        // Check if this COA has the selected unit (or show all if semua/empty)
                        let hasSelectedUnit = false;
                        let coaFilteredCurrent = 0, coaFilteredPast = 0;

                        coaInfo.unit.forEach((unitInfo, unitCode) => {
                            // check if this unit matches the selected filter
                            if (kodeUnit === 'semua' || !kodeUnit || unitCode.startsWith(kodeUnit)) {
                                hasSelectedUnit = true;
                                coaFilteredCurrent += unitInfo.currentYear.realisasi || 0;
                                coaFilteredPast += unitInfo.beforeYear.realisasi || 0;
                            }
                        });

                        // Skip this COA if it doesn't have the selected unit
                        if (!hasSelectedUnit) return;

                        // Add to filtered totals
                        filteredTotalCurrent += coaFilteredCurrent;
                        filteredTotalPast += coaFilteredPast;

                        const currentYearData = coaInfo.years.get(currentYear.toString()) || { target: 0, realisasi: 0, nominal: 0 }
                        const beforeYearData = coaInfo.years.get(pastYear.toString()) || { target: 0, realisasi: 0, nominal: 0 }

                        // Calculate percentages
                        const selisihCurrentYear  = Math.abs( currentYearData.target - coaFilteredCurrent )
                        const selisihBeforeYear  = Math.abs( beforeYearData.target - coaFilteredPast )

                        const ratarataCurrentYear = ( currentYearData.target + coaFilteredCurrent ) / 2
                        const ratarataBeforeYear = ( beforeYearData.target + coaFilteredPast ) / 2

                        // const currentYearPercent = (ratarataCurrentYear && !isNaN(ratarataCurrentYear)) ? Math.min( (selisihCurrentYear / ratarataCurrentYear) * 100, 100) : null;
                        // const beforeYearPercent = (ratarataBeforeYear && !isNaN(ratarataBeforeYear)) ? Math.min( (selisihBeforeYear / ratarataBeforeYear) * 100, 100) : null;
                        const currentYearPercent = null;
                        const beforeYearPercent = null;

                        // Generate main COA row with toggle functionality
                        const row = `
                            <tr class="fw-bold coa-parent-row" data-coa-code="${coaCode}" style="cursor: pointer; transition: background-color 0.3s ease;">
                                <td>
                                    <span class="toggle-icon" style="display: inline-block; transition: transform 0.3s ease; color: #007bff; font-weight: bold; margin-right: 8px;">▶</span> ${coaInfo.kodeCoa}
                                </td>
                                <td>${coaInfo.namaCoa}</td>
                                <td class="text-right" style="border-left: 2px solid white">-</td>
                                <td class="text-right" style="border-right: 2px solid white">-</td>
                                <td class="text-right">${rupiah(coaFilteredPast)}</td>
                                <td class="text-right" style="border-right: 2px solid white">${rupiah(coaFilteredCurrent)}</td>
                                <td class="text-right">${(currentYearPercent !== null && !isNaN(currentYearPercent)) ? currentYearPercent.toFixed(2) + '%' : '-'}</td>
                                <td class="text-right" style="border-right: 2px solid white">${(beforeYearPercent !== null && !isNaN(beforeYearPercent)) ? beforeYearPercent.toFixed(2) + '%' : '-'}</td>
                                <td class="text-right">${rupiah(selisihBeforeYear)}</td>
                                <td class="text-right">${rupiah(selisihCurrentYear)}</td>
                            </tr>
                        `
                        tabel.find('tbody').append(row)

                        coaInfo.unit.forEach((unitInfo, unitCode) => {
                            if (kodeUnit === 'semua' || !kodeUnit || unitCode.startsWith(kodeUnit)) {
                                unitInfo.currentYear.selisih = Math.abs( unitInfo.currentYear.nominal - unitInfo.currentYear.realisasi )
                                unitInfo.beforeYear.selisih  = Math.abs( unitInfo.beforeYear.nominal - unitInfo.beforeYear.realisasi )
                                const ratarataCurrentYear = ( unitInfo.currentYear.nominal + unitInfo.currentYear.realisasi ) / 2
                                const ratarataBeforeYear = ( unitInfo.beforeYear.nominal + unitInfo.beforeYear.realisasi ) / 2
                                // let percentCurrent = (ratarataCurrentYear && !isNaN(ratarataCurrentYear)) ? Math.min((selisihCurrentYear / ratarataCurrentYear) * 100, 100) : null;
                                // let percentBefore = (ratarataBeforeYear && !isNaN(ratarataBeforeYear)) ? Math.min((selisihBeforeYear / ratarataBeforeYear) * 100, 100) : null;
                                let percentCurrent = null;
                                let percentBefore =  null;
                                unitInfo.currentYear.percent = (percentCurrent !== null && !isNaN(percentCurrent)) ? percentCurrent : null;
                                unitInfo.beforeYear.percent = (percentBefore !== null && !isNaN(percentBefore)) ? percentBefore : null;
                                // Generate unit rows (initially hidden)
                                const unitRow = `<tr class="unit-row" data-parent-coa="${coaCode}" style="display: none;">
                                    <td style="padding-left: 25px">${unitInfo.kodeUnit}</td>
                                    <td style="">${unitInfo.namaUnit}</td>
                                    <td class="text-right" style="border-left: 2px solid white">-</td>
                                    <td class="text-right" style="border-right: 2px solid white">-</td>
                                    <td class="text-right">${rupiah(unitInfo.beforeYear.realisasi)}</td>
                                    <td class="text-right" style="border-right: 2px solid white">${rupiah(unitInfo.currentYear.realisasi)}</td>
                                    <td class="text-right">${(unitInfo.currentYear.percent !== null && !isNaN(unitInfo.currentYear.percent)) ? unitInfo.currentYear.percent + '%' : '-'}</td>
                                    <td class="text-right" style="border-right: 2px solid white">${(unitInfo.beforeYear.percent !== null && !isNaN(unitInfo.beforeYear.percent)) ? unitInfo.beforeYear.percent + '%' : '-'}</td>
                                    <td class="text-right">${rupiah(unitInfo.beforeYear.selisih)}</td>
                                    <td class="text-right">${rupiah(unitInfo.currentYear.selisih)}</td>
                                </tr>`
                                tabel.find('tbody').append(unitRow)
                            }
                        })
                    })

                    // Update total row with filtered totals
                    tabel.find('tbody').find(`.total[key='${currentYear}']`).text( rupiah(filteredTotalCurrent) )
                    tabel.find('tbody').find(`.total[key='${pastYear}']`).text( rupiah(filteredTotalPast) )

                    // Calculate selisih and percent for total row based on filtered data
                    const totalSelisihCurrent = Math.abs(0 - filteredTotalCurrent); // Target is 0 for now
                    const totalSelisihPast = Math.abs(0 - filteredTotalPast);
                    const totalRatarataCurrent = (0 + filteredTotalCurrent) / 2;
                    const totalRatarataPast = (0 + filteredTotalPast) / 2;
                    // const totalPercentCurrent = (totalRatarataCurrent && !isNaN(totalRatarataCurrent)) ? Math.min((totalSelisihCurrent / totalRatarataCurrent) * 100, 100) : null;
                    // const totalPercentPast = (totalRatarataPast && !isNaN(totalRatarataPast)) ? Math.min((totalSelisihPast / totalRatarataPast) * 100, 100) : null;
                    const totalPercentCurrent =  null;
                    const totalPercentPast =  null;

                    tabel.find('tbody').find(`.selisih[key='${currentYear}']`).text( rupiah(totalSelisihCurrent) )
                    tabel.find('tbody').find(`.selisih[key='${pastYear}']`).text( rupiah(totalSelisihPast) )
                    tabel.find('tbody').find(`.persentase[key='${currentYear}']`).text( (totalPercentCurrent !== null && !isNaN(totalPercentCurrent)) ? totalPercentCurrent.toFixed(2) + '%' : '-' )
                    tabel.find('tbody').find(`.persentase[key='${pastYear}']`).text( (totalPercentPast !== null && !isNaN(totalPercentPast)) ? totalPercentPast.toFixed(2) + '%' : '-' )
                    $("button.cari").prop('disabled', false).html('Submit')
                    console.log('Table generation completed')
                })
            },
        }

        const methods = window.laporan.pendapatan.methods

        $("button.btnExportPendapatanPdf").on("click", methods.exportPdf)

        $("button.cari").on("click", function(){
            // Clear any existing table data
            const btn = $(this)
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...')

            tabel.find('tbody').empty();
            methods.getDataRealtime()
        })

        tabel.find('tbody').off('click', '.coa-parent-row').on('click', '.coa-parent-row', function() {
            const coaCode = $(this).data('coa-code');
            const unitRows = tabel.find(`tr.unit-row[data-parent-coa="${coaCode}"]`);
            const toggleIcon = $(this).find('.toggle-icon');

            if (unitRows.is(':visible')) {
                unitRows.hide();
                toggleIcon.html('▶');
            } else {
                unitRows.show();
                toggleIcon.html('▼');
            }
        });
    })
</script>
