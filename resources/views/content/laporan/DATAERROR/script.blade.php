<script>
    $(document).ready(function() {
        let select2 = $(".select2").select2();
        let table = $(".tabel-data-error-tidak-ditemukan")
        $("#pejabatTidakDitemukan").on("click", function(){
            const kd_sumberdana = $("select[name='sumberdana']").val()
            const kd_unit_kerja = $("select[name='unitkerja']").val()

            if ( kd_sumberdana == "" || kd_unit_kerja == "" ) {
                return tata.error("Galat", "Pilih sumberdana dan unit kerja")
            }
            $(".loading-div").show()
            $.ajax({
                type: "GET",
                url: "{{ route('dataerror.get') }}",
                data: { kd_sumberdana, kd_unit_kerja},
                success: (res) => {
                    const { data } = res;
                    console.log( res )
                    $(".tabel-data-error-tidak-ditemukan tbody").empty()
                    table.show()
                    if (data.length === 0) {
                        tata.info("Informasi", "Data tidak ditemukan")
                        $(".loading-div").hide()
                    } else {
                        data.forEach((element) => {
                            $(".tabel-data-error-tidak-ditemukan tbody").append(`<tr>
                                <td>${ element.id_rekat}</td>
                                <td>${ element.keg}</td>
                                <td>${ element.id_item_coa}</td>
                                <td>${ element.item_coa}</td>
                                <td>${ element.kd_coa + " | " + element.nama_coa}</td>
                                <td>${ element.rpd}</td>
                                <td>${rupiah(element.total_biaya_kegiatan)}</td>
                                </tr>
                            `)
                        })
                        // if (!$.fn.DataTable.isDataTable('table')) {
                        //     const tableRpdNull = table.DataTable({
                        //         "autoWidth": false,
                        //         "rowsGroup": [0,1,3,4]
                        //     })
                        // }
                        tata.success("Sukses", "Berhasil mendapatkan data")
                        $(".loading-div").hide()
                    }
                },
                error: ( err ) => {
                    $(".loading-div").show()
                    console.log( err )
                    return tata.error("Galat", "Gagal memuat data")
                }
            })
        })
    })
</script>
