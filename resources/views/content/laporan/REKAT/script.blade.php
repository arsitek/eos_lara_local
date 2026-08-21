<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            type:'GET',
            url:" {{ route('rktReport.syncKro') }} ",
			success:function(data){
                let bodyLaporan = $('.body-tbl')
                let kro = data.kro
                let ro = data.ro
                let kp = data.kp
                let sk = data.sk
                let uk = data.unit_kerja
                let rekat_coa = data.rekat_coa
                let rekat_kk = data.rekat_kk
                let coa = data.coa
                let sum_dk = data.sum_detail_kegiatan
                let sum_uk = data.sum_unit_kerja
                let sum_rk = data.sum_rincian_komponen
                let sum_ikv = data.sum_ikv
                let sum_ikk = data.sum_ikk
                let sum_ss = data.sum_ss

                // kro looping 👇
                kro.forEach((item, index) => {
                    $('.tabel-rekat tr:last').after(`
                    <tr>
                            <td></td>
                            <td colspan="7" class="${item.kd_ss}">${item.kd_ss}</td>
                            <td></td>
                            <td colspan="8" class="ss ">${item.sasaran_program}</td>
                    </tr>
                    `)
                })
                
                let rowSKL = $('.SKL')
                let rowSKK = $('.SKK')
                let rowSKD = $('.SKD')
                let rowSTK = $('.STK')
                
                // ro looping 👇
                ro.forEach((item, index) => {   
                    if(item.kode_ikk.slice(0,1) == "2"){
                         rowSKD.parent().after(`<tr>
                            <td></td>
                            <td></td>
                            <td class="ikk ${item.kode_ikk}" colspan="6">${item.kode_ikk}</td>
                            <td></td>
                            <td></td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan}" colspan="7">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "3"){
                         rowSKK.parent().after(`<tr>
                            <td></td>
                            <td></td>
                            <td class="ikk ${item.kode_ikk}" colspan="6">${item.kode_ikk}</td>
                            <td></td>
                            <td></td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan}" colspan="7">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "4"){
                         rowSTK.parent().after(`<tr>
                            <td></td>
                            <td></td>
                            <td class="ikk ${item.kode_ikk}" colspan="6">${item.kode_ikk}</td>
                            <td></td>
                            <td></td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan}" colspan="7">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                    if(item.kode_ikk.slice(0,1) == "1"){
                         rowSKL.parent().after(`<tr>
                            <td></td>
                            <td></td>
                            <td class="ikk ${item.kode_ikk}" colspan="6">${item.kode_ikk}</td>
                            <td></td>
                            <td></td>
                            <td class="ikk_text .${item.indikator_kinerja_kegiatan}" colspan="7">${item.indikator_kinerja_kegiatan}</td>
                        </tr>`)
                    }
                })

                // kp looping 👇
                kp.forEach((item, index) => {
                    let kd_ikv = item.kd_ikv.replace(/[A-Za-z]/g, "").substring(0,4)
                    $(".ikk").each(function() {
                        if(kd_ikv == "."+$(this).attr('class').substring(4,7)){
                            $(this).parent().after(`<tr>
                                <td></td>    
                                <td></td>    
                                <td></td>    
                                <td class="kp |${item.kd_ikv}" colspan="5">${item.kd_ikv.substring(7,8)+'.'}</td>
                                <td></td>    
                                <td></td>    
                                <td style="width:40px"></td>    
                                <td colspan="6" class="ikv .${item.ikv}">${item.ikv}</td>
                            </tr>`)
                        }
                    })
                })

                // sk looping 👇
                sk.forEach((item, index) => {
                    $(".kp").each(function() {
                        let kpTD = $(this).attr('class').substring(7).replace(" ", "").replace(/[TK]/, "")
                        if(kpTD == item.kd_keg_compare +'.'){
                            $(this).parent().after(`<tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="4" class="kp ${item.kd_keg}">${item.kd_keg}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="5" class="rincian_kegiatan">${item.rincian_kegiatan}</td>
                            </tr>`)
                        }
                    })
                })
                //unitkerja looping
                uk.forEach((item, index) => {
                    $(".rincian_kegiatan").each(function() {
                        if(item.rincian_komponen == $(this).text()){
                            $(this).parent().after(`<tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="3" class="uk ${item.idunit}">${item.idunit}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="width:40px"></td>
                                <td colspan="4" class="unitkerja .${item.rincian_komponen}.${item.idunit}">${item.unitkerja} | ${item.unit_pelaksana}</td>
                            </tr>`)
                        }
                    })
                })
                // Detail kegiatan looping
                data.detail_kegiatan.forEach((item, index) => {
                    $(".unitkerja").each(function() {
                        let rk = $(this).attr('class').split(".")[1]
                        let uk = $(this).attr('class').split(".")[2]
                        if(uk == item.unit_kerja && item.rincian_komponen == rk){
                            $(this).parent().after(`<tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="2">${item.id_sub_judul}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="width:40px"></td>
                                <td style="width:40px"></td>
                                <td colspan="3" class="dk .${item.sub_judul}.${item.unit_kerja}.${item.id_sub_judul}">${item.sub_judul}</td>
                                </tr>`)
                        }
                        
                    })
                })
                // rekat coa
                rekat_coa.forEach((item,index) => {
                    $(".dk").each(function(){
                        let uk = $(this).attr("class").split(".")[2]
                        let dk = $(this).attr("class").split(".")[1]
                        if(uk == item.unit_kerja && dk == item.sub_judul){
                            $(this).parent().after(`<tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="1">${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                }</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="width:40px"></td>
                                <td style="width:40px"></td>
                                <td style="width:40px"></td>
                                <td colspan="2" class="jb .${item.rk_rekat}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                }.${item.unit_kerja}">${
                                    item.belanja_keg ? item.belanja_keg : (item.belanja_per) ? item.belanja_per : item.belanja_gdg
                                }</td>
                                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                <td style="text-align: right;" class="biaya .${item.id_sub_judul}.${item.unit_kerja}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg
                                }"></td>
                                <td></td><td></td>
                            </tr>`)
                        }
                    })
                })

                rekat_kk.forEach((item, index) => {
                    $(".jb").each(function(){
                        let rincian_komponen = $(this).attr("class").split(".")[1]
                        let id_belanja = $(this).attr("class").split(".")[2]
                        let uk = $(this).attr("class").split(".")[3]
                        let bintang = ""
                        if(item.verifikasi_tim_keg == "SILAHKAN PILIH" && item.verifikasi_pimpinan_keg == "SILAHKAN PILIH" && item.verifikasi_univ_keg == "SILAHKAN PILIH"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_keg == "Setuju" && item.verifikasi_pimpinan_keg == "Setuju" && item.verifikasi_univ_keg == "Setuju"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_keg == "Tolak" && item.verifikasi_pimpinan_keg == "Tolak" && item.verifikasi_univ_keg == "Tolak"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_keg == "Tolak" && item.verifikasi_pimpinan_keg == "Setuju" && item.verifikasi_univ_keg == "Setuju"){
                            bintang = "**"
                        }else if(item.verifikasi_tim_keg == "Tolak" && item.verifikasi_pimpinan_keg == "Tolak" && item.verifikasi_univ_keg == "Setuju"){
                            bintang = "*"
                        }else if(item.verifikasi_tim_keg == "NULL" && item.verifikasi_pimpinan_keg == "NULL" && item.verifikasi_univ_keg == "NULL"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_per == "Setuju" && item.verifikasi_pimpinan_per == "Setuju" && item.verifikasi_univ_per == "Setuju"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_per == "SILAHKAN PILIH" && item.verifikasi_pimpinan_per == "SILAHKAN PILIH" && item.verifikasi_univ_per == "SILAHKAN PILIH"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_per == "Tolak" && item.verifikasi_pimpinan_per == "Tolak" && item.verifikasi_univ_per == "Tolak"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_per == "Tolak" && item.verifikasi_pimpinan_per == "Setuju" && item.verifikasi_univ_per == "Setuju"){
                            bintang = "**"
                        }else if(item.verifikasi_tim_per == "Tolak" && item.verifikasi_pimpinan_per == "Tolak" && item.verifikasi_univ_per == "Setuju"){
                            bintang = "*"
                        }else if(item.verifikasi_tim_per == "NULL" && item.verifikasi_pimpinan_per == "NULL" && item.verifikasi_univ_per == "NULL"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_gdg == "Setuju" && item.verifikasi_pimpinan_gdg == "Setuju" && item.verifikasi_univ_gdg == "Setuju"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_gdg == "SILAHKAN PILIH" && item.verifikasi_pimpinan_gdg == "SILAHKAN PILIH" && item.verifikasi_univ_gdg == "SILAHKAN PILIH"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_gdg == "Tolak" && item.verifikasi_pimpinan_gdg == "Tolak" && item.verifikasi_univ_gdg == "Tolak"){
                            bintang = "***"
                        }else if(item.verifikasi_tim_gdg == "Tolak" && item.verifikasi_pimpinan_gdg == "Setuju" && item.verifikasi_univ_gdg == "Setuju"){
                            bintang = "**"
                        }else if(item.verifikasi_tim_gdg == "Tolak" && item.verifikasi_pimpinan_gdg == "Tolak" && item.verifikasi_univ_gdg == "Setuju"){
                            bintang = "*"
                        }else if(item.verifikasi_tim_gdg == "NULL" && item.verifikasi_pimpinan_gdg == "NULL" && item.verifikasi_univ_gdg == "NULL"){
                            bintang = "***"
                        }
                        else{
                            bintang = ""
                        }
                        if(item.rk_rekat == rincian_komponen && item.idunit == uk.replace(" ", "")){
                            if(item.id_belanja_keg == id_belanja || item.id_belanja_per == id_belanja || item.id_belanja_gdg == id_belanja){
                                $(this).parent().after(`<tr>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    
                                    <td></td><td></td><td></td><td></td>
                                    <td style="width:40px"></td><td style="width:40px"></td><td style="width:40px"></td>
                                    <td style="width:40px"></td>
                                    <td colspan="1" class="kk .${item.detail_kegiatan}.${item.idunit}.${
                                    item.id_belanja_keg ? item.id_belanja_keg : (item.id_belanja_per) ? item.id_belanja_per : item.id_belanja_gdg}">${item.kebutuhan_keg ? item.kebutuhan_keg+bintang : (item.kebutuhan_per) ? item.kebutuhan_per+bintang : item.kebutuhan_gdg+bintang}</td>
                                    <td id="kuantitas">${item.kuantitas ? item.kuantitas : (item.qt) ? item.qt : 1}</td>
                                    <td>${item.satuan_kuantitas ? item.satuan_kuantitas : (item.satuan_qt_per) ? item.satuan_qt_per : 'Orang'}</td>
                                    <td id="durasi">${item.durasi ? item.durasi : 1}</td>
                                    <td>${item.satuan_durasi ? item.satuan_durasi : 'Hari'}</td>
                                    <td>${item.kegiatan ? item.kegiatan : 1}</td>
                                    <td>${item.satuan_kegiatan ? item.satuan_kegiatan : 'Pkt'}</td>
                                    <td>${(item.kuantitas * item.durasi * item.kegiatan) ? (item.kuantitas * item.durasi * item.kegiatan) : 1}</td>
                                    <td>Pkt</td>
                                    <td style="text-align: right;">${
                                    item.biaya_satuan ? item.biaya_satuan : (item.biaya_satuan_per) ? item.biaya_satuan_per : item.biaya_satuan_gdg}</td>
                                    <td style="text-align: right;" class="biaya_kk">${
                                    item.total_per ? item.total_per : (item.total_gdg) ? item.total_gdg : item.total_keg
                                    }</td>
                                    <td></td>
                                    <td>realisasi</td></tr>`)
                            }
                        }
                    })
                })

                // the sum looping is begin in this line :
                // 1. jenis belanja sum
                coa.forEach((item, index) => {
                    $(".biaya").each(function(){
                        let dk = $(this).attr("class").split(".")[1]
                        let uk = $(this).attr("class").split(".")[2]
                        let jb = $(this).attr("class").split(".")[3]
                        if(dk == item.id_sub_judul && uk == item.unit_kerja){
                            if(jb == item.id_belanja_keg || jb == item.id_belanja_per || jb == item.id_belanja_gdg){
                                if(item.total_coa_keg != null){
                                    return $(this).text(item.total_coa_keg)}
                                else if(item.total_coa_per != null){
                                    return $(this).text(item.total_coa_per)}
                                else if(item.total_coa_gdg != null){
                                    return $(this).text(item.total_coa_gdg)}
                            }
                        }
                    })
                })

                //2. Detail kegiatan sum
                sum_dk.forEach((item, index) => {
                    $(".dk").each(function(){
                        let dk = $(this).attr("class").split(".")[1]
                        let uk = $(this).attr("class").split(".")[2]
                        let id_dk = $(this).attr("class").split(".")[3]
                        if(uk == item.unit_kerja && id_dk == item.id_sub_judul){
                            let dk_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">`
                            if(item.total_dk_per != null){
                                dk_td += `${item.total_dk_per}</td><td>${item.rpd}</td><td></td>`
                                return $(this).after(dk_td)}
                            else if(item.total_dk_gdg != null){
                                dk_td += `${item.total_dk_gdg}</td><td>${item.rpd}</td><td></td>`
                                return $(this).after(dk_td)}
                            else if(item.total_dk_keg != null){
                                dk_td += `${item.total_dk_keg}</td><td>${item.rpd}</td><td></td>`
                                return $(this).after(dk_td)}
                            $(this).after(dk_td)
                        }
                    })
                })

                //3. Unit kerja sum
                sum_uk.forEach((item,index) => {
                    $('.unitkerja').each(function(){
                        let rincian_komponen = $(this).attr("class").split(".")[1]
                        let unitkerja = $(this).attr("class").split(".")[2]
                        if(unitkerja == item.unit_kerja && rincian_komponen == item.rincian_komponen){
                            let uk_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">`
                            if(item.total_uk_per != null){
                                uk_td += `${item.total_uk_per}</td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            else if(item.total_uk_gdg != null){
                                uk_td += `${item.total_uk_gdg}</td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            else if(item.total_uk_keg != null){
                                uk_td += `${item.total_uk_keg}</td><td></td><td></td>`
                                return $(this).after(uk_td)}
                            $(this).after(uk_td);
                        }
                    })
                })

                //4. Rincian komponen sum
                sum_rk.forEach((item,index) => {
                    $('.rincian_kegiatan').each(function(){
                        let rincian_komponen = $(this).text()
                        if(rincian_komponen == item.rincian_komponen){
                            let rk_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_RK}</td><td></td><td></td>`
                            return $(this).after(rk_td)
                        }
                    })
                })

                //5. Ikv sum
                sum_ikv.forEach((item, index) => {
                    $('.ikv').each(function(){
                        let ikv = $(this).attr("class").split(".")[1]
                        if(ikv == item.rincian_kegiatan.split("] ")[1]){
                            let ikv_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_IKV}</td><td></td><td></td>`
                            return $(this).after(ikv_td)
                            // if(item.total_ikv_per != null){
                            //     ikv_td += `${item.total_ikv_per}</td><td></td><td></td>`
                            //     return $(this).after(ikv_td)}
                            // else if(item.total_ikv_gdg != null){
                            //     ikv_td += `${item.total_ikv_gdg}</td><td></td><td></td>`
                            //     return $(this).after(ikv_td)}
                            // else if(item.total_ikv_keg != null){
                            //     ikv_td += `${item.total_ikv_keg}</td><td></td><td></td>`
                            //     return $(this).after(ikv_td)}
                        }
                    })
                })

                //6. Ikk sum
                sum_ikk.forEach((item, index) => {
                    $('.ikk_text').each(function(){
                        let ikk_text = $(this).attr("class").split("] ")[1]
                        if(ikk_text == item.indikator_kinerja_kegiatan.split("] ")[1]){
                            let ikk_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_IKK}</td><td></td><td></td>`
                            return $(this).after(ikk_td)
                            // if(item.total_ikk_per != null){
                            //     ikk_td += `${item.total_ikk_per}</td><td></td><td></td>`
                            //     return $(this).after(ikk_td)}
                            // else if(item.total_ikk_gdg != null){
                            //     ikk_td += `${item.total_ikk_gdg}</td><td></td><td></td>`
                            //     return $(this).after(ikk_td)}
                            // else if(item.total_ikk_keg != null){
                            //     ikk_td += `${item.total_ikk_keg}</td><td></td><td></td>`
                            //     return $(this).after(ikk_td)}
                        }
                    })
                })

                //7. Sasaran sum
                sum_ss.forEach((item, index) => {
                    $('.ss').each(function(){
                        let ss = $(this).text()
                        if(ss == item.sasaran_program){
                            let ss_td = `<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            <td style="text-align: right;">${item.TOTAL_SS}</td><td></td><td></td>`
                            return $(this).after(ss_td)
                            // if(item.total_ss_per != null){
                            //     ss_td += `${item.total_ss_per}</td><td></td><td></td>`
                            //     return $(this).after(ss_td)}
                            // else if(item.total_ss_gdg != null){
                            //     ss_td += `${item.total_ss_gdg}</td><td></td><td></td>`
                            //     return $(this).after(ss_td)}
                            // else if(item.total_ss_keg != null){
                            //     ss_td += `${item.total_ss_keg}</td><td></td><td></td>`
                            //     return $(this).after(ss_td)}
                        }
                    })
                })
                // hapus baris yang kosong
                clearUnwantedRow();
			},
            error: function (request, status, error) {
		        tata.error('Galat', 'Gagal menampilkan data', {
    			duration : 3000,
				animate  : slide
			})
		}}) // End ajax 

        const bodyTable = document.querySelector('.tabel-rekat [body]')
        $(document).on('click', '.cari', function (e) {
            let unit = {
                "1" :{"idunit" : "01", "unitkerja" : "Fakultas Ekonomi dan Bisnis"},
                "2" :{"idunit" : "02", "unitkerja" : "Fakultas Kedokteran Hewan"},
                "3" :{"idunit" : "03", "unitkerja" : "Fakultas Hukum"},
                "3" :{"idunit" : "04", "unitkerja" : "Fakultas Teknik"},
                "3" :{"idunit" : "05", "unitkerja" : "Fakultas Pertanian"},
                "6"	:{ "idunit" : "06", "unitkerja" :"Fakultas Keguruan dan Ilmu Pendidikan"},
                "7"	:{ "idunit" : "07", "unitkerja" :"Fakultas Kedokteran"},
                "8"	:{ "idunit" : "08", "unitkerja" :"Fakultas Matematika dan Ilmu Pengetahuan Alam"},
                "9"	:{ "idunit" : "09", "unitkerja" :"Fakultas Ilmu Sosial dan Ilmu Politik"},
                "10":{ "idunit" : "10", "unitkerja" :"Fakultas Kelautan dan Perikanan"},
                "11" :{"idunit"	: "11", "unitkerja" : "Fakultas Kedokteran Gigi"},
                "12" :{"idunit"	: "12", "unitkerja" : "Fakultas Keperawatan"},
                "13" :{"idunit"	: "13", "unitkerja" : "Program Pascasarjana"},
                "14" :{"idunit"	: "14", "unitkerja" : "LP2M"},
                "15" :{"idunit"	: "15", "unitkerja" : "LP3M"},
                "16" :{"idunit"	: "16", "unitkerja" : "UPT Perpustakaan"},
                "17" :{"idunit"	: "17", "unitkerja" : "UPT TIK"},
                "18" :{"idunit"	: "18", "unitkerja" : "UPT Percetakan"},
                "19" :{"idunit"	: "19", "unitkerja" : "UPT Lembaga Bahasa"},
                "20" : {"idunit": "20", "unitkerja" : "UPT Lab Terpadu"},
                "21" :{"idunit" : "21", "unitkerja" : "UPT Asrama"},
                "22" :{"idunit" : "22", "unitkerja" : "UPT Kewirausahaan"},
                "23" :{"idunit" : "23", "unitkerja" : "UPT Mitigasi Bencana"},
                "24" :{"idunit" : "24", "unitkerja" : "Mata Kuliah Umum"},
                "25" :{"idunit" : "25", "unitkerja" : "Academik Activity Center"},
                "26" :{"idunit" : "26", "unitkerja" : "Wisuda"},
                "27" :{"idunit" : "27", "unitkerja" : "Biro Akademik"},
                "28" :{"idunit" : "28", "unitkerja" : "Biro Kemahasiswaan"},
                "29" :{"idunit" : "29", "unitkerja" : "Biro Perencanaan dan Humas"},
                "30" : {"idunit": "30", "unitkerja": "Biro Umum dan Keuangan"},
                "31" : {"idunit": "31", "unitkerja": "Rumah Sakit Pendidikan USK"},
                "32" : {"idunit": "32", "unitkerja": "Kantor Urusan International"},
                "33" : {"idunit": "33", "unitkerja": "Kantor Pusat Administrasi"},
                "34" : {"idunit": "34", "unitkerja": "S3 Ilmu Pertanian"},
                "35" : {"idunit": "35", "unitkerja": "S3 Pendidikan IPS"},
                "36" : {"idunit": "36", "unitkerja": "S3 Ilmu Keteknikan"},
                "37" : {"idunit": "37", "unitkerja": "S3 Matematika dan Aplikasi Sains"},
                "38" : {"idunit": "38", "unitkerja": "S2 Konservasi Sumber Daya Lahan"},
                "39" : {"idunit": "39", "unitkerja": "S2 Administrasi Pendidikan"},
                "40" : {"idunit":"40", "unitkerja":"S2 PSPT"},
                "41" : {"idunit":"41", "unitkerja":"SMMPTN"},
                "42" : {"idunit":"42", "unitkerja":"S2 Pendidikan IPA"},
                "43" : {"idunit":"43", "unitkerja":"S2 Pengelolaan Lingkungan"},
                "44" : {"idunit":"44", "unitkerja":"UPT CDC"},
                "46" : {"idunit":"46", "unitkerja":"Pusat Pelayanan Psikologi dan Konseling"},
                "47" : {"idunit":"47", "unitkerja":"Kerjasama"},
                "48" : {"idunit":"48", "unitkerja":"Gayo Lues"},
                "49" : {"idunit":"49", "unitkerja":"Pengelola Lingkungan Hidup"},
                "50" : {"idunit":"50", "unitkerja":"Pranata Laboratorium Pendidikan"},
                "51" : {"idunit":"51", "unitkerja":"FKIP Pendidikan Profesi Guru"},
                "52" : {"idunit":"52", "unitkerja":"Unit Bisnis"},
                "53" : {"idunit":"53", "unitkerja":"RSGM"},
            }
            let unitkerja = $('.unitkerja').val()
            let tahun = $('select.tahun').val()
            $.ajax({
                type: 'GET',
                url: "{{ route('rktReport.getUnit') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    unitkerja,
                    tahun
                },
                success: function (data) {
                    datatable.destroy()
                    console.log(data)
                    bodyTable.innerHTML = ''
                    data.forEach((row, index) => {
                        let rowContent = `<tr><td>`
                            Object.entries(unit).forEach(([key, value]) => {
                                if(value.idunit == row.unit_kerja){
                                    return rowContent += value.unitkerja + `</td>`
                                }
                            })
                            rowContent += `<td>${row.sasaran_program}</td>
                            <td>${row.indikator_kinerja_kegiatan}</td>
                            <td>${row.rincian_kegiatan}</td>
                            <td>${row.rincian_komponen}</td>
                            <td>${row.jenis_belanja}</td>`
                            if(row.KEBUTUHAN_GDG !== null){
                                rowContent += `<td>${row.KEBUTUHAN_GDG ?? ''}</td>`
                            }else if(row.KEBUTUHAN_KEG !== null){
                                rowContent += `<td>${row.KEBUTUHAN_KEG ?? ''}</td>`
                            }else if(row.KEBUTUHAN_PER !== null){
                                rowContent += `<td>${row.KEBUTUHAN_PER ?? ''}</td>`
                            }
                            if(row.TOTAL_GEDUNG !== null){
                                rowContent += `<td>${row.TOTAL_GEDUNG ?? ''}</td></tr>`
                            }else if(row.TOTAL_KEGIATAN !== null){
                                rowContent += `<td>${row.TOTAL_KEGIATAN ?? ''}</td></tr>`
                            }else if(row.TOTAL_PERALATAN !== null){
                                rowContent += `<td>${row.TOTAL_PERALATAN ?? ''}</td></tr>`
                            }
                        bodyTable.innerHTML += rowContent;
                    })
                    datatable = $('.tabel-rekat').DataTable({
                        ordering: true
                    })
                }
            });
        })

        function clearUnwantedRow(){
            $(".ss").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".dk").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".ikk_text").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".ikv").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".rincian_kegiatan").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
                $(".unitkerja").each(function(){
                    if(!$(this).closest('td').next('td')[0]){
                        $(this).closest('tr').remove()
                    }
                })
        }
    })
</script>
