<script type="text/javascript">
    $(document).ready(function () {
        let select2 = $(".s").select2();
        let datatable = $('.tabel-usul').DataTable({})

         // Saat tombol simpan di tekan
        $(document).on('click', ".save_btn", function (e) {
            let setiapBaris                = $(this).closest('tr')[0].innerText.split("\t").slice(0, -1)
            let id                         = setiapBaris[0]
            let sasaran_program            = setiapBaris[1]
            let indikator_kinerja_kegiatan = setiapBaris[2]
            let rincian_kegiatan           = setiapBaris[3]
            let verifikasi_tim             = $(this).closest('tr').find('select.verifikasi_tim').val()
            let verifikasi_pimpinan 	   = $(this).closest('tr').find('select.verifikasi_pimpinan').val()
            let tanggapan				   = setiapBaris[6]
            $.ajax({
                type: 'POST',
                url: "{{ route('usul.verHndlr') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    id,
                    sasaran_program,
                    indikator_kinerja_kegiatan,
                    rincian_kegiatan,
                    verifikasi_tim,
                    verifikasi_pimpinan,
                    tanggapan
                },
                success: function (data) {
                    console.log(data)
                },
                error: function (xhr, status, error) {
                    console.log(xhr.statusText)
                }
            }) // Ajax --
        }) // Simpan data --

   })

    // Komen handleupdate di halaman usul karena data terupdate secara otomatis ke halaman ikk 
  const handleUpdate = (domSelected) => {
       		// let setiapBaris                = $(domSelected).closest('tr')[0].innerText.split("\t").slice(0, -1)
         //    let id                         = setiapBaris[0]
         //    let sasaran_program            = setiapBaris[1]
         //    let indikator_kinerja_kegiatan = setiapBaris[2]
         //    let rincian_kegiatan           = setiapBaris[3]
         //    let verifikasi_tim            = $(domSelected).closest('tr').find('select.verifikasi_tim').val()
         //    let verifikasi_pimpinan 	   = $(domSelected).closest('tr').find('select.verifikasi_pimpinan').val()
         //    let tanggapan				   = setiapBaris[6]
         //    $.ajax({
         //        type: 'POST',
         //        url: "{{ route('usul.verHndlr') }}",
         //        data: {
         //            "_token": "{{ csrf_token() }}",
         //            id,
         //            sasaran_program,
         //            indikator_kinerja_kegiatan,
         //            rincian_kegiatan,
         //            verifikasi_tim,
         //            verifikasi_pimpinan,
         //            tanggapan
         //        },
         //        success: function (data) {
         //            console.log(data)
         //        },
         //        error: function (xhr, status, error) {
         //            console.log(xhr.statusText)
         //        }
         //    }) // Ajax --
    }
</script>
