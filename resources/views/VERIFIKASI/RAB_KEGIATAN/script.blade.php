<script>
    $( document ).ready( function() {

        const url                 = new URL( window.location.href )
        const idunit              = $("select.unit_kerja")
        const kodeSd              = $("select.sumberdana")
        const modal               = $("#modal-setuju")
        const modalTanggapan      = $("#modal-tanggapan")
        const bodyTblTanggapan    = $(".bodyTblTanggapan")
        const btnTriggerTanggapan = $("button#btn_triggerTanggapan")
        const btnSetujuiTanggapan = $("button.btn_setujuiTanggapan")
        const userAgent           = navigator.userAgent
        const screenSize          = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform            = navigator.platform
        const lang                = navigator.language
        const modalTor            = $("div#modal-tor-viewer")

        $("#btn-setujui-semua").on("click", function(){
            Swal.fire({
                title: 'Apakah anda yakin untuk menyetujui semua kegiatan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'IYA',
                cancelButtonText: 'TIDAK'
            }).then((result) => {
                if (result.isConfirmed) {
                    const isSetujui     = 1
                    const url           = new URL(window.location.href)
                    const idunit        = url.pathname.split("/")[3]
                    const kd_sumberdana = url.searchParams.get('kd_sumberdana')

                    $.ajax({
                        type: 'POST',
                        url: " {{ route('vRabKeg.store') }} ",
                        data: { "_token": "{{ csrf_token() }}", isSetujui, idunit, kd_sumberdana },
                        success: function ( data ) {
                            const { message } = data
                            tata.success('✅ Sukses', 'Halaman akan dimuat ulang...')
                            setTimeout(() => {
                                window.location.reload()
                            }, 2000)
                        },
                        error: function ( error ) {
                            const msg = error.responseJSON.message || "Gagal menyetujui kegiatan"
                            return tata.error('⛔ Error', msg)
                        }
                    })
                }
            })
        })

        const syncParam = () => {
            url.searchParams.set("idunit", idunit.val())
            window.history.replaceState({}, '', url)

            if ( url.searchParams.get("idunit") !== null ) {
                idunit.val( url.searchParams.get("idunit") ).trigger("change")
            }
            if ( url.searchParams.get("kd_sumberdana") !== null ) {
                kodeSd.val( url.searchParams.get("kd_sumberdana") ).trigger("change")
            }
        }
        syncParam()

        $(document).on("click", "button#btn_triggerTanggapan", function(){
            const $tr = $(this).closest("tr")
            const id  = $tr.find("td:first-child").attr("key") ?? 0
            modalTanggapan.attr("key", id)
            modalTanggapan.modal("show")

            $.ajax({
                type: 'GET',
                url: "{{ route('vRabKeg.get.tanggapan') }}",
                data: { id },
                success: ( res ) => {
                    // clear tanggapan
                    bodyTblTanggapan.find("td.tanggapan").text("")
                    const { data, message } = res
                    data.forEach( item => {
                        const { role, tanggapan } = item
                        const tanggapanVerifikator = bodyTblTanggapan.find(`td.tanggapan[jenis="${role}"]`)
                        const tanggapanOperator    = bodyTblTanggapan.find(`td.tanggapan[jenis="${role}"]`)
                        if ( tanggapanVerifikator )
                            tanggapanVerifikator.text( tanggapan )
                        if ( tanggapanOperator )
                            tanggapanOperator.text( tanggapan )
                    })
                    // return tata.success('✅ Sukses', 'Tanggapan berhasil disimpan')
                },
                error: ( error ) => {
                    const msg = error.responseJSON.message || "Gagal menyimpan tanggapan"
                    return tata.error('⛔ Error', msg)
                }
            })
        })
        $("button#close-modal-tanggapan").on("click", function(){
            modalTanggapan.modal("hide")
        })

        $(document).on("click", "button.btn_setujuiTanggapan", function(){
            const id    = modalTanggapan.attr("key")
            const jenis = $(this).attr("jenis")
            const status = "Setuju"
            $.ajax({
                type: "POST",
                url: "{{ route('vRabKeg.storeVerif') }}",
                data: { _token: "{{ csrf_token() }}", id, jenis, status, userAgent, screenSize, platform, lang, },
                success: ( res ) => {
                    const { message } = res
                    return tata.success("✅ Sukses", message || "Data berhasil disimpan")
                },
                error: ( err ) => {
                    const message = res.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        })
        $( document ).on( "click", ".btn_simpanTanggapan", function(){
            const tr           = $(this).closest("tr")
            const tanggapanDom = tr.find("td.tanggapan")
            const role         = $(this).attr("attr")
            const id           = modalTanggapan.attr("key")

            if ( !id )
                return tata.error('⛔ Error', 'ID tidak ditemukan')

            // mapping jenis tanggapan
            const tanggapan = tanggapanDom.map( (i, el) => {
                return {
                    jenis: $(el).attr("jenis"),
                    value: $(el).text()
                }
            }).get()
            const buttonHtml = $(this).html()
            $(this).html('<i class="fa fa-spinner fa-spin"></i>')
            $.ajax({
                type: 'POST',
                url: "{{ route('vRabKeg.storeTanggapan') }}",
                data: { "_token": "{{ csrf_token() }}", id, role, tanggapan },
                success: ( res ) => {
                    $(this).html(buttonHtml)
                    const { data, message } = res
                    return tata.success('✅ Sukses', 'Tanggapan berhasil disimpan')
                },
                error: ( error ) => {
                    $(this).html(buttonHtml)
                    const msg = error.responseJSON.message || "Gagal menyimpan tanggapan"
                    return tata.error('⛔ Error', msg)
                }
            })

        })
        $( document ).on("click", "a.document-link", async function(e){
            const id       = $(this).data("id")
            const subJudul = $(this).data("subjudul")
            e.preventDefault()
            try {
                const response = await $.ajax({
                    type: "GET",
                    url: "{{ route('laporan.analisis.getTOR') }}",
                    data: { id },
                    xhrFields: { responseType: 'blob' },
                    timeout: 10000
                })

                const isPdf = response.type === 'application/pdf'; // Check if the blob is a PDF by checking its MIME type
                if ( isPdf ) {
                    const blobUrl = URL.createObjectURL(response);
                    $("#pdfContainer").html(
                        `<iframe src="${blobUrl}" width="100%" height="1000px"></iframe>`
                    );
                    modalTor.find("#nama-kegiatan").text(subJudul ?? '-')
                    modalTor.modal("show")
                    // Clean up blob URL after some time to free memory
                    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
                } else {
                    return tata.error("⛔ Error", "Gagal mendapatkan data", { animate: "slide", duration: 5000})
                }
            } catch (error) {
                const status  = error?.status || 500
                const message = "Terjadi kesalahan saat memuat data"
                if ( status == "404" ) {
                    return tata.error("⛔ Error", "Dokumen tidak ditemukan", { animate: "slide", duration: 5000})
                }
                return tata.error("⛔ Error", message, { animate: "slide", duration: 5000})
            }
        })
        $( document ).on("click", "button#close-modal-pdf", function(){
            modalTor.modal("hide")
        })
    })
</script>
