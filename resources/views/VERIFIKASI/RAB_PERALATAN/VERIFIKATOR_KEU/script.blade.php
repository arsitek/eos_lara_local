<script>
    $(document).ready(function() {
        // 🚀 Initialize variable
        const url        = new URL( window.location.href )
        const idunit     = $("select.unit_kerja").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const kodeSd      = $("select.sumberdana").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const userAgent  = navigator.userAgent
        const screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform   = navigator.platform
        const lang       = navigator.language
        const select2    = $(".s").select2()
        const datatable  = $('.tabel-verper').DataTable({
            "ordering": false,
            "rowsGroup": [ 1 ],
            "autoWidth": false,
            "drawCallback": function(dt) {
                const select2 = $(".s").select2()
            }
        });
        let anggaran_teralokasi = $(".alokasi-anggaran").text()
        let anggaran_terpetakan = $(".anggaran-terpetakan").text()
        if ( '-' !== anggaran_teralokasi ) {
            $(".alokasi-anggaran").text( rupiah(anggaran_teralokasi) )
        }
        if ( '-' !== anggaran_terpetakan ) {
            $(".anggaran-terpetakan").text( rupiah(anggaran_terpetakan) )
        }

        // ✅ oncheck select coa
        $( document ).on("change", "select.jenis_belanja", function(){
            const id_coa = $(this).val()
            const $tr    = $(this).closest("tr")
            const id_rab = $tr.find(".id").attr("key")
            const coa    = $tr.find("select.jenis_belanja option:selected").text().split("] ")[1]
            const jenis  = "updateCoa"
            $.ajax({
                type: "POST",
                url: "{{ route('vRabPer.store') }}",
                data: { "_token": "{{ csrf_token() }}", id_rab, id_coa, coa, jenis },
                success: ( res ) => {
                    // console.log(res)
                    const { message } = res
                    return tata.success("✅ Sukses", message)
                },
                error: ( err ) => {
                    const message = err.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })

        })
        // ✅ onclick button serach by unitkerja
        $( document ).on("click", ".btn-filter-unitkerja", function(){
            if ( idunit.val() === "") {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja atau tahun.")
            }
            if ( kodeSd.val() === "" || kodeSd.val() === "-" ) {
                return tata.warn("Perhatian", "Anda belum memilih sumberdana")
            }
            window.open(`/verifikasi/peralatan/${idunit.val()}?kd_sumberdana=${kodeSd.val()}`, "_self")
        })

        // ✅ oncheck input verifikasi pimpinan unit
        $( document ).on("click", ".switchVerifikasi", (e) => {
            const $target   = $(e.target)
            const $tr       = $target.closest("tr")
            const isChecked = $target.is(":checked")
            const id        = $tr.find(".id").attr("key")
            const jenis     = $target.data("jenis")
            const tanggapan = $tr.find(".tanggapan").text()
            const status    = isChecked ? "Setuju" : "Tolak"

            $.ajax({
                type: "POST",
                url: "{{ route('vRabPer.storeVerif') }}",
                data: { _token: "{{ csrf_token() }}", id, isChecked, tanggapan, jenis, status, userAgent, screenSize, platform, lang, },
                success: ( res ) => {
                    // console.log( res )
                    const { message } = res
                    return tata.success("✅ Sukses", message)
                },
                error: ( err ) => {
                    const message = err.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        })
    })
</script>

