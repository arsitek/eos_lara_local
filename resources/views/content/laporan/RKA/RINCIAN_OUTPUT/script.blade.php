<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.4.1/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/2.3.5/jspdf.plugin.autotable.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        const url = window.location.pathname.split("/")[3]
        $(".s").select2()
        // tata.warn('Warning', 'Laporan sedang dalam perbaikan', { duration: 3000, animate:slide})
        $.ajax({
            type:'GET',
            url: url === undefined ? " {{ route('rktReport.syncKro') }} " : `/laporan/rkaoutput/get/${url}`,
			success:function(data){
                let bodyLaporan = $('.body-tbl')
                let kro = data.kro
                let ro = data.ro
                let sum_ikk = data.sum_ikk
                let sum_ss = data.sum_ss
                // kro looping 👇
                kro.forEach((item, index) => {
                    $('.tabel-rekat tr:last').after(`<tr class="ss_row" style="font-weight:bold">
                        <td class="${item.kd_ss}">41.${item.kd_ss}</td>
                        <td class="ss">${item.sasaran_program}</td>
                    </tr>
                    `)
                })
                let rowSKL = $('.SKL')
                let rowSKK = $('.SKK')
                let rowSKD = $('.SKD')
                let rowSKT = $('.SKT')
                // ro looping 👇
                ro.forEach((item, index) => {   
                    if(item.kode_ikk.slice(0,1) == "2"){
                         rowSKD.parent().after(`<tr>
                            <td class="ikk ${item.kode_ikk} SKD">41.SKD.${item.kode_ikk}</td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan} =${item.kode_ikk}">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "3"){
                         rowSKK.parent().after(`<tr>
                            <td class="ikk ${item.kode_ikk} SKK">41.SKK.${item.kode_ikk}</td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan} =${item.kode_ikk}">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "4"){
                         rowSKT.parent().after(`<tr>
                            <td class="ikk ${item.kode_ikk} SKT">41.SKT.${item.kode_ikk}</td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan} =${item.kode_ikk}">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "1"){
                         rowSKL.parent().after(`<tr>
                            <td class="ikk ${item.kode_ikk} SKL">41.SKL.${item.kode_ikk}</td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan} =${item.kode_ikk}">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                })
                // //6. Ikk sum
                sum_ikk.forEach((item, index) => {
                    $('.ikk_text').each(function(){
                        let ik_td = $(this).attr("class").split("=")
                        if( ik_td[1]  == item.indikator_kinerja_kegiatan.split("] ")[0].substring(5) ){
                            let ikk_td = `<td></td><td></td><td></td><td style="text-align: right;">${item.TOTAL_IKK}</td><td></td><td></td>`
                            return $(this).after(ikk_td)
                        }
                    })
                })
                // //7. Sasaran sum
                sum_ss.forEach((item, index) => {
                    $('.ss').each(function(){
                        let ss = $(this).text()
                        if(ss == item.sasaran_program){
                            let ss_td = `<td></td><td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_SS}</td><td></td><td></td>`
                            return $(this).after(ss_td)
                        }
                    })
                })
                // //8. sum sd
                data.sum_sd.forEach(item => {
                    $(".non-apbn").after(`<td></td><td></td><td></td><td style="text-align:right">${item.TOTAL_SD}<td><td></td>`)
                })
                clearUnwantedRow()
                $('.ss_row').each( function() {
                    if(!$(this).closest('tr').next('tr').hasClass("kk_row")){
                        $(this).attr('style',  'border-top:2.5px solid black');
                    }
                })
            }
        })
        function clearUnwantedRow(){
                $(".ss").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".ikk_text").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
        }
        $(document).on("click", ".btn-export-xlsx", function(){
            let tgl = new Date().toJSON().slice(0, 10)
            $(".tabel-rekat").table2excel({
                filename: `${tgl}.Laporan RKT.xls`,
            });
        })
        // ✅ onclick button serach by unitkerja
        $( document ).on("click", ".btn-filter-unitkerja", function(){
            const idunit = $("select.unit_kerja").val()
            if ( idunit === "" ) {
                return tata.warn("Perhatian", "Anda belum memilih unitkerja")
            }
            window.open(`/laporan/rkaoutput/${idunit}`, "_blank")
        })
    })
</script>
