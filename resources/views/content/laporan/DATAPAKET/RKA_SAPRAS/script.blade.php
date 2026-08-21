<script>
    $(document).ready(function () {
        const tabelPPK        = $("#tabel-rka-ppk")
        const totalBiayaCoa   = []
        const totalBiayaRekat = []
        const totalBiayaUnit  = []
        const totalBiayaKeg   = []
        const totalBiayaIkv   = []
        const totalBiayaRo    = []
        const totalBiayaKro   = []
        const url             = window.location.href
        const params          = new URL(url)
        const isPdf           = params.searchParams.get("isPdf")
        const nip             = params.searchParams.get("nip")
        const sd              = params.searchParams.get("sumberdana")
        const jenis           = params.searchParams.get("jenis")
        const exportPdfAnchor = document.getElementById("exportPdfAnchor")
        $(".ppk").select2()

        $("#btn-submit-filter-rka-ppk").on("click", function(){
            const selectPPK = $("select[name=ppk]").val()
            const filterData = $("select[name=filter-data]").val()
            const sumberdana = $("select[name=sumberdana]").val()
            if ( selectPPK == "" ) {
                return tata.warn('Perhatian ⚠️', 'Silahkan memilih PPK terlebih dahulu')
            } if ( filterData == "" ) {
                return tata.warn('Perhatian ⚠️', 'Silahkan memilih filter data terlebih dahulu')
            } if ( sumberdana == "" ) {
                return tata.warn('Perhatian ⚠️', 'Silahkan memilih sumberdana terlebih dahulu')
            }
            const url = "{{ route('laporan.datapaket.get.rkappk') }}"
            return getRkaPpk( url, selectPPK, filterData, sumberdana )
        })
        const getRkaPpk = ( url, ppk, filterData, sumberdana ) => {
            $.ajax({
                url: url,
                type: "GET",
                data: { nip: ppk, jenis: filterData, sumberdana: sumberdana },
                beforeSend: function(){
                    if ( isPdf != "true" ) {
                        exportPdfAnchor.href = `/laporan/datapaket/rka/sapras/pdf?isPdf=true&nip=${ppk}&jenis=${filterData}&sumberdana=${sumberdana}`
                    }
                    $(".loading-msg").show()
                    $("#loading-msg-text").html("Sedang memuat data...")
                },
                success: ( res ) => {
                    // console.log( res )
                    const {
                        dataMaster, unitKerja, subJudul, coa, itemCoa, sumberdana, sumKeg, sumIkv, sumRo, sumKro, sumSd
                    } = res.data
                    if ( itemCoa.length == 0 ) {
                        hideLoading(".loading-msg")
                        $("#loading-msg").html("Data tidak ditemukan...")
                        return tata.warn('Perhatian ⚠️', 'Data tidak ditemukan')
                    }
                    tabelPPK.find("tbody").html("")
                    muatSubkomponen( dataMaster, sumberdana )
                    muatRka( unitKerja, subJudul, coa, itemCoa, sumKeg, sumIkv, sumRo, sumKro, sumSd )
                    hideLoading(".loading-msg")
                    $("#loading-msg").hide()
                },
                error: ( xhr ) => {
                    $("#loading-msg").html("Terjadi kesalahan saat memproses data...")
                    const message = xhr.responseJSON.message
                    tata.error('Galat', message || 'Terjadi kesalahan saat memproses data')
                }
            })
        }
        if ( isPdf == "true") {
            $("#loading-msg").show()
            $("#loading-msg").html("Sedang memuat data...")
            const $selectPPK = $("select[name=ppk]").val()
            const url = "{{ route('laporan.datapaket.get.rkappk') }}"
            getRkaPpk( url, nip, jenis, sd )
        }
        /*
        * @param {Array} masterData
        * ⤵️ Load `subkomponen` into table rka ppk : 48
        */
        const muatSubkomponen = ( masterData, sumberdana ) => {
            // 🔵 Show loading message
            showLoading( ".loading-msg", "#loading-msg-text", "Sedang memuat data subkomponen..." )
            // 👇 Get & Clear the tbody : 50 - 51
            const $tbody = tabelPPK.find("tbody")
            $tbody.html("")
            // 👇 Loop through the data
            let html = ""
            const kodeSd = sumberdana.kd_sumberdana ?? '-'
            const Sd     = sumberdana.sumberdana ?? '-'
            html += `<tr class="sd-${kodeSd}">
                <td style="width: 150px">${kodeSd}</td>
                <td>${Sd}</td>
                <td></td><td style="text-align: right;" class="kodeSumberdanaBiaya kodeSumberdanaBiaya-${kodeSd}"></td><td></td>
                <td class="kodeSumberdanaAmprahan"></td>
                <td class="kodeSumberdanaRealisasi"></td>
                <td class="kodeSumberdanaSisa"></td>
            </tr>`
            masterData.forEach( item => {
                html += `
                    <tr class="ss-${item.kode_ss}">
                        <td style="width: 150px">${item.kode_ss}</td>
                        <td>${item.sasaran_program}</td>
                        <td></td><td style="text-align: right;"  class="kodeKroBiaya kodeKroBiaya-${item.kode_ss}"></td><td></td>
                        <td class="kodeKroAmprahan"></td>
                        <td class="kodeKroRealisasi"></td>
                        <td class="kodeKroSisa"></td>
                    </tr>`
                item.ro.forEach( itemRo => {
                    html += `
                        <tr class="iku-${itemRo.kode_ikk}">
                            <td>${itemRo.kode_ikk}</td>
                            <td>${itemRo.indikator_kinerja_kegiatan}</td>
                            <td></td><td style="text-align: right;" class="kodeRoBiaya kodeRoBiaya-${itemRo.kode_ikk}"></td><td></td>
                            <td class="kodeRoAmprahan"></td>
                            <td class="kodeRoRealisasi"></td>
                            <td class="kodeRoSisa"></td>
                        </tr>`
                    itemRo.ikv.forEach( itemIkv => {
                        html += `
                            <tr class="ikv-${itemIkv.kode_ikv}">
                                <td>${itemIkv.kode_ikv}</td>
                                <td>${itemIkv.ikv}</td>
                                <td></td><td style="text-align: right;" class="kodeIkvBiaya kodeIkvBiaya-${itemIkv.kode_ikv}"></td><td></td>
                                <td class="kodeIkvAmprahan"></td>
                                <td class="kodeIkvRealisasi"></td>
                                <td class="kodeIkvSisa"></td>
                            </tr>`
                        itemIkv.subkomponen.forEach( itemSub => {
                            html += `
                                <tr class="keg-${itemSub.kode_keg}">
                                    <td>${itemSub.kode_keg}</td>
                                    <td>${itemSub.rincian_kegiatan}</td>
                                    <td></td><td style="text-align: right;" class="kodeKegBiaya kodeKegBiaya-${itemSub.kode_keg}"></td><td></td>
                                    <td class="kodeKegAmprahan"></td>
                                    <td class="kodeKegRealisasi"></td>
                                    <td class="kodeKegSisa"></td>
                                </tr>`
                        })
                    })
                })
            })
            // 👇 append html data ( kro ) to tbody
            $tbody.html( html )
            // 🔵 Hide loading message
            hideLoading(".loading-msg")
        }
        /**
        * Load PPK data into the "rka ppk" table.
        *
        * @param {Array} unitkerjaGroup - PPK data grouped by unitkerja.
        * @param {Array} rktGroup - PPK data grouped by rkt.
        * @param {Array} coaGroup - PPK data grouped by rkt.
        * @param {Array} itemGroup - PPK data grouped by rkt.
        * @returns {void}
        * @description This function loads PPK data into the "rka ppk" table.
        */
        const muatRka = ( unitkerjaGroup, rktGroup, coaGroup, itemGroup, sumKeg, sumIkv, sumRo, sumKro, sumSd ) => {
            // 👇 Get & Clear the tbody : 50 - 51
            const $tbody = tabelPPK.find("tbody")
            // 👇 Loop through the data
            let html = ""
            // 🔵 Show loading message
            unitkerjaGroup.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                let kodeKeg = `${item.kd_rk.replace(/\./g, '\\.')}`
                $(`.keg-${kodeKeg}`).after(`<tr class="unit-${item.kd_rk}-${item.unit_kerja}">
                    <td>${item.unit_kerja}</td>
                    <td>${item.nama ?? '-'}</td>
                    <td></td>
                    <td style="text-align: right;" class="unitBiaya ${item.kd_rk}">${ rupiah(item.TOTAL) }</td>
                    <td></td>
                    <td>${ rupiah( item.TOTAL_AMPRAH ?? '0')}</td>
                    <td>${ rupiah( item.TOTAL_REALISASI ?? '0') }</td>
                    <td>${ rupiah( item.TOTAL - realisasi ) }</td>
                </tr>`)
            })
            rktGroup.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                let kodeKeg = `${item.kd_rk.replace(/\./g, '\\.')}`
                $(`.unit-${kodeKeg}-${item.unit_kerja}`).after(`<tr class="fw-bold rkt-${item.id_rekat}">
                    <td>${item.id_rekat}</td>
                    <td>${item.sub_judul}</td>
                    <td></td><td style="text-align: right;" class="rktBiaya ${item.kd_rk}-${item.unit_kerja}">${ rupiah(item.TOTAL) }</td>
                    <td></td>
                    <td>${ rupiah( item.TOTAL_AMPRAH ?? '0')}</td>
                    <td>${ rupiah( item.TOTAL_REALISASI ?? '0') }</td>
                    <td>${ rupiah( item.TOTAL - realisasi ) }</td>
                </tr>`)
            })
            coaGroup.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeSs   = item.kode_ss
                const kodeKeg  = item.kd_rk.substring(3, 12)
                const kodeUnit = item.unit_kerja
                const idRekat  = item.id_rekat
                const mak = `${item.kd_sumberdana}.${kodeSs}.${kodeKeg}.${kodeUnit}.${idRekat}.${item.id_jenis_belanja}`
                $(`.rkt-${idRekat}`).after(`<tr class="coa coa-${idRekat}-${item.id_jenis_belanja}">
                    <td>${mak}</td>
                    <td>${item.jenis_belanja}</td>
                    <td></td>
                    <td style="text-align: right;" class="coaBiaya ${idRekat}">${ rupiah(item.TOTAL) }</td>
                    <td></td>
                    <td>${ rupiah( item.TOTAL_AMPRAH ?? '0')}</td>
                    <td>${ rupiah( item.TOTAL_REALISASI ?? '0') }</td>
                    <td>${ rupiah( item.TOTAL - realisasi ) }</td>
                </tr>`)
            })
            itemGroup.forEach( item => {
                const realisasi = Number(item.jumlah_amprahan) + Number(item.jumlah_realisasi)
                $(`.coa-${item.id_rekat}-${item.id_jenis_belanja}`).after(`<tr class="item-${item.id_rekat}-${item.id_jenis_belanja} row-item-coa jenis-${item.jenis}-${item.id}">
                    <td style="width: 150px" class="text-end" nowrap="nowrap">
                            ${item.nama_ppk} <br>
                            ${item.nama_bpp}
                    </td>
                    <td>${item.kebutuhan_kegiatan}</td>
                     <td>
                        ( ${item.kuantitas ?? '-'} ${item.satuan_kuantitas ?? '-'} X
                        ${item.durasi ?? 1} ${item.satuan_durasi ?? 'Pkt'} X
                        ${item.kegiatan ?? 1} ${item.satuan_keg ?? 'Keg'} X ${ rupiah( item.harga_satuan ) } )
                    </td>
                    <td style="text-align: right;" class="itemCoaBiaya ${item.id_rekat}-${item.id_jenis_belanja}">${ rupiah(item.jumlah_biaya)}</td>
                    <td>${item.rpd ?? "-"}</td>
                    <td>${ rupiah(item.jumlah_amprahan ?? 0) }</td>
                    <td>${ rupiah( item.jumlah_realisasi) }</td>
                    <td>${ rupiah( item.jumlah_biaya ?? 0  - realisasi ?? 0)}</td>
                </tr>`)
            })
            sumKeg.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeKeg = `${item.kd_rk.replace(/\./g, '\\.')}`
                $(`.kodeKegBiaya-${kodeKeg}`).text( rupiah(item.TOTAL) )
                $(`.kodeKegBiaya-${kodeKeg}`).parent().find(".kodeKegAmprahan").text( rupiah(item.TOTAL_AMPRAH) )
                $(`.kodeKegBiaya-${kodeKeg}`).parent().find(".kodeKegRealisasi").text( rupiah(item.TOTAL_REALISASI) )
                $(`.kodeKegBiaya-${kodeKeg}`).parent().find(".kodeKegSisa").text( rupiah(item.TOTAL - realisasi) )
            })
            sumIkv.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeIkv = `${item.kode_ikv.replace(/\./g, '\\.')}`
                $(`.kodeIkvBiaya-${kodeIkv}`).text( rupiah(item.TOTAL) )
                $(`.kodeIkvBiaya-${kodeIkv}`).parent().find(".kodeIkvAmprahan").text( rupiah(item.TOTAL_AMPRAH) )
                $(`.kodeIkvBiaya-${kodeIkv}`).parent().find(".kodeIkvRealisasi").text( rupiah(item.TOTAL_REALISASI) )
                $(`.kodeIkvBiaya-${kodeIkv}`).parent().find(".kodeIkvSisa").text( rupiah(item.TOTAL - realisasi) )
            })
            sumRo.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeIkk = `${item.kode_ikk.replace(/\./g, '\\.')}`
                $(`.kodeRoBiaya-${kodeIkk}`).text( rupiah(item.TOTAL) )
                $(`.kodeRoBiaya-${kodeIkk}`).parent().find(".kodeRoAmprahan").text( rupiah(item.TOTAL_AMPRAH) )
                $(`.kodeRoBiaya-${kodeIkk}`).parent().find(".kodeRoRealisasi").text( rupiah(item.TOTAL_REALISASI) )
                $(`.kodeRoBiaya-${kodeIkk}`).parent().find(".kodeRoSisa").text( rupiah(item.TOTAL - realisasi) )
            })
            sumKro.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeKro = `${item.kode_ss.replace(/\./g, '\\.')}`
                $(`.kodeKroBiaya-${kodeKro}`).text( rupiah(item.TOTAL) )
                $(`.kodeKroBiaya-${kodeKro}`).parent().find(".kodeKroAmprahan").text( rupiah(item.TOTAL_AMPRAH) )
                $(`.kodeKroBiaya-${kodeKro}`).parent().find(".kodeKroRealisasi").text( rupiah(item.TOTAL_REALISASI) )
                $(`.kodeKroBiaya-${kodeKro}`).parent().find(".kodeKroSisa").text( rupiah(item.TOTAL - realisasi) )
            })
            sumSd.forEach( item => {
                const realisasi = Number(item.TOTAL_AMPRAH) + Number(item.TOTAL_REALISASI)
                const kodeSd = item.kd_sumberdana
                $(`.kodeSumberdanaBiaya-${kodeSd}`).text( rupiah(item.TOTAL) )
                $(`.kodeSumberdanaBiaya-${kodeSd}`).parent().find(".kodeSumberdanaAmprahan").text( rupiah(item.TOTAL_AMPRAH) )
                $(`.kodeSumberdanaBiaya-${kodeSd}`).parent().find(".kodeSumberdanaRealisasi").text( rupiah(item.TOTAL_REALISASI) )
                $(`.kodeSumberdanaBiaya-${kodeSd}`).parent().find(".kodeSumberdanaSisa").text( rupiah(item.TOTAL - realisasi) )
                $("td.total").text( rupiah(item.TOTAL) )
            })
            hideLoading(".loading-msg")

            // remove empty rows
            clearEmptyPrice(".coaBiaya")
            clearEmptyPrice(".rktBiaya")
            clearEmptyPrice(".unitBiaya")
            clearEmptyPrice(".kodeKegBiaya")
            clearEmptyPrice(".kodeIkvBiaya")
            clearEmptyPrice(".kodeRoBiaya")
            clearEmptyPrice(".kodeKroBiaya")

            // 🔵 Hide loading message
            hideLoading(".loading-msg")
        }

        /*
        * @param {String} $el
        * 🔵 Sum all data up to every row
        */
        const sumBiayaCoa = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode = $(el).attr("class").split(" ")[1]
                const biaya = rupiahToNumber( $(el).text() )
                // Find the object with matching kode
                let foundObject = totalBiayaCoa.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaCoa.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaCoa.forEach( item => {
                $(`.coa-${item.kode}`).find(".coaBiaya").text( rupiah(item.price) )
            })
        }
        const sumBiayaRekat = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode = $(el).attr("class").split(" ")[1]
                const biaya = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaRekat.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaRekat.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaRekat.forEach( item => {
                $(`.rkt-${item.kode}`).find(".rktBiaya").text( rupiah(item.price) )
            })
        }
        const sumBiayaUnit = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode      = $(el).attr("class").split(" ")[1]
                const biaya     = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaUnit.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaUnit.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaUnit.forEach( unit => {
                $(`.unit-${unit.kode.replace(/\./g, '\\.')}`).find(".unitBiaya").text( rupiah(unit.price))
            })
        }
        const sumBiayaKeg = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode      = $(el).attr("class").split(" ")[1]
                const biaya     = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaKeg.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaKeg.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaKeg.forEach( keg => {
                $(`.keg-${keg.kode.replace(/\./g, '\\.')}`).find(".kodeKegBiaya").text( rupiah(keg.price))
            })
        }
        const sumBiayaIkv = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode      = $(el).attr("class").split(" ")[1]
                const biaya     = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaIkv.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaIkv.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaIkv.forEach( ikv => {
                $(`.ikv-${ikv.kode.replace(/\./g, '\\.')}`).find(".kodeIkvBiaya").text( rupiah(ikv.price))
            })
        }
        const sumBiayaRo = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode      = $(el).attr("class").split(" ")[1]
                const biaya     = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaRo.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaRo.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaRo.forEach( ro => {
                $(`.iku-${ro.kode.replace(/\./g, '\\.')}`).find(".kodeRoBiaya").text( rupiah(ro.price))
            })
        }
        const sumBiayaKro = ( $el ) => {
            $($el).each( ( i, el ) => {
                const kode      = $(el).attr("class").split(" ")[1]
                const biaya     = rupiahToNumber( $(el).text() )
                let foundObject = totalBiayaKro.find(item => item.kode === kode)
                if (foundObject) {
                    foundObject.price += biaya
                } else {
                    totalBiayaKro.push({ kode: kode, price: biaya })
                }
            })
            totalBiayaKro.forEach( kro => {
                $(`.ss-${kro.kode.replace(/\./g, '\\.')}`).find(".kodeKroBiaya").text( rupiah(kro.price))
            })
            const total = totalBiayaKro.reduce((acc, item) => acc + item.price, 0)
            $(".total").text(rupiah(total))
            $(".total_ptnbh").text(rupiah(total))
        }
        const sumBiayaSd = ( $el ) => {
            const total = totalBiayaKro.reduce((acc, item) => acc + item.price, 0)
            $(".total").text(rupiah(total))
            $(".total_ptnbh").text(rupiah(total))
            $(".kodeSumberdanaBiaya").text(rupiah(total))
        }

        /**
        * @param {String} $el, $elMsg, msg
        * 🔵 Toggle loading message
        */
        const showLoading = ( $el, $elMsg, msg ) => {
            $($el).show("fast")
            $($elMsg).text(msg)
        }
        const hideLoading = ( $el ) => {
            $($el).hide("fast")
        }

        /**
         * @param {array} data
         * clear given array
        */
        const clearArray = ( array ) => {
            array.length = 0
        }

        /**
         * @param {string} className
         * clear if price is empty
        */
        const clearEmptyPrice = ( className ) => {
            $(className).each( ( i, el ) => {
                if ( rupiahToNumber($(el).text()) == rupiahToNumber("Rp 0") || $(el).text() == "" ) {
                    $(el).parent().remove()
                }
            })
        }

    })
    </script>
