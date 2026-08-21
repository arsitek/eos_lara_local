<script>
    $( document ).ready( function () {
        const url = window.location.href.split( "rkat" )[1]
        $.ajax({
            type:'GET', url:"/rkat/sync" + url,
            success: function( data ) {
                let rkat = data.data
                $("#loading-msg").hide()
                // 👇 Looping data master ke-1 untuk sumberDana
                $(".tabel-rkat").append(`<tr><td>51</td><td class="non-apbn">Non APBN</td></tr>`)
                // 👇 Looping data master ke-2 untuk kro
                rkat.kro.forEach( item => {
                    $(".tabel-rkat").append(`<tr><td class="kro-${item.kd_kro}">51.${item.kd_kro}</td>
                        <td class="nama_kro .${item.nama_kro}">${item.nama_kro}</td>
                    </tr>`)
                })
                // 👇 Looping rkat master ke-3 untuk ro
                rkat.ro.forEach( item => {
                    if( item.kd_kro === 1){
                        $(".kro-1").parent().after(`<tr><td class="ro ro-1 ${item.kd_ro}">51.1.${item.kd_ro}</td>
                            <td class="nama_ro ro-1 ${item.kd_ro}">${item.nama_ro}</td>
                        </tr>`)
                    }
                    if(item.kd_kro === 2){
                        $(".kro-2").parent().after(`<tr><td class="ro ro-2 ${item.kd_ro}">51.2.${item.kd_ro}</td>
                            <td colspan="8" class="nama_ro ro-2 ${item.kd_ro}">${item.nama_ro}</td>
                        </tr>`)
                    }
                })
                // 👇 Looping data master ke-4 untuk komponen
                rkat.kp.forEach( item => {
                    $(".ro").each(function() {
                        let ro_row    = $( this ).attr("class").split(" ")
                        let jenis_kro = ro_row[1].charAt(3)
                        let jenis_ro  = ro_row[2]
                        if( jenis_kro == item.kd_kro ){
                            if(jenis_ro == item.kd_ro ){
                                $(this).parent().after(`<tr>
                                    <td class="kp ${jenis_kro} ${jenis_ro} ${item.kd_kp}">51.${jenis_kro}.${jenis_ro}.${item.kd_kp}</td>
                                    <td class="nama_kp ${jenis_kro} ${jenis_ro} ${item.kd_kp}">${item.nama_kp}</td>
                                </tr>`)
                            }
                        }
                    })
                })
                // 👇 Looping data master ke-5 untuk sub komponen
                rkat.sk.forEach( item => {
                    $(".kp").each(function() {
                        let kp_row    = $( this ).attr("class").split(" ")
                        let jenis_kro = kp_row[1]
                        let jenis_ro  = kp_row[2]
                        let jenis_kp  = kp_row[3]
                        if(item.kd_kro == jenis_kro){
                            if(item.kd_ro == jenis_ro){
                                if(item.kd_kp == jenis_kp){
                                    $(this).parent().after(`<tr  style="font-weight:bold" >
                                        <td class="sk">51.${item.kd_kro}.${item.kd_ro}.${item.kd_kp}.${item.kd_sk}</td>
                                        <td class="nama_sk ${item.kd_kro} ${item.kd_ro} ${item.kd_kp} ${item.kd_sk} ${item.ekuivalensi}">${item.nama_sk}</td>
                                        <td></td><td></td><td class="biaya_sk ${item.ekuivalensi}"></td>    
                                    </tr>`)
                                }
                            }
                        }
                    })
                })
                // 👇 Looping data rekat ke-1 untuk sub-komponen
                // rkat.rekat_jenis_belanja.forEach((item,index) => {
                //     $(".nama_sk").each(function(){
                //         let sk_row      = $(this).attr("class").split(" ")
                //         let ekuivalensi = sk_row[5]
                //         if( ekuivalensi == item.kd_rk.replace("[", "") ){
                //             $(this).parent().after(`<tr>
                //                 <td>${
                //                 item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                //                 }</td>
                //                 <td class="jb ~${
                //                     item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                //                     }~${item.unit_kerja}~${item.kd_rk}">${
                //                     item.belanja_keg ? item.belanja_keg : (item.belanja_per) ? item.belanja_per : item.belanja_gdg
                //                 }</td><td></td><td></td><td class="biaya ${
                //                     item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                //                     }"></td>
                //             </tr>`)
                //         }
                //     })
                // })
                // rkat.sum_coa.forEach((item, index) => {
                //     $(".biaya").each(function(){
                //         let biaya_row = $(this).attr("class").split(" ")
                //         let jb        = biaya_row[1]
                //         if(jb == item.id_belanja_keg || jb == item.id_belanja_per || jb == item.id_belanja_gdg){
                //             if(item.total_coa_keg != null){
                //                 return $(this).text(item.total_coa_keg)}
                //             else if(item.total_coa_per != null){
                //                 return $(this).text(item.total_coa_per)}
                //             else if(item.total_coa_gdg != null){
                //                 return $(this).text(item.total_coa_gdg)}
                //         }
                //     })
                // })
                // rkat.sum_rincian_kom.forEach((item,index) => {
                //     $('.biaya_sk').each(function(){
                //         let sk_row      = $(this).attr("class").split(" ")
                //         let ekuivalensi = sk_row[1]
                //         if( ekuivalensi == item.kd_rk.replace("[", "") ){
                //             return $(this).text(item.TOTAL_RK)
                //         }
                //     })
                // })
            }
        })
    })
</script>