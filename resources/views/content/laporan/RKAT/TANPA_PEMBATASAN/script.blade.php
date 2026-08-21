<script>
    $(document).ready(function () {
        $("#loading-msg").hide()
        const url = window.location.href.split("/")
        const url_destination = url[5] + '/' + url[6]
        $.ajax({
            type: 'GET',
            url: "/rkat/tanpapembatasan/sync/" + url_destination,
            success: function (data) {
                let rkat = data.data
                // 👇 Looping rkat master ke-3 untuk ro
                rkat.ro.forEach(item => {
                    $(".tabel-rkat").append(`<tr><td class="ro ro-1 ${item.kd_ro}">41.0${item.kd_kro}.0${item.kd_ro}</td>
                            <td class="nama_ro ${item.kd_ro}">${item.nama_ro}</td>
                        </tr>`)
                })
                // 👇 Looping rkat master untuk komponen
                rkat.kp.forEach(item => {
                    $(".ro").each(function () {
                        $(this).parent().after(`<tr style="font-weight:bold; font-size:16px">
                                <td class="kp ${item.kd_kp}">41.0${item.kd_kro}.0${item.kd_ro}.0${item.kd_kp}</td>
                                <td class="nama_kp ${item.kd_kp}">${item.nama_kp}</td>
                            </tr>`)
                    })
                })
                // 👇 Looping data master untuk sub komponen
                rkat.sk.forEach(item => {
                    $(".kp").each(function () {
                        let kp_row = $(this).attr("class").split(" ")
                        let jenis_kp = kp_row[1]
                        if (item.kd_kp == jenis_kp) {
                            $(this).parent().after(`<tr style="font-weight: bold">
                                    <td class="sk ${item.ekuivalensi}">41.0${item.kd_kro}.0${item.kd_ro}.0${item.kd_kp}.0${item.kd_sk}</td>
                                    <td class="nama_sk ${item.ekuivalensi}">${item.nama_sk}</td>
                                </tr>`)
                        }
                    })
                })
                // Looping data rekat untuk detail kegiatan
                rkat.rekat_dk.forEach((item, index) => {
                    $(".sk").each(function () {
                        let sk_row = $(this).attr('class').split(" ")
                        let kd_rk = sk_row[1]
                        if (item.kd_rk == kd_rk) {
                            $(this).parent().after(`<tr>
                                    <td>${item.id}</td>
                                    <td class="dk ~${item.sub_judul}~${item.kd_rk}~${item.id}">${item.sub_judul}</td>
                                </tr>`)
                        }
                    })
                })
                // sum
                rkat.sum_dk.forEach((item, index) => {
                    $(".dk").each(function () {
                        let dk_row = $(this).attr("class").split("~")
                        let sj = dk_row[1]
                        let kd_rk = dk_row[2]
                        let id = dk_row[3]
                        if (id == item.id) {
                            let dk_td = `<td>1`
                            if (item.total_dk_per != null) {
                                dk_td +=
                                    `</td><td>Paket</td><td style="text-align: right;">${item.total_dk_per}</td>`
                                return $(this).after(dk_td)
                            } else if (item.total_dk_gdg != null) {
                                dk_td +=
                                    `</td><td>Paket</td><td style="text-align: right;">${item.total_dk_gdg}</td>`
                                return $(this).after(dk_td)
                            } else if (item.total_dk_keg != null) {
                                dk_td +=
                                    `</td><td>Paket</td><td style="text-align: right;">${item.total_dk_keg}</td>`
                                return $(this).after(dk_td)
                            }
                            $(this).after(dk_td)
                        }
                    })
                })
                rkat.sum_rk.forEach((item, index) => {
                    console.log(item)
                    $('.nama_sk').each(function () {
                        let kd_rk = $(this).attr("class").split(" ")[1]
                        if (item.kd_rk == kd_rk) {
                            let rk_td =
                                `<td>1</td><td>Paket</td><td style="text-align: right;">${item.TOTAL_RK}</td>`
                            return $(this).after(rk_td)
                        }
                    })
                })
                rkat.sum_kp.forEach((item, index) => {
                    $('.nama_kp').each(function () {
                        let komponen = $(this).attr("class").split(" ")[1]
                        if (komponen == item.kd_kp) {
                            let kp_td =
                                `<td>1</td><td>Paket</td><td style="text-align: right;">${item.TOTAL_KP}</td>`
                            return $(this).after(kp_td)
                        }
                    })
                })
                rkat.sum_ro.forEach((item, index) => {
                    $(".nama_ro").each(function () {
                        let rincian_output = $(this).attr("class").split(" ")
                        let ro = rincian_output[1]
                        if (ro == item.kd_ro) {
                            let ro_td =
                                `<td>1</td><td>Paket</td><td style="text-align: right;">${item.TOTAL_RO}</td>`
                            return $(this).after(ro_td)
                        }
                    })
                })
                // clearUnwantedRow()   
                $("#loading-spin").hide()
                $(".loading-msg").hide()
            }
        })
        //         // 👇 Looping data master ke-1 untuk sumberDana
        //         $(".tabel-rkat").append(`<tr><td>51</td><td class="non-apbn" colspan="8">Non APBN</td></tr>`)
        //         // 👇 Looping data master ke-2 untuk kro
        //         // rkat.kro.forEach( item => {
        //         //     $(".tabel-rkat").append(`<tr><td class="kro-${item.kd_kro}">51.${item.kd_kro}</td>
        //         //         <td colspan="8" class="nama_kro .${item.nama_kro}">${item.nama_kro}</td>
        //         //     </tr>`)
        //         // })
        //         
        //         // // 👇 Looping data rekat ke-1 untuk unitkerja
        //         // rkat.rekat_unitkerja.forEach( item => {
        //         //     let kd_rk = item.kd_rk.replace("[", "")
        //         //     $(".nama_sk").each( function(){
        //         //         let sk_row = $( this ).attr("class").split(" ")
        //         //         if( sk_row[5] == kd_rk ){
        //         //             $(this).parent().after(`<tr><td class="idunit ${item.uk_rekat}">${item.uk_rekat}</td>
        //         //                 <td colspan="8" class="unitkerja ${item.uk_rekat} ${kd_rk} ${sk_row[3]} ${item.unit_pelaksana}">
        //         //                 ${item.unitkerja} | ${item.unit_pelaksana}</td>
        //         //             </tr>`)
        //         //         }
        //         //     })
        //         // })
        //         // // 👇 Looping data rekat ke-2 untuk detail kegiatan
        //         // rkat.rekat_detail_kegiatan.forEach((item, index) => {
        //         //     let kd_rk_db = item.kd_rk.replace("[", "")
        //         //     $(".unitkerja").each(function() {
        //         //         let unit_row = $(this).attr('class').split(" ")
        //         //         let uk       = unit_row[1]
        //         //         let kd_rk    = unit_row[2]
        //         //         let kd_kp    = unit_row[3]
        //         //         let up       = unit_row[4]
        //         //         if(uk == item.unit_kerja && kd_rk_db == kd_rk && up == item.unit_pelaksana){
        //         //             $(this).parent().after(`<tr>
        //         //                 <td style="font-weight: bold">${item.id}</td>
        //         //                 <td colspan="8" class="dk ${item.sub_judul} ${item.unit_kerja} ${item.id_sub_judul} ${kd_rk_db} ${item.id} ${kd_kp}">${item.sub_judul}</td>
        //         //             </tr>`)
        //         //         }
        //         //     })
        //         // })
        //         // 👇 Looping data rekat ke-3 untuk jenis belanja
        //         // rkat.rekat_jenis_belanja.forEach((item,index) => {
        //         //     $(".dk").each(function(){
        //         //         let dk_row   = $(this).attr("class").split(" ")
        //         //         let uk       = dk_row[2]
        //         //         let dk       = dk_row[1]
        //         //         let kd_rk    = dk_row[4]
        //         //         let id_rekat = dk_row[5]
        //         //         let kd_kp    = dk_row[6]
        //         //         let codebase = item.kd_rk.substring(3,4) + "." + item.kd_rk.substring(5,6) + "." + kd_kp + "." + item.kd_rk.slice(-1)
        //         //         if(uk == item.unit_kerja && dk == item.sub_judul && kd_rk == item.kd_rk){
        //         //             $(this).parent().after(`<tr>
        //         //                 <td>51.${codebase}.${uk}.${
        //         //                     item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
        //         //                 }</td>
        //         //                 <td colspan="8" class="jb ~${
        //         //                     item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
        //         //                 }~${item.unit_kerja}~${item.kd_rk}">${
        //         //                     item.belanja_keg ? item.belanja_keg : (item.belanja_per) ? item.belanja_per : item.belanja_gdg
        //         //                 }</td>
        //         //                 <td style="text-align:right" class="biaya ~${item.id_sub_judul}~${item.unit_kerja}~${
        //         //                     item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
        //         //                 }~${item.kd_rk}"></td><td></td><td></td><td></td>
        //         //             </tr>`)
        //         //         }
        //         //     })
        //         // })
        //         // 👇 Looping data rekat ke-4 untuk kebutuhan kegiatan
        //         // rkat.rekat_kebutuhan_keg.forEach((item, index) => {
        //         //     let bintang = []
        //         //     let html_status = null
        //         //     if(item.id_belanja_keg != null){
        //         //         bintang = []
        //         //         const verifikasi_array = [item.verifikasi_tim_keg,item.verifikasi_pimpinan_keg,item.verifikasi_univ_keg];
        //         //         let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null || word === "Tolak");
        //         //         for (let i = 0; i < total_ver.length; i++) {
        //         //             bintang.push("*")
        //         //         }
        //         //     }else if(item.id_belanja_per != null){
        //         //         bintang = []
        //         //         const verifikasi_array = [item.verifikasi_tim_per,item.verifikasi_pimpinan_per,item.verifikasi_univ_per];
        //         //         let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null);
        //         //         for (let i = 0; i < total_ver.length; i++) {
        //         //             bintang.push("*")
        //         //         }
        //         //     }else if(item.id_belanja_gdg != null){
        //         //         bintang = []
        //         //         const verifikasi_array = [item.verifikasi_tim_gdg,item.verifikasi_pimpinan_gdg,item.verifikasi_univ_gdg];
        //         //         let total_ver = verifikasi_array.filter(word => word === "SILAHKAN PILIH" || word === null);
        //         //         for (let i = 0; i < total_ver.length; i++) {
        //         //             bintang.push("*")
        //         //         }
        //         //     }
        //         //     if ( bintang.length !== 0 ) {
        //         //         html_status = "<span class='px-1 py-1 text-white ml-1' style='font-size:9px;background-color:red'>Belum diverifikasi</span>"
        //         //     }
        //         //     $(".jb").each(function(){
        //         //         let coa_row          = $(this).attr("class").split("~")
        //         //         let id_belanja       = coa_row[1]
        //         //         let uk               = coa_row[2]
        //         //         let kd_rk            = coa_row[3]
        //         //         if(item.kd_rk == kd_rk && item.idunit == uk){
        //         //             if(item.id_belanja_keg == id_belanja || item.id_belanja_per == id_belanja || item.id_belanja_gdg == id_belanja){
        //         //                 $(this).parent().after(`<tr class="kk_row">
        //         //                 <td style="text-align:right">-</td>
        //         //                 <td class="kk .${item.detail_kegiatan}.${item.idunit}.${
        //         //                 item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg}">${item.kebutuhan_keg ? item.kebutuhan_keg : (item.kebutuhan_per) ? item.kebutuhan_per : item.kebutuhan_gdg} ${html_status}</td>
        //         //                 <td id="kuantitas">${item.kuantitas ? item.kuantitas : (item.qt) ? item.qt : 1}</td>
        //         //                 <td>${item.satuan_kuantitas ? item.satuan_kuantitas : (item.satuan_qt_per) ? item.satuan_qt_per : 'Bangunan'}</td>
        //         //                 <td id="durasi">${item.durasi ? item.durasi : 1}</td>
        //         //                 <td>${item.satuan_durasi ? item.satuan_durasi : 'Pkt'}</td>
        //         //                 <td>${item.kegiatan ? item.kegiatan : 1}</td>
        //         //                 <td>${item.satuan_keg ? item.satuan_keg : 'Keg'}</td>
        //         //                 <td style="text-align: right;">${
        //         //                 item.biaya_satuan ? item.biaya_satuan : (item.biaya_satuan_per) ? item.biaya_satuan_per : item.biaya_satuan_gdg}</td>
        //         //                 <td style="text-align: right;" class="biaya_kk">${
        //         //                 item.total_per ? item.total_per : (item.total_gdg) ? item.total_gdg : item.total_keg
        //         //                 }</td><td></td><td>realisasi</td><td></td></tr>`)
        //         //             }
        //         //         }
        //         //     })
        //         // })
        //         // ✅ Looping data untuk penjumlahan biaya
        //         // 👇 Jenis belanja
        //         // rkat.sum_coa.forEach((item, index) => {
        //         //     $(".biaya").each(function(){
        //         //         let biaya_row = $(this).attr("class").split("~")
        //         //         let dk        = biaya_row[1]
        //         //         let uk        = biaya_row[2]
        //         //         let jb        = biaya_row[3]
        //         //         let kd_rk     = biaya_row[4]
        //         //         if(dk == item.id_sub_judul && uk == item.unit_kerja && kd_rk == item.kd_rk){
        //         //             if(jb == item.id_belanja_keg || jb == item.id_belanja_per || jb == item.id_belanja_gdg){
        //         //                 if(item.total_coa_keg != null){
        //         //                     return $(this).text(item.total_coa_keg)}
        //         //                 else if(item.total_coa_per != null){
        //         //                     return $(this).text(item.total_coa_per)}
        //         //                 else if(item.total_coa_gdg != null){
        //         //                     return $(this).text(item.total_coa_gdg)}
        //         //             }
        //         //         }
        //         //     })
        //         // })
        //         // 👇 Detail kegiatan
        //         // rkat.sum_detail_keg.forEach((item, index) => {
        //         //     $(".dk").each(function(){
        //         //         let dk_row = $(this).attr("class").split(".")
        //         //         let dk     = dk_row[1]
        //         //         let uk     = dk_row[2]
        //         //         let id_dk  = dk_row[3]
        //         //         if(uk == item.unit_kerja && id_dk == item.id_sub_judul){
        //         //             let dk_td = `<td style="text-align: right;">`
        //         //             if(item.total_dk_per != null){
        //         //                 dk_td += `${item.total_dk_per}</td><td>${item.rpd}</td><td></td><td></td>`
        //         //                 return $(this).after(dk_td)}
        //         //             else if(item.total_dk_gdg != null){
        //         //                 dk_td += `${item.total_dk_gdg}</td><td>${item.rpd}</td><td></td><td></td>`
        //         //                 return $(this).after(dk_td)}
        //         //             else if(item.total_dk_keg != null){
        //         //                 dk_td += `${item.total_dk_keg}</td><td>${item.rpd}</td><td></td><td></td>`
        //         //                 return $(this).after(dk_td)}
        //         //             $(this).after(dk_td)
        //         //         }
        //         //     })
        //         // })
        //     }
        // })
        //       
        //         // // 👇 Unit kerja
        //         // data.sum_unitkerja.forEach((item,index) => {
        //         //     $('.unitkerja').each(function(){
        //         //         let rincian_komponen = $(this).attr("class").split(".")[1]
        //         //         let unitkerja = $(this).attr("class").split(".")[2]
        //         //         if(unitkerja == item.unit_kerja && rincian_komponen == item.rincian_komponen){
        //         //             let uk_td = `<td style="text-align: right;">`
        //         //             if(item.total_uk_per != null){
        //         //                 uk_td += `${item.total_uk_per}</td><td></td><td></td><td></td>`
        //         //                 return $(this).after(uk_td)}
        //         //             else if(item.total_uk_gdg != null){
        //         //                 uk_td += `${item.total_uk_gdg}</td><td></td><td></td><td></td>`
        //         //                 return $(this).after(uk_td)}
        //         //             else if(item.total_uk_keg != null){
        //         //                 uk_td += `${item.total_uk_keg}</td><td></td><td></td><td></td>`
        //         //                 return $(this).after(uk_td)}
        //         //             $(this).after(uk_td);
        //         //         }
        //         //     })
        //         // })
        //         // // 👇 Sub komponen
        //         // data.sum_rincian_kom.forEach((item,index) => {
        //         //     $('.nama_sk').each(function(){
        //         //         let rincian_komponen = $(this).text()
        //         //         if(rincian_komponen == item.rincian_komponen){
        //         //             let rk_td = `<td style="text-align: right;">${item.TOTAL_RK}</td><td></td><td></td><td></td>`
        //         //             return $(this).after(rk_td)
        //         //         }
        //         //     })
        //         // })
        //         // // 👇 komponen
        //         // data.sum_komponen.forEach((item, index) => {
        //         //     $('.nama_kp').each(function(){
        //         //         let komponen = $(this).attr("class").split(" ")
        //         //         if(komponen[2] === item.kd_ro){
        //         //             if(komponen[3].substr(1) === item.kd_kp){
        //         //                 let kp_td = `<td style="text-align: right;">${item.TOTAL_KP}</td><td></td><td></td><td></td>`
        //         //                 return $(this).after(kp_td)
        //         //             }
        //         //         }
        //         //     })
        //         // })
        //         // data.sum_rincian_out.forEach((item, index) => {
        //         //     $(".nama_ro").each(function(){
        //         //         let rincian_output = $(this).attr("class").split(" ")
        //         //         let kro            = rincian_output[1].substr(3)
        //         //         let ro             = rincian_output[2]
        //         //         if(kro === item.kd_kro){
        //         //             if(ro === item.kd_ro){
        //         //                let ro_td = `<td style="text-align: right;">${item.TOTAL_RO}</td><td></td><td></td><td></td>`
        //         //                 return $(this).after(ro_td)
        //         //             }
        //         //         }
        //         //     })
        //         // })
        //         // data.sum_kro.forEach((item) => {
        //         //     $(".nama_kro").each(function(){
        //         //         let kro = $(this).attr("class").split(".")
        //         //         if(kro[1] === item.nama_kro){
        //         //             let kro_td = `<td style="text-align: right;">${item.TOTAL_KRO}</td>
        //         //             <td></td><td></td><td></td>`
        //         //             return $(this).after(kro_td)
        //         //         }
        //         //     })
        //         // })
        //         // // // //8. sum sd
        //         // data.sum_sd.forEach(item => {
        //         //     $(".non-apbn").after(`<td style="text-align:right">${item.TOTAL_SD}<td><td></td><td></td>`)
        //         // })
        //         // hapus baris yang kosong
        //         // clearUnwantedRow();
        //     },
        // 	error:function(error){
        //         console.log( error )
        //     },
        // }) // end ajax
        function clearUnwantedRow() {
            // $(".nama_kro").each(function(){
            //     if($(this).closest('td').next('td')[0] === undefined){
            //         $(this).closest('tr').remove()
            //     }
            // })
            // $(".dk").each(function(){
            //     if($(this).closest('td').next('td')[0] === undefined){
            //         $(this).closest('tr').remove()
            //     }
            // })
            // $(".nama_ro").each(function(){
            //     if($(this).closest('td').next('td')[0] === undefined){
            //         $(this).closest('tr').remove()
            //     }
            // })
            // $(".nama_kp").each(function(){
            //     if($(this).closest('td').next('td')[0] === undefined){
            //         $(this).closest('tr').remove()
            //     }
            // })
            $(".nama_sk").each(function () {
                if ($(this).closest('td').next('td')[0] === undefined) {
                    $(this).closest('tr').remove()
                }
            })
        }
    }) // end document ready

</script>
