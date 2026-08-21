<script>
$(document).ready(function () {
    const url        = new URL(window.location.href)
    const params     = new URLSearchParams(window.location.search);
    const unitParams = params.get("idunit") ?? ""
    const sdParams   = params.get("sumberdana") ?? ""
    const unitSelect = $("select.unitkerja")
    const sdSelect   = $("select.sumberdana")
    const isPdf      = window.location.href.includes("pdf")
    const modalRekap = $("#modal-rekap-semua-unit")
    let tabelRekap   = $(".tabelRekapSemuaUnit")

    // cek apakah user mengganti tema
    const currentTheme  = localStorage.getItem("BgImage") ?? " "
    const listOfThemes  = ["bg-img1", "bg-img2", "bg-img3", "bg-img4"]
    const isThemed      = listOfThemes.includes(currentTheme.split(" ")[0])

    if ( isPdf === false ) {
        $(sdSelect).select2()
        $(unitSelect).select2()
        tabelRekap = tabelRekap.DataTable({
            pageLength: 10, rowsGroup: [1], ordering: false
        })
    }

    if ( unitParams ) {
        unitSelect.val( unitParams ).trigger("change")
    } if ( sdParams ) {
        sdSelect.val( sdParams ).trigger("change")
    }
    window.laporan = {}
    window.laporan.rkat = {
        elements: {
            btnSubmit: $("button.cari"),
            tabelRkat: $("table#tabel-rkat")
        },
        constants: {
            DATA: null,
            TIMEOUT: 30000,
            CSRF_TOKEN: "{{ csrf_token() }}",
            ROUTES: {
                GET_DATA: "{{ route('rkat.lampiran.get') }}",
            }
        },
        methods: {
            rupiah: (number) => {
                const formattedValue = new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                }).format(number)
                // Replace dots with commas
                return formattedValue.replace(/\./g, ',');
            },
            createOrUpdateMap: ( map, key, createNode ) => {
                if ( !map.has(key) ) map.set(key, createNode() )
                return map.get(key)
            },
            getData: async function() {
                try {
                    const data = {
                        idunit: isPdf === false ? unitSelect.val() : params.get("idunit"),
                        sumberdana: isPdf === false ? sdSelect.val() : params.get("sumberdana"),
                    }
                    const response = await $.ajax({
                        type: "GET",
                        url: window.laporan.rkat.constants.ROUTES.GET_DATA,
                        timeout: window.laporan.rkat.constants.TIMEOUT,
                        data: data
                    })
                    if ( response.success ) {
                        console.log( response )
                        window.laporan.rkat.constants.DATA = response.data
                        return response
                    } else {
                        const message = response.message || "Gagal mendapatkan data"
                        return tata.error("⛔ Error", message)
                    }
                } catch ( error ) {
                    const message = error.responseJSON?.message || "Terjadi kesalahan saat mendapatkan data"
                    return tata.error("⛔ Error", message)
                }
            },
            buildData: function( rawData ) {
                return new Promise( ( resolve, reject ) => {
                    try {
                        const { data, sumberdana } = rawData
                        const baseData = new Map()
                        const methods = this
                        const createOrUpdateMap = methods.createOrUpdateMap
                        let total = 0
                        // Generate Base Data --
                        data.forEach( item => {
                            if ( item.kodeSd8 == "41010101" ) {
                                total += Number(item.TOTAL_KEG)
                            }
                            const sd2     = createOrUpdateMap( baseData, item.kodeSd2, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.sumberDana2, sub: new Map() }) )
                            const sd4     = createOrUpdateMap( sd2.sub, item.kodeSd4, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.sumberDana4, sub: new Map() }) )
                            const sd6     = createOrUpdateMap( sd4.sub, item.kodeSd6, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.sumberDana6, sub: new Map() }) )
                            const sd8     = createOrUpdateMap( sd6.sub, item.kodeSd8, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.sumberDana8, sub: new Map() }) )
                            const kodeSs  = createOrUpdateMap( sd8.sub, item.kode_ss_rkat, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.sasaran_rkat, sub: new Map() }) )
                            const kodeRo  = createOrUpdateMap( kodeSs.sub, item.kode_ro_rkat, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.ro_rkat, sub: new Map() }) )
                            const kodeIkv = createOrUpdateMap( kodeRo.sub, item.kode_ikv_rkat, () => ({ total: 0, jumlahKeg: 0, jumlahReal: 0, jumlahAmprah: 0, desc: item.ikv_rkat, sub: new Map() }) )
                            const kodeKeg = createOrUpdateMap( kodeIkv.sub, item.kode_keg_rkat, () => ({
                                total: Number(item.TOTAL_KEG),
                                jumlahKeg: Number(item.JUMLAH_KEG),
                                jumlahReal: Number(item.TOTAL_REALISASI),
                                jumlahAmprah: Number(item.TOTAL_AMPRAH),
                                desc: item.keg_rkat,
                                sub: new Map()
                            }) );
                            // Calculate totals
                            [ kodeIkv, kodeRo, kodeSs, sd8, sd6, sd4, sd2 ].forEach( level => {
                                level.total += Number(item.TOTAL_KEG)
                                level.jumlahKeg += Number(item.JUMLAH_KEG)
                                level.jumlahReal += Number(item.TOTAL_REALISASI)
                                level.jumlahAmprah += Number(item.TOTAL_AMPRAH)
                            })
                        })
                        console.log( total )
                        resolve( baseData )
                    } catch ( error ) {
                        return reject( error )
                    }
                })
            },
            generateData: function( data ) {
                const tabelRkat = window.laporan.rkat.elements.tabelRkat
                const tbody     = tabelRkat.find("tbody")
                const methods   = this
                const rupiah    = methods.rupiah
                tbody.empty()
                const sdColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, 1); color: black; border-bottom: 1px solid gray"
                    : "background-color: rgba(0,255,255, 1); color: darkblue")
                const kroColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .8); color: white; border-bottom: 1px solid gray"
                    : "background-color: rgba(0,255,255, .8); color: darkblue")
                const roColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .7); color: white; border-bottom: 1px solid gray"
                    : "background-color: rgba(0,255,255, .7); color: darkblue")
                const ikvColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .6); color: white; border-bottom: 1px solid gray"
                    : "background-color: rgba(0,255,255, .5); color: darkblue")
                const skColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .4); color: white; border-bottom: 1px solid gray"
                    : "background-color: rgba(0,255,255, .5); color: darkblue")
                let total = 0
                data.forEach( ( sd2, kodeSd2 ) => {
                    const tr = `<tr style="${sdColor}; font-weight: bold; font-size: 16px">
                        <td>${ kodeSd2 }</td>
                        <td>${ sd2.desc }</td>
                        <td>${sd2.jumlahKeg}</td><td>Keg</td>
                        <td>1</td><td>Tahun</td>
                        <td>1</td><td>Paket</td>
                        <td>${ rupiah(sd2.total)}</td>
                    </tr>`
                    total = sd2.total
                    tbody.append( tr )
                    sd2.sub.forEach( ( sd4, kodeSd4 ) => {
                        const tr = `<tr style="${sdColor}; font-weight: bold;">
                            <td>${ kodeSd4 }</td>
                            <td>${ sd4.desc }</td>
                            <td>${sd4.jumlahKeg}</td><td>Keg</td>
                            <td>1</td><td>Tahun</td>
                            <td>1</td><td>Paket</td>
                            <td>${ rupiah(sd4.total)}</td>
                        </tr>`
                        tbody.append( tr )
                        sd4.sub.forEach( ( sd6, kodeSd6 ) => {
                            const tr = `<tr style="${sdColor}; font-weight: bold;">
                                <td>${ kodeSd6 }</td>
                                <td>${ sd6.desc }</td>
                                <td>${sd6.jumlahKeg}</td><td>Keg</td>
                                <td>1</td><td>Tahun</td>
                                <td>1</td><td>Paket</td>
                                <td>${ rupiah(sd6.total)}</td>
                            </tr>`
                            tbody.append( tr )
                            sd6.sub.forEach( ( sd8, kodeSd8 ) => {
                                const tr = `<tr style="${sdColor}; font-weight: bold;">
                                    <td>${ kodeSd8 }</td>
                                    <td>${ sd8.desc }</td>
                                    <td>${sd8.jumlahKeg}</td><td>Keg</td>
                                    <td>1</td><td>Tahun</td>
                                    <td>1</td><td>Paket</td>
                                    <td>${ rupiah(sd8.total)}</td>
                                </tr>`
                                $(`.totalPtnbh-${kodeSd8}`).text( rupiah(sd8.total) )
                                tbody.append( tr )
                                sd8.sub.forEach( ( ss, kodeSs ) => {
                                    const tr = `<tr style="${kroColor};">
                                        <td>${ kodeSs }</td>
                                        <td>${ ss.desc }</td>
                                        <td>${ss.jumlahKeg}</td><td>Keg</td>
                                        <td>1</td><td>Tahun</td>
                                        <td>1</td><td>Paket</td>
                                        <td>${ rupiah(ss.total)}</td>
                                    </tr>`
                                    tbody.append( tr )
                                    ss.sub.forEach( ( ro, kodeRo ) => {
                                        const tr = `<tr style="${roColor};">
                                            <td>${ kodeRo }</td>
                                            <td>${ ro.desc }</td>
                                            <td>${ro.jumlahKeg}</td><td>Keg</td>
                                            <td>1</td><td>Tahun</td>
                                            <td>1</td><td>Paket</td>
                                            <td>${ rupiah( ro.total )}</td>
                                        </tr>`
                                        tbody.append( tr )
                                        ro.sub.forEach( ( ikv, kodeIkv ) => {
                                            const tr = `<tr style="${skColor};">
                                                <td>${ kodeIkv }</td>
                                                <td>${ ikv.desc }</td>
                                                <td>${ikv.jumlahKeg}</td><td>Keg</td>
                                                <td>1</td><td>Tahun</td>
                                                <td>1</td><td>Paket</td>
                                                <td>${ rupiah( ikv.total )}</td>
                                            </tr>`
                                            tbody.append( tr )
                                            ikv.sub.forEach( ( keg, kodeKeg ) => {
                                                const tr = `<tr>
                                                    <td>${ kodeKeg }</td>
                                                    <td>${ keg.desc }</td>
                                                    <td>${keg.jumlahKeg}</td><td>Keg</td>
                                                    <td>1</td><td>Tahun</td>
                                                    <td>1</td><td>Paket</td>
                                                    <td>${ rupiah( keg.total )}</td>
                                                </tr>`
                                                tbody.append( tr )
                                            })
                                        })
                                    })
                                })
                            })
                        })
                    })
                })
                $("td.totalSumberdana").text( rupiah(total) )
            }
        }
    }
    function bindEvents() {
        const { btnSubmit } = window.laporan.rkat.elements
        btnSubmit.on("click", handleOnSubmit)
    }
    const handleOnSubmit = () => {
        if ( !sdSelect.val() )
            return tata.error("⛔ Error", "Harap memilih sumberdana terlebih dahulu")
        if ( !unitSelect.val() )
            return tata.error("⛔ Error", "Harap memilih unitkerja terlebih dahulu")

        window.history.pushState({}, null, `?idunit=${unitSelect.val()}&sumberdana=${sdSelect.val()}`)  // change url

        showLoader()
        setLoaderText("Sedang memuat data...")
        window.laporan.rkat.methods.getData().finally( () => {
            window.laporan.rkat.methods.buildData( window.laporan.rkat.constants.DATA ).then( data => {
                console.log( data )
                window.laporan.rkat.methods.generateData( data )
            }).catch( err => {
                console.error( err )
                tata.error("⛔ Error", "Terjadi kesalahan saat memproses data")
            })
            removeLoader()
        })
    }
    bindEvents()
    $("button#btn_exportPdf").on("click", function(){
        if ( !sdSelect.val() ) {
            return tata.error("⛔ Error", "Harap memilih sumberdana terlebih dahulu")
        }
        if ( !unitSelect.val() ) {
            return tata.error("⛔ Error", "Harap memilih unitkerja terlebih dahulu")
        }
        window.open(`{{ route('rkat.lampiran.pdf') }}?idunit=${unitSelect.val()}&sumberdana=${sdSelect.val()}`, "_blank")
    })
    $("button#btn_exportXlsx").on("click", function(){
        const bodyTable = document.getElementsByClassName("bodyTbl")
        const unitkerja = $("select.unitkerja option:selected").text()
        const buttonDom = $("#btn_exportXlsx").html()

        // Cek data
        if ( $("select.unitkerja").val() == "" )
            return tata.warn("Perhatian", "Silahkan memilih unit kerja")
        if ( $("select.sumberdana").val() == "" )
            return tata.warn("Perhatian", "Silahkan memilih sumber dana")
        if (bodyTable[0].rows.length === 0)
            return tata.warn("Perhatian", "Tidak terdapat data")

        $("#btn_exportXlsx").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengunduh file...`)
        setTimeout(() => {
            try {
                const tabel = document.getElementById('tabel-rkat');
                const rows = tabel.rows
                const wb = XLSX.utils.table_to_book(tabel, {
                    sheet: "sheet1",
                    raw: true, // Keeps original data without auto-detection
                })

                // Iterate through the workbook to enforce text formatting
                const ws = wb.Sheets["sheet1"]
                Object.keys(ws).forEach((cell) => {
                    if (cell[0] === '!') return // Skip special keys
                    const cellValue = ws[cell].v
                    // Force text type for cells that might be misinterpreted
                    if (typeof cellValue === 'string' && /^\d{1,2}\/\d{1,2}\/\d{2,4}$/.test(cellValue)) {
                        ws[cell].z = '@'; // Explicitly set as text
                    }
                })
                $("#btn_exportXlsx").html(buttonDom);
                return XLSX.writeFile(wb, `Lampiran RKAT-${unitkerja}.xlsx`)
            } catch (e) {
                $("#btn_exportXlsx").html(buttonDom)
                return tata.error("Error", "Terjadi kesalahan saat export data")
            }
            }, 1000)
    })

    $( document ).on("click", "tr.rekapSemuaUnit",  function(){
        if ( isPdf ) return
        if ( unitSelect.val() !== "semua_unit" )
            return

        modalRekap.modal("show")

        $.ajax({
            url: `{{ route('rkat.lampiran.get.semuaunit') }}?sumberdana=${sdSelect.val()}`,
            type: "GET",
            success: ( res ) => {
                // console.log( res )
                const { data } = res

                let html = ``
                data.forEach( ( item, index ) => {
                    html += `
                        <tr>
                            <td style="width: 30px">${ index + 1 }</td>
                            <td style="width: 350px">${item.kd_sumberdana} | ${item.sumberdana}</td>
                            <td style="width: 350px">${item.nama_unit}</td>
                            <td class="text-end">${ rupiah( item.TOTAL )}</td>
                        </tr>
                    `
                })
                $(".tabelRekapSemuaUnit tbody").html( html )
                tabelRekap.clear().rows.add($(".tabelRekapSemuaUnit tbody").find("tr")).draw()
            },
            error: ( err ) => {
                const message = err.responseJSON.message || "Gagal mendapatkan data"
                return tata.error("⛔ Error", message)
            }
        })
    })
    $( document ).on("click", "button#close-modal-rekap-semua-unit", function(){
        modalRekap.modal("hide")
    })

})
</script>
