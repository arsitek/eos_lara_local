<script>
    $( document ).ready( function(){
        $("td.persentase").each((index, item) => {
            const row      = $(item).closest("tr")
            const getValue = (year, type) => rupiahToNumber(row.find(`.${type}[tahun='${year}']`).text())

            // hitung persentase dari 2 angka dengan tahun yang sama
            const hitungPersentase = (target, real) => {
                if ( target === null || real === null || isNaN(target) || isNaN(real) ) return null // jika salah satu target atau realisasi tidak ada, maka return null
                const selisih  = Math.abs( target - real )
                const ratarata = ( target + real ) / 2
                return Math.min( ( selisih / ratarata ) * 100, 100 )
            }
            // Calculate percentages for both years
            const targets    = [ 2024, 2025 ].map(year => getValue(year, "target"))
            const reals      = [ 2024, 2025 ].map(year => getValue(year, "realisasi"))
            const percentages = targets.map((target, i) => hitungPersentase(target, reals[i]));

            // Update DOM for both years
            [2024, 2025].forEach((year, i) => {
                const percentage = percentages[i]
                const selisih    = targets[i] - reals[i]

                const targetText = ( percentage === null || isNaN( percentage ) ) ? "-" : `${percentage.toFixed(1)}%`
                // const targetText = ( percentage === null ) ? "100%" : `${percentage.toFixed(1)}%`
                if ( percentage !== null && selisih !== null ) {
                    row.find(`.persentase[tahun='${year}']`).text(targetText)
                    row.find(`.selisih[tahun='${year}']`).text( rupiah( selisih ))
                }
                if ( reals[i] > targets[i] ) {
                    row.find(`.persentase[tahun='${year}']`).css("color", "red")
                    row.find(`.selisih[tahun='${year}']`).css("color", "red")
                }
            })
        })
        $(".realisasi, .target").each((index, item) => {
            const row = $(item).closest("tr")
            const text = $(item).text()
            if ( !text.includes('Rp') ) {
                text != "-" ? $(item).text( rupiah( $(item).text() ) ) : '-'
            }
        })

        $(".selisih, .totalselisih").each((index, item) => {
            const row = $(item).closest("tr")
            const text = $(item).text()
            if ( text.includes('-Rp') )  {
                $(item).css("color", "red")
            }
        })
        $(".totaltarget").each((index, item) => {
            const year = $(item).attr("tahun")
            const total = $("td.target[tahun='"+year+"']").toArray().reduce((acc, item) => acc + rupiahToNumber($(item).text()), 0)
            $(item).text( rupiah(total) )
        })
        $(".totalrealisasi").each((index, item) => {
            const year = $(item).attr("tahun")
            const total = $("td.realisasi[tahun='"+year+"']").toArray().reduce((acc, item) => acc + rupiahToNumber($(item).text()), 0)
            $(item).text( rupiah(total) )
        })
        $(".totalpersentase").each((index, item) => {
            const year = $(item).attr("tahun")
            const totalTarget = rupiahToNumber($("td.totaltarget[tahun='"+year+"']").text())
            const totalRealisasi = rupiahToNumber($("td.totalrealisasi[tahun='"+year+"']").text())

            // hitung persentase dari 2 angka dengan tahun yang sama
            const hitungPersentase = (target, real) => {
                if ( target === null || real === null || isNaN(target) || isNaN(real) ) return null // jika salah satu target atau realisasi tidak ada, maka return null
                const selisih  = Math.abs( target - real )
                const ratarata = ( target + real ) / 2
                return Math.min( ( selisih / ratarata ) * 100, 100 )
            }
            if ( totalTarget !== 0 && totalRealisasi !== 0 ) {
                const total = hitungPersentase(totalTarget, totalRealisasi)
                $(item).text( ( total === null || isNaN(total) )? "-" : `${total.toFixed(1)}%` )
                if ( totalRealisasi > totalTarget ) {
                    $(item).css("color", "red")
                }
            } else {
                $(item).text("-")
            }
        })
        $(".totalselisih").each((index, item) => {
            const year = $(item).attr("tahun")
            // if item.text contain - then subtract the value
            const total = $("td.selisih[tahun='"+year+"']").toArray().reduce((acc, item) => {
                const text = $(item).text()
                return acc + ( text.includes('-') ? -rupiahToNumber(text) : rupiahToNumber(text) )
            }, 0)
            $(item).text( rupiah(total) )
            if ( $(item).text().includes('-Rp') )  {
                $(item).css("color", "red")
            }
        })
    })
</script>
