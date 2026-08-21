<script>
$(document).ready(function () {
    const selectJenisCustom = $("select#jenisCustom")
    const selectIdRekat     = $("select#selectIdRekat")
    const btnFilterExport   = $("#btnFilterExport")
    const tabelPPK      = $("#tabel-rekat-ppk")
    const modalInfo     = $("#modal-info")
    const modalPpk      = $("#modal-ppk")
    const modalExport   = $("div#modal-custom-export")
    const divTagihan    = $("#divTagihan")
    const divInfo       = $("#divInfo")
    const tahunAngka    = "{{ $tahunAngka }}"
    const modalInstance = modalInfo.modal.bind(modalInfo)
    const isPdf         = window.location.href.includes("pdf")
    let kodeSd          = ""
    // client information
    let userAgent  = navigator.userAgent
    let screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
    let platform   = navigator.platform
    let lang       = navigator.language

    const tabelPpkNull = $("#tabel-ppk-null").DataTable({
        "pageLength" : 7,
        "rowsGroup"  : [1]
    })

        let selectedUnitKerjaFromRow = null

    $(".ppk, .bpp").select2({
        dropdownParent: $('#modal-ppk')
    })
    $("#jenisCustom, #selectIdRekat").select2({
        width: "100%",
        dropdownParent: modalExport.find(".modal-body")
    });

    $("#btn_togglePPK").on("click", function(){
        $(".rkaUnit").hide("slow")
        $(".rkaPPK").show("fast")
        $("#btn_toggleUnit").show("slow")
        this.style.display = "none"
    })

    $("#btn_toggleUnit").on("click", function(){
        $(".rkaUnit").show("slow")
        $(".rkaPPK").hide("fast")
        $("#btn_togglePPK").show("slow")
        this.style.display = "none"
    })

    $(document).on("click", ".info-pagu", function(){
        modalInstance.modal("show")
        divTagihan.hide()
        divInfo.show()
    })
    $("#close-modal-info").on("click", function(){
        modalInfo.modal("hide")
    })
    $(document).on("click", ".realisasi", function(){
        const $this          = $(this)
        const totalBiayaAttr = $this.attr("total-biaya")
        const totalTghAttr   = $this.attr("total-tagihan")
        const totalBiaya     = ( totalBiayaAttr  == "null" || totalBiayaAttr == "undefined" ) ? 0 : totalBiayaAttr
        const totalTagihan   = ( totalTghAttr  == "null" || totalTghAttr == "undefined" ) ? 0 : totalTghAttr

        modalInstance("show")
        divInfo.hide()
        divTagihan.show()
        $("#jumlahBiaya").text( rupiah(totalBiaya) )
        $("#jumlahTagihan").text( rupiah(totalTagihan) )
    })
    $( document ).on("click", ".itemCoa", function(){
        if ( "{{session('role')}}" != "superadmin" && "{{session('role')}}" != "admin" )
            return
        modalPpk.modal("show")
        const $tr         = $(this).closest("tr")
        kodeSd      = $tr.attr("kodeSd")
        selectedUnitKerjaFromRow = $tr.attr("idunit") || null
        const itemCoa     = $(this).next().text()
        const jumlahBiaya = $tr.attr("jumlahBiaya")
        const idCoa       = $tr.attr("idCoa")
        const nipPPK      = $tr.attr("ppk")
        const nipBPP      = $tr.attr("bpp")

        if ( nipPPK != "" ){
            $(".ppk").val(nipPPK).trigger("change")
        } if ( nipBPP != "" ){
            $(".bpp").val(nipBPP).trigger("change")
        }
        $("#jenisRab").text( $tr.attr("jenis") )
        $("#idRab").text( $tr.attr("key") )
        $("#item-coa").text(itemCoa)
        $("#idJenisBelanja").text(idCoa)
        $("#jumlahBiaya").text(jumlahBiaya)
    })

    $( document ).on("click", "#close-modal-ppk", function(){
        modalPpk.modal("hide")
    })

    $( document ).on("click", "#simpanPpk", function(){
        if ( $(".ppk").val() == "" || $(".bpp").val() == "" )
            return tata.error('⛔ Error', 'PPK & BPP tidak boleh kosong')
        const idunit         = selectedUnitKerjaFromRow || $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get().filter(v => v !== "X")[0]
        const idJenisBelanja = $("#idJenisBelanja").text()
        const jumlahBiaya    = $("#jumlahBiaya").text()
        const ppk            = $(".ppk").val()
        const bpp            = $(".bpp").val()
        const jenis          = $("#jenisRab").text()
        const idRab          = $("#idRab").text()

        $.ajax({
            url: "{{ route('rktReportUnit.update.ppk') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", idRab, ppk, bpp, jenis, idunit, jumlahBiaya, idJenisBelanja, kodeSd, userAgent, screenSize, platform, lang },
            success: ( res ) => {
                const { data } = res
                modalPpk.modal("hide")
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: res.message || "Berhasil memperbarui data PPK & BPP"
                })
            }, error: ( err ) => {
                const message = err.responseJSON.message || "Gagal menghapus data"
                return tata.error('⛔ Error', message )
            }
        })
    })

    const generateStatusHtml = ( jenis, text ) => {
        return `<span class="badge rounded-pill ${jenis == 'Setuju' ? 'bg-success' : 'bg-danger'} bg-opacity-10 text-white px-3 py-2 kolomStatus" style="width: 150px">
            <i class="fa ${jenis == 'Setuju' ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>${text}
        </span>`
     }
    // map roles to their unique suffixes
    const roleMap = {
        'Verifikator Keuangan': 'Keu',
        'Pimpinan Unit':       'PimpinanUnit',
        'Verifikator Aset':    'Aset',
        'Pimpinan Univ':       'PimpinanUniv',
        'Verifikator RKAT':    'RKAT'
    };
    $( document ).on( "click", ".status-verif", (e) => {
        if ( isPdf ) return
        const $target = $(e.target)
        const $tr     = $target.closest("tr")
        const id      = $tr.attr("key")
        const kk      = $target.text()
        const jenis   = $tr.attr("jenis")
        const isDraft = $tr.attr("isDraft")
        const kodeKeg = $tr.attr("kodeKeg")

        if ( !id || !kk || !jenis ) {
            return tata.error("⛔ Error", "Terjadi kesalahan.")
        }

        $( "#modal-status" ).modal( "show" )
        $( ".judulKegiatan" ).text( kk )
        if ( isDraft == "true" ) {
            $( ".judulKegiatan" ).append(" <span class='badge bg-danger'>Menunggu persetujuan</span>")
        }

        $.ajax({
            type: "GET",
            url: `{{ route('rktReportUnit.getStatusVerifikasi') }}?id=${id}&jenis=${jenis}&kodeKeg=${kodeKeg}`,
            success: ( res ) => {
                const { verifikasi, isPosting, isPaket, isRUP, isProses, isTorAvail, tanggapan, tanggapanKAI, statusKAI, pesanKlarifikasiKAI, semulaMenjadi, tanggalBayar, isWillPaket } = res.data

                if ( !verifikasi )
                    return tata.error("⛔ Error", "Data tidak ditemukan")
                $( ".kolomStatus" ).remove()
                $( ".groupTOR" ).append( ( isTorAvail !== '' && isTorAvail !== null ) ? generateStatusHtml("Setuju", "Lengkap") : generateStatusHtml("Tolak", "Tidak Lengkap") )
                $( ".groupPimpinanUnit" ).append( verifikasi.verifikasi_pimpinan_unit == 'Setuju' ? generateStatusHtml("Setuju", "Terverifikasi") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                $( ".groupKeu" ).append( verifikasi.verifikasi_keu == 'Setuju' ? generateStatusHtml("Setuju", "Terverifikasi") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                $( ".groupRKAT" ).append( verifikasi.verifikasi_tim == 'Setuju' ? generateStatusHtml("Setuju", "Terverifikasi") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                $( ".groupAset" ).append( verifikasi.verifikasi_aset == 'Setuju' ? generateStatusHtml("Setuju", "Terverifikasi") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                $( ".groupPimpinanUniv" ).append( verifikasi.verifikasi_pimpinan_univ == 'Setuju' ? generateStatusHtml("Setuju", "Terverifikasi") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                // Status KAI mengikuti verifikasi SPI dari Pengawasan Internal/Auditor.
                $( ".groupKAI" ).append( statusKAI == "Setuju" ? generateStatusHtml("Setuju", "Disetujui") : generateStatusHtml("Tolak", "Belum Diverifikasi") )
                $( ".groupPosting" ).append( jQuery.isEmptyObject( isPosting ) ? generateStatusHtml("Tolak", "Belum Terposting") : generateStatusHtml("Setuju", "Terposting") )
                $( ".groupAmprah" ).append( ( isProses !== null && isProses?.jumlah_amprahan != '0' || isProses?.jumlah_amprahan != null ) === true ? generateStatusHtml("Setuju", rupiah( isProses.jumlah_amprahan ) ) : generateStatusHtml("Tolak", "Belum Amprah" ) )
                $( ".groupRealisasi" ).append( ( isProses !== null && isProses?.jumlah_realisasi != '0' || isProses?.jumlah_realisasi != null ) === true ? generateStatusHtml("Setuju", rupiah( isProses.jumlah_realisasi ) ) : generateStatusHtml("Tolak", "Belum Realisasi" ) )
                $( ".groupTglBayar" ).append( tanggalBayar !== null ? generateStatusHtml("Setuju", tanggalBayar ) : generateStatusHtml("Tolak", "-" ) )
                if ( jenis === "OPERASIONAL" && isWillPaket === false ) {
                    $( ".groupRUP" ).attr("style", "display: none !important");
                    $( ".groupPaket" ).attr("style", "display: none !important");
                } else {
                    $( ".groupRUP" ).attr("style", "display: flex !important");
                    $( ".groupPaket" ).attr("style", "display: flex !important");
                    $( ".groupRUP" ).append( isRUP == "false" ? generateStatusHtml("Tolak", "Belum RUP") : generateStatusHtml("Setuju", "Sudah RUP" ) )
                    $( ".groupPaket" ).append( jQuery.isEmptyObject( isPaket ) ? generateStatusHtml("Tolak", "Belum Terpaketkan") : generateStatusHtml("Setuju", "Terpaketkan") )
                }
                // tanggapan
                tanggapan.forEach(({ role, tanggapan }) => {
                    if ( !role || !tanggapan ) return; // skip empty roles or tanggapan
                    const key = roleMap[role];
                    if (!key) return; // skip unknown roles
                    const $container = $(`.containerTanggapan${key}`);
                    $container.removeClass('d-none').find(`.tanggapan${key}`).text(tanggapan);
                });
                const pesanKai = statusKAI === "Tolak" && pesanKlarifikasiKAI ? `<span class="text-danger small">${pesanKlarifikasiKAI}</span>` : "";
                const tanggapanKaiHtml = [tanggapanKAI, pesanKai].filter(Boolean).join("<br>");

                if (tanggapanKaiHtml) {
                    // Pesan klarifikasi hanya ditampilkan ketika verifikasi SPI ditolak.
                    $(".containerTanggapanKAI").removeClass("d-none").find(".tanggapanKAI").html(tanggapanKaiHtml);
                } else {
                    $(".containerTanggapanKAI").addClass("d-none");
                }

                // semulaMenjadi
                if ( semulaMenjadi && semulaMenjadi.length > 0 ) {
                    $( ".groupSemulaMenjadi" ).removeClass("d-none")
                    semulaMenjadi.forEach(({ jenis_revisi, jenis_validasi, status }) => {
                        const jenis = status === 'Setuju' ? 'Setuju' : 'Tolak'
                        const text = jenis == "Setuju" ? "Terverifikasi" : "Belum diverifikasi"
                        $( ".groupSemulaMenjadi" ).append( generateStatusHtml(jenis, text) )
                    })
                } else {
                    $( ".groupSemulaMenjadi" ).addClass("d-none")
                }
                return
            }, error: ( err ) => {
                const message = err.responseJSON.message || "Terjadi kesalahan saat mendapatkan data"
                return tata.error("⛔ Error", message)
            }
        })
    })
    $( "#modal-status" ).on( "hidden.bs.modal", (e) => {
        $( ".containerTanggapanPimpinanUnit" ).addClass("d-none")
        $( ".containerTanggapanKeu" ).addClass("d-none")
        $( ".containerTanggapanAset" ).addClass("d-none")
        $( ".containerTanggapanPimpinanUniv" ).addClass("d-none")
        $( ".containerTanggapanRKAT" ).addClass("d-none")
        $( ".containerTanggapanKAI" ).addClass("d-none")
        $( ".groupSemulaMenjadi" ).addClass("d-none")
    })
    $( document ).on( "click", "#close-modal-status", (e) => {
        $( "#modal-status" ).modal( "hide" )
    })
    $( document ).on( "click", "#close-modal-ppk-null", (e) => {
        $( "#modal-ppk-null" ).modal( "hide" )
    })
    $( document ).on( "click", "#close-modal-pdf", (e) => {
        $( "#modal-tor-viewer" ).modal( "hide" )
        // Clear the iframe to free memory
        $("#pdfContainer").html('<div class="d-flex justify-content-center align-items-center" style="height: 500px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>')
    })

    $( document ).on("click", ".listDataPpkBppNull", function(){
        $( "#modal-ppk-null" ).modal("show")
        const idRekat = $(this).attr("idRekat")
        $.ajax({
            type: "GET",
            url: `/laporan/rktunit/get/ppkNull?idRekat=${idRekat}`,
            success: ( res ) => {
                const { data } = res

                const html = []
                data.forEach( ( item, index) => {
                    html.push(`
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${item.id_rekat} | ${ item.sub_judul }</td>
                            <td>${ item.rpd }</td>
                            <td>${ item.kebutuhan_kegiatan }</td>
                        </tr>
                    `)
                })
                $("#tabel-ppk-null").find(".bodyTbl").html( html.join("") )
                tabelPpkNull.clear().rows.add( $("#tabel-ppk-null").find(".bodyTbl tr") ).draw()
            },
            error: ( err ) => {
                const message = err.responseJSON.message || "Terjadi kesalahan saat mendapatkan data"
                return tata.error("⛔ Error", message)
            }
        })
    })

    let rightClickedElement = null
    $( document ).mousedown( function(e) {
        switch (e.which) {
            case 3:
                rightClickedElement = document.elementFromPoint(e.clientX, e.clientY)
                break
        }
    })
    $( document ).on("click", ".context-menu-item", async function(e){
        const action  = $(this).data("action")
        const $tr     = rightClickedElement.closest("tr")
        const idRekat = $tr.getAttribute("key")
        const secondTd = $tr.querySelectorAll("td")[1];
        if ( action === "tor" ) {
            if ( !idRekat ) return

            const subJudul = secondTd.textContent || 'Lihat TOR'
            const modalTor = $("#modal-tor-viewer")

            try {
                const response = await $.ajax({
                    type: "GET",
                    url: "/rekat/tor",
                    data: { id: idRekat },
                    xhrFields: { responseType: 'blob' },
                    timeout: 10000
                })

                const isPdf = response.type === 'application/pdf';
                if ( isPdf ) {
                    const blobUrl = URL.createObjectURL(response);
                    $("#pdfContainer").html(
                        `<iframe src="${blobUrl}" width="100%" height="1000px" style="border: none;"></iframe>`
                    );
                    modalTor.find("#nama-kegiatan").text(subJudul)
                    modalTor.modal("show")
                    // Clean up blob URL after some time to free memory
                    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
                } else {
                    return tata.error("⛔ Error", "Gagal mendapatkan data. Format dokumen tidak valid.", { animate: "slide", duration: 5000})
                }
            } catch (error) {
                const status  = error?.status || 500
                let message = "Terjadi kesalahan saat memuat dokumen TOR"

                if ( status == 404 ) {
                    message = "Dokumen tidak ditemukan"
                } else if ( status == 403 ) {
                    message = "Akses ditolak. Anda tidak memiliki izin untuk melihat dokumen ini"
                } else if ( status == 400 ) {
                    message = "Parameter tidak valid"
                }

                return tata.error("⛔ Error", message, { animate: "slide", duration: 5000})
            }
        }
    })

    $("button.btn-custom-export").on("click", function(){
        modalExport.modal("show")
    })
    btnFilterExport.on("click", function(){
        const $this   = $(this)
        const btnHtml = $this.html()
        const filter = $(".filter-data").val()
    const idunit = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get().filter(v => v !== "X")
        const kodeSd = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
        const backup = $(".riwayatOption.selected").map((_, el) => $(el).data("value") ).get()
        const tahun  = "{{ $tahunAngka }}"
        const urlObj = new URL( window.location.href )
        const params = new URLSearchParams( urlObj.search )


        if ( !selectJenisCustom.val() )
            return $("#alertCustomExport").show()

        if ( selectJenisCustom.val() === "idrekat" && !selectIdRekat.val() )
            return tata.error("⛔ Error", "Silakan pilih ID Rekat yang ingin di filter terlebih dahulu")

        let url = `/laporan/rktunit/get/${idunit}/${kodeSd}?filterdata=${filter}`
        // change button into loading
        $(".body-tbl-unit").children().remove()
        $this.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...`).attr("disabled", true)
        generateRKA( idunit, kodeSd, backup, filter, url, '#loader-div', '.body-tbl-unit', 'Memuat data RKA...Mohon menunggu', false, false, selectIdRekat.val().join(",") ).then( e => {
            $("#successFilter").show().delay(3000).fadeOut().css("display", "inline")
            // scroll to top of card body
            $(".body-tbl-unit").scrollTop(0)
            $this.html( btnHtml ).attr("disabled", false)
            modalExport.modal("hide")
        }).catch( err => {
            $("#successFilter").hide().delay(3000).fadeOut().css("display", "none")
            console.error( err )
            tata.error("⛔ Error", err.message || "Terjadi kesalahan tak terduga", { animate: "slide", duration: 5000 })
            $this.html( btnHtml ).attr("disabled", false)
            modalExport.modal("hide")
        })
    })
    selectJenisCustom.on("change", async function(){
        try {
            const val    = $(this).val()
            const idunit = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get().filter(v => v !== "X")
            const kodeSd = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()

            if ( idunit.length === 0 ) {
                $(this).val("").trigger("change.select2")
                return tata.error("⛔ Error", "Silakan pilih Unit Kerja terlebih dahulu", { animate: "slide", duration: 5000 })
            }
            if ( kodeSd.length === 0 ) {
                $(this).val("").trigger("change.select2")
                return tata.error("⛔ Error", "Silakan pilih Sumber Dana terlebih dahulu", { animate: "slide", duration: 5000 })
            }
            if ( !val ){
                $(".basedOnIdRekat").hide()
                $("#selectIdRekat").val("").trigger("change.select2")
                return $("#alertCustomExport").show()
            }

            const mappingCustomExport = {
                "idrekat": {
                    divClass: "basedOnIdRekat"
                }
            }
            const { divClass } = mappingCustomExport[val] || {}
            if ( !divClass ) {
                $targetDiv.find("select#selectIdRekat").empty().append(html)
                $(".basedOnIdRekat").hide()
                return $("#alertCustomExport").show()
            }

            const $targetDiv = $(`#containerCustomExport .${divClass}`)

            if ( $targetDiv.length ) {
                const response = await $.ajax({
                    type: "GET",
                    url: "{{ route('rktReportUnit.getIdRekats') }}",
                    data: { idunit, kodeSd },
                    timeout: 10000
                })
                if ( response.success ) {
                    const { data } = response
                    if ( data && data.length ) {
                        const html = data.map( item => `<option value="${item.id}">${item.id} | ${item.sub_judul}</option>` ).join("")
                        $targetDiv.find("select#selectIdRekat").empty().append(html)
                    }
                } else {
                    return tata.error("⛔ Error", "Terjadi kesalahan saat mendapatkan data ID Rekat", { animate: "slide", duration: 5000 })
                }
                $targetDiv.show()
                $("#alertCustomExport").hide()
            }
        } catch ( e ) {
            console.error( e )
            return tata.error("⛔ Error", e.message || "Terjadi kesalahan tak terduga", { animate: "slide", duration: 5000 })
        }
    })
})
</script>
