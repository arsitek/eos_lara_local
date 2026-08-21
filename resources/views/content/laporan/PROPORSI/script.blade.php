<script>
$(document).ready(function () {
    let select2       = $('.s').select2()
    const currentUrl  = new URL(window.location.href)
    const urlParams   = new URLSearchParams(currentUrl.search)
    const isPdf       = window.location.href.includes("pdf")
    const dataBuilder = { "TOTAL" : 0, "PERSENTASE": 0, "TOTAL_ALOKASI" : 0, "PAGU" : 0 }
    $(".loading-div").hide()

    let rupiah = (number) => {
        const formattedValue = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number)
        return formattedValue.replace(/\./g, ',');
    }
    let rupiahToNumber = (rupiahString) => {
        const numericString = rupiahString.replace(/[^\d.]/g, '');
        const numericValue = parseFloat(numericString.replace(/,/g, ''));
        return isNaN(numericValue) ? null : numericValue;
    }

    // 📝 Set url params based on user selected dropdown
    if ( urlParams.size != 0 ) {
        const tahun      = urlParams.get('tahun')
        const idunit     = urlParams.get('unitkerja')
        const sumberdana = urlParams.get('sumberdana')

        $("select.tahun").val(tahun).change()
        $("select.unit_kerja").val(idunit).change()
        $("select.sumberdana").val(sumberdana).change()

    }

    /***
     *  📝 Format decimal with percentage for `proporsi anggaran`
     * @param {number} number
     * @returns {string}
    */
    const formatDecimal = ( number ) => {
        return (Math.round( number * 100) / 100).toFixed(2) + '%'
    }

    /***
     *  📝 Format decimal with percentage for `proporsi anggaran`
     * @param {inputString} string
     * @returns {string}
    */
    function toCamelCase(inputString) {
        let words = inputString.toLowerCase().split(/\s+/);

        for (let i = 1; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
        }

        return words.join(' ');
    }

    $("#btn-cari").on("click", function(){
        // 📦 Init variable
        const idunit     = $("select.unit_kerja").val()
        const sumberdana = $("select.sumberdana").val()

        // 🧩 Validate input
        if ( idunit == "" || sumberdana == "" ) {
            return tata.warn('Perhatian', 'Harap memilih unit kerja dan sumberdana ⚠️ ')
        }

        // 📝 Set url params based on user selected dropdown
        urlParams.set('unitkerja', idunit)
        urlParams.set('sumberdana', sumberdana)
        window.history.replaceState({}, '', `${window.location.pathname}?${urlParams}`);
        generateData( idunit, sumberdana )
    })

    const generateData = ( idunit, sumberdana ) => {
        // pakai promise untuk tangkap resolve dan reject dari ajax
        return new Promise( ( resolve, reject) => {
            $(".loading-div").show()

            // 🧲 Send ajax request
            $.ajax({
                type : "GET", url : "/laporan/proporsianggaran/ProporsiAlokasi/"+ idunit,
                data: { sumberdana },
                success : function( res ){
                    // console.log( res )
                    const { data } = res
                    if ( ( data.alokasi_terpetakan == 0 || data.alokasi == 0 ) && data.coa.length == 0 ) {
                        $(".alokasi-anggaran").text( "" )
                        $(".anggaran-terpetakan").text( "" )
                        $(".loading-div").hide()
                        tata.warn("⚠️ Perhatian", "Data tidak ditemukan")
                        return false
                    }
                    const pagu               = data.alokasi
                    const alokasi_terpetakan = data.alokasi_terpetakan
                    $(".alokasi-anggaran").text( rupiah(pagu) )
                    $(".anggaran-terpetakan").text( rupiah(alokasi_terpetakan) )

                    // empty object
                    dataBuilder["sumberdana"] = {}
                    // 📝 Build data
                    let bodyLaporan = $('.body-tbl')
                    const coa       = data.coa
                    const coa_api   = data.coa_api

                    bodyLaporan.children().remove()
                    // build base data
                    coa.forEach((item, index) => {
                        if ( !dataBuilder["sumberdana"] ) dataBuilder["sumberdana"] = {}
                        if ( !dataBuilder["sumberdana"][item.sd] ) dataBuilder["sumberdana"][item.sd] = { "TOTAL": 0, "TOTAL_ALOKASI" : 0, "PERSENTASE": 0, "NAMA": item.sumberdana, "PAGU" : 0 }
                        if ( !dataBuilder["sumberdana"][item.sd]["unitkerja"] ) dataBuilder["sumberdana"][item.sd]["unitkerja"] = {}
                        if ( !dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja] ) dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja] = { "TOTAL": 0, "TOTAL_ALOKASI" : 0, "PERSENTASE": 0, "NAMA": item.nama_unit}
                        if ( !dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["coa"] ) dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["coa"] = {}
                        if ( !dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["coa"][item.kd_coa_parent] ) dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["coa"][item.kd_coa_parent] = { "TOTAL": 0, "TOTAL_ALOKASI" : 0, "PERSENTASE": 0, "NAMA": item.kd_coa_parent}

                        // generate total nya bosque
                        dataBuilder["TOTAL"] += Number(item.TOTAL_COA)
                        dataBuilder["sumberdana"][item.sd]["TOTAL"] += Number(item.TOTAL_COA)
                        dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["TOTAL"] += Number(item.TOTAL_COA)
                        dataBuilder["sumberdana"][item.sd]["unitkerja"][item.unit_kerja]["coa"][item.kd_coa_parent]["TOTAL"] += Number(item.TOTAL_COA)
                    })

                    // generate total alokasi
                    data.semuaAlokasi.forEach((itemAlokasi, index) => {
                        dataBuilder["TOTAL_ALOKASI"] += Number(itemAlokasi.pagu)
                        if ( dataBuilder["sumberdana"][itemAlokasi.kd_sumberdana] ) {
                            dataBuilder["sumberdana"][itemAlokasi.kd_sumberdana]["TOTAL_ALOKASI"] += Number(itemAlokasi.pagu)
                            if ( dataBuilder["sumberdana"][itemAlokasi.kd_sumberdana]["unitkerja"][itemAlokasi.unit_kerja]) {
                                dataBuilder["sumberdana"][itemAlokasi.kd_sumberdana]["unitkerja"][itemAlokasi.unit_kerja]["TOTAL_ALOKASI"] = Number(itemAlokasi.pagu)
                            }
                        }
                    })
                    data.semuaSumberdana.forEach((itemSumberdana, index) => {
                        dataBuilder["PAGU"] += Number(itemSumberdana.pagu_alokasi)
                        if ( dataBuilder["sumberdana"][itemSumberdana.kd_sumberdana] ) {
                            dataBuilder["sumberdana"][itemSumberdana.kd_sumberdana]["PAGU"] = Number(itemSumberdana.pagu_alokasi)
                        }
                    })
                    // console.log( dataBuilder )
                    // generate persentase
                    coa.forEach((item, index) => {
                        const { sd, unit_kerja, kd_coa_parent } = item
                        const sumberdana  = dataBuilder.sumberdana[sd]
                        const unitkerja   = sumberdana.unitkerja[unit_kerja]
                        const coaItem     = unitkerja.coa[kd_coa_parent]

                        const totalTerpetakanUnit       = unitkerja.TOTAL_ALOKASI
                        const totalTerpetakanSumberdana = sumberdana.TOTAL_ALOKASI
                        const totalPagu                 = sumberdana.PAGU

                        // Calculate percentages
                        dataBuilder["PERSENTASE"] = formatDecimal( Math.min(( dataBuilder["TOTAL_ALOKASI"] / sumberdana.TOTAL  ) * 100, 100));
                        sumberdana.PERSENTASE = Math.min((sumberdana.TOTAL / totalPagu) * 100, 100);
                        unitkerja.PERSENTASE = ( Math.min(( unitkerja.TOTAL / totalTerpetakanUnit) * 100, 100 ))
                        coaItem.PERSENTASE = formatDecimal( Math.min( (coaItem.TOTAL / totalTerpetakanUnit) * 100, 100 ))
                    })

                    const colorTotal = isPdf ? "" : "background-color: rgba(0,255,255, 1); color: darkblue";
                    const colorSd    = isPdf ? "" : "background-color: rgba(0,255,255, .7); color: darkblue";
                    const colorUk    = isPdf ? "" : "background-color: rgba(0, 255, 255, .4); color: darkblue";

                    // 📝 Build table
                    // bodyLaporan.append(`<tr style="font-weight: bold; ${colorTotal}">
                    //     <td class="sumberdana text-center">TOTAL</td>
                    //     <td>${ dataBuilder["PERSENTASE"] }</td>
                    //     <td style="text-align: right">${ rupiah( dataBuilder["TOTAL"]) }</td>
                    // </tr>`)
                    Object.keys(dataBuilder["sumberdana"]).forEach((key) => {
                        // generate total row
                        const totalSumberdana      = dataBuilder["sumberdana"][key].TOTAL
                        const persentaseSumberdana = dataBuilder["sumberdana"][key].PERSENTASE

                        const sumberdana = dataBuilder["sumberdana"][key];
                        // Column order: URAIAN | PAGU ALOKASI | PROPORSI BIAYA | JUMLAH BIAYA
                        bodyLaporan.append(`<tr style="font-size: 18px; font-weight: bold; ${colorSd}">
                            <td>${sumberdana.NAMA}</td>
                            <td style="text-align: right">${rupiah(Number(sumberdana.PAGU) || 0)}</td>
                            <td>${formatDecimal(sumberdana.PERSENTASE)}</td>
                            <td style="text-align: right">${rupiah(sumberdana.TOTAL)}</td>
                        </tr>`)

                        // unitkerja looping 👇
                        Object.keys(sumberdana.unitkerja).forEach((key) => {
                            const unitkerja = sumberdana.unitkerja[key];
                            bodyLaporan.append(`<tr style="${colorUk} ${isPdf ? 'font-weight: bold' : ''}">
                                <td style="padding-left: 20px;">${unitkerja.NAMA}</td>
                                <td style="text-align: right">${rupiah(Number(unitkerja.TOTAL_ALOKASI) || 0)}</td>
                                <td>${formatDecimal(unitkerja.PERSENTASE)}</td>
                                <td style="text-align: right">${rupiah(unitkerja.TOTAL)}</td>
                            </tr>`)
                            // coa looping 👇
                            Object.keys( unitkerja.coa ).forEach((key) => {
                                const coa = unitkerja.coa[key];
                                if ( coa.TOTAL == 0 ) return
                                bodyLaporan.append(`<tr>
                                    <td class="coa" key="${key}" style="padding-left: 35px">${coa.NAMA}</td>
                                    <td></td>
                                    <td>${coa.PERSENTASE}</td>
                                    <td style="text-align: right">${rupiah(coa.TOTAL)}</td>
                                </tr>`)
                            })
                        })
                    })
                    // coa looping 👇
                    coa_api.forEach( ( item, index ) => {
                        $(".coa").each(function () {
                            const id_coa_parent = $(this).text()
                            if ( id_coa_parent == item.coa_parent ) {
                                $(this).text( item.nama_parent.charAt(0).toUpperCase() + '' + toCamelCase(item.nama_parent.slice(1)) )
                            }
                        })
                    })
                    removeEmptyCoa()
                    $(".loading-div").hide()
                    resolve()
                },
                error: function( error ){
                    const msg = error.responseJSON.message || "Gagal memuat data"
                    reject()
                    return tata.error('⛔ Error', msg, { duration: 3000, animate:slide })
                }
            })
        })
    }
    /***
     *  📝 Remove row based on coa
    */
    const removeEmptyCoa = () => {
        $(".coa").each(function () {
            const coa = $(this).text()
            if ( coa == "" || coa == "Pilih ") {
                $(this).parent().remove()
            }
        })
    }

    // 📰 Export to excel
    $(".btn-export-xlsx").on("click", function(){
        if ( urlParams.size == 0 ) {
            return tata.warn('Perhatian', 'Pilih tahun, unit kerja, dan sumberdana ⚠️ ')
        } if ( $(".body-tbl").children().length == 0 ) {
            return tata.warn('Perhatian', 'Data kosong ⚠️ ')
        }
        const idunit = urlParams.get('unitkerja')
        exportExcel("tabel-proporsi", `${idunit}-Proporsi Anggaran`)
    })

    // 📝 Export to pdf
    $("button#btn_exportPdf").on("click", function(){
        // 🌻 Init variable
        const unitkerjaSelected = $("select.unit_kerja").val()
        const sumberdanaSelected = $("select.sumberdana").val()

        // 🧩 Validate input
        if ( urlParams.size == 0 || ( unitkerjaSelected == "" || sumberdanaSelected == ""  ) ) {
            return tata.warn('Perhatian ⚠️', 'Harap memilih unit kerja dan sumberdana.')
        }
        window.open(`/laporan/proporsianggaran/pdf?idunit=${unitkerjaSelected}&sumberdana=${sumberdanaSelected}`, "_blank")
    })

    // check if page is pdf
    if ( isPdf ) {
        // 🌻 Init variable
        const idunit     = urlParams.get('idunit')
        const sumberdana = urlParams.get('sumberdana')
        generateData( idunit, sumberdana ).then( () => {
            window.print() // immediately print the pdf
        })
    }
})
</script>
