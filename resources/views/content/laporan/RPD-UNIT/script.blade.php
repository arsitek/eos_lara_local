<script>
    const parseNumericValue = (text) => {
        if (!text) return 0
        const numbers = text.toString().replace(/[^\d-]/g, "")
        return Number(numbers) || 0
    }

    const animateCurrencyCells = (tableBody) => {
        const monthCells = tableBody.find("td").filter(function() {
            const cls = ($(this).attr("class") || "").trim()
            return /^\d{2}$/.test(cls) && $(this).data("currency-value") !== undefined
        })

        if (!monthCells.length) return

        monthCells.each(function() {
            const $cell = $(this)
            const storedValue = $cell.data("currency-value")
            const finalValue = typeof storedValue === "number" ? storedValue : parseNumericValue($cell.text())
            if (isNaN(finalValue)) {
                $cell.text(rupiah(0))
                return
            }

            const tween = { value: 0 }
            gsap.to(tween, {
                value: finalValue,
                duration: 1,
                ease: "power3.out",
                onUpdate: () => {
                    $cell.text(rupiah(Math.round(tween.value)))
                }
            })
            gsap.fromTo($cell, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.35 })
        })
    }

    $(document).ready(function() {
        $(".select2").select2()
        $(".btn-tanpa-rpd").on("click", function(){
            $("select[name=sumberdana]").select2().next().hide()
            $(".btn-filter-sumberdana").hide()
            $(".tanpa-rpd").show()
            $(".rpd").hide()
            $(".btn-tanpa-rpd").hide()
            $(".btn-dengan-rpd").show()
            $(".dengan-rpd").hide()
            gsap.fromTo(".tanpa-rpd", { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4 })
            if (!$.fn.DataTable.isDataTable('#tabel-rpd-null')) {
                const tableRpdNull = $("#tabel-rpd-null").DataTable({
                    "rowsGroup": [1, 2, 3]
                })
            }
        })
        $(".btn-dengan-rpd").on("click", function(){
            $(".tanpa-rpd").hide()
            $(".dengan-rpd").show()
            $(".btn-tanpa-rpd").show()
            $(".btn-dengan-rpd").hide()
            $("select[name=sumberdana]").select2().next().show()
            $(".btn-filter-sumberdana").show()
            gsap.fromTo(".dengan-rpd", { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4 })
        })
        $(".btn-filter-sumberdana").on("click", function(){
            $(".loading-div").show()
            let sumberdana = $("select[name=sumberdana]").val()

            if ( sumberdana == null || sumberdana == "" ) {
                $(".loading-div").hide()
                return tata.error("⛔ Error", "Sumber Dana tidak ditemukan", { duration: 5000, animate: "slide" })
            }
            const btnHtml = $(this).html()
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled", true)
            $.ajax({
                type: "GET",
                url: "{{ route('rpdunit.get') }}",
                data: { sd: sumberdana },
                success: ( res ) => {
                    const month = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"]
                    const bodyTbl = $(".dengan-rpd .body-tbl")
                    bodyTbl.find('td').removeData('currency-value')
                    month.forEach( item => {
                        bodyTbl.find(`.${item}`).text("")
                    })
                    if ( res.length == 0 ) {
                        $(".loading-div").hide()
                        $(this).html(btnHtml).prop("disabled", false)
                        return tata.error("⛔ Error", "Data tidak ditemukan", { duration: 5000, animate: "slide" })
                    }
                    res.forEach( item => {
                        let unitKerjaElement = bodyTbl.find(`.${item.unit_kerja}`)
                        // Cek semua kolom samping unit kerja
                        unitKerjaElement.nextAll().each(function(index, element) {
                            if ($(element).attr("class") == item.rpd) {
                                const amount = Number(item.jumlah_biaya || 0)
                                $(element).data('currency-value', amount)
                                $(element).text(rupiah(0))
                                return false; // Kalau sudah ketemu, stop loop
                            }
                        })
                    })
                    animateCurrencyCells(bodyTbl)
                    $(".loading-div").hide()
                    $(".idunit").nextAll().filter(':empty').text(rupiah(0))
                    $(this).html(btnHtml).prop("disabled", false)
                },
                error: ( err ) => {
                    $(this).html(btnHtml).prop("disabled", false)
                    $(".loading-div").hide()
                    return tata.error("⛔ Error", "Gagal memuat data", { duration: 5000, animate: "slide" })
                },
            })
        })
    })
</script>
