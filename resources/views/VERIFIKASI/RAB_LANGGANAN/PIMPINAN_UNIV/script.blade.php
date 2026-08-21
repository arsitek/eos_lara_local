<script>
    $(document).ready(function() {
        // 🚀 Initialize variable
        const url          = new URL( window.location.href )
        const idunit       = $("select.unit_kerja").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const kodeSd       = $("select.sumberdana").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const userAgent    = navigator.userAgent
        const screenSize   = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform     = navigator.platform
        const lang         = navigator.language
        const select2      = $(".s").select2()
        const datatable    = $('.tabel-verlangganan').DataTable({
            "ordering": false,
            "autoWidth": false,
            "rowsGroup": [1, 2, 3, 4],
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
        // ❓ check button click count
        let clickCount = 0;

        // ✅ onclick button serach by unitkerja
       $( document ).on("click", ".btn-filter-unitkerja", function(){
            if ( idunit.val() === "") {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja atau tahun.")
            }
            if ( kodeSd.val() === "" || kodeSd.val() === "-" ) {
                return tata.warn("Perhatian", "Anda belum memilih sumberdana")
            }
            window.open(`/verifikasi/langganan/${idunit.val()}?kd_sumberdana=${kodeSd.val()}`, "_self")
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
                url: "{{ route('vRabLangganan.storeVerif') }}",
                data: { _token: "{{ csrf_token() }}", id, isChecked, tanggapan, jenis, status, userAgent, screenSize, platform, lang, },
                success: ( res ) => {
                    // console.log( res )
                    const { message } = res
                    return tata.success("✅ Sukses", message)
                },
                error: ( err ) => {
                    const message = res.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        })

        $( document ).on( "input", ".tanggapan", debounce( function(e) {
            const $target       = $(e.target)
            const $tr           = $target.closest("tr")
            const sanitizedHTML = DOMPurify.sanitize( $target.html() )
            const text          = sanitizedHTML.replace(/<br\s*\/?>/g, '\n')
            const id            = $tr.find(".id").attr("key")

            $.ajax({
                type: "POST",
                url: "{{ route('vRabLangganan.storeTanggapan') }}",
                data: { _token: "{{ csrf_token() }}", id, text },
                success: ( res ) => {
                    return
                },
                error: ( err ) => {
                    const message = res.responseJSON.message || "Terjadi kesalahan saat menyimpan data tanggapan"
                    return tata.error("⛔ Error", message)
                }
            })
        }, 500))
    })
</script>

