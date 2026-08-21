<script>
$( document ).ready(function() {
	$('select[name="filter_status"]').change(function(e) {
	  	const status     =  $(this).val()
	  	const currentUrl = window.location.href
	  	if ( status ) {
	  		const url = new URL(currentUrl)
	  		url.searchParams.set('status', status)
	  		url.searchParams.delete('id_rekat')
	  		window.open(url,"_self")
	  	} else {
            const url = new URL(currentUrl)
            url.searchParams.delete('status')
            window.open(url,"_self")
        }
	})
})
</script>
