<script type="text/javascript">
$(document).ready(function() {
	$('.tabel-verrekat ').DataTable({
			"ordering" : false
	});
	
	// -- Tombol save
	$(document).on('click',".save_btn", function(e){
		let baris				=  $(this).closest('tr')
		let setiapBaris 		=  baris[0].innerText.split("\t").slice(0, -1)
		let id 					= setiapBaris[0]
		let verifikasi_tim		= $(this).closest('tr').find('select.verifikasi_tim').val()
		let verifikasi_pimpinan	= $(this).closest('tr').find('select.verifikasi_pimpinan').val()
		let verifikasi_univ		= $(this).closest('tr').find('select.verifikasi_univ').val()
		let tanggapan			= setiapBaris[13]
		$.ajax({
	        type:'POST',
		    url:" {{ route('vRekat.add') }} ",
	        data:{
		    	"_token": "{{ csrf_token() }}"
				,id
        		,verifikasi_tim
         		,verifikasi_pimpinan
         		,verifikasi_univ
		 		,tanggapan	                
			},
             success:function(data){
            	// if(verifikasi_tim == 'Setuju' && verifikasi_pimpinan == 'Setuju'){
            	// 	baris.remove()
            	// }
                tata.success('Sukses', 'Berhasil verifikasi data', {
					duration: 3000,
					animate:slide
				})
            },
            error: function (request, status, error) {
                tata.error('Galat', 'gagal verifikasi data', {
					duration: 3000,
					animate:slide
				})
            }
        })// Ajax --
	})	// Tombol tambah --
})
	const handleUpdate = (domSelected) => {
        let setiapBaris 		= $(domSelected).closest('tr')[0].innerText.split("\t").slice(0, -1)
        let id 					= setiapBaris[0]
		let verifikasi_tim		= $(domSelected).closest('tr').find('select.verifikasi_tim').val()
		let verifikasi_pimpinan	= $(domSelected).closest('tr').find('select.verifikasi_pimpinan').val()
		let verifikasi_univ		= $(domSelected).closest('tr').find('select.verifikasi_univ').val()
		let tanggapan			= setiapBaris[13]
		console.log(tanggapan)
        // -- Ajax
        $.ajaxSetup({
            // setup header
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ route('vRekat.add') }}",
            data: {
                "_token": "{{ csrf_token() }}"
                ,id
                ,verifikasi_tim
				,verifikasi_pimpinan
				,verifikasi_univ
				,tanggapan
            },
            success: function (data) {
                console.log(data)
            },
            error: function (xhr, status, error) {
                console.log(xhr.statusText)
            }
        })} // Ajax --
</script>