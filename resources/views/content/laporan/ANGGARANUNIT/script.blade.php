<script type="text/javascript">
    $(document).ready(function () {
        // tata.warn('Warning', 'Laporan sedang dalam perbaikan', { duration: 3000, animate:slide})
        $.ajax({
            type:'GET',
            url:" {{ route('rekap.anggaran.get') }} ",
			success:function(data){
                let bodyLaporan = $('.body-tbl')
                const REKAP = data.data
                REKAP.unitkerja.forEach((item, index) => {
                    $('.tabel-rekat tr:last').after(`<tr class="ss_row" style="font-weight:bold">
                        <td class="idunit ${item.unit_kerja}">${item.unit_kerja}</td>
                        <td class="unitkerja ${item.unit_kerja}">${item.unit ? item.unit.unitkerja : '-'}</td>
                    </tr>
                    `)
                })
                REKAP.sasaran.forEach( item => {
                    $('.idunit').each( function() {
                        if ( $( this ).text() == item.unit_kerja ) {
                            $(this).parent().after(`<tr><td>${item.kd_ss}</td>
                                <td class="sasaran ${item.unit_kerja}">${item.sasaran_program}</td>
                            </tr>`)
                        }
                    } )
                })
                REKAP.sum_sasaran.forEach( item => {
                    $('.sasaran').each(function(){
                        let ss = $(this).text()
                        if(ss == item.sasaran_program && $(this).attr("class").split(" ")[1] == item.unit_kerja ){
                            let ss_td = `<td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_SS}</td>`
                            return $(this).after(ss_td)
                        }
                    })
                })
                REKAP.sum_unit.forEach( item => {
                    $('.unitkerja').each(function(){
                        if($(this).attr("class").split(" ")[1] == item.unit_kerja ){
                            let ss_td = `<td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_SS}</td>`
                            return $(this).after(ss_td)
                        }
                    })
                })
                clearUnwantedRow()
            }
        })

        const clearUnwantedRow = () => {
            $(".idunit").each(function () {
                if ( $(this).text() == "-") {
                    $(this).closest('tr').remove()
                }
            })
            $(".sasaran").each(function () {
                if (!$(this).closest('td').next('td')[0]) {
                    $(this).closest('tr').remove()
                }
            })
        }
    })
</script>