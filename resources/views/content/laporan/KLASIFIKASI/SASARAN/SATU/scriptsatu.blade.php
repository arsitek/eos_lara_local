<script>
    $(document).ready( function () {
        // tata.warn('Warning', 'Laporan sedang dalam perbaikan', { duration: 3000, animate:slide})
        $.ajax({
            type:'GET',
            url:" {{ route('rka.satu.getNonApbn') }} ",
			success:function(data){
                $("#loading-msg").hide()
                let bodyLaporan = $('.body-tbl')
                data.data.kro.forEach((item, index) => {
                    $('.tabel-rekat tr:last').after(`<tr>
                        <td class="${item.kd_ss}">41.${item.kd_ss}</td>
                        <td colspan="8" class="ss-non-apbn">${item.sasaran_program}</td>
                    </tr>
                    `)
                })
                let rowSKL = $('.SKL')
                // ro looping 👇
                data.data.ro.forEach((item, index) => {
                    rowSKL.parent().after(`<tr>
                        <td class="ikk ${item.kode_ikk} SKL">41.SKL.${item.kode_ikk}</td>
                        <td colspan="8" class="ikk_text-non-apbn .${item.indikator_kinerja_kegiatan} =${item.kode_ikk}">${item.indikator_kinerja_kegiatan}</td>
                    </tr>`)
                })
                // kp looping 👇
                data.data.kp.forEach((item, index) => {
                    let kd_ikv = item.kd_ikv.replace(/[A-Za-z]/g, "").substring(0,4)
                    $(".ikk").each(function() {
                        let kro = $(this).attr("class").split(" ")
                        let ikk_td = $(this).attr('class').split(" ")
                        if(kd_ikv == "."+$(this).attr('class').substring(4,7)){
                            $(this).parent().after(`<tr>
                                <td class="kp ${item.kd_ikv} ${kro[2]} ${kro[1]}">41.${kro[2]}.${kro[1]}.${item.kd_ikv.substring(7,8)+'.'}</td>
                                <td colspan="8" class="ikv-non-apbn .${item.ikv} 41.${ikk_td[2]}.${ikk_td[1]}.${item.kd_ikv.substring(7,8)+'.'}">${item.ikv}</td>
                            </tr>`)
                        }
                    })
                })
                // sk looping 👇
                data.data.sk.forEach((item, index) => {
                    $(".kp").each(function() {
                        let kp = $(this).attr('class').split(" ")
                        // let kpTD = $(this).attr('class').substring(7).replace(" ", "").replace(/[TK]/, "")
                        if(kp[1].substring(3) == item.kd_keg_compare){
                            $(this).parent().after(`<tr>
                                <td class="kp ${item.kd_keg}">41.${kp[2]}.${kp[3]}.${kp[1].substring(7,9)}.${item.kd_keg}.</td>
                                <td colspan="8" class="rincian_kegiatan-non-apbn ${kp[2]} ${kp[3]} ${kp[1].substring(7,8)} ${item.kd_keg}">${item.rincian_kegiatan}</td>
                            </tr>`)
                        }
                    })
                })
                data.data.uk.forEach((item, index) => {
                    $(".rincian_kegiatan-non-apbn").each(function() {
                        let rincian_td = $(this).attr("class").split(" ")
                        const kode_rk =  `${rincian_td[2]}.${rincian_td[3]}.${rincian_td[4]}`
                        if( kode_rk == item.kd_rk.replace("[", "").substring(3)){
                            $(this).parent().after(`<tr>
                                <td class="uk-non-apbn ${item.idunit}"style="text-align:left">${item.idunit}</td>
                                <td colspan="8" class="unitkerja-non-apbn =${item.rincian_komponen}=${item.idunit}=${rincian_td[1]}=${rincian_td[2]}=${rincian_td[3]}=${rincian_td[4]}=${item.unit_pelaksana}=${item.id_sub_judul}">${item.unitkerja} | ${item.unit_pelaksana}</td>
                            </tr>`)
                        }
                    })
                })
                // Detail kegiatan looping
                data.data.dk.forEach((item, index) => {
                    $(".unitkerja-non-apbn").each(function() {
                        let uk_td = $(this).attr('class').split("=")
                        let rk = uk_td[1]
                        let uk = uk_td[2]
                        let up = uk_td[7]
                        let kode_rk = `${uk_td[4]}.${uk_td[5]}.${uk_td[6]}`
                        if ( uk == item.unit_kerja && kode_rk == item.kd_rk.substring(3) && item.unit_pelaksana == up) {
                        // if ( kode_rk === item.kd_rk.replace("[", "").substring(3) && item.unit_pelaksana == up) {
                            $(this).parent().after(`<tr>
                                <td style="font-weight:bold">${item.id}</td>
                                <td style="font-weight:bold" colspan="8" class="dk-non-apbn =${item.sub_judul}=${item.unit_kerja}=${item.id_sub_judul}=${uk_td[3]}=${uk_td[4]}=${uk_td[5]}=${item.id_sub_judul}=${uk_td[6]}=${item.id}">${item.sub_judul}</td>
                            </tr>`)
                        }
                    })
                })
                // rekat coa
                data.data.rekat_coa.forEach((item,index) => {
                    $(".dk-non-apbn").each(function(){
                        let detail_td = $(this).attr("class").split("=")
                        let dk = detail_td[1]
                        let uk = detail_td[2]
                        let sj = detail_td[3]
                        let kode = `${detail_td[5]}.${detail_td[6]}.${detail_td[8]}`
                        if( detail_td[9] == item.id && item.id_sub_judul == sj){
                            $(this).parent().after(`<tr class="kk_row">
                                <td>41.${detail_td[4]}.${detail_td[5]}.${detail_td[6]}.${detail_td[8]}.${uk}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                }</td>
                                <td colspan="8" class="jb ~${item.rk_rekat}~${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                }~${item.unit_kerja}~${item.id_sub_judul}">${
                                    item.belanja_keg ? item.belanja_keg : (item.belanja_per) ? item.belanja_per : item.belanja_gdg
                                }</td>
                                <td style="text-align: right;" class="biaya .${item.id_sub_judul}.${item.unit_kerja}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                } -41.${detail_td[4]}.${detail_td[5]}.${detail_td[6]}.${detail_td[8]}"></td>
                                <td></td><td></td><td></td>
                            </tr>`)
                        }
                    })
                })
                // rekat kebutuhan kegiatan
                data.data.rekat_kk.forEach((item, index) => {
                    let bintang = []
                    let html_status = ''
                    if(item.id_belanja_keg != null){
                        bintang = []
                        const verifikasi_array = [item.verifikasi_tim_keg,item.verifikasi_pimpinan_keg,item.verifikasi_univ_keg];
                        let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null || word === "Tolak");
                        for (let i = 0; i < total_ver.length; i++) {
                            bintang.push("*")
                        }
                    }else if(item.id_belanja_per != null){
                        bintang = []
                        const verifikasi_array = [item.verifikasi_tim_per,item.verifikasi_pimpinan_per,item.verifikasi_univ_per];
                        let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null);
                        for (let i = 0; i < total_ver.length; i++) {
                            bintang.push("*")
                        }
                    }else if(item.id_belanja_gdg != null){
                        bintang = []
                        const verifikasi_array = [item.verifikasi_tim_gdg,item.verifikasi_pimpinan_gdg,item.verifikasi_univ_gdg];
                        let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null);
                        for (let i = 0; i < total_ver.length; i++) {
                            bintang.push("*")
                        }
                    }
                    if ( bintang.length !== 0 ) {
                        html_status = "<span class='px-1 py-1 text-white ml-1' style='font-size:9px;background-color:red'>Belum diverifikasi</span>"
                    }
                    $(".jb").each(function(){
                        let belanja_td       = $(this).attr("class").split("~")
                        let rincian_komponen = belanja_td[1]
                        let id_belanja       = belanja_td[2]
                        let uk               = belanja_td[3]
                        let sj               = belanja_td[4]
                        if(item.rk_rekat == rincian_komponen && item.idunit == uk.replace(" ", "") && sj == item.id_sub_judul){
                            if(item.id_belanja_keg == id_belanja || item.id_belanja_per == id_belanja || item.id_belanja_gdg == id_belanja){
                                $(this).parent().after(`<tr class="kk_row">
                                    <td style="text-align:right">-</td>
                                    <td class="kk .${item.detail_kegiatan}.${item.idunit}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg}">${item.kebutuhan_keg ? item.kebutuhan_keg : (item.kebutuhan_per) ? item.kebutuhan_per : item.kebutuhan_gdg} ${html_status}</td>
                                    <td id="kuantitas">${item.kuantitas ? item.kuantitas : (item.qt) ? item.qt : 1}</td>
                                    <td>${item.satuan_kuantitas ? item.satuan_kuantitas : (item.satuan_qt_per) ? item.satuan_qt_per : 'Bangunan'}</td>
                                    <td id="durasi">${item.durasi ? item.durasi : 1}</td>
                                    <td>${item.satuan_durasi ? item.satuan_durasi : 'Pkt'}</td>
                                    <td>${item.kegiatan ? item.kegiatan : 1}</td>
                                    <td>${item.satuan_keg ? item.satuan_keg : 'Keg'}</td>
                                    <td style="text-align: right;">${
                                    item.biaya_satuan ? item.biaya_satuan : (item.biaya_satuan_per) ? item.biaya_satuan_per : item.biaya_satuan_gdg}</td>
                                    <td style="text-align: right;" class="biaya_kk">${
                                    item.total_per ? item.total_per : (item.total_gdg) ? item.total_gdg : item.total_keg
                                    }</td><td></td><td>realisasi</td><td></td></tr>`)
                            }
                        }
                    })
                })
                //the sum looping is begin in this line :
                //1. jenis belanja sum
                data.data.sum_coa.forEach((item, index) => {
                    $(".biaya").each(function(){
                        let biaya_td = $(this).attr("class").split("-")
                        let jb = $(this).attr("class").split(".")[3].split("-")[0].replace(" ", "")
                        let sj = $(this).attr("class").split(".")[1]
                        if ( biaya_td[1] != undefined) {
                            if ( item.kd_rk.replace("[", "").substring(3) == biaya_td[1].substring(7) && item.id_sub_judul == sj) {
                                if(jb == item.id_belanja_keg || jb == item.id_belanja_per || jb == item.id_belanja_gdg){
                                    if(item.total_coa_keg != null){
                                        return $(this).text(item.total_coa_keg)}
                                    else if(item.total_coa_per != null){
                                        return $(this).text(item.total_coa_per)}
                                    else if(item.total_coa_gdg != null){
                                        return $(this).text(item.total_coa_gdg)}
                                }
                            }
                        }
                    })
                })
                //2. Detail kegiatan sum
                data.data.sum_dk.forEach((item, index) => {
                    $(".dk-non-apbn").each(function(){
                        let detail_td = $(this).attr("class").split("=")
                        let uk = detail_td[2]
                        let id_dk = detail_td[3]
                        let kode_sk =  `${detail_td[5]}.${detail_td[6]}.${detail_td[8]}`
                        if(uk == item.unit_kerja && id_dk == item.id_sub_judul && kode_sk == item.kd_rk.replace("[", "").substring(3) ){
                            let dk_td = `<td style="text-align: right;">`
                            if(item.total_dk_per != null){
                                dk_td += `${item.total_dk_per}</td><td>${item.rpd}</td><td></td><td></td>`
                                return $(this).after(dk_td)}
                            else if(item.total_dk_gdg != null){
                                dk_td += `${item.total_dk_gdg}</td><td>${item.rpd}</td><td></td><td></td>`
                                return $(this).after(dk_td)}
                            else if(item.total_dk_keg != null){
                                dk_td += `${item.total_dk_keg}</td><td>${item.rpd}</td><td></td><td></td>`
                                return $(this).after(dk_td)}
                            $(this).after(dk_td)
                        }
                    })
                })
                //3. Unit kerja sum
                data.data.sum_uk.forEach((item,index) => {
                    $('.unitkerja-non-apbn').each(function(){
                        let rincian_td = $(this).attr("class").split("=")
                        let kode_sk = `${rincian_td[4]}.${rincian_td[5]}.${rincian_td[6]}`
                        if( item.kd_rk.replace("[", "").substring(3) ==  kode_sk && item.id_sub_judul == rincian_td[8] && rincian_td[2] == item.unit_kerja ){
                            let uk_td = `<td style="text-align: right;">`
                            if(item.total_uk_per != null){
                                uk_td += `${item.total_uk_per}</td><td></td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            else if(item.total_uk_gdg != null){
                                uk_td += `${item.total_uk_gdg}</td><td></td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            else if(item.total_uk_keg != null){
                                uk_td += `${item.total_uk_keg}</td><td></td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            $(this).after(uk_td);
                        }
                    })
                })
                //4. Rincian komponen sum
                data.data.sum_rk.forEach((item,index) => {
                    $('.rincian_kegiatan-non-apbn').each(function(){
                        let rincian_td = $(this).attr("class").split(" ")
                        let kode_sk = `${rincian_td[2]}.${rincian_td[3]}.${rincian_td[4]}`
                        if ( item.kd_rk.replace("[", "").substring(3) == kode_sk ) {
                            let rk_td = `<td style="text-align: right;">${item.TOTAL_RK}</td><td></td><td></td><td></td>`
                            return $(this).after(rk_td)
                        }
                    })
                })
                //5. Ikv sum
                data.data.sum_ikv.forEach((item, index) => {
                    $('.ikv-non-apbn').each(function(){
                        let ikv = $(this).attr("class").split(" ")
                        if(  ikv[ ikv.length - 1].substring( 7 ) == item.rincian_kegiatan.split("] ")[0].substring( 4 ) + "." ){
                            let ikv_td = `<td style="text-align: right;">${item.TOTAL_IKV}</td><td></td><td></td><td></td>`
                            return $(this).after(ikv_td)
                        }
                    })
                })
                //6. Ikk sum
                data.data.sum_ikk.forEach((item, index) => {
                    $('.ikk_text-non-apbn').each(function(){
                        let ik_td = $(this).attr("class").split("=")
                        // if(ikk_text == item.indikator_kinerja_kegiatan.split("] ")[1]){
                        if( ik_td[1]  == item.indikator_kinerja_kegiatan.split("] ")[0].substring(5) ){
                            let ikk_td = `
                            <td style="text-align: right;">${item.TOTAL_IKK}</td><td></td><td></td><td></td>`
                            return $(this).after(ikk_td)
                        }
                    })
                })
                //7. Sasaran sum
                data.data.sum_ss.forEach((item, index) => {
                    $('.ss-non-apbn').each(function(){
                        let ss = $(this).text()
                        if(ss == item.sasaran_program){
                            let ss_td = `<td style="text-align: right;">${item.TOTAL_SS}</td><td></td><td></td><td></td>`
                            return $(this).after(ss_td)
                        }
                    })
                })
                //8. sum sd
                data.data.sum_sd.forEach(item => {
                    $(".nilai_ptnbh").text( item.TOTAL_SD )
                    $(".non-apbn").after(`<td class="text-right">${item.TOTAL_SD}<td><td></td>`)
                })
                clearUnwantedRow();
                $('.kk_row').each( function() {
                    if(!$(this).closest('tr').next('tr').hasClass("kk_row")){
                        $(this).attr('style',  'border-bottom:2.5px solid black');
                    }
                })
            }   
        })
        function clearUnwantedRow(){
            $(".ss-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                }
            })
            $(".dk-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                }
            })
            $(".ikk_text-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                }
            })
            $(".ikv-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                }
            })
            $(".rincian_kegiatan-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                }
            })
            $(".unitkerja-non-apbn").each(function(){
                if(!$(this).closest('td').next('td')[0]){
                    $(this).closest('tr').remove()
                    }
            })
        }
        $(document).on("click", ".btn-export-xlsx", function() {
            let tgl = new Date().toJSON().slice(0, 10)
            $(".tabel-rekat").table2excel({
                filename: `${tgl}.Laporan_RKT_SASARAN_1.xls`,
            });
        })
    })
</script>
