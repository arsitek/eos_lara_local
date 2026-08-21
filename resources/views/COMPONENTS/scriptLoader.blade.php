<script>
    const setLoaderText = ( text ) => {
        $(".loading-msg").text( text )
    }
    const removeLoader = () => {
        $(".loader-div").removeClass("d-flex")
        $(".loader-div").addClass("d-none")
        $(".loading-msg").hide()
    }
    const showLoader = () => {
        $(".loader-div").removeClass("d-none")
        $(".loader-div").addClass("d-flex")
        $(".loading-msg").show()
    }
</script>
