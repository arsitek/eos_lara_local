<script>
    // ✅ Untuk mengupdate url ketika dropdown jumlah data diubah
	const fetchEntries = ( jumlah_data, kd_sumberdana, status ) => {
	    let url = `${window.location.pathname}?jumlah_data=${jumlah_data}&page=1&kd_sumberdana=${kd_sumberdana}`
        if ( status ) {
            url += `&status=${status}`
        }
	    window.location.href = url
	}
    // ✅ Untuk mengupdate link pagination ketika jumlah data per halaman diubah
	const updatePaginationLinks = ( sumberdana = false ) => {
        const paginationLinks  = document.querySelectorAll('.page-link')
        const prioritasElement = document.querySelector('.prioritas');
        const prioritas        = prioritasElement ? prioritasElement.value : null;
        paginationLinks.forEach(link => {
            const url = new URL(link.href)
            url.searchParams.set('jumlah_data', document.querySelector('select[name=jumlah_data]').value)
            if ( prioritas !== null ) {
                url.searchParams.set('prioritas', prioritas)
            }
            if ( true == sumberdana ) {
            	url.searchParams.set('kd_sumberdana', document.querySelector('select[name=sumberdana]').value)
            }
            link.href = url.toString()
        })
    }
    // ✅ Untuk mengganti jumlah data per halaman yg dipilih pada dropdown
    const fetchPagination = ( element ) => {
        $( element ).on("change", function (e) {
            let jumlah_data = $(this).val()
            const url       = new URL(window.location.href)
            url.searchParams.set('jumlah_data', jumlah_data)
            window.location.href = url.toString()
        })
    }
</script>
