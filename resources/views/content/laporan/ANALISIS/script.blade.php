<script>
    $(document).ready(function () {
        // 💻 Init variable & client information (preserve existing globals)
        const existingAnalisis = (window.laporan && window.laporan.analisis) ? window.laporan.analisis : {}
        const preIsPdf = (existingAnalisis.isPdf === true) || window.location.href.includes("/pdf")
        window.laporan = window.laporan || {}
        window.laporan.analisis = {
            elements : {
                idunit: $("select.unit_kerja"),
                sumberdana: $("select.sumberdana"),
                table: $("table#tabel-analisis")
            }, 
            constants: {
                ROUTES: {
                    GET_DATA_ANALISIS: "{{ route('laporan.analisis.getAnalisis') }}",
                },
                TAHUN: "{{ $tahunAngka }}",
                TIMEOUT: 30000,
                TATA_OPTIONS: { animate: true, duration: 5000 },
                IS_PDF: preIsPdf
            },
            isPdf: preIsPdf,
            methods: {
                createOrUpdateMap: ( node, key, createNode) => {
                    if (!node.has(key)) node.set(key, createNode());
                    return node.get(key);
                },
                escapeHTML: (str) => {
                    const p = document.createElement('p');
                    p.textContent = str;
                    return p.innerHTML;
                },
                handleOnClickCari: (idunit, sumberdana) => {
                    showLoader()
                    setLoaderText("Memuat data ... ⏳")
                    const { methods } = window.laporan.analisis
                    const { elements } = window.laporan.analisis
                    const { TATA_OPTIONS } = window.laporan.analisis.constants
                    window.laporan.analisis.methods.fetchDataAnalisis(idunit, sumberdana)
                        .then( res => {
                            const { data } = res
                            if (data.length == 0 )
                                return tata.info("Error", "Data analisis tidak ditemukan", TATA_OPTIONS)

                            const baseMap = new Map()
                            data?.baseData?.forEach( item => {
                                const sdMap = methods.createOrUpdateMap( baseMap ||= new Map(), item.kd_sumberdana, () => ({ sumberdana: item.sumberdana, sub: new Map(), total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                                const kroMap = methods.createOrUpdateMap( sdMap.sub ||= new Map(), item.kode_ss, () => ({ ss:item.ss, sub: new Map(), total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                                const roMap  = methods.createOrUpdateMap( kroMap.sub ||= new Map(), item.kode_ikk, () => ({ ikk:item.ikk, sub: new Map(), total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                                const ikvMap = methods.createOrUpdateMap( roMap.sub ||= new Map(), item.kode_ikv, () => ({ ikv:item.ikv, sub: new Map(), total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                                const kegMap = methods.createOrUpdateMap( ikvMap.sub ||= new Map(), item.kode_keg, () => ({ keg:item.rincian_kegiatan, sub: new Map(), total: 0, totalRealisasi: 0, totalAmprah: 0 }) )
                                const rekatMap = methods.createOrUpdateMap( kegMap.sub ||= new Map(), item.id_rekat, () => ({ ...item, totalTanggapan: 0 }) );
                                if (item.tanggapan && item.tanggapan != "" && item.tanggapan != null && item.tanggapan != 'null') {
                                    rekatMap.totalTanggapan += 1
                                }
                            })
                            // Clear existing table body
                            elements.table.find("tbody").empty()

                            // Generate table rows
                            const isPdf        = window.laporan.analisis.constants.IS_PDF === true
                            const role         = "{{ $role }}"
                            const allowedRoles = ["Reviewer", "superadmin", "Pengawasan Internal"]
                            const isEditable     = ( !isPdf && allowedRoles.includes(role)) ? "true" : "false"
                            const isEditableForOperator = ( !isPdf && role == "operator" ) ? "true" : "false"
                            const isClicked      = isPdf ? "" : "role='button'"
                            
                            const documentFragment = document.createDocumentFragment()
                            baseMap.forEach( ( sdValue, sdKey ) => {
                                const tr = document.createElement("tr")
                                tr.classList.add("fw-bold")
                                tr.innerHTML = `
                                    <td>${methods.escapeHTML(sdKey)}</td>
                                    <td>${methods.escapeHTML(sdValue.sumberdana)}</td>
                                    <td></td>`
                                documentFragment.append(tr)

                                sdValue.sub.forEach( (kroValue, kroKey) => {
                                    if (!kroKey) return;
                                    const tr = document.createElement("tr")
                                    tr.innerHTML = `
                                        <td>${methods.escapeHTML(kroKey)}</td>
                                        <td>${methods.escapeHTML(kroValue.ss)}</td>
                                        <td></td>`
                                    documentFragment.append(tr)

                                    kroValue.sub.forEach( (roValue, roKey) => {
                                        const tr = document.createElement("tr")
                                        tr.innerHTML = `
                                            <td>${methods.escapeHTML(roKey)}</td>
                                            <td>${methods.escapeHTML(roValue.ikk)}</td>
                                            <td></td>`
                                        documentFragment.append(tr)

                                        roValue.sub.forEach( (ikvValue, ikvKey ) => {
                                            const tr = document.createElement("tr")
                                            tr.innerHTML = `
                                                <td>${methods.escapeHTML(ikvKey)}</td>
                                                <td>${methods.escapeHTML(ikvValue.ikv)}</td>
                                                <td></td>`
                                            documentFragment.append(tr)

                                            ikvValue.sub.forEach( (kegValue, kegKey) => {
                                                const tr = document.createElement("tr")
                                                tr.innerHTML = `
                                                    <td>${methods.escapeHTML(kegKey)}</td>
                                                    <td>${methods.escapeHTML(kegValue.keg)}</td>
                                                    <td></td>`
                                                documentFragment.append(tr)

                                                kegValue.sub.forEach( (rekatValue, rekatKey) => {
                                                                        const tr             = document.createElement("tr")
                                                                        tr.classList.add('rekat-row')
                                                                        tr.setAttribute('data-will-animate', 'true')
                                                    const isTorExists    = rekatValue.tor ? true : false
                                                    const kendala        = rekatValue.kendala ?? '-'
                                                    const tujuan         = rekatValue.tujuan ?? '-'
                                                    const resiko         = rekatValue.resiko ?? '-'
                                                    const alternatif     = rekatValue.alternatif ?? '-'
                                                    const hasil          = rekatValue.hasil ?? '-'
                                                    const dampak         = rekatValue.dampak ?? '-'
                                                    const tggpKendala    = rekatValue.tanggapan_kendala ?? '-'
                                                    const tggpTujuan     = rekatValue.tanggapan_tujuan ?? '-'
                                                    const tggpResiko     = rekatValue.tanggapan_resiko ?? '-'
                                                    const tggpAlternatif = rekatValue.tanggapan_alternatif ?? '-'
                                                    const tggpHasil      = rekatValue.tanggapan_hasil ?? '-'
                                                    const tggpDampak     = rekatValue.tanggapan_dampak ?? '-'

                                                    tr.classList.add(`rekat-${rekatValue.id_rekat}`)
                                                    tr.setAttribute("key", rekatValue.id_rekat)
                                                    tr.setAttribute("jenis", rekatValue.rab_type)
                                                    tr.innerHTML = `<td class="idRekat">${rekatValue.id_rekat}</td>
                                                        <td class="rekat" data-id="${rekatValue.id_rekat}" ${isClicked}>
                                                            ${rekatValue.sub_judul}
                                                            ${isPdf === false && rekatValue.totalTanggapan && rekatValue.totalTanggapan > 0 ? '<span class="badge bg-danger text-white ms-2 rounded-pill badge-tanggapan" title="'+rekatValue.totalTanggapan+' tanggapan" style="font-size:12px;padding:.18rem .5rem;vertical-align:middle;box-shadow:0 0 0 2px rgba(220,53,69,0.08)">'+rekatValue.totalTanggapan+'</span>' : ''}
                                                        </td>
                                                        <td class="status-simpan">
                                                            ${!isPdf ? `<span class="lihat-tor bg-info badge p-2" data-id="${rekatValue.id_rekat}" data-subjudul="${rekatValue.sub_judul}" role="button">Lihat tor</span>` : ''}
                                                            <span class="loading-info text-light bg-info px-2 py-1 d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...</span>
                                                        </td>`
                                                    // Tambahkan baris untuk kendala
                                                    const inputRow = `<tr data-rka="true" key="${rekatValue.id_rekat}" jenis="kendala">
                                                            <td class="text-end">Kendala:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${kendala}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpKendala}</td>
                                                        </tr>
                                                        <tr data-rka="true" jenis="tujuan" key="${rekatValue.id_rekat}">
                                                            <td class="text-end">Tujuan:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${tujuan}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpTujuan}</td>
                                                        </tr>
                                                        <tr data-rka="true" key="${rekatValue.id_rekat}" jenis="resiko">
                                                            <td class="text-end">Resiko:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${resiko}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpResiko}</td>
                                                        </tr>
                                                        <tr data-rka="true" key="${rekatValue.id_rekat}" jenis="alternatif">
                                                            <td class="text-end">Alternatif:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${alternatif}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpAlternatif}</td>
                                                        </tr>
                                                        <tr data-rka="true" key="${rekatValue.id_rekat}" jenis="hasil">
                                                            <td class="text-end">Hasil:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${hasil}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpHasil}</td>
                                                        </tr>
                                                        <tr data-rka="true" class="rekat_row" jenis="dampak" key="${rekatValue.id_rekat}">
                                                            <td class="text-end">Dampak/Manfaat:</td>
                                                            <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${dampak}</td>
                                                            <td class="analisis-input" contenteditable="${isEditable}">${tggpDampak}</td>
                                                        </tr>
                                                    `
                                                    const tempContainer = document.createElement("tbody")
                                                    tempContainer.innerHTML = inputRow
                                                    documentFragment.append(tr, ...tempContainer.children)
                                                })
                                            })
                                        })
                                    })
                                })
                            })
                            elements.table.find("tbody").append(documentFragment)
                            removeLoader()
                        })
                        .catch( err => {
                            console.log( err )
                            removeLoader()
                            return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data", TATA_OPTIONS)
                        })
                }, fetchDataAnalisis: (idunit, sumberdana) => {
                    console.log("Fetch data analisis ...")
                    const { GET_DATA_ANALISIS } = window.laporan.analisis.constants.ROUTES
                    return new Promise( async( resolve, reject ) => {
                        const res = await $.ajax({
                            url: GET_DATA_ANALISIS,
                            type: "GET",
                            dataType: "json",
                            data: { idunit, kodeSd: sumberdana }
                        })
                        if ( !res.success )
                            reject("Gagal mendapatkan data analisis")
                        resolve(res)
                    })
                }
            },
        }
        let isPdf = window.laporan.analisis.constants.IS_PDF === true
        if (isPdf === false)
            initSelect2("s")
        const currentUrl   = new URL(window.location.href)
        const idunitParams = currentUrl.searchParams.get("idunit")
        const sdParams     = currentUrl.searchParams.get("sumberdana")
        const idunit       = $(".unit_kerja")
        const sumberdana   = $(".sumberdana")
        const bodyTable    = ".body-tbl-unit"
        const url          = "{{route('laporan.analisis.getAnalisis')}}"
        const userAgent    = navigator.userAgent
        const screenSize   = `${window.screen.availWidth} x ${window.screen.availHeight}`
        const platform     = navigator.platform
        const lang         = navigator.language
        const $modalRab    = $("#modal-rab")
        const modalTor     = $("div#modal-tor-viewer")

        if ( idunitParams != null && sdParams != null ){
            idunit.val(idunitParams).trigger("change")
            sumberdana.val(sdParams).trigger("change")
        }

        $(".cari").on("click", function(){
            const { handleOnClickCari } = window.laporan.analisis.methods
            if ( idunit.val() == "" || sumberdana.val() == "" ){
                return tata.warn('Perhatian ⚠️', 'Silahkan memilih unit kerja dan sumber dana terlebih dahulu')
            }
            handleOnClickCari(idunit.val(), sumberdana.val())
            // Assign parameter to the URL
            window.history.pushState("", "", `/laporan/analisis?idunit=${idunit.val()}&sumberdana=${sumberdana.val()}`)
        })

        function debounce( func, delay ) {
            let timeout
            return function(...args) {
                const context = this
                clearTimeout(timeout)
                timeout = setTimeout(() => func.apply(context, args), delay)
            }
        }

        $(document).on("input", ".analisis-input", debounce(function() {
            if (window.laporan.analisis.constants.IS_PDF) return
            const tanggapan = $(this).text()
            const idRekat   = $(this).parent().attr("key")
            const jenis     = $(this).parent().attr("jenis")

            $.ajax({
                url: "{{route('laporan.analisis.storeAnalisis')}}",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}", "id": idRekat, "jenis": jenis, "tanggapan": tanggapan,
                    userAgent, screenSize, platform, lang
                }, beforeSend: () => {
                    $(`.rekat-${idRekat}`).find(":last-child span.loading-info").removeClass(`d-none`)
                },
                success: ( res ) => {
                    $(`.rekat-${idRekat}`).find(":last-child span.loading-info").addClass(`d-none`)
                },
                error: (xhr) => {
                    $(`.rekat-${idRekat}`).find(":last-child").html(`<span class="bg-danger px-2 py-1">Terjadi kesalahan</span>`)
                    const message = xhr.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        }, 300))
        $(document).on("input", ".analisis-operator-input", debounce(function() {
            if (window.laporan.analisis.constants.IS_PDF) return
            const tanggapan = $(this).text()
            const idRekat   = $(this).parent().attr("key")
            const jenis     = $(this).parent().attr("jenis")

            $.ajax({
                url: "{{route('laporan.analisis.storeAnalisisOperator')}}",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}", "id": idRekat, "jenis": jenis, "tanggapan": tanggapan, userAgent, screenSize, platform, lang
                }, beforeSend: () => {
                    $(`.rekat-${idRekat}`).find(":last-child span.loading-info").removeClass(`d-none`)
                },
                success: ( res ) => {
                    $(`.rekat-${idRekat}`).find(":last-child span.loading-info").addClass(`d-none`)
                },
                error: (xhr) => {
                    $(`.rekat-${idRekat}`).find(":last-child").html(`<span class="bg-danger px-2 py-1">Terjadi kesalahan</span>`)
                    const message = xhr.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        }, 300))
        $(document).on("input", ".tanggapan-input", debounce(function() {
            if (window.laporan.analisis.constants.IS_PDF) return
            const tanggapan = $(this).html()
            const idItemCoa = $(this).parent().attr("data-id")
            const jenisRab  = $(this).parent().attr("data-jenis")
            const coa       = $(this).parent().attr("data-coa")

            $.ajax({
                url: "{{route('laporan.analisis.storeTanggapan')}}",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}", idItemCoa, jenisRab, tanggapan,
                    userAgent, screenSize, platform, lang
                }, beforeSend: () => {
                    $(`.status-${coa}`).html(`<span class="bg-info px-2 py-1"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan data...</span>`)
                },
                success: ( res ) => {
                    $(`.status-${coa}`).html('')
                },
                error: (xhr) => {
                    $(`.status-${coa}`).html(`<span class="bg-danger px-2 py-1">Terjadi kesalahan</span>`)
                    const message = xhr.responseJSON.message || "Terjadi kesalahan saat menyimpan data"
                    return tata.error("⛔ Error", message)
                }
            })
        }, 300))

        /**
         * Generate Rows for COA
         * @param {Object} data
         * @param {String} key
         * @param {String} keyBiaya
         * @returns {String}
         * @description data merupakan object yang berisi data rabkeg, rabper, dan rabgdg.
         * key merupakan key dari object data yang akan diambil.
         * keyBiaya merupakan key dari object data yang akan diambil untuk menampilkan biaya.
        */
        function generateRowsCoa(data, key, keyBiaya) {
            return new Promise((resolve, reject) => {
                if ( !data || !data[key] ) {
                    return resolve('')
                }
                try {
                    const { TAHUN } = window.laporan.analisis.constants
                    const rows     = data[key].map(item => {
                    const sd       = data.sd
                    const kodeSs   = data.subkomponen?.ikv?.ro?.kro?.kode_ss ?? '-'
                    const kodeKeg  = data.kd_rk.substring(3,12) ?? '-'
                    const kodeIkv  = data.subkomponen?.ikv?.kode_ikv ?? '-'
                    const idRekat  = data.id
                    const mak      = parseFloat(TAHUN) >= 2026 ? `${sd}.${kodeSs}.${kodeIkv}.${kodeKeg}.<br>${idRekat}.${item.id_jenis_belanja ?? '-'}`
                        : `${sd}.${kodeSs}.${kodeKeg}.${idRekat}.${item.id_jenis_belanja ?? '-'}`

                    return `<tr class="fw-bold">
                        <td class="coa-${item.id_jenis_belanja}">${mak}</td>
                        <td>${item.jenis_belanja ?? "-"}</td>
                        <td>${rupiah(item.total_biaya)}</td>
                        <td class="status-${item.id_jenis_belanja}"></td>
                    </tr>`
                    }).join('')
                    resolve(rows)
                } catch (error) {
                    reject("Gagal memuat data")
                }
            })
        }

        /**
         * Generate Rows for COA Item
         * @param {Object} data
         * @param {String} key
         * @param {String} keyBiaya
         * @returns {String}
         * @description data merupakan object yang berisi data rabkeg, rabper, dan rabgdg.
         * key merupakan key dari object data yang akan diambil (contoh: rabkeg, rabper).
        */
        function generateRowsItemCoa( data, key, biayaSatuanKey ) {
            const kalkulasiVerifikasi = (verifikasiArray) => {
                let htmlStatus = ""
                verifikasiArray.filter(word => {
                    let verif = word === "SILAHKAN PILIH" || word === "" || word === null || word === "Tolak"
                    if ( verif ) {
                        htmlStatus = `<b>*</b>`
                    }
                })
                return htmlStatus
            }
            if (data[key]) {
                data[key].forEach(item => {
                    let htmlStatus = kalkulasiVerifikasi([item.verifikasi_tim, item.verifikasi_pimpinan_unit, item.verifikasi_pimpinan_univ, item.verifikasi_keu, item.verifikasi_aset]);
                    if ( item.id_jenis_belanja == null ){
                        return tata.warn("Perhatian ⚠️", "Data tidak ditemukan")
                    }
                    const tanggapan  = ( item.tanggapan != null && item.tanggapan != "" ) ? item.tanggapan : "Tanggapan..."
                    const isEditable = window.laporan.analisis.constants.IS_PDF ? "false" : "{{ in_array(session('role'), ['Reviewer', 'superadmin', 'Pengawasan Internal']) ? 'true' : 'false' }}"
                    const kuantitas  = item.kuantitas ?? 1
                    const sKuantitas = item.satuan_kuantitas ?? ''
                    const durasi     = item.durasi ?? 1
                    const sDurasi    = item.satuan_durasi ?? 'Pkt'
                    const hargaSatuan = key === "rabgdg" ? rupiah(item.jumlah_nilai) :
                        ( key === "rabper" ? rupiah(item.harga_satuan) + ' + ' + rupiah(item.biaya_pajak)  + ' + ' + rupiah(item.biaya_lainnya): rupiah(item.biaya_satuan) )
                    $(".coa-" + item.id_jenis_belanja).parent().after(`
                        <tr data-id="${item.id}" data-jenis="${key}" data-coa="${item.id_jenis_belanja}">
                            <td class="text-end">${item.ppk?.nama_pejabat ?? '-'}<br>
                                ${item.bpp?.nama_pejabat ?? '-'}
                            </td>
                            <td>${item.kebutuhan_kegiatan ? item.kebutuhan_kegiatan + ' ('+ kuantitas + ' ' +
                                sKuantitas + ' x ' + durasi + ' ' + sDurasi + ' x ' +
                                hargaSatuan + ')' : '-'} ${item.kebutuhan_kegiatan ? htmlStatus : ''}</td>
                            <td>${rupiah(item.jumlah_biaya ?? item.jumlah_nilai)}</td>
                            <td class="tanggapan-input" contenteditable="${isEditable}">${tanggapan}</td>
                        </tr>
                    `)
                })
            }
        }

        $(document).on("click", ".rekat", function (){
            if (window.laporan.analisis.constants.IS_PDF) return
            $modalRab.modal("show")
            const idRekat = $(this).attr("data-id")
            $.ajax({
                type: "GET",
                url: "{{ route('laporan.analisis.getItemCoa') }}",
                data: { id: idRekat },
                success: ( res ) => {
                    const { rekat, coa } = res.data

                    let html = ""
                    // 👇 Tampilkan data rab kegiatan
                    generateRowsCoa(coa, 'rabkeg', 'total_biaya')
                        .then(( res ) => {
                            if ( res != "" ){
                                $(".body-tbl-rab").html(res)
                                generateRowsItemCoa(rekat, "rabkeg", "biaya_satuan")
                            }
                        })
                        .catch((error) => {
                            return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data")
                    })
                    // 👇 Tampilkan data rab peralatan
                    generateRowsCoa(coa, 'rabper', 'total_biaya')
                        .then(( res ) => {
                            if ( res != "" ){
                                $(".body-tbl-rab").html(res)
                                generateRowsItemCoa(rekat, "rabper", "biaya_satuan")
                            }
                        })
                        .catch((error) => {
                            return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data")
                    })
                    // 👇 Tampilkan data rab gedung
                    generateRowsCoa(coa, 'rabgdg', 'total_biaya')
                        .then(( res ) => {
                            if ( res != "" ){
                                $(".body-tbl-rab").html(res)
                                generateRowsItemCoa(rekat, "rabgdg", "jumlah_nilai")
                            }
                        })
                        .catch((error) => {
                            return tata.error("⛔ Error", "Terjadi kesalahan saat memuat data")
                    })
                    $("#nama-kegiatan").text(rekat.sub_judul ?? '-')
                },
                err: ( err ) => {
                    const message = err.responseJSON.message || "Gagal mendapatkan data"
                    return tata.error("⛔ Error", message)
                },
            })
        })
        $(document).on("click", ".lihat-tor", async function(){
            const id       = $(this).data("id")
            const subJudul = $(this).data("subjudul")

            try {
                const response = await $.ajax({
                    type: "GET",
                    url: "{{ route('laporan.analisis.getTOR') }}",
                    data: { id },
                    xhrFields: { responseType: 'blob' },
                    timeout: 10000
                })

                const isPdf = response.type === 'application/pdf'; // Check if the blob is a PDF by checking its MIME type
                if ( isPdf ) {
                    const blobUrl = URL.createObjectURL(response);
                    $("#pdfContainer").html(
                        `<iframe src="${blobUrl}" width="100%" height="1000px"></iframe>`
                    );
                    modalTor.find("#nama-kegiatan").text(subJudul ?? '-')
                    modalTor.modal("show")
                    // Clean up blob URL after some time to free memory
                    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
                } else {
                    return tata.error("⛔ Error", "Gagal mendapatkan data", { animate: "slide", duration: 5000})
                }
            } catch (error) {
                const status  = error?.status || 500
                const message = "Terjadi kesalahan saat memuat data"
                if ( status == "404" ) {
                    return tata.error("⛔ Error", "Dokumen tidak ditemukan", { animate: "slide", duration: 5000})
                }
                return tata.error("⛔ Error", message, { animate: "slide", duration: 5000})
            }
        })
        $("#close-modal-rab").on("click", function (){
           $modalRab.modal("hide")
        })
        $("#close-modal-pdf").on("click", function (){
           modalTor.modal("hide")
        })

        $("#btn_save_xlsx").on("click", function(){
            const fileName = `Laporan Analisis Resiko ${idunit.val()}`
            exportExcel("tabel-analisis", fileName)
        })
        $("#btn_save_pdf").on("click", function(){
            const url        = new URL(window.location.href)
            const idunit     = url.searchParams.get("idunit")
            const sumberdana = url.searchParams.get("sumberdana")

            if ( idunit == null || sumberdana == null ){
                return tata.warn('Perhatian ⚠️', 'Silahkan memilih unit kerja dan sumber dana terlebih dahulu')
            }

            window.open(`/laporan/analisis/pdf?idunit=${idunit}&sumberdana=${sumberdana}`, '_blank')
        })

    })
</script>
