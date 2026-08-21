<script type="text/javascript">
// Disarankan ngoding sambil mendengar lagu watashi psikopat ~ 🎵Unravel ♫
	$(document).ready(function() {
        // 💻 client information
        const userAgent  = navigator.userAgent
        const screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform   = navigator.platform
        const lang       = navigator.language

		$('.tabel-perkin').dataTable({
			"pageLength": 5
		});
		$('.tabel-perkinSetuju').dataTable({
			"pageLength": 3
		});
		$(".s").select2()

		$( document ).on("click", ".btn-filter-unitkerja", function(){
            const idunit = $("select.unit_kerja").val()
            if ( idunit === "" ) {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja")
            }
            window.open(`/verifikasi/perkin/${idunit}`, "_self")
        })

    	$(document).on('click', ".save_btn", function(e){
            // 📦 Init variable
    		let $tr   				 = $(this).closest("tr")
			let id 					 = $tr.find("td[key]").attr("key")
        	let tanggapan	         = $tr.find("td.tanggapan").text()
        	let verifikasi_tim		 = $tr.find("select.verifikasi_tim").val()
        	let verifikasi_pimpinan	 = $tr.find("select.verifikasi_pimpinan").val()

	        $.ajax({
	            type:"POST",
		        url:" {{ route('verPerkin.add') }} ",
	            data:{ "_token": "{{ csrf_token() }}", id, verifikasi_tim, verifikasi_pimpinan, tanggapan,
                    userAgent, screenSize, platform, lang
                },
                success: ( res ) => {
                    // console.log( res )
					// if( verifikasi_tim == "Setuju" && verifikasi_pimpinan == "Setuju" ){
					// 	$tr.remove()
					// }
                    return tata.success('Sukses', 'Berhasil menyimpan data', { duration: 3000, animate:slide })
				},
				error: ( xhr, status, error) => {
                    const message = xhr.responseJSON.message || "Gagal menyimpan data"
		            return tata.error('⛔ Error ', message, { duration: 3000, animate:slide })
	            }
            })
         })


 });
</script>
