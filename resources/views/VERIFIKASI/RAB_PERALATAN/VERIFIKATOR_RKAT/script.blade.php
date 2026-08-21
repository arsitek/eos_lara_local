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
	    let tempdata 	   = []
	    const editable_row = [4,6,7,8,12,13,16]
	    let is_edit   	   = false
        const select2 	   = $(".s").select2()
        const datatable    = $('.tabel-verper').DataTable({
            "ordering": false,
            "autoWidth": false,
            "rowsGroup": [ 1, 2, 3, 8, 9 ],
            "drawCallback": function(dt) {
                const select2 = $(".s").select2()
            },
        });
        let anggaran_teralokasi = $(".alokasi-anggaran").text()
        let anggaran_terpetakan = $(".anggaran-terpetakan").text()
        if ( '-' !== anggaran_teralokasi ) {
        	$(".alokasi-anggaran").text( rupiah(anggaran_teralokasi) )
        }
        if ( '-' !== anggaran_terpetakan ) {
        	$(".anggaran-terpetakan").text( rupiah(anggaran_terpetakan) )
        }

        // ✅ onclick btn save
        $( document ).on("click", ".btn-save", function() {
            // get dropdown and id value
        	const row       = $(this).closest("tr")
            const id_rab    = row.find('.id').attr('key')
            const rpd       = row.find('select.rpd').text()
            const id_coa    = row.find("select.jenis_belanja").val() ?? ''
            const coa 	    = row.find("select.jenis_belanja option:selected").text().split(" | ")[1] ?? ''
            const kk   	    = row.find('.kk').text()
            const merk      = row.find('.merk').text()
            const type      = row.find('.type').text()
            const kode_aset = row.find('.kodefikasi_aset').val() ?? row.find(".kodefikasiAsetDb").text().split(" | ")[0]
            const aset      = row.find('.kodefikasi_aset option:selected').text().split(" | ")[1] ?? row.find(".kodefikasiAsetDb").text().split(" | ")[1]
            const jenis     = "updateItemCoa"

            if ( kode_aset === "" ) {
                return tata.warn("Perhatian", "Kode aset tidak boleh kosong")
            } else if ( aset === "" ) {
                return tata.warn("Perhatian", "Aset tidak boleh kosong")
            }
            if ( id_coa === "" || coa === "" ) {
                return tata.warn("Perhatian", "Jenis belanja tidak boleh kosong")
            }
            $.ajax({
                type: "POST", url: "{{ route('vRabPer.store') }}",
                data: { "_token": "{{ csrf_token() }}",
                 	rpd, id_rab, id_coa, coa, kk, merk, type, lang, platform, userAgent, screenSize, kode_aset, aset, jenis
               	},
                success: ( res ) => {
                	const data = res.data
                    return tata.success("✅ Sukses", res.message || "Berhasil menyimpan data")
                }, error: ( err ) => {
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
        getKodefikasiAset()
    })
</script>
