<script>
    $( document ).ready( function(){
        $("select.unit_kerja").select2()
        const tabel      = $("table#tabelPembiayaan")
        const penerimaan = {
            total10: {},
            total8: {},
            total6: {},
            total4: {},
            total2: {},
        }

        $("button.cari").on("click", function(){
            const unitkerja = $("select.unit_kerja").val()
            const route = "{{ route('laporan.pendapatan.getData') }}"
            generateRka( unitkerja, route )
        })

        const generateRka = ( unitkerja, route ) => {
            $.ajax({
                type: "GET",
                url: `${ route }?unitkerja=${unitkerja}`,
                beforeSend: () => {
                    showLoader()
                    setLoaderText("Sedang memuat data...")
                }, success: ( res ) => {
                    // console.log( res )
                    const { data } = res
                    tabel.find("tbody").empty()

                    // if ( data?.sd2.length == 0 ) {
                    //     tabel.find("tbody").append(`<tr><td colspan="13" class="text-center">Data tidak ditemukan</td></tr>`)
                    //     return
                    // }
                    generateMasterData( data )
                    // create object for penerimaan data
                    data.sd10.forEach( item => {
                        $("tr.sd10[kode='" + item.kd_parent_sd10 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })
                    data.sd8.forEach( item => {
                        $("tr.sd8[kode='" + item.kd_parent_sd8 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })
                    data.sd8.forEach( item => {
                        $("tr.sd8[kode='" + item.kd_parent_sd8 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })
                    data.sd6.forEach( item => {
                        $("tr.sd6[kode='" + item.kd_parent_sd6 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })
                    data.sd4.forEach( item => {
                        $("tr.sd4[kode='" + item.kd_parent_sd4 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })
                    data.sd2.forEach( item => {
                        $("tr.sd2[kode='" + item.kd_parent_sd2 + "']").find(`.realisasi[tahun='${item.tahun}']`).text( rupiah( item.total ) )
                    })

                    const anggaran2024 = {}
                    data.anggaran2024.forEach( item => {
                        const parent2 = item.kd_2025.length === 10 ? item.sd10kd_parent2 : ( item.kd_2025.length === 4 ? item.sd4kd_parent2 : ( item.kd_2025.length === 6 ? item.sd6kd_parent2 : item.sd8kd_parent2 ) )
                        const parent4 = item.kd_2025.length === 10 ? item.sd10kd_parent4 : ( item.kd_2025.length === 6 ? item.sd6kd_parent4 : ( item.kd_2025.length === 8 ? item.sd8kd_parent4 : item.kd_2025 ) )
                        const parent6 = item.kd_2025.length === 10 ? item.sd10kd_parent6 : ( item.kd_2025.length === 8 ? item.sd8kd_parent6 : ( item.kd_2025.length === 6 ? item.sd6kd_parent6 : '-' ) )
                        const parent8 = item.kd_2025.length === 10 ? item.sd10kd_parent8 : ( item.kd_2025.length === 8 ? item.sd8kd_parent8 : '-' )
                        const parent10 = item.sd10kd_parent10 ?? '-'

                        // Buat objek kosong.
                        if ( !anggaran2024["sd2"] ) anggaran2024["sd2"] = { total: 0 }
                        if ( !anggaran2024["sd2"][parent2] ) anggaran2024["sd2"][parent2] = { total: 0, kd_sumberdana: parent2 }
                        if ( !anggaran2024["sd2"][parent2]["sd4"] ) anggaran2024["sd2"][parent2]["sd4"] = {}
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4] ) anggaran2024["sd2"][parent2]["sd4"][parent4] = { total: 0, kd_sumberdana: parent4 }
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"] = {}
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6] = { total: 0, kd_sumberdana: parent6 }
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"] = {}
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]  = { total: 0, kd_sumberdana: parent8 }
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"]  = {}
                        if ( !anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"][parent10] ) anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"][parent10]  = { total: 0, kd_sumberdana: parent10 }

                        // Kelompokkan anggaran berdasarkan parent sumberdana nya.
                        anggaran2024["sd2"][parent2]["total"] += Number( item.pagu_alokasi )
                        anggaran2024["sd2"][parent2]["sd4"][parent4]["total"] += Number( item.pagu_alokasi )
                        anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["total"] += Number( item.pagu_alokasi )
                        anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["total"] += Number( item.pagu_alokasi )
                        anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"][parent10]["total"] += Number( item.pagu_alokasi )
                        anggaran2024["sd2"]["total"] += Number( item.pagu_alokasi )

                        // Masukkan nilai nya ke tiap - tiap baris ( khusus item paling kecil ).
                        $("tr.sd2[kode='" + item.kd_2025 + "']").find(`.target[tahun='2024']`).text( rupiah( item.pagu_alokasi ) )
                        $("tr.sd4[kode='" + item.kd_2025 + "']").find(`.target[tahun='2024']`).text( rupiah( item.pagu_alokasi ) )
                        $("tr.sd6[kode='" + item.kd_2025 + "']").find(`.target[tahun='2024']`).text( rupiah( item.pagu_alokasi ) )
                        $("tr.sd8[kode='" + item.kd_2025 + "']").find(`.target[tahun='2024']`).text( rupiah( item.pagu_alokasi ) )
                        $("tr.sd10[kode='" + item.kd_2025 + "']").find(`.target[tahun='2024']`).text( rupiah( item.pagu_alokasi ) )

                        // Masukkan nilai nya ke tiap - tiap baris ( khusus parent ).
                        // Harus dilakukan berulang dikarenakan tahun 2024, sumberdananya gelondongan / hanya sampai parent 4.
                        $("tr.sd2[kode='" + parent2 + "']").find(`.target[tahun='2024']`).text( rupiah( anggaran2024["sd2"][parent2]["total"] ) )
                        $("tr.sd4[kode='" + parent4 + "']").find(`.target[tahun='2024']`).text( rupiah( anggaran2024["sd2"][parent2]["sd4"][parent4]["total"] ) )
                        $("tr.sd6[kode='" + parent6 + "']").find(`.target[tahun='2024']`).text( rupiah( anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["total"] ) )
                        $("tr.sd8[kode='" + parent8 + "']").find(`.target[tahun='2024']`).text( rupiah( anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["total"] ) )
                        // $("tr.sd10[kode='" + parent10 + "']").find(`.target[tahun='2024']`).text( rupiah( anggaran2024["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["sd10"][parent10]["total"] ) )

                    })
                    const anggaran2025 = {}
                    data.anggaran2025.forEach( item => {
                        const parent2 = item.kd_parent_sd2
                        const parent4 = item.kd_parent_sd4
                        const parent6 = item.kd_parent_sd6
                        const parent8 = item.kd_parent_sd8

                        // Buat objek kosong.
                        if ( !anggaran2025["sd2"] ) anggaran2025["sd2"] = { total: 0}
                        if ( !anggaran2025["sd2"][parent2] ) anggaran2025["sd2"][parent2] = { total: 0, kd_sumberdana: parent2 }
                        if ( !anggaran2025["sd2"][parent2]["sd4"] ) anggaran2025["sd2"][parent2]["sd4"] = {}
                        if ( !anggaran2025["sd2"][parent2]["sd4"][parent4] ) anggaran2025["sd2"][parent2]["sd4"][parent4] = { total: 0, kd_sumberdana: parent4 }
                        if ( !anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"] ) anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"] = {}
                        if ( !anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6] ) anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6] = { total: 0, kd_sumberdana: parent6 }
                        if ( !anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"] ) anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"] = {}
                        if ( !anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8] ) anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]  = { total: 0, kd_sumberdana: parent8 }

                        // Kelompokkan anggaran berdasarkan parent sumberdana nya
                        anggaran2025["sd2"][parent2]["total"] += Number( item.pagu_alokasi )
                        anggaran2025["sd2"][parent2]["sd4"][parent4]["total"] += Number( item.pagu_alokasi )
                        anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["total"] += Number( item.pagu_alokasi )
                        anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["total"] += Number( item.pagu_alokasi )
                        anggaran2025["sd2"]["total"] += Number( item.pagu_alokasi )

                        // Masukkan nilai nya ke tiap - tiap baris.
                        $("tr.sd2[kode='" + parent2 + "']").find(`.target[tahun='2025']`).text( rupiah( anggaran2025["sd2"][parent2]["total"] ) )
                        $("tr.sd4[kode='" + parent4 + "']").find(`.target[tahun='2025']`).text( rupiah( anggaran2025["sd2"][parent2]["sd4"][parent4]["total"] ) )
                        $("tr.sd6[kode='" + parent6 + "']").find(`.target[tahun='2025']`).text( rupiah( anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["total"] ) )
                        $("tr.sd8[kode='" + parent8 + "']").find(`.target[tahun='2025']`).text( rupiah( anggaran2025["sd2"][parent2]["sd4"][parent4]["sd6"][parent6]["sd8"][parent8]["total"] ) )
                    })

                    // generate persentase
                    const persentase = {}
                    $("td.persentase").each((index, item) => {
                        const row      = $(item).closest("tr")
                        const getValue = (year, type) => rupiahToNumber(row.find(`.${type}[tahun='${year}']`).text())

                        // hitung persentase dari 2 angka dengan tahun yang sama
                        const hitungPersentase = (target, real) => {
                            if ( target === null || real === null || isNaN(target) || isNaN(real) ) return null // jika salah satu target atau realisasi tidak ada, maka return null
                            // if (target === null && real === null ) return null // jika salah satu target atau realisasi tidak ada, maka return null
                            const selisih  = Math.abs( target - real )
                            const ratarata = ( target + real ) / 2
                            // return ( selisih / ratarata ) * 100
                            return Math.min( (selisih / ratarata) * 100, 100)
                        }


                        // Calculate percentages for both years
                        const targets    = [ 2024, 2025, 2026 ].map(year => getValue(year, "target"))
                        const reals      = [ 2024, 2025, 2026 ].map(year => getValue(year, "realisasi"))
                        const percentages = targets.map((target, i) => hitungPersentase(target, reals[i]));

                        // Update DOM for both years
                        [2024, 2025 ].forEach((year, i) => {
                            const percentage = percentages[i]
                            const selisih    = reals[i] - targets[i]
                            const targetText = percentage === null ? "-" : `${percentage.toFixed(1)}%`
                            row.find(`.persentase[tahun='${year}']`).text(targetText)
                            if ( targets[i] !== null && reals[i] !== null ) {
                                row.find(`.selisih[tahun='${year}']`).text( rupiah(selisih) )
                                if ( selisih < 0 ) {
                                    row.find(`.persentase[tahun='${year}']`).addClass("text-danger")
                                    row.find(`.selisih[tahun='${year}']`).addClass("text-danger")
                                }
                            }
                        })
                    })
                }, error: ( err ) => {
                    // console.log( err )
                    return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data")
                    removeLoader()
                }, complete: () => {
                    removeLoader()
                }
            })
        }
        const generateMasterData = ( data ) => {
            const masterHtml = []
            data.dataMaster.forEach( item => {
                masterHtml.push(`<tr class="sd2" kode="${item.kd_sumberdana}">
                    <td style="width: 220px" class="ps-2">${item.sumberdana}</td>
                    <td style="width: 100px; " class="text-right target" tahun="2024">-</td>
                    <td style="width: 100px; " class="text-right target" tahun="2025">-</td>
                    <td style="width: 100px; " class="text-right realisasi" tahun="2024">-</td>
                    <td style="width: 100px; " class="text-right realisasi" tahun="2025">-</td>
                    <td style="width: 50px;" class="text-right persentase" tahun="2024">-</td>
                    <td style="width: 50px;" class="text-right persentase" tahun="2025">-</td>
                    <td style="width: 50px;" class="text-right selisih" tahun="2024">-</td>
                    <td style="width: 50px;" class="text-right selisih" tahun="2025">-</td>
                </tr>`)
                item.child4.forEach( item4 => {
                    masterHtml.push(`<tr class="sd4" kode="${item4.kd_sumberdana}">
                        <td class="ps-4">${item4.sumberdana}</td>
                        <td class="text-right target" tahun="2024">-</td>
                        <td class="text-right target" tahun="2025">-</td>
                        <td class="text-right realisasi" tahun="2024">-</td>
                        <td class="text-right realisasi" tahun="2025">-</td>
                        <td class="text-right persentase" tahun="2024">-</td>
                        <td class="text-right persentase" tahun="2025">-</td>
                        <td style="width: 50px;" class="text-right selisih" tahun="2024">-</td>
                        <td style="width: 50px;" class="text-right selisih" tahun="2025">-</td>
                    </tr>`)
                    item4.child6.forEach( item6 => {
                        masterHtml.push(`<tr class="sd6" kode="${item6.kd_sumberdana}">
                            <td class="ps-5" kode="${item6.kd_sumberdana}">${item6.sumberdana}</td>
                            <td class="text-right target" tahun="2024">-</td>
                            <td class="text-right target" tahun="2025">-</td>
                            <td class="text-right realisasi" tahun="2024">-</td>
                            <td class="text-right realisasi" tahun="2025">-</td>
                            <td class="text-right persentase" tahun="2024">-</td>
                            <td class="text-right persentase" tahun="2025">-</td>
                            <td style="width: 50px;" class="text-right selisih" tahun="2024">-</td>
                            <td style="width: 50px;" class="text-right selisih" tahun="2025">-</td>
                        </tr>`)
                        item6.child8.forEach( item8 => {
                            masterHtml.push(`<tr class="sd8" kode="${item8.kd_sumberdana}">
                                <td class="ps-6" kode="${item8.kd_sumberdana}">${item8.sumberdana}</td>
                                <td class="text-right target" tahun="2024">-</td>
                                <td class="text-right target" tahun="2025">-</td>
                                <td class="text-right realisasi" tahun="2024">-</td>
                                <td class="text-right realisasi" tahun="2025">-</td>
                                <td class="text-right persentase" tahun="2024">-</td>
                                <td class="text-right persentase" tahun="2025">-</td>
                                <td style="width: 50px;" class="text-right selisih" tahun="2024">-</td>
                                <td style="width: 50px;" class="text-right selisih" tahun="2025">-</td>
                            </tr>`)
                            if ( item8.kd_sumberdana == "41010101" || item8.kd_sumberdana == "41010102" || item8.kd_sumberdana == "41010201" ) {
                            item8.child10.forEach( item10 => {
                                masterHtml.push(`<tr class="sd10" kode="${item10.kd_sumberdana}">
                                    <td class="ps-7" kode="${item10.kd_sumberdana}">${item10.sumberdana}</td>
                                    <td class="text-right target" tahun="2024">-</td>
                                    <td class="text-right target" tahun="2025">-</td>
                                    <td class="text-right realisasi" tahun="2024">-</td>
                                    <td class="text-right realisasi" tahun="2025">-</td>
                                    <td class="text-right persentase" tahun="2024">-</td>
                                    <td class="text-right persentase" tahun="2025">-</td>
                                    <td style="width: 50px;" class="text-right selisih" tahun="2024">-</td>
                                    <td style="width: 50px;" class="text-right selisih" tahun="2025">-</td>
                                </tr>`)
                            })
                        }
                        })
                    })
                })
            })
            tabel.find("tbody").append( masterHtml.join("") )
        }
        // generateRka( "semua", "{{ route('laporan.pendapatan.getData') }}" )
    })
</script>
