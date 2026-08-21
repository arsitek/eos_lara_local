<script>
$(document).ready( function(){
    window.rka = window.rka || {}
    window.rka.sapras = {
        constants: {
            ROUTES: {
                GET_SUMBER_DANA_PPK: "{{ route('laporan.datapaket.getSumberDanaPPK') }}",
            },
            TIMEOUT: 5000,
        },
        elements : {
            btnSubmit: $("button#btn-submit-filter-rka-ppk"),
            btnPdf: $("button#exportPdfAnchor"),
            statusFilter: $("select.filter-data"),
            sumberdanaFilter: $("select.sumberdana"),
            ppkFilter: $("select.ppk"),
        },
        methods: {
            init: () => {
                const { methods, elements } = window.rka.sapras
                const selectedPpk = elements.ppkFilter.val()
                methods.bindEvents()

                if ( selectedPpk ) {
                    methods.getSumberDanaPPK(selectedPpk)
                }
            },
            bindEvents: () => {
                const { elements, methods } = window.rka.sapras
                $(elements.ppkFilter).on("change", function() {
                    const ppk = $(this).val()
                    methods.getSumberDanaPPK(ppk)
                })
            },
            getSumberDanaPPK: (ppk) => {
                const { constants, elements } = window.rka.sapras
                const sumberdanaFilter = $(elements.sumberdanaFilter)

                sumberdanaFilter.html('<option value="">Pilih Sumberdana</option>')

                if ( !ppk ) {
                    sumberdanaFilter.trigger("change")
                    return
                }

                $.ajax({
                    url: constants.ROUTES.GET_SUMBER_DANA_PPK,
                    type: "GET",
                    data: { ppk },
                    timeout: constants.TIMEOUT,
                    success: ( res ) => {
                        const sumberdanaData = Array.isArray(res?.data) ? res.data : []

                        if ( sumberdanaData.length === 0 ) {
                            return tata.error("Info", "Sumberdana untuk PPK terpilih tidak ditemukan", { duration: 4000, animate: "slide" })
                        }

                        sumberdanaData.forEach( (item) => {
                            const value = item.kd_sumberdana || ''
                            const text = item.sumberdana || '-'
                            sumberdanaFilter.append(`<option value="${value}">${text}</option>`)
                        })

                        sumberdanaFilter.trigger("change")
                    },
                    error: ( err ) => {
                        const message = err?.responseJSON?.message || "Terjadi kesalahan saat memuat sumberdana"
                        tata.error("Error", message, { duration: 5000, animate: "slide" })
                    }
                })
            },
            handleSubmit: (ppk, kodeSd, status) => {
                if ( !ppk || !kodeSd || !status ) {
                    return tata.error("Error", "Harap memilih semua filter terlebih dahulu", { duration: 5000, animate: "slide" })
                }
                $('.body-tbl-unit').children().remove()
                generateRKA( null, kodeSd, null, status, `/laporan/rktunit/get/null/${kodeSd}`, '#loader-div', '.body-tbl-unit', 'Memuat data RKA...Mohon menunggu', false, false, [], ppk )
            }
        },
    }
    const elements = window.rka.sapras.elements
    window.rka.sapras.methods.init()
    $(elements.btnSubmit).on("click", function() {
        const { methods } = window.rka.sapras
        const ppk    = elements.ppkFilter.val()
        const kodeSd = elements.sumberdanaFilter.val()
        const status = elements.statusFilter.val()
        methods.handleSubmit(ppk, kodeSd, status )
    })
    $(elements.btnPdf).on("click", function(e) {
        e.preventDefault()
        const url = new URL(window.location.href)
        const ppk    = elements.ppkFilter.val()
        const kodeSd = elements.sumberdanaFilter.val()
        const status = elements.statusFilter.val()
        if ( !ppk || !kodeSd || !status ) {
            return tata.error("Error", "Harap memilih semua filter terlebih dahulu sebelum mengunduh PDF", { duration: 5000, animate: "slide" })
        }
        url.pathname = "/laporan/datapaket/rka/sapras/pdf"
        url.searchParams.set("ppk", ppk)
        url.searchParams.set("sumberdana", kodeSd)
        url.searchParams.set("status", status)
        window.open(url.toString(), "_blank")
    })
})
</script>
