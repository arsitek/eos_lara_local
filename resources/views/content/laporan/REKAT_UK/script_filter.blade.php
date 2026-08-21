<script type="text/javascript">
$(document).ready(function () {
    let select2 = $('.s').select2()
    window.laporan = window.laporan || {}
    window.laporan.rka = window.laporan.rka || {}
    window.laporan.rka.methods = window.laporan.rka.methods || {}
    $(".sumberdanaOption").on("click", function(){
        const value = $(this).data("value")
        const text  = $(this).text()
        if ( value == "semua" ) {
            $(".sumberdanaOption").addClass("selected")
        }
    })
    $( document ).on("click", ".cari", function(){
        const filter     = $(".filter-data").val()
        const idunit     = $(".unitkerjaOption.selected").map((_, el) => $(el).data("value") ).get().filter(v => v !== "X")
        const sumberdana = $(".sumberdanaOption.selected").map((_, el) => $(el).data("value") ).get()
        const backup     = $(".riwayatOption.selected").map((_, el) => $(el).data("value") ).get()
        const tahun      = "{{$tahunAngka}}"

        if( idunit.length === 0 ){
            return tata.warn("Perhatian", "Silahkan memilih unitkerja terlebih dahulu")
        } if ( sumberdana.length === 0){
            return tata.warn("Perhatian", "Silahkan memilih sumberdana terlebih dahulu")
        }
        let url = `/laporan/rktunit/get/${idunit}/${sumberdana}?filterdata=${filter}`
        $(".body-tbl-unit").children().remove()

        if ( "paket" === filter ) {
            url = `{{ route('rktReportUnit.getPaket') }}?idunit=${idunit}&sumberdana=${sumberdana}`
            generateRkaPaket( idunit, sumberdana, url, ".body-tbl-unit", false )
            return
        }
        if ( "final" === filter ) {
            showLoader()
            setLoaderText("Memproses data RKA...Mohon menunggu")
            window.laporan.rka.methods.getBaseData( idunit, sumberdana, filter, backup )
                .then( data => {
                    window.laporan.rka.methods.generateRkaFinal( data.data )
                }).catch( err => tata.error("Error", err || "Terjadi kesalahan saat mengambil data base RKA", { duration: 3000, animate: "slide" } ) )
            return
        }
        // this function located on resources/views/helpers/report_function.blade.php
        generateRKA( idunit, sumberdana, backup, filter, url, '#loader-div', '.body-tbl-unit', 'Memuat data RKA...Mohon menunggu', false, false )
    })

    $( document ).on("change", ".filter-data", function() {
        const value     = $(this).val()
        const idunit    = $("select.unit_kerja").val()
        const sd        = $("select.sumberdana").val()
        const filterDom = $(".filter-data")

        // Check if the value is empty
        if ( idunit == "" ) {
            filterDom.val('').html(filterDom.html())
            return tata.warn('⚠️ Perhatian', 'Silahkan memilih unitkerja terlebih dahulu')
        }
        if ( sd == "" ) {
            filterDom.val('').html(filterDom.html())
            return tata.warn('⚠️ Perhatian', 'Silahkan memilih sumberdana terlebih dahulu')
        }
    })
})
</script>
