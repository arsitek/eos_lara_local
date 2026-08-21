<script>
    // 💻 client information
    let userAgent  = navigator.userAgent
    let screenSize = `${window.screen.availWidth} x ${window.screen.availHeight}`
    let platform   = navigator.platform
    let lang       = navigator.language

    const layoutSetting = document.getElementsByClassName("layout-setting")
    const body          = document.getElementsByTagName("body")[0]

    const createOrUpdateMap = ( map, key, createNode ) => {
        if ( !map.has(key) ) map.set(key, createNode() )
        return map.get(key)
    }
    const mutasiAnggaranPercetakan = (dark) => {
        if (dark === true) {
            // Increase specificity or use !important to ensure it works
            $(".rekap_mutatsi_td_sumberdana_menjadi").css("border-left", "0.8px solid hsla(0, 0%, 100%, .1) !important");
        } else {
            $(".rekap_mutatsi_td_sumberdana_menjadi").css("border-left", "0.8px solid #e9edf4 !important");
        }
    };

    $(document).ready(function() {
        layoutSetting[0].addEventListener("click", function() {
            if ( body.classList.contains("dark-mode") ) {
                // save dark mode state to local storage
                localStorage.setItem("dark-mode", "true")
                mutasiAnggaranPercetakan( true )
            } else {
                // save dark mode state to local storage
                localStorage.setItem("dark-mode", "false")
                mutasiAnggaranPercetakan( false )
            }
        })
        // check if dark mode state is true
        if ( localStorage.getItem("dark-mode") === "true" ) {
            body.classList.add("dark-mode")
            mutasiAnggaranPercetakan( true )
        } else {
            body.classList.remove("dark-mode")
            mutasiAnggaranPercetakan( false )
        }
    })

</script>
