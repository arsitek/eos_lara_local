<script>
    $(document).ready(function() {
        // Jalur SPI dipisahkan agar filter dan simpan tidak memakai endpoint umum BHP.
        const idunit = $("select.unit_kerja").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const kodeSd = $("select.sumberdana").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const userAgent  = navigator.userAgent
        const screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform   = navigator.platform
        const lang       = navigator.language
        const select2    = $(".s").select2()
        const datatable  = $('.tabel-verper').DataTable({
            "ordering": false,
            "autoWidth": false,
            "rowsGroup": [ 1, 2, 3 ],
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

        $(document).on("click", ".btn-filter-unitkerja", function(){
            if ( idunit.val() === "") {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja atau tahun.")
            }
            if ( kodeSd.val() === "" || kodeSd.val() === "-" ) {
                return tata.warn("Perhatian", "Anda belum memilih sumberdana")
            }
            window.open(`/verifikasi/bhp-spi/${idunit.val()}?kd_sumberdana=${kodeSd.val()}`, "_self")
        })

        // Handler umum diarahkan ulang agar bulk approve SPI hanya menyentuh verifikasi_spi.
        $("#btn-setujui-semua").off("click").on("click", function(){
            Swal.fire({
                title: 'Apakah anda yakin untuk menyetujui semua BHP?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'IYA',
                cancelButtonText: 'TIDAK'
            }).then((result) => {
                if (result.isConfirmed) {
                    const isSetujui = 1
                    const url = new URL(window.location.href)
                    const idunit = url.pathname.split("/")[3]
                    const kd_sumberdana = url.searchParams.get('kd_sumberdana')

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('vRabBhpSpi.store') }}",
                        data: { "_token": "{{ csrf_token() }}", isSetujui, idunit, kd_sumberdana },
                        success: function () {
                            tata.success('✅ Sukses', 'Halaman akan dimuat ulang...')
                            setTimeout(() => window.location.reload(), 2000)
                        },
                        error: function ( error ) {
                            const msg = error.responseJSON?.message || "Gagal menyetujui BHP"
                            return tata.error('⛔ Error', msg)
                        }
                    })
                }
            })
        })

        $(document).on("click", ".switchVerifikasi", (e) => {
            const $target = $(e.target)
            const $tr = $target.closest("tr")
            const isChecked = $target.is(":checked")
            const id = $tr.find(".id").attr("key")
            const jenis = "verifikasiSpi"
            const status = isChecked ? "Setuju" : "Tolak"

            $.ajax({
                type: "POST",
                url: "{{ route('vRabBhpSpi.storeVerif') }}",
                data: { _token: "{{ csrf_token() }}", id, isChecked, jenis, status, userAgent, screenSize, platform, lang },
                success: (res) => {
                    const { message } = res
                    return tata.success("✅ Sukses", message)
                },
                error: (err) => {
                    const message = err.responseJSON?.message || "Terjadi kesalahan saat menyimpan data"
                    $target.prop('checked', !isChecked)
                    return tata.error("⛔ Error", message)
                }
            })
        })
    })
</script>