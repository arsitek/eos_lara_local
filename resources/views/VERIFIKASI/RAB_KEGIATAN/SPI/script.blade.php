<script>
    $(document).ready(function() {
        const select2 = $(".s").select2()
        const idunit  = $("select.unit_kerja").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const kodeSd = $("select.sumberdana").filter(function () {
            return $(this).parent().parent().css("display") !== "none"
        })
        const userAgent  = navigator.userAgent
        const screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform   = navigator.platform
        const lang       = navigator.language
        const datatable  = $('.tabel-verkeg').DataTable({
            "ordering": false,
            "autoWidth": false,
            "rowsGroup": [1, 2, 3, 4, 5, 6],
            "drawCallback": function(dt) {
                const select2 = $(".s").select2()
            },
        });

        const anggaran_teralokasi = $(".alokasi-anggaran").text()
        const anggaran_terpetakan = $(".anggaran-terpetakan").text()
        if ( '-' !== anggaran_teralokasi ) {
            $(".alokasi-anggaran").text( rupiah(anggaran_teralokasi) )
        }
        if ( '-' !== anggaran_terpetakan ) {
            $(".anggaran-terpetakan").text( rupiah(anggaran_terpetakan) )
        }

        $(document).on("click", ".btn-filter-unitkerja", function(){
            if ( idunit.val() === "") {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja.")
            }
            if ( kodeSd.val() === "" || kodeSd.val() === "-" ) {
                return tata.warn("Perhatian", "Anda belum memilih sumberdana")
            }
            window.open(`/verifikasi/kegiatan-spi/${idunit.val()}?kd_sumberdana=${kodeSd.val()}`, '_self')
        })

        // Handler umum diarahkan ulang agar bulk approve SPI tidak memakai proses verifikasi lain.
        $("#btn-setujui-semua").off("click").on("click", function(){
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
                    const isSetujui = 1
                    const url = new URL(window.location.href)
                    const idunit = url.pathname.split("/")[3]
                    const kd_sumberdana = url.searchParams.get('kd_sumberdana')

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('vRabKegSpi.store') }}",
                        data: { "_token": "{{ csrf_token() }}", isSetujui, idunit, kd_sumberdana },
                        success: function ( data ) {
                            tata.success('✅ Sukses', 'Halaman akan dimuat ulang...')
                            setTimeout(() => window.location.reload(), 2000)
                        },
                        error: function ( error ) {
                            const msg = error.responseJSON?.message || "Gagal menyetujui kegiatan"
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
                url: "{{ route('vRabKegSpi.storeVerif') }}",
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