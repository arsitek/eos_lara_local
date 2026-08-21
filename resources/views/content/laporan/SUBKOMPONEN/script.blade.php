<script>
    $( document ).ready( function () {
        const url        = new URL(window.location.href)
        const idunit     = url.searchParams.get("idunit")
        const sumberdana = url.searchParams.get("sumberdana")
        const filter     = url.searchParams.get("filterdata")
        const groupData  = url.searchParams.get("groupData")
        const tblBody    = ".body-tbl-unit"
        const isPdf      = window.location.href.includes("pdf")
        window.laporan   = {}
        window.laporan.subkomponen = {}
        window.laporan.subkomponen.methods = {}
        $(".cari").on("click", function() {
            const idunit     = $("select.unit_kerja").val()
            const sumberdana = $("select.sumberdana").val()
            const filter     = $("select.filter-data").val()
            const groupData  = url.searchParams.get("groupData")

            if( idunit == "" ){
                return tata.warn('Perhatian', 'Silahkan memilih unit kerja terlebih dahulu')
            } if ( sumberdana == ""){
                return tata.warn('Perhatian', 'Silahkan memilih sumber dana terlebih dahulu')
            }
            window.laporan.subkomponen.methods.buildData( idunit, sumberdana, filter )
        })
        $( document ).on("change", ".filter-data", function() {
            const value     = $(this).val()
            const idunit    = $("select.unit_kerja").val()
            const sd        = $("select.sumberdana").val()
            const filterDom = $(".filter-data")

            // Check if the value is empty
            if ( idunit == "" ) {
                filterDom.val('').html(filterDom.html())
                return tata.warn('⚠️ Perhatian', 'Silahkan memilih unitkerja terlebih dahulu')
            }
            if ( sd == "" ) {
                filterDom.val('').html(filterDom.html())
                return tata.warn('⚠️ Perhatian', 'Silahkan memilih sumberdana terlebih dahulu')
            }
        })
        $( document ).on("change", "select.groupData", function() {
            const val = $(this).val()
            url.searchParams.set("groupData", val)
            window.history.pushState({}, '', url)
        })
        if ( !isPdf ) {
            $(".s").select2()
        }
        window.laporan.subkomponen.methods = {
            createOrUpdateObject: ( obj, key, createNode ) => {
                if ( !obj[key] ) obj[key] = createNode()
                return obj[key]
            },
            getDataSubkomponen: ( idunit, kodeSd, filter ) => {
                return new Promise( ( resolve, reject ) => {
                    $.ajax({
                        type : "GET",
                        url : `/laporan/subkomponen/get/${idunit}/${kodeSd}?filterdata=${filter}`,
                        success: ( res ) => {
                            resolve( res )
                        }, error: ( err ) => {
                            const message = err.responseJSON?.message || "Terjadi kesalahan saat memuat data"
                            reject( message )
                        }
                    })
                })
            },
            buildData: ( idunit, sumberdana, filter ) => {
                return new Promise( ( resolve, reject ) => {
                    showLoader()
                    setLoaderText( "Memuat data subkomponen..." )
                    window.laporan.subkomponen.methods.getDataSubkomponen( idunit, sumberdana, filter )
                    .then( ( res ) => {
                        const { dataMaster, baseDataKeg, sumberdana, sumSisaSaldo } = res.data
                        if ( baseDataKeg.length === 0 ) {
                            removeLoader()
                            if ( isPdf ) {
                                $(".pdf-status").text("Data tidak ditemukan")
                            }
                            return tata.warn("⚠️ Perhatian", "Data tidak ditemukan")
                        }
                        // create structure data
                        const baseData = { total: 0, totalAmprah: 0, totalRealisasi: 0, sub: {} }
                        baseDataKeg.forEach( item => {
                            const { kode_sd: kodeSd, sumberdana, kode_ss: kodeSs, sasaran_program, rincian_kegiatan: rincianKeg, kode_keg: kodeKeg, kode_ikv: kodeIkv, ikv,
                                kode_ikk: kodeIkk, indikator_kinerja_kegiatan: ikk, jumlah_biaya: TOTAL } = item
                            const sumberdanaBuilder = window.laporan.subkomponen.methods.createOrUpdateObject( baseData.sub, kodeSd, () => ({ kodeSd, sumberdana, sub: {}, total: 0, totalAmprah: 0, totalRealisasi: 0 }) )
                            const sasaranBuilder    = window.laporan.subkomponen.methods.createOrUpdateObject( sumberdanaBuilder.sub, kodeSs, () => ({ kodeSs, sasaran_program, sub: {}, total: 0, totalAmprah: 0, totalRealisasi: 0 }) )
                            const indikatorBuilder = window.laporan.subkomponen.methods.createOrUpdateObject( sasaranBuilder.sub, kodeIkk, () => ({ kodeIkk, ikk, sub: {}, total: 0, totalAmprah: 0, totalRealisasi: 0 }) )
                            const komponenBuilder = window.laporan.subkomponen.methods.createOrUpdateObject( indikatorBuilder.sub, kodeIkv, () => ({ kodeIkv, ikv, sub: {}, total: 0, totalAmprah: 0, totalRealisasi: 0 }) )
                            const subkomponenBuilder = window.laporan.subkomponen.methods.createOrUpdateObject( komponenBuilder.sub, kodeKeg, () => ({ kodeKeg, rincianKeg, data: [], total: 0, totalAmprah: 0, totalRealisasi: 0 }) )
                            subkomponenBuilder.data.push( item )

                            // calculate totals
                            sumberdanaBuilder.total += Number(TOTAL)
                            sumberdanaBuilder.totalAmprah += Number(item.TOTAL_AMPRAH)
                            sumberdanaBuilder.totalRealisasi += Number(item.TOTAL_REALISASI)

                            sasaranBuilder.total += Number(TOTAL)
                            sasaranBuilder.totalAmprah += Number(item.TOTAL_AMPRAH)
                            sasaranBuilder.totalRealisasi += Number(item.TOTAL_REALISASI)

                            indikatorBuilder.total += Number(TOTAL)
                            indikatorBuilder.totalAmprah += Number(item.TOTAL_AMPRAH)
                            indikatorBuilder.totalRealisasi += Number(item.TOTAL_REALISASI)

                            komponenBuilder.total += Number(TOTAL)
                            komponenBuilder.totalAmprah += Number(item.TOTAL_AMPRAH)
                            komponenBuilder.totalRealisasi += Number(item.TOTAL_REALISASI)

                            subkomponenBuilder.total += Number(TOTAL)
                            subkomponenBuilder.totalAmprah += Number(item.TOTAL_AMPRAH)
                            subkomponenBuilder.totalRealisasi += Number(item.TOTAL_REALISASI)

                            baseData.total += Number(TOTAL)
                            baseData.totalAmprah += Number(item.TOTAL_AMPRAH)
                            baseData.totalRealisasi += Number(item.TOTAL_REALISASI)
                        });
                        window.laporan.subkomponen.methods.loadRkaSubkomponen( baseData )
                        removeLoader()
                        $(".pdf-status").text("")
                    })
                    .catch( ( err ) => {
                        removeLoader()
                        $("#loading-msg").text("Terjadi Kesalahan saat memuat data")
                        return tata.error("⛔ Error", err)
                    })
                })
            },
            loadRkaSubkomponen: ( data ) => {
                $(`${tblBody}`).children().remove()
                const totalRow = `<tr class="total fw-bold" style="font-size: 14px;">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-right">${ rupiah( data.total ) }</td>
                    <td class="text-right">${ rupiah( data.totalAmprah ) }</td>
                    <td class="text-right">${ rupiah( data.totalRealisasi ) }</td>
                    <td class="text-right">${ rupiah( data.total - data.totalRealisasi ) }</td></tr>`
                $(tblBody).append( totalRow )
                $("td.total").text( rupiah( data.total ) )
                Object.values( data.sub ).forEach( itemSd => {
                    const { kodeSd, sumberdana, total, totalAmprah, totalRealisasi } = itemSd
                    const realisasi = totalAmprah + totalRealisasi
                    const row = `<tr class="sumberdana fw-bold" data-kode-sd="${kodeSd}">
                        <td>${kodeSd}</td>
                        <td style="width: 600px;">${sumberdana}</td>
                        <td class="text-right">${ rupiah( total ) }</td>
                        <td class="text-right">${ rupiah( totalAmprah ) }</td>
                        <td class="text-right">${ rupiah( totalRealisasi ) }</td>
                        <td class="text-right">${ rupiah( total - realisasi ) }</td></tr>`
                    $(tblBody).append( row )
                    Object.values( itemSd.sub ).forEach( itemSs => {
                        const { kodeSs, sasaran_program, total: totalSs, totalAmprah: totalAmprahSs, totalRealisasi: totalRealisasiSs } = itemSs
                        const realisasiSs = totalAmprahSs + totalRealisasiSs
                        const style = !kodeSs ? "color: red; font-style: italic;" : ""
                        const rowSs = `<tr class="sasaran" data-kode-ss="${kodeSs}" style="${style}">
                            <td>${kodeSs ?? '-'}</td>
                            <td class="">${sasaran_program ?? 'Data tidak ditemukan'}</td>
                            <td class="text-right">${ rupiah( totalSs ) }</td>
                            <td class="text-right">${ rupiah( totalAmprahSs ) }</td>
                            <td class="text-right">${ rupiah( totalRealisasiSs ) }</td>
                            <td class="text-right">${ rupiah( totalSs - realisasiSs ) }</td></tr>`
                        $(tblBody).append( rowSs )

                        Object.values( itemSs.sub ).forEach( itemIkk => {
                            const { kodeIkk, ikk, total: totalIkk, totalAmprah: totalAmprahIkk, totalRealisasi: totalRealisasiIkk } = itemIkk
                            const realisasiIkk = totalAmprahIkk + totalRealisasiIkk
                            const style = !kodeIkk ? "color: red; font-style: italic;" : ""
                            const rowIkk = `<tr class="indikator" data-kode-ikk="${kodeIkk}" style="${style}">
                                <td>${kodeIkk ?? '-'}</td>
                                <td class="">${ikk ?? 'Data tidak ditemukan'}</td>
                                <td class="text-right">${ rupiah( totalIkk ) }</td>
                                <td class="text-right">${ rupiah( totalAmprahIkk ) }</td>
                                <td class="text-right">${ rupiah( totalRealisasiIkk ) }</td>
                                <td class="text-right">${ rupiah( totalIkk - realisasiIkk ) }</td></tr>`
                            $(tblBody).append( rowIkk )
                            Object.values( itemIkk.sub ).forEach( itemIkv => {
                                const { kodeIkv, ikv, total: totalIkv, totalAmprah: totalAmprahIkv, totalRealisasi: totalRealisasiIkv } = itemIkv
                                const realisasiIkv = totalAmprahIkv + totalRealisasiIkv
                                const style = !kodeIkv ? "color: red; font-style: italic;" : ""
                                const rowIkv = `<tr class="komponen" data-kode-ikv="${kodeIkv}" style="${style}">
                                    <td>${kodeIkv ?? '-'}</td>
                                    <td class="">${ikv ?? 'Data tidak ditemukan'}</td>
                                    <td class="text-right">${ rupiah( totalIkv ) }</td>
                                    <td class="text-right">${ rupiah( totalAmprahIkv ) }</td>
                                    <td class="text-right">${ rupiah( totalRealisasiIkv ) }</td>
                                    <td class="text-right">${ rupiah( totalIkv - realisasiIkv ) }</td></tr>`
                                $(tblBody).append( rowIkv )
                                Object.values( itemIkv.sub ).forEach( itemKeg => {
                                    const { kodeKeg, rincianKeg, data, total: totalKeg, totalAmprah: totalAmprahKeg, totalRealisasi: totalRealisasiKeg } = itemKeg
                                    const realisasiKeg = totalAmprahKeg + totalRealisasiKeg
                                    const style = !kodeKeg ? "color: red; font-style: italic;" : ""
                                    const rowKeg = `<tr class="subkomponen" data-kode-keg="${kodeKeg}" style="${style}">
                                        <td>${kodeKeg ?? '-'}</td>
                                        <td class="">${rincianKeg ?? 'Data tidak ditemukan'}</td>
                                        <td class="text-right">${ rupiah( totalKeg ) }</td>
                                        <td class="text-right">${ rupiah( totalAmprahKeg ) }</td>
                                        <td class="text-right">${ rupiah( totalRealisasiKeg ) }</td>
                                        <td class="text-right">${ rupiah( totalKeg - realisasiKeg ) }</td></tr>`
                                    $(tblBody).append( rowKeg )
                                })
                            })
                        })
                    })
                })
            },
        }
    })
</script>
