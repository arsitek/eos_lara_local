<script>
    // cek apakah user mengganti tema
    const currentTheme  = localStorage.getItem("BgImage") ?? " "
    const listOfThemes  = ["bg-img1", "bg-img2", "bg-img3", "bg-img4"]
    const isThemed      = listOfThemes.includes(currentTheme.split(" ")[0])
    const sumberdanaObj = []
    const amprahObj     = []
    const sisaAmprahObj = []
    window.rka = {}
    window.rka.isFirstEmpty = false
    rupiah = (number) => {
        const formattedValue = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number)
        return formattedValue.replace(/\./g, ',')
    }
    rupiahToNumber = (rupiahString) => {
        const numericString = rupiahString.replace(/[^\d.]/g, '')
        const numericValue = parseFloat(numericString.replace(/,/g, ''))
        return isNaN(numericValue) ? null : numericValue
    }
    const safeText = (value, fallback = "-") => {
        return (value === null || value === undefined || value === "") ? fallback : value
    }
    const createOrUpdateObject = ( object, node, createNode ) => {
        if ( !object[node] ) object[node] = createNode()
        return object[node]
    }
    const generateRKA = ( idunit, kd_sumberdana, backup, filter, url, loading_msg_id, body_table_class, message, isLooping, isNotFirst, idRekats = [], ppk = null ) => {
        const urlObj      = new URL(window.location.href)
        const filterData = $("select.filter-data").val() || urlObj.searchParams.get("filterdata")
        return new Promise((resolve, reject) => {
            const isPdf = window.location.href.includes("pdf")
            showLoader()
            setLoaderText( message )

            $.ajax({
                type : "GET", url : url, data: { backup, filter, idRekats, ppk },
                success : ( res ) => {
                    const { baseData, tahun } = res.data
                    const dataBuilder = { sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }
                    if ( baseData === null || baseData.length === 0 ) {
                        if ( isPdf ) {
                            return $(`${loading_msg_id}`).text("Data tidak ditemukan.")
                        }
                        $(body_table_class).children().remove()
                        $(body_table_class).append(`<tr class="text-center"><td colspan="9">Tidak ada data yang ditemukan</td></tr>`)
                        removeLoader()
                        return resolve( false )
                    }
                    // 🚀 tampilkan master data
                    const sdColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, 1); color: black; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, 1); color: darkblue")
                    const kroColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .8); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .8); color: darkblue")
                    const roColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .7); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .7); color: darkblue")
                    const ikvColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .6); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .6); color: darkblue")
                    const skColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .5); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .5); color: darkblue")
                    const unitColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .4); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .4); color: #FF8355")
                    const rekatColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .3); color: white; border-bottom: 1px solid gray;"
                        : "background-color: rgba(0,255,255, .3); cursor: pointer;")
                    const coaColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .2); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .2)")

                    baseData.forEach( item => {
                        const { rab_type: jenis, kd_sumberdana: kodeSd, sumberdana, kode_ss: kodeSs, ss, kode_ikk: kodeIkk, ikk, kode_ikv: kodeIkv, ikv,
                            kd_rk: kodeKeg, rincian_kegiatan: rincianKeg, unit_kerja_rkt: idunit, nama_unit: namaUnit, id_rekat: idRekat, sub_judul: subJudul,
                            id_jenis_belanja: idCoa, jenis_belanja: coa, nama_ppk: namaPPK,  nip_ppk: nipPpk, nip_bpp: nipBpp, nama_bpp: namaBPP,
                            id: idItemCoa, id_mak: idMak, biaya_satuan: biayaSatuan, jumlah_biaya: jumlahBiaya, jumlah_biaya_usulan: jumlahBiayaUsulan,
                            kuantitas, sKuantitas, durasi, sDurasi, kegiatan, sKegiatan, jumlah_tagihan: jumlahTagihan, jumlah_pengalihan: jumlahPengalihan, sisa_pengalihan: sisaPengalihan,
                            rpd, itemCoa, verifikasi_pimpinan_unit: verifikasiPimpinanUnit, verifikasi_tim: verifikasiTim, verifikasi_keu: verifikasiKeu, verifikasi_aset: verifikasiAset, verifikasi_pimpinan_univ: verifikasiPimpinanUniv,
                            verifikasi_spi: verifikasiSpi, is_draft: isDraft, jenis_validasi: jenisValidasi, jenis_revisi: jenisRevisi, status: statusRev, selisih_semula_menjadi: selisihSemulaMenjadi,
                            id_mak_paket: idMakPaket, judul_paket: judulPaket, id_paket: idPaket, rpd_paket: rpdPaket,
                        } = item

                        const toNumber      = (value) => Number(value ?? 0) || 0;
                        const hasPaket      = idPaket !== null && idPaket !== undefined && String(idPaket).trim() !== "";
                        const nilaiTotal    = toNumber(jumlahBiaya);
                        const nilaiAmprahan = toNumber(item.TOTAL_AMPRAH);
                        const nilaiRealisasi = toNumber(item.TOTAL_REALISASI) + toNumber(jumlahTagihan);

                        const sdBuilder   = createOrUpdateObject(dataBuilder.sub, kodeSd, () => ({ kodeSd, sumberdana, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                        const ssBuilder   = createOrUpdateObject(sdBuilder.sub, kodeSs, () => ({ kodeSs, ss, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                        const IkuBuilder  = createOrUpdateObject(ssBuilder.sub, kodeIkk, () => ({ kodeIkk, ikk, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                        const IkvBuilder  = createOrUpdateObject(IkuBuilder.sub, kodeIkv, () => ({ kodeIkv, ikv, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                        const kegBuilder  = createOrUpdateObject(IkvBuilder.sub, kodeKeg, () => ({ kodeKeg, rincianKeg, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                        const unitBuilder = createOrUpdateObject(kegBuilder.sub, idunit, () => ({ idunit, namaUnit, sub: { rekat: {}, paket: {} }, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));

                        const coaItem = {
                            jenis, idItemCoa, idMak, biayaSatuan, jumlahBiaya: nilaiTotal, kuantitas, sKuantitas, durasi, sDurasi, kegiatan, sKegiatan,
                            jumlahAmprahan: nilaiAmprahan, jumlahRealisasi: toNumber(item.TOTAL_REALISASI), jumlahTagihan: toNumber(jumlahTagihan),
                            jumlahPengalihan: toNumber(jumlahPengalihan), sisaPengalihan: toNumber(sisaPengalihan), jumlahBiayaUsulan: toNumber(jumlahBiayaUsulan),
                            rpd, itemCoa, verifikasiPimpinanUnit, verifikasiSpi, verifikasiTim, verifikasiKeu, verifikasiAset, verifikasiPimpinanUniv, isDraft, jenisValidasi, jenisRevisi, statusRev,
                            selisihSemulaMenjadi, rpdPaket
                        };

                        const parentBuilders = [unitBuilder, kegBuilder, IkvBuilder, IkuBuilder, ssBuilder, sdBuilder, dataBuilder];
                        let targetCoaBuilder = null;
                        let branchBuilders = [];
                        if (hasPaket) { // Jika item sudah dipaketkan oleh PPK, tampilkan node paket setara dengan rekat
                            const paketBuilder = createOrUpdateObject(unitBuilder.sub.paket, idPaket, () => ({ idPaket, idMakPaket, judulPaket, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                            const ppkPaketBuilder = createOrUpdateObject(paketBuilder.sub, nipPpk, () => ({ namaPPK, nipPpk, nipBpp, namaBPP, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                            const coaPaketBuilder = createOrUpdateObject(ppkPaketBuilder.sub, idCoa, () => ({ idCoa, coa, data: [], total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                            // sinkronkan total di node paket dengan node rekat agar total di node rekat tetap akurat meskipun ada item yang dipaketkan
                            targetCoaBuilder = coaPaketBuilder;
                            branchBuilders = [coaPaketBuilder, ppkPaketBuilder, paketBuilder];
                        } else {
                            // Jika item tidak dipaketkan, tampilkan di bawah node rekat seperti biasa
                            const rekatBuilder = createOrUpdateObject(unitBuilder.sub.rekat, idRekat, () => ({ idRekat, subJudul, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                            const ppkBuilder = createOrUpdateObject(rekatBuilder.sub, nipPpk, () => ({ namaPPK, nipPpk, nipBpp, namaBPP, sub: {}, total: 0, totalAmprahan: 0, totalRealisasi: 0 }));
                            const coaBuilder = createOrUpdateObject(ppkBuilder.sub, idCoa, () => ({ idCoa, coa, data: [], total: 0, totalAmprahan: 0, totalRealisasi: 0 }));

                            targetCoaBuilder = coaBuilder;
                            branchBuilders = [coaBuilder, ppkBuilder, rekatBuilder];
                        }

                        targetCoaBuilder.data.push(coaItem);
                        // update total dari node coa sampai ke root dataBuilder
                        const builders = [...branchBuilders, ...parentBuilders];
                        builders.forEach((builder) => {
                            builder.total += nilaiTotal;
                            builder.totalAmprahan += nilaiAmprahan;
                            builder.totalRealisasi += nilaiRealisasi;
                        });
                    })

                    const totalRow = `<tr class="headerSumberdana fw-bold" style="${sdColor}">
                        <td style="text-align: center; font-size: 14px" colspan="3">Total</td>
                        <td class="total text-end">${ rupiah( dataBuilder.total) }</td>
                        <td style="width: 50px;"></td>
                        <td style="width: 50px;" class="text-end">${ rupiah( dataBuilder.totalAmprahan) }</td>
                        <td style="width: 50px;" class="text-end">${ rupiah( dataBuilder.totalRealisasi) }</td>
                        <td style="width: 50px;" class="text-end"></td>
                        <td style="width: 50px;" class="text-end">${ rupiah( dataBuilder.total - ( dataBuilder.totalRealisasi + dataBuilder.totalAmprahan ) ) }</td>
                    </tr>`

                    $(body_table_class).append(totalRow)
                    Object.values(dataBuilder.sub).forEach( itemSd => {
                        const { kodeSd, sumberdana, total, totalAmprahan, totalRealisasi } = itemSd
                        const realisasi = Number( totalAmprahan + totalRealisasi )
                        $(`.total-${kodeSd}`).text( rupiah(total) )
                        const sdRow = `<tr class="fw-bold headerSumberdana" key="${kodeSd}" style="${sdColor}">
                            <td style="width: 50px">${safeText(kodeSd)}</td>
                            <td style="width: 130px">${safeText(sumberdana)}</td>
                            <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                            <td style="width: 50px;"></td>
                            <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                            <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                            <td style="width: 50px;" class="text-end"></td>
                            <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                        </tr>`
                        $(body_table_class).append(sdRow)

                        Object.values(itemSd.sub).forEach( itemSs => {
                            const { kodeSs, ss, total, totalAmprahan, totalRealisasi } = itemSs
                            const realisasi = Number( totalAmprahan + totalRealisasi )
                            const ssRow = `<tr class="headerSs" key="${kodeSs}" style="${kroColor}">
                                <td>${safeText(kodeSs)}</td>
                                <td style="width: 100px">${safeText(ss)}</td>
                                <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                <td style="width: 50px;"></td>
                                <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                <td style="width: 50px;" class="text-end"></td>
                                <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                            </tr>`
                            $(body_table_class).append(ssRow)

                            Object.values( itemSs.sub ).forEach( itemIkk => {
                                const { kodeIkk, ikk, total, totalAmprahan, totalRealisasi } = itemIkk
                                const realisasi = Number( totalAmprahan + totalRealisasi )
                                const ikkRow = `<tr class="headerIkk" key="${kodeIkk}" style="${roColor}">
                                    <td>${safeText(kodeIkk)}</td>
                                    <td style="width: 100px">${safeText(ikk)}</td>
                                    <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                    <td style="width: 50px;"></td>
                                    <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                    <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                    <td style="width: 50px;" class="text-end"></td>
                                    <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                </tr>`
                                $(body_table_class).append(ikkRow)

                                Object.values( itemIkk.sub ).forEach( itemIkv => {
                                    const { kodeIkv, ikv, total, totalAmprahan, totalRealisasi } = itemIkv
                                    const realisasi = Number(totalAmprahan + totalRealisasi)
                                    const ikvRow = `<tr class="headerIkv" key="${kodeIkv}" style="${ikvColor}">
                                        <td>${safeText(kodeIkv)}</td>
                                        <td style="width: 100px">${safeText(ikv)}</td>
                                        <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                        <td style="width: 50px;"></td>
                                        <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                        <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                        <td style="width: 50px;" class="text-end"></td>
                                        <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                    </tr>`
                                    $(body_table_class).append(ikvRow)

                                    Object.values( itemIkv.sub ).forEach( itemKeg => {
                                        const { kodeKeg, rincianKeg, total, totalAmprahan, totalRealisasi } = itemKeg
                                        const realisasi = Number(totalAmprahan + totalRealisasi)
                                        const kegRow = `<tr class="headerKeg" key="${kodeKeg}" style="${skColor}">
                                            <td>${safeText(kodeKeg)}</td>
                                            <td style="width: 100px">${safeText(rincianKeg)}</td>
                                            <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                            <td style="width: 50px;"></td>
                                            <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                            <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                            <td style="width: 50px;" class="text-end"></td>
                                            <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                        </tr>`
                                        $(body_table_class).append(kegRow)

                                        Object.values( itemKeg.sub ).forEach( itemUnit => {
                                            const { idunit, namaUnit, total, totalAmprahan, totalRealisasi } = itemUnit
                                            const realisasi = Number(totalAmprahan + totalRealisasi)
                                            const unitRow = `<tr class="headerUnit" key="${idunit}" style="${unitColor}">
                                                <td>${idunit}</td>
                                                <td style="width: 100px">${safeText(namaUnit)}</td>
                                                <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                                <td style="width: 50px;"></td>
                                                <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                <td style="width: 50px;" class="text-end"></td>
                                                <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                            </tr>`
                                            $(body_table_class).append(unitRow)

                                            Object.values( itemUnit.sub.rekat ).forEach( itemRekat => {
                                                const { idRekat, subJudul, total, totalAmprahan, totalRealisasi } = itemRekat
                                                const realisasi = Number(totalAmprahan + totalRealisasi)
                                                const subJudulRow = `<tr class="target-element fw-bold headerRekat" key="${idRekat}" style="${rekatColor};user-select:auto">
                                                    <td>${idRekat}</td>
                                                    <td style="width: 100px" colspan="2">${safeText(subJudul)}</td>
                                                    <td class="total-sd text-end">${ rupiah(total) }</td>
                                                    <td style="width: 50px;"></td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                    <td style="width: 50px;" class="text-end"></td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                </tr>`
                                                $(body_table_class).append(subJudulRow)

                                                Object.values( itemRekat.sub ).forEach( itemPPK => {
                                                    const { nipPpk, nipBpp, namaPPK, namaBPP, total, totalAmprahan, totalRealisasi } = itemPPK
                                                    const realisasi = Number(totalAmprahan + totalRealisasi)
                                                        const isPpkEmpty = !nipPpk || !namaPPK
                                                        const isBppEmpty = !nipBpp || !namaBPP
                                                        const ppkRow = `<tr class="headerPPK" colspan="3" key="${nipPpk}" style="${rekatColor}">
                                                        <td>${ isPpkEmpty ? 'PPK Tidak ditemukan.' : safeText(namaPPK)} <br/> ${isBppEmpty ? 'BPP Tidak ditemukan.' : safeText(namaBPP)}</td>
                                                        <td style="width: 100px"></td>
                                                        <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                                        <td style="width: 50px;"></td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                        <td style="width: 50px;" class="text-end"></td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                    </tr>`
                                                    $(body_table_class).append(ppkRow)

                                                    Object.values( itemPPK.sub ).forEach( itemCoa => {
                                                        const { idCoa, coa, total, totalAmprahan, totalRealisasi } = itemCoa
                                                        const realisasi       = Number(totalAmprahan + totalRealisasi)
                                                        const kodeKegPartSafe = parseFloat(tahun) >= 2026 ? `${safeText(kodeIkv)}.<br>${safeText(kodeKeg)}` : (kodeKeg && kodeKeg.length >= 11) ? `${kodeKeg.substring(3, 11)}<br>` : safeText(null)
                                                        const mak             = `${safeText(kodeSd)}.${safeText(kodeSs)}.${kodeKegPartSafe}.${idunit}.${idRekat}.${safeText(idCoa, '-')}`

                                                        const coaRow = `<tr class="headerCoa kk_row" key="${idCoa}" style="${coaColor}">
                                                            <td>${mak}</td>
                                                            <td style="width: 100px">${safeText(coa)}</td>
                                                            <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                                            <td style="width: 50px;"></td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                            <td style="width: 50px;" class="text-end"></td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                        </tr>`
                                                        $(body_table_class).append(coaRow)

                                                        Object.values( itemCoa.data ).forEach( itemData => {
                                                            let { idItemCoa, idMak, biayaSatuan, jumlahBiaya, kuantitas, sKuantitas,
                                                                durasi, sDurasi, kegiatan, sKegiatan, jumlahAmprahan, jumlahRealisasi, rpd, itemCoa,
                                                                jumlahTagihan, jumlahBiayaUsulan, jumlahPengalihan, verifikasiPimpinanUnit, selisihSemulaMenjadi,
                                                                verifikasiTim, verifikasiKeu, verifikasiAset, verifikasiPimpinanUniv, verifikasiSpi, jenis, isDraft, statusRev, rpdPaket
                                                            } = itemData
                                                            const verifColumn = [ verifikasiPimpinanUnit, verifikasiTim, verifikasiKeu, verifikasiAset, verifikasiPimpinanUniv, verifikasiSpi ]
                                                            let verifStatus   = ""
                                                            verifColumn.forEach( item => {
                                                                if ( item === "" || item === null || !item || item === "Tolak" ) {
                                                                    verifStatus = "*"
                                                                }
                                                            })
                                                            let trStyle              = isDraft == "true" ? 'color: red;' : ''
                                                            const akumulasiRealisasi = Number( jumlahRealisasi + jumlahAmprahan )
                                                            const isRealisasi        = akumulasiRealisasi != 0 ? true : false
                                                            const isTagihan          = ( jumlahTagihan == 0 || !jumlahTagihan ) ? false : true
                                                            const realisasi          = akumulasiRealisasi != 0 ? akumulasiRealisasi : ( jumlahTagihan ?? 0 )
                                                            if ( statusRev == '' ) {
                                                                verifStatus = "*"
                                                                if ( "draft" == filterData ) {
                                                                    trStyle += ' background-color: yellow;'
                                                                    jumlahBiayaUsulan = Number(jumlahBiayaUsulan) + Number(selisihSemulaMenjadi ?? 0)
                                                                    jumlahBiaya = Number(jumlahBiayaUsulan)
                                                                }
                                                            }

                                                            const dataRow = `<tr style="${trStyle}" class="headerItemCoa kk_row" key="${idItemCoa}" jenis="${jenis}" ppk="${nipPpk}" bpp="${nipBpp}" jumlahBiaya="${jumlahBiaya}" idCoa="${idCoa}" kodeSd="${kodeSd}" idunit="${idunit}" kodeKeg="${kodeKeg}" isDraft="${isDraft}">
                                                                <td class="text-end itemCoa">-</td>
                                                                <td class="status-verif" role="button">${safeText(itemCoa)}${verifStatus}</td>
                                                                <td>${kuantitas} ${sKuantitas ?? 'Keg'} X ${durasi ?? '1'} ${sDurasi ?? 'Keg'} X ${kegiatan ?? '1'} ${sKegiatan ?? 'Keg'} X ${ rupiah(biayaSatuan) }</td>
                                                                <td class="text-end">${ rupiah(jumlahBiaya) }</td>
                                                                <td class="text-end">${rpd ?? '-'}</td>
                                                                <td class="text-end">${ rupiah(jumlahAmprahan) }</td>
                                                                <td class="text-end ${isTagihan ? 'text-primary' : '' }"> ${ rupiah( isRealisasi === true ? jumlahRealisasi : ( jumlahTagihan ?? 0 ) ) }</td>
                                                                <td class="text-end">${ rupiah( jumlahPengalihan ?? 0 ) }</td>
                                                                <td class="text-end">${ rupiah( jumlahBiayaUsulan - realisasi - Number( jumlahPengalihan ?? 0 ) ) }</td>
                                                            </tr>`
                                                            $(body_table_class).append(dataRow)
                                                        })
                                                    })
                                                })
                                            })
                                            Object.values( itemUnit.sub.paket ).forEach( itemRekat => {
                                                const { idMakPaket, judulPaket, subJudul, total, totalAmprahan, totalRealisasi } = itemRekat
                                                const realisasi = Number(totalAmprahan + totalRealisasi)
                                                const slicedIdMak = idMakPaket.slice(2)
                                                const subJudulRow = `<tr class="target-element fw-bold headerRekat" key="${idMakPaket}" style="${rekatColor};user-select:auto">
                                                    <td>${slicedIdMak}</td>
                                                    <td style="width: 100px" colspan="2">${safeText(judulPaket)}</td>
                                                    <td class="total-sd text-end">${ rupiah(total) }</td>
                                                    <td style="width: 50px;"></td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                    <td style="width: 50px;" class="text-end"></td>
                                                    <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                </tr>`
                                                $(body_table_class).append(subJudulRow)

                                                Object.values( itemRekat.sub ).forEach( itemPPK => {
                                                    const { nipPpk, nipBpp, namaPPK, namaBPP, total, totalAmprahan, totalRealisasi } = itemPPK
                                                    const realisasi = Number(totalAmprahan + totalRealisasi)
                                                        const isPpkEmpty = !nipPpk || !namaPPK
                                                        const isBppEmpty = !nipBpp || !namaBPP
                                                        const ppkRow = `<tr class="headerPPK" colspan="3" key="${nipPpk}" style="${rekatColor}">
                                                        <td>${ isPpkEmpty ? 'PPK Tidak ditemukan.' : safeText(namaPPK)} <br/> ${isBppEmpty ? 'BPP Tidak ditemukan.' : safeText(namaBPP)}</td>
                                                        <td style="width: 100px"></td>
                                                        <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                                        <td style="width: 50px;"></td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                        <td style="width: 50px;" class="text-end"></td>
                                                        <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                    </tr>`
                                                    $(body_table_class).append(ppkRow)

                                                    Object.values( itemPPK.sub ).forEach( itemCoa => {
                                                        const { idCoa, coa, total, totalAmprahan, totalRealisasi } = itemCoa
                                                        const realisasi       = Number(totalAmprahan + totalRealisasi)
                                                        const kodeKegPartSafe = parseFloat(tahun) >= 2026 ? `${safeText(kodeIkv)}.<br>${safeText(kodeKeg)}` : (kodeKeg && kodeKeg.length >= 11) ? `${kodeKeg.substring(3, 11)}<br>` : safeText(null)
                                                        const mak             = `${safeText(kodeSd)}.${safeText(kodeSs)}.${kodeKegPartSafe}.${idunit}.${slicedIdMak}.${safeText(idCoa, '-')}`

                                                        const coaRow = `<tr class="headerCoa kk_row" key="${idCoa}" style="${coaColor}">
                                                            <td>${mak}</td>
                                                            <td style="width: 100px">${safeText(coa)}</td>
                                                            <td></td><td class="total-sd text-end">${ rupiah(total) }</td>
                                                            <td style="width: 50px;"></td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah(totalAmprahan) }</td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah(totalRealisasi) }</td>
                                                            <td style="width: 50px;" class="text-end"></td>
                                                            <td style="width: 50px;" class="text-end">${ rupiah( total - realisasi ) }</td>
                                                        </tr>`
                                                        $(body_table_class).append(coaRow)

                                                        Object.values( itemCoa.data ).forEach( itemData => {
                                                            let { idItemCoa, idMak, biayaSatuan, jumlahBiaya, kuantitas, sKuantitas,
                                                                durasi, sDurasi, kegiatan, sKegiatan, jumlahAmprahan, jumlahRealisasi, rpd, itemCoa,
                                                                jumlahTagihan, jumlahBiayaUsulan, jumlahPengalihan, verifikasiPimpinanUnit, selisihSemulaMenjadi,
                                                                verifikasiTim, verifikasiKeu, verifikasiAset, verifikasiPimpinanUniv, jenis, isDraft, statusRev, rpdPaket
                                                            } = itemData
                                                            const verifColumn = [ verifikasiPimpinanUnit, verifikasiTim, verifikasiKeu, verifikasiAset, verifikasiPimpinanUniv ]
                                                            let verifStatus   = ""
                                                            verifColumn.forEach( item => {
                                                                if ( item === "" || item === null || !item || item === "Tolak" ) {
                                                                    verifStatus = "*"
                                                                }
                                                            })
                                                            let trStyle              = isDraft == "true" ? 'color: red;' : ''
                                                            const akumulasiRealisasi = Number( jumlahRealisasi + jumlahAmprahan )
                                                            const isRealisasi        = akumulasiRealisasi != 0 ? true : false
                                                            const isTagihan          = ( jumlahTagihan == 0 || !jumlahTagihan ) ? false : true
                                                            const realisasi          = akumulasiRealisasi != 0 ? akumulasiRealisasi : ( jumlahTagihan ?? 0 )
                                                            if ( statusRev == '' ) {
                                                                verifStatus = "*"
                                                                if ( "draft" == filterData ) {
                                                                    trStyle += ' background-color: yellow;'
                                                                    jumlahBiayaUsulan = Number(jumlahBiayaUsulan) + Number(selisihSemulaMenjadi ?? 0)
                                                                    jumlahBiaya = Number(jumlahBiayaUsulan)
                                                                }
                                                            }

                                                            const dataRow = `<tr style="${trStyle}" class="headerItemCoa kk_row" key="${idItemCoa}" jenis="${jenis}" ppk="${nipPpk}" bpp="${nipBpp}" jumlahBiaya="${jumlahBiaya}" idCoa="${idCoa}" kodeSd="${kodeSd}" idunit="${idunit}" kodeKeg="${kodeKeg}" isDraft="${isDraft}">
                                                                <td class="text-end itemCoa">-</td>
                                                                <td class="status-verif" role="button">${safeText(itemCoa)}${verifStatus}</td>
                                                                <td>${kuantitas} ${sKuantitas ?? 'Keg'} X ${durasi ?? '1'} ${sDurasi ?? 'Keg'} X ${kegiatan ?? '1'} ${sKegiatan ?? 'Keg'} X ${ rupiah(biayaSatuan) }</td>
                                                                <td class="text-end">${ rupiah(jumlahBiaya) }</td>
                                                                <td class="text-end">${ rpdPaket !== null && rpdPaket !== undefined && rpdPaket !== '' ? String(rpdPaket).padStart(2, '0') : '-' }</td>
                                                                <td class="text-end">${ rupiah(jumlahAmprahan) }</td>
                                                                <td class="text-end ${isTagihan ? 'text-primary' : '' }"> ${ rupiah( isRealisasi === true ? jumlahRealisasi : ( jumlahTagihan ?? 0 ) ) }</td>
                                                                <td class="text-end">${ rupiah( jumlahPengalihan ?? 0 ) }</td>
                                                                <td class="text-end">${ rupiah( jumlahBiayaUsulan - realisasi - Number( jumlahPengalihan ?? 0 ) ) }</td>
                                                            </tr>`
                                                            $(body_table_class).append(dataRow)
                                                        })
                                                    })
                                                })
                                            })
                                        })
                                    })
                                })
                            })
                        })
                    })

                    // 🚀 tampilkan total keseluruhan
                    $(`.total`).text( rupiah( dataBuilder.total ) )
                    $(".containerTtd").show()
                    $(`${loading_msg_id}`).text("")
                    if( idunit && idunit.includes(",") ) $("td#unitkerja").text("-")
                    removeLoader()

                    $('.kk_row').each( function() {
                        if(!$(this).closest('tr').next('tr').hasClass("kk_row")){
                            $(this).attr('style',  $(this).attr('style') + 'border-bottom:2.5px solid black');
                        }
                    })
                    resolve( true )
                },
                error: ( err ) => {
                    const message = err.responseJSON?.message || "Terjadi kesalahan saat memuat data"
                    reject( message )
                    $(`${loading_msg_id}`).text("Terjadi Kesalahan saat memuat data.")
                    if ( isPdf === false ) {
                        return tata.error(" Error", message)
                        removeLoader()
                    }
                    return
                }
            })
        })
    }
    const getTanggapan = (value) => {
        // 📢 Jika value null atau kosong, maka tampilkan "Tanggapan..."
        return (value == null || value === '') ? "Tanggapan..." : value
    }
    const getAnalisis = ( value ) => {
        // 📢 Jika value null atau kosong, maka tampilkan "-"
        return (value == null || value === '') ? "-" : value
    }
    const generateRkaAnalisis = (...params) => {
        const [ idunit, kd_sumberdana, url, loading_msg_id, body_table_class, message, isPdf ] = params
        showLoader()
        setLoaderText( message )
        $.ajax({
            type : "POST",
            data: { "_token":"{{ csrf_token() }}", idunit, kd_sumberdana},
            url : url,
        	success : ( res ) => {
                const role         = "{{ session()->get('role') }}"
                const allowedRoles = ["Reviewer", "superadmin", "Pengawasan Internal"]
                const { dataMaster, sumberdana, subJudul } = res.data
                if ( subJudul.length === 0 ) {
                    removeLoader()
                    return tata.warn("⚠️ Perhatian", "Data tidak ditemukan")
                }
                // hapus semua isi tabel
                $(`${body_table_class}`).children().remove()
                // 🚀 tampilkan master data
                let htmlMaster = []
                dataMaster.forEach(item => {
                    htmlMaster.push(`
                        <tr>
                            <td class="kro kro-${item.kode_ss}">${item.kode_ss}</td>
                            <td>${item.sasaran_program}</td>
                            <td></td>
                        </tr>
                    `)
                    item.ro.forEach(itemRo => {
                        htmlMaster.push(`
                            <tr>
                                <td class="ro ro-${itemRo.kode_ikk}">${itemRo.kode_ikk}</td>
                                <td>${itemRo.indikator_kinerja_kegiatan}</td>
                                <td></td>
                            </tr>
                        `)
                        itemRo.ikv.forEach(itemIkv => {
                            htmlMaster.push(`
                                <tr>
                                    <td class="ikv ikv-${itemIkv.kode_ikv}">${itemIkv.kode_ikv}</td>
                                    <td>${itemIkv.ikv}</td>
                                    <td></td>
                                </tr>
                            `)
                            itemIkv.subkomponen.forEach(itemKeg => {
                                htmlMaster.push(`
                                    <tr>
                                        <td class="kode_keg keg-${itemKeg.kode_keg}">${itemKeg.kode_keg}</td>
                                        <td>${itemKeg.rincian_kegiatan}</td>
                                        <td></td>
                                    </tr>
                                `)
                            })
                        })
                    })
                })
                $(`${body_table_class}`).append(`<tr data-rka="true">
                    <td>${sumberdana.kd_sumberdana}</td>
                    <td>${sumberdana.sumberdana}</td>
                    <td class=""></td>
                </tr>`)
                $(`${body_table_class}`).append(htmlMaster.join(''))

                // 🚀 tampilkan data rekat
                subJudul.forEach( item => {
                    const kodeKeg        = item.kd_rk.replace(/\./g, "\\.")
                    const kodeIkv        = item.subkomponen?.ikv?.kode_ikv.replace(/\./g, "\\.")
                    const kodeRo         = item.subkomponen?.ikv?.ro?.kode_ikk.replace(/\./g, "\\.")
                    const kodeKro        = item.subkomponen?.ikv?.ro?.kro?.kode_ss.replace(/\./g, "\\.")
                    const foundKegClass  = $(`.keg-${kodeKeg}`)
                    foundKegClass.parent().attr("data-rka", "true")
                    const foundIkvClass  = $(`.ikv-${kodeIkv}`).parent().attr("data-rka", "true")
                    const foundRoClass   = $(`.ro-${kodeRo}`).parent().attr("data-rka", "true")
                    const foundKroClass  = $(`.kro-${kodeKro}`).parent().attr("data-rka", "true")
                    const kendala        = getAnalisis(item?.analisis?.kendala)
                    const tujuan         = getAnalisis(item?.analisis?.tujuan)
                    const resiko         = getAnalisis(item?.analisis?.resiko)
                    const alternatif     = getAnalisis(item?.analisis?.alternatif)
                    const hasil          = getAnalisis(item?.analisis?.hasil)
                    const dampak         = getAnalisis(item?.analisis?.dampak)
                    const tggpKendala    = getTanggapan(item?.analisis?.tanggapan_kendala)
                    const tggpTujuan     = getTanggapan(item?.analisis?.tanggapan_tujuan)
                    const tggpResiko     = getTanggapan(item?.analisis?.tanggapan_resiko)
                    const tggpAlternatif = getTanggapan(item?.analisis?.tanggapan_alternatif)
                    const tggpHasil      = getTanggapan(item?.analisis?.tanggapan_hasil)
                    const tggpDampak     = getTanggapan(item?.analisis?.tanggapan_dampak)
                    const isEditable     = ( !isPdf && allowedRoles.includes(role)) ? "true" : "false"
                    const isEditableForOperator = ( !isPdf && role == "operator" ) ? "true" : "false"
                    const isClicked      = isPdf ? "" : "role='button'"
                    const isShow         = isPdf ? "d-none" : ""
                    const isTorExists    = item.tor ? true : false
                    foundKegClass.parent().after(`<tr class="fw-bold rekat-${item.id}" role="button" data-rka="true">
                        <td class="idRekat">${item.id}</td>
                        <td class="rekat" data-id="${item.id}" ${isClicked}>${item.sub_judul}</td>
                        <td class="status-simpan">
                            ${isTorExists ? `<span class="lihat-tor bg-info badge p-2 ${isShow}" data-id="${item.id}" data-subjudul="${item.sub_judul}" role="button">Lihat tor</span>` : ''}
                            <span class="loading-info text-light bg-info px-2 py-1 d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...</span>
                        </td>
                    </tr>
                    <tr data-rka="true" key="${item.id}" jenis="kendala">
                        <td class="text-end">Kendala:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${kendala}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpKendala}</td>
                    </tr>
                    <tr data-rka="true" jenis="tujuan" key="${item.id}">
                        <td class="text-end">Tujuan:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${tujuan}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpTujuan}</td>
                    </tr>
                    <tr data-rka="true" key="${item.id}" jenis="resiko">
                        <td class="text-end">Resiko:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${resiko}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpResiko}</td>
                    </tr>
                    <tr data-rka="true" key="${item.id}" jenis="alternatif">
                        <td class="text-end">Alternatif:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${alternatif}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpAlternatif}</td>
                    </tr>
                    <tr data-rka="true" key="${item.id}" jenis="hasil">
                        <td class="text-end">Hasil:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${hasil}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpHasil}</td>
                    </tr>
                    <tr data-rka="true" class="rekat_row" jenis="dampak" key="${item.id}">
                        <td class="text-end">Dampak/Manfaat:</td>
                        <td contenteditable="${isEditableForOperator}" class="analisis-operator-input">${dampak}</td>
                        <td class="analisis-input" contenteditable="${isEditable}">${tggpDampak}</td>
                    </tr>`)
                })
                $('tr').not('[data-rka="true"]').remove()
                $('.rekat_row').each( function() {
                    if(!$(this).closest('tr').next('tr').hasClass("rekat_row")){
                        $(this).attr('style',  'border-bottom:2.5px solid black')
                    }
                })
                removeLoader()
            }, error: ( err ) => {
                removeLoader()
                $(`${loading_msg_id}`).text("Terjadi Kesalahan saat memuat data")
                return
            }
        })
    }
	const clearUnwantedRowsByClass = (className) => {
		$(`.${className}`).each(function () {
			if ( $(this).text() == "" ) {
				$(this).closest('tr').remove()
			}
		})
	}
    const generateRKARPD = ( idunit, sd, rpd, bodyTbl, isPdf ) => {
        $.ajax({
            type: "POST",
            url: "{{ route('rpd.getRPD') }}",
            data: { "_token": "{{ csrf_token() }}", idunit, sd, rpd },
            beforeSend: () => {
                $(".loading-div").show()
                bodyTbl.html("")
            },
            success: ( res ) => {
                const { data } = res
                $(".loading-div").hide()
                if ( data.itemCoa.length == 0 ) {
                    return tata.warn("⚠️ Perhatian", "Data tidak ditemukan")
                }
                // Generate RKA Data
                let htmlMaster = []
                data.dataMaster.forEach(item => {
                    htmlMaster.push(`
                        <tr>
                            <td style="min-width: 250px" class="kro kro-${item.kode_ss}">${item.kode_ss}</td>
                            <td style="min-width: 250px">${item.sasaran_program}</td>
                            <td class="text-end sum"></td>
                            <td></td><td></td><td></td>
                        </tr>
                    `)
                    item.ro.forEach(itemRo => {
                        htmlMaster.push(`
                            <tr>
                                <td class="ro ro-${itemRo.kode_ikk}">${itemRo.kode_ikk}</td>
                                <td>${itemRo.indikator_kinerja_kegiatan}</td>
                                <td class="text-end sum"></td>
                                <td></td><td></td><td></td>
                            </tr>
                        `)
                        itemRo.ikv.forEach(itemIkv => {
                            htmlMaster.push(`
                                <tr>
                                    <td class="ikv ikv-${itemIkv.kode_ikv}">${itemIkv.kode_ikv}</td>
                                    <td>${itemIkv.ikv}</td>
                                    <td class="text-end sum"></td>
                                    <td></td><td></td><td></td>
                                </tr>
                            `);
                            itemIkv.subkomponen.forEach(itemKeg => {
                                htmlMaster.push(`
                                    <tr>
                                        <td class="kode_keg keg-${itemKeg.kode_keg}">${itemKeg.kode_keg}</td>
                                        <td>${itemKeg.rincian_kegiatan}</td>
                                        <td class="sum text-end"></td>
                                        <td></td><td></td><td></td>
                                    </tr>
                                `)
                            })
                        })
                    })
                })
                $(bodyTbl).append(`<tr>
                    <td style="min-width: 250px" class="sd-${data.sumberdana.kd_sumberdana}">${data.sumberdana.kd_sumberdana}</td>
                    <td style="min-width: 250px">${data.sumberdana.sumberdana}</td>
                    <td class="text-end sum"></td>
                    <td></td><td></td><td></td><td>
                </tr>`)
                $(bodyTbl).append(htmlMaster.join(''))

                data.rekat.forEach( item => {
                    const kodeKeg = item.kd_rk.replace(/\./g, "\\.")
                    $(`.keg-${kodeKeg}`).parent().after(`<tr class="fw-bold">
                        <td class="rekat rekat-${item.id}">${item.id}</td>
                        <td>${item.sub_judul}</td>
                        <td class="text-end sum"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>`)
                })
                data.coa.forEach( item => {
                    const mak = `${data.sumberdana.kd_sumberdana}.${item.kode_ss}.${item.kd_rk.substring(3, 11)}.${item.unit_kerja}.${item.id_rekat}.${item.id_jenis_belanja}`
                    $(`.rekat-${item.id_rekat}`).parent().after(`<tr>
                        <td class="coa coa-${item.id_rekat}-${item.id_jenis_belanja}">${mak}</td>
                        <td>${item.jenis_belanja}</td>
                        <td class="text-end sum"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>`)
                })
                data.itemCoa.forEach( item => {
                    $(`.coa-${item.id_rekat}-${item.id_jenis_belanja}`).parent().after(`<tr>
                        <td style="min-width: 300px; text-align: end;" class="itemCoa itemCoa-${item.id_item_coa}">
                            ${item.nama_ppk ?? 'PPK tidak ditemukan'} <br>
                            ${item.nama_bpp ?? 'BPP tidak ditemukan'}
                        </td>
                        <td>${item.kebutuhan_kegiatan} (
                            ${item.kuantitas ?? '1'} x
                            ${item.durasi ?? '1'} x
                            ${item.kegiatan ?? '1'} x
                            ${rupiah(Number(item.biaya_satuan ?? item.harga_satuan ?? item.jumlah_nilai ?? 0))}
                            )
                        </td>
                        <td class="text-end">${rupiah(item.jumlah_biaya)}</td>
                        <td class="text-end">${rupiah(item.jumlah_amprahan)}</td>
                        <td class="text-end">${rupiah(item.jumlah_biaya - item.jumlah_amprahan)}</td>
                        <td></td>
                    </tr>`)
                })
                data.sumCoa.forEach( item => {
                    $(`.coa-${item.id_rekat}-${item.id_jenis_belanja}`).parent().find("td.sum").text(rupiah(item.TOTAL_COA))
                })
                data.sumRekat.forEach( item => {
                    $(`.rekat-${item.id_rekat}`).parent().find("td.sum").text(rupiah(item.TOTAL_REKAT))
                })
                data.sumKeg.forEach( item => {
                    const kodeKeg = item.kodeKeg.replace(/\./g, "\\.") // Escape dot
                    $(`.keg-${kodeKeg}`).parent().find("td.sum").text(rupiah(item.TOTAL_KEG))
                })
                if ( isPdf === false ) {
                    clearUnwantedRowsByClass("sum")
                    return
                }
                data.sumIkv.forEach( item => {
                    const kodeIkv = item.kodeIkv.replace(/\./g, "\\.") // Escape dot
                    $(`.ikv-${kodeIkv}`).parent().find("td.sum").text(rupiah(item.TOTAL_IKV))
                })
                data.sumIkv.forEach( item => {
                    const kodeIkv = item.kodeIkv.replace(/\./g, "\\.") // Escape dot
                    $(`.ikv-${kodeIkv}`).parent().find("td.sum").text(rupiah(item.TOTAL_IKV))
                })
                data.sumIkk.forEach( item => {
                    const kodeIkk = item.kodeIkk.replace(/\./g, "\\.") // Escape dot
                    $(`.ro-${kodeIkk}`).parent().find("td.sum").text(rupiah(item.TOTAL_IKK))
                })
                data.sumKro.forEach( item => {
                    const kodeSs = item.kodeSs
                    $(`.kro-${kodeSs}`).parent().find("td.sum").text(rupiah(item.TOTAL_KRO))
                })
                data.sumSd.forEach( item => {
                    const kodeSd = item.kodeSd
                    $(`.sd-${kodeSd}`).parent().find("td.sum").text(rupiah(item.TOTAL_SD))
                    $(`.total`).text(rupiah(item.TOTAL_SD))
                    $(`.total_ptnbh`).text(rupiah(item.TOTAL_SD))
                })
                clearUnwantedRowsByClass("sum")
            }, error: ( err ) => {
                $(".loading-div").hide()
                return tata.error("⛔ Error", "Gagal memuat data rpd")
            }
        })
    }
    const generateRkaTahunan = ( idunit, sd, bodyTbl ) => {
        showLoader()
        setLoaderText( "Sedang Memuat Data..." )
        $.ajax({
            type : "GET", url : `/laporan/tahunan/getDetail?idunit=${idunit}&kd_sumberdana=${sd}`,
            success : ( res ) => {
                const { subKomponen, sumberDana, subJudul, coa, itemCoa,
                    dataMaster, unitKerja, sumItemCoa, sumCoa, sumRekat,
                    sumCoaRev, sumRekatRev, sumUnit, sumUnitRev, sumKeg, sumKegRev,
                    sumIkv, sumIkvRev, sumIkk, sumIkkRev, sumKro, sumKroRev, sumSd, sumSdRev
                 } = res.data
                const kodeSd = sumberDana.kd_sumberdana
                const sd     = sumberDana.sumberdana
                $(bodyTbl).children().remove()

                let htmlMaster = []
                dataMaster.forEach(item => {
                    htmlMaster.push(`<tr>
                        <td style="" class="kro kro-${item.kode_ss}">${item.kode_ss}</td>
                        <td style="">${item.sasaran_program}</td>
                        <td class="text-end total_kro total_kro_${item.kode_ss}"></td>
                        <td class="text-end total_kro total_kro_rev1_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev2_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev3_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev4_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev5_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev6_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev7_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev8_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev9_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev10_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev11_${item.kode_ss}">-</td>
                        <td class="text-end total_kro total_kro_rev12_${item.kode_ss}">-</td>
                    </tr>`)
                    item.ro.forEach(itemRo => {
                        htmlMaster.push(`<tr class="total_ikk-${itemRo.kode_ikk}-${kodeSd}">
                            <td>${itemRo.kode_ikk}</td>
                            <td>${itemRo.indikator_kinerja_kegiatan}</td>
                            <td class="text-end total_ikk total_ikk_${itemRo.kode_ikk}"></td>
                            <td class="text-end total_ikk total_ikk_rev1_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev2_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev3_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev4_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev5_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev6_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev7_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev8_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev9_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev10_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev11_${itemRo.kode_ikk}">-</td>
                            <td class="text-end total_ikk total_ikk_rev12_${itemRo.kode_ikk}">-</td>
                        </tr>`)
                        itemRo.ikv.forEach(itemIkv => {
                            htmlMaster.push(`
                                <tr>
                                    <td class="ikv ikv-${itemIkv.kode_ikv}">${itemIkv.kode_ikv}</td>
                                    <td style="">${itemIkv.ikv}</td>
                                    <td class="text-end total_ikv total_ikv_${itemIkv.kode_ikv}"></td>
                                    <td class="text-end total_ikv total_ikv_rev1_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev2_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev3_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev4_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev5_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev6_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev7_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev8_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev9_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev10_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev11_${itemIkv.kode_ikv}">-</td>
                                    <td class="text-end total_ikv total_ikv_rev12_${itemIkv.kode_ikv}">-</td>
                                </tr>
                            `)
                            itemIkv.subkomponen.forEach(itemKeg => {
                                htmlMaster.push(`<tr class="keg-${itemKeg.kode_keg}-${kodeSd}">
                                    <td>${itemKeg.kode_keg}</td>
                                    <td style="">${itemKeg.rincian_kegiatan}</td>
                                    <td class="text-end total_keg total_keg_${itemKeg.kode_keg}"></td>
                                    <td class="text-end total_keg total_keg_rev1_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev2_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev3_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev4_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev5_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev6_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev7_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev8_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev9_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev10_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev11_${itemKeg.kode_keg}">-</td>
                                    <td class="text-end total_keg total_keg_rev12_${itemKeg.kode_keg}">-</td>
                                </tr>`)
                            })
                        })
                    })
                })
                $(bodyTbl).append(`<tr>
                    <td>${kodeSd}</td>
                    <td style="width: 100px">${sd}</td>
                    <td class="text-end total_sd total_sd_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev1_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev2_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev3_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev4_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev5_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev6_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev7_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev8_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev9_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev10_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev11_${kodeSd}">-</td>
                    <td class="text-end total_sd total_sd_rev12_${kodeSd}">-</td>
                </tr>`)
                $(bodyTbl).append(htmlMaster.join(''))

                // 🚀 tampilkan data unitkerja
                unitKerja.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.keg-${kodeKeg}-${kodeSd}`)
                    foundClass.after(`<tr class="tr-unit-${item.kd_rk}-${item.unit_kerja}-${kodeSd}">
                        <td class="idunit idunit-${item.unit_kerja}">${item.unit_kerja}</td>
                        <td>${item.unit_api.nama}</td>
                        <td class="text-end total_unit total_unit-${item.kd_rk}-${item.unit_kerja}"></td>
                        <td class="text-end total_unit total_unit_rev1_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev2_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev3_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev4_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev5_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev6_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev7_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev8_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev9_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev10_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev11_${item.kd_rk}-${item.unit_kerja}">-</td>
                        <td class="text-end total_unit total_unit_rev12_${item.kd_rk}-${item.unit_kerja}">-</td>
                    </tr>`)
                })
                subJudul.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.tr-unit-${kodeKeg}-${item.unit_kerja}-${kodeSd}`)
                    foundClass.after(`<tr class="fw-bold rekat-${item.id}">
                        <td style="" class="kode_keg keg-${item.id}-${kodeSd}">${item.id}</td>
                        <td style="">${item.sub_judul}</td>
                        <td class="text-end total_rekat total_rekat-${item.id}"></td>
                        <td class="text-end total_rekat total_rekat_rev1_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev2_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev3_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev4_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev5_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev6_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev7_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev8_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev9_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev10_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev11_${item.id}">-</td>
                        <td class="text-end total_rekat total_rekat_rev12_${item.id}">-</td>
                    </tr>`)
                })
                coa.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.rekat-${item.id_rekat}`)
                    const mak        = `${kodeSd}.${item.kode_ss}.${item.kd_rk.substring(3, 11)}<br>.${item.unit_kerja}.${item.id_rekat}.${item.id_jenis_belanja}`
                    foundClass.after(`<tr class="coa-${item.id_rekat}-${item.id_jenis_belanja} kk_row">
                        <td class="coa coa-${item.id_jenis_belanja}">${mak}</td>
                        <td>${item.jenis_belanja}</td>
                        <td class="text-end total_coa total_coa-${item.id_rekat}-${item.id_jenis_belanja}"></td>
                        <td class="text-end total_coa jumlah_coa_rev1_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev2_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev3_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev4_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev5_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev6_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev7_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev8_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev9_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev10_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev11_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                        <td class="text-end total_coa jumlah_coa_rev12_${item.id_rekat}-${item.id_jenis_belanja}">-</td>
                    </tr>`)
                })
                // 🚀 tampilkan data item coa
                itemCoa.forEach( item => {
                    const total      = item.jumlah_biaya ?? item.jumlah_nilai ?? 0
                    const foundClass = $(`.coa-${item.id_rekat}-${item.id_jenis_belanja}`)
                    foundClass.after(`<tr class="kk_row">
                        <td style="width: 50px;text-align: end;" class="itemCoa itemCoa-${item.id_item_coa}"></td>
                        <td>${item.kebutuhan_kegiatan}</td>
                        <td style="width: 100px" class="text-end">${ rupiah(total) }</td>
                        <td class="text-end jumlah_item_rev1_${item.id_mak}">${ item.jumlah_rev1 ? rupiah(item.jumlah_rev1) : '-'}</td>
                        <td class="text-end jumlah_item_rev2_${item.id_mak}">${ item.jumlah_rev2 ? rupiah(item.jumlah_rev2) : '-'}</td>
                        <td class="text-end jumlah_item_rev3_${item.id_mak}">${ item.jumlah_rev3 ? rupiah(item.jumlah_rev3) : '-'}</td>
                        <td class="text-end jumlah_item_rev4_${item.id_mak}">${ item.jumlah_rev4 ? rupiah(item.jumlah_rev4) : '-'}</td>
                        <td class="text-end jumlah_item_rev5_${item.id_mak}">${ item.jumlah_rev5 ? rupiah(item.jumlah_rev5) : '-'}</td>
                        <td class="text-end jumlah_item_rev6_${item.id_mak}">${ item.jumlah_rev6 ? rupiah(item.jumlah_rev6) : '-'}</td>
                        <td class="text-end jumlah_item_rev7_${item.id_mak}">${ item.jumlah_rev7 ? rupiah(item.jumlah_rev7) : '-'}</td>
                        <td class="text-end jumlah_item_rev8_${item.id_mak}">${ item.jumlah_rev8 ? rupiah(item.jumlah_rev8) : '-'}</td>
                        <td class="text-end jumlah_item_rev9_${item.id_mak}">${ item.jumlah_rev9 ? rupiah(item.jumlah_rev9) : '-'}</td>
                        <td class="text-end jumlah_item_rev10_${item.id_mak}">${ item.jumlah_rev10 ? rupiah(item.jumlah_rev10) : '-'}</td>
                        <td class="text-end jumlah_item_rev11_${item.id_mak}">${ item.jumlah_rev11 ? rupiah(item.jumlah_rev11) : '-'}</td>
                        <td class="text-end jumlah_item_rev12_${item.id_mak}">${ item.jumlah_rev12 ? rupiah(item.jumlah_rev12) : '-'}</td>
                    </tr>`)
                })

                sumItemCoa.forEach( item => {
                    $(`.jumlah_item_${item.jenis}_${item.id_mak}`).text( rupiah(item.jumlah_biaya) )
                })
                sumCoa.forEach( item => {
                    $(`.total_coa-${item.id_rekat}-${item.id_jenis_belanja}`).text( rupiah(item.TOTAL_COA) )
                })
                sumRekat.forEach( item => {
                    $(`.total_rekat-${item.id_rekat}`).text( rupiah(item.TOTAL_REKAT) )
                })
                sumUnit.forEach( item => {
                    const kodeKeg = item.kodeKeg.replace(/\./g, "\\.")
                    $(`.total_unit-${kodeKeg}-${item.unit_kerja}`).text( rupiah(item.TOTAL_UNIT) )
                })
                sumKeg.forEach( item => {
                    const kodeKeg = item.kodeKeg.replace(/\./g, "\\.")
                    $(`.total_keg_${kodeKeg}`).text( rupiah(item.TOTAL_KEG) )
                })
                sumIkv.forEach( item => {
                    const kodeIkv = item.kodeIkv.replace(/\./g, "\\.")
                    $(`.total_ikv_${kodeIkv}`).text( rupiah(item.TOTAL_IKV) )
                })
                sumIkv.forEach( item => {
                    const kodeIkv = item.kodeIkv.replace(/\./g, "\\.")
                    $(`.total_ikv_${kodeIkv}`).text( rupiah(item.TOTAL_IKV) )
                })
                sumIkk.forEach( item => {
                    const kodeIkk = item.kodeIkk.replace(/\./g, "\\.")
                    $(`.total_ikk_${kodeIkk}`).text( rupiah(item.TOTAL_IKU) )
                })
                sumKro.forEach( item => {
                    const kodeKro = item.kodeSs
                    $(`.total_kro_${kodeKro}`).text( rupiah(item.TOTAL_KRO) )
                })
                sumSd.forEach( item => {
                    const kodeSd = item.kodeSd
                    $(`.total_sd_${kodeSd}`).text( rupiah(item.TOTAL_SD) )
                })
                sumKegRev.forEach( item => {
                    const kodeKeg = item.kd_rk.replace(/\./g, "\\.")
                    $(`.total_keg_${item.jenis}_${kodeKeg}`).text( rupiah(item.TOTAL_KEG) )
                })
                sumCoaRev.forEach( item => {
                    $(`.jumlah_coa_${item.jenis}_${item.id_rekat}-${item.id_coa}`).text( rupiah(item.TOTAL_COA) )
                })
                sumRekatRev.forEach( item => {
                    $(`.total_rekat_${item.jenis}_${item.id_rekat}`).text( rupiah(item.TOTAL_REKAT) )
                })
                sumUnitRev.forEach( item => {
                    const kodeKeg = item.kd_rk.replace(/\./g, "\\.")
                    $(`.total_unit_${item.jenis}_${kodeKeg}-${item.unit_kerja}`).text( rupiah(item.TOTAL_UNIT) )
                })
                sumIkvRev.forEach( item => {
                    const kodeIkv = item.kode_ikv.replace(/\./g, "\\.")
                    $(`.total_ikv_${item.jenis}_${kodeIkv}`).text( rupiah(item.TOTAL_IKV) )
                })
                sumIkkRev.forEach( item => {
                    const kodeIkk = item.kode_ikk.replace(/\./g, "\\.")
                    $(`.total_ikk_${item.jenis}_${kodeIkk}`).text( rupiah(item.TOTAL_IKU) )
                })
                sumKroRev.forEach( item => {
                    const kodeSs = item.kodeSs
                    $(`.total_kro_${item.jenis}_${kodeSs}`).text( rupiah(item.TOTAL_KRO) )
                })
                sumSdRev.forEach( item => {
                    const kodeSd = item.kodeSd
                    $(`.total_sd_${item.jenis}_${kodeSd}`).text( rupiah(item.TOTAL_SD) )
                })
                clearUnwantedRowsByClass("total_coa")
                // clearUnwantedRowsByClass("total_rekat")
                // clearUnwantedRowsByClass("total_unit")
                clearUnwantedRowsByClass("total_keg")
                clearUnwantedRowsByClass("total_ikv")
                clearUnwantedRowsByClass("total_ikk")
                clearUnwantedRowsByClass("total_kro")
                removeLoader()
            },
            err : ( err ) => {
                const message = err.responseJSON.message || "Gagal memuat data"
                return tata.error("Error", message)
            }
        })
    }
    const generateRkaPaket = ( idunit, sd, url, bodyTbl, isPdf ) => {
        showLoader()
        setLoaderText( "Sedang Memuat Data..." )
        $.ajax({
            type: "GET",
            url: url,
            success: ( res ) => {
                const { dataMaster, unitKerja, subJudul, coa, itemCoa, sumberdana } = res.data
                const kodeSd = sumberdana.kd_sumberdana
                const sd     = sumberdana.sumberdana

                // 🚀 tampilkan master data
                const sdColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, 1); color: black; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, 1); color: darkblue")
                const kroColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .8); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .8); color: darkblue")
                const roColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .7); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .7); color: darkblue")
                const ikvColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .6); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .6); color: darkblue")
                const skColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .5); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .5); color: darkblue")
                const unitColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .4); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .4); color: #FF8355")
                const rekatColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .3); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .3)")
                const coaColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .2); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .2)")

                let htmlMaster = []
                dataMaster.forEach(item => {
                    htmlMaster.push(`
                        <tr>
                            <td style="${kroColor}" class="kro kro-${item.kode_ss}">${item.kode_ss}</td>
                            <td style="${kroColor}">${item.sasaran_program}</td>
                            <td style="${kroColor}"></td><td style="${kroColor}"></td>
                            <td style="${kroColor}" class="text-end total_kro total_kro-${item.kode_ss}-${kodeSd}"></td>
                            <td style="${kroColor}"></td><td style="${kroColor}" class="text-end"></td>
                            <td style="${kroColor}" class="text-end"></td><td style="${kroColor}" class="text-end"></td>
                            <td style="${kroColor}" class="text-end"></td>
                        </tr>
                    `)
                    item.ro.forEach(itemRo => {
                        htmlMaster.push(`
                            <tr>
                                <td style="${roColor}" class="ro ro-${itemRo.kode_ikk}">${itemRo.kode_ikk}</td>
                                <td style="${roColor}">${itemRo.indikator_kinerja_kegiatan}</td>
                                <td style="${roColor}"></td><td style="${roColor}"></td>
                                <td style="${roColor}" class="text-end total_ikk total_ikk-${itemRo.kode_ikk}-${kodeSd}"></td>
                                <td style="${roColor}"></td><td style="${roColor}" class="text-end"></td>
                                <td style="${roColor}" class="text-end"></td><td style="${roColor}" class="text-end"></td>
                                <td style="${roColor}" class="text-end"></td>
                            </tr>
                        `)
                        itemRo.ikv.forEach(itemIkv => {
                            htmlMaster.push(`
                                <tr>
                                    <td style="${ikvColor}" class="ikv ikv-${itemIkv.kode_ikv}">${itemIkv.kode_ikv}</td>
                                    <td style="${ikvColor}">${itemIkv.ikv}</td>
                                    <td style="${ikvColor}"></td><td style="${ikvColor}"></td>
                                    <td style="${ikvColor}" class="text-end total_ikv total_ikv-${itemIkv.kode_ikv}-${kodeSd}"></td>
                                    <td style="${ikvColor}" ></td><td style="${ikvColor}" class="text-end"></td>
                                    <td style="${ikvColor}" class="text-end"></td><td style="${ikvColor}" class="text-end"></td>
                                    <td style="${ikvColor}" class="text-end"></td>
                                </tr>
                            `)
                            itemIkv.subkomponen.forEach(itemKeg => {
                                htmlMaster.push(`
                                    <tr>
                                        <td style="${skColor}" class="kode_keg keg-${itemKeg.kode_keg}-${kodeSd}">${itemKeg.kode_keg}</td>
                                        <td style="${skColor}">${itemKeg.rincian_kegiatan}</td>
                                        <td style="${skColor}"></td><td style="${skColor}"></td>
                                        <td style="${skColor}" class="text-end total_keg total_keg-${itemKeg.kode_keg}-${kodeSd}"></td>
                                        <td style="${skColor}"></td><td style="${skColor}" class="text-end"></td>
                                        <td style="${skColor}" class="text-end"></td><td style="${skColor}" class="text-end"></td>
                                        <td style="${skColor}" class="text-end"></td>
                                    </tr>
                                `)
                            })
                        })
                    })
                })

                $(`${bodyTbl}`).append(`<tr>
                    <td style="${sdColor}">${kodeSd}</td>
                    <td style="width: 100px;${sdColor}">${sd}</td>
                    <td style="${sdColor}"></td><td style="${sdColor}"></td>
                    <td style="width: 50px; ${sdColor}" class="total-sd text-end total-${kodeSd}"></td>
                    <td style="width: 50px; ${sdColor}"></td><td style="width: 50px;${sdColor}" class="text-end total_proses">Rp 0</td>
                    <td style="width: 50px; ${sdColor}" class="text-end total_amprah-${kodeSd}"></td>
                    <td style="width: 50px; ${sdColor}" class="text-end"></td>
                    <td style="width: 50px; ${sdColor}" class="text-end sisa_amprah-${kodeSd}"></td>
                </tr>`)
                $(`${bodyTbl}`).append(htmlMaster.join(''))

                // 🚀 tampilkan data unitkerja
                unitKerja.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.keg-${kodeKeg}-${kodeSd}`)
                    foundClass.parent().after(`<tr class="tr-unit-${item.kd_rk}-${item.unit_kerja}-${kodeSd}">
                        <td style="${unitColor}" class="idunit idunit-${item.unit_kerja}">${item.unit_kerja}</td>
                        <td style="${unitColor}">${item.unit_api.nama}</td>
                        <td style="${unitColor}"></td><td style="${unitColor}"></td>
                        <td style="${unitColor}" class="text-end total_unit total_unit-${item.kd_rk}-${item.unit_kerja}-${kodeSd}"></td>
                        <td style="${unitColor}"></td><td style="${unitColor}" class="text-end"></td>
                        <td style="${unitColor}" class="text-end"></td>
                        <td style="${unitColor}" class="text-end"></td><td style="${unitColor}" class="text-end"></td>
                    </tr>`)
                })
                // 🚀 tampilkan data rekat
                subJudul.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.tr-unit-${kodeKeg}-${item.unit_kerja}-${kodeSd}`)
                    foundClass.after(`<tr class="fw-bold rekat-${item.id}" style="border-top:2.5px solid black">
                        <td style="${rekatColor}" class="idRekat idRekat-${item.id}">${item.id}</td>
                        <td style="${rekatColor}">${item.sub_judul}</td>
                        <td style="${rekatColor}"></td><td style="${rekatColor}"></td>
                        <td style="${rekatColor}" class="text-end total_rekat total_rekat-${item.id}-${kodeSd}"></td>
                        <td style="${rekatColor}"></td><td style="${rekatColor}" class="text-end"></td>
                        <td style="${rekatColor}" class="text-end"></td><td style="${rekatColor}" class="text-end"></td>
                        <td style="${rekatColor}" class="text-end"></td>
                    </tr>`)
                })
                // 🚀 tampilkan data coa
                coa.forEach( item => {
                    const kodeKeg    = item.kd_rk.replace(/\./g, "\\.")
                    const foundClass = $(`.rekat-${item.id_rekat}`)
                    const mak        = `${sumberdana.kd_sumberdana}.${item.kode_ss}.${item.kd_rk.substring(3, 11)}<br>.${item.unit_kerja}.${item.id_rekat}.${item.id_jenis_belanja}`
                    foundClass.after(`<tr class="coa-${item.id_rekat}-${item.id_jenis_belanja} kk_row">
                        <td style="${coaColor};" class="coa coa-${item.id_jenis_belanja}">${mak}</td>
                        <td style="${coaColor}">${item.jenis_belanja}</td>
                        <td style="${coaColor}"></td><td style="${coaColor}"></td>
                        <td style="${coaColor}" class="text-end total_coa total_coa-${item.id_rekat}-${item.id_jenis_belanja}-${kodeSd}"></td>
                        <td style="${coaColor}"></td><td style="${coaColor}" class="text-end"></td>
                        <td style="${coaColor}" class="text-end"></td><td style="${coaColor}" class="text-end"></td>
                        <td style="${coaColor}" class="text-end"></td>
                    </tr>`)
                })
                // 🚀 tampilkan data item coa
                itemCoa.forEach( item => {
                    function kalkulasiVerifikasi(verifikasiArray) {
                        let bintang = []
                        let totalVer = verifikasiArray.filter(word => word === "SILAHKAN PILIH" || word === "" || word === null || word === "Tolak")
                        for (let i = 0; i < totalVer.length; i++) {
                            bintang.push("*")
                        }
                        return bintang
                    }
                    let bintang    = []
                    let htmlStatus = ''
                    if (item.jenis === "OPERASIONAL") {
                        bintang = kalkulasiVerifikasi([item.verifikasi_tim_keg, item.verifikasi_pimpinan_keg, item.verifikasi_univ_keg, item.verifikasi_keu_keg]);
                    } else if (item.jenis === "SARANA") {
                        bintang = kalkulasiVerifikasi([item.verifikasi_tim_per, item.verifikasi_pimpinan_per, item.verifikasi_univ_per, item.verifikasi_keu_per, item.verifikasi_aset_per]);
                    } else if (item.jenis === "PRASARANA") {
                        bintang = kalkulasiVerifikasi([item.verifikasi_tim_gdg, item.verifikasi_pimpinan_gdg, item.verifikasi_univ_gdg, item.verifikasi_keu_gdg, item.verifikasi_aset_gdg]);
                    }
                    if ( bintang.length !== 0 ) {
                        htmlStatus = "<p class='px-1 py-1 ml-1 text-white ml-1 d-inline-block' style='font-size:9px;background-color:red'>Belum diverifikasi</p>";
                    }
                    const realisasi  = Number(item.jumlah_amprahan) + Number(item.jumlah_realisasi)
                    const isTagihan  = item.jumlah_tagihan ? true : false
                    const foundClass = $(`.coa-${item.id_rekat}-${item.id_jenis_belanja}`)
                    foundClass.after(`<tr class="kk_row">
                        <td style="width: 50px;text-align: end;" class="itemCoa itemCoa-${item.id_item_coa}" ppk="${item.nip_ppk}" bpp="${item.nip_bpp}"
                            jenis="${item.jenis}" key="${item.id_item_coa}" jumlahBiaya="${item.jumlah_biaya}" idCoa="${item.id_jenis_belanja}">
                            ${isPdf ? '-' : ( item.nama_ppk ?? 'PPK tidak ditemukan')} <br>
                            ${isPdf ? '' : ( item.nama_bpp ?? 'BPP tidak ditemukan') }
                        </td>
                        <td class="status-verif" role="button">
                        ${item.kebutuhan_kegiatan}
                        ${item.kebutuhan_kegiatan && item.kebutuhan_kegiatan.length > 20 ? '<br>' : ''}
                        ${isPdf ? '' : htmlStatus}
                        </td>
                        <td style="width: 100px">
                        (${item.kuantitas ?? '-'} ${item.satuan_kuantitas ?? '-'} X
                        ${item.durasi ?? 1} ${item.satuan_durasi ?? 'Pkt'} X
                        ${item.kegiatan ?? 1} ${item.satuan_keg ?? 'Keg'})
                        </td>
                        <td style="width: 100px;text-align: right;">${rupiah(Number(item.biaya_satuan ?? item.harga_satuan ?? item.jumlah_biaya ?? 0))}</td>
                        <td style="text-align: right;">${ rupiah( Number(item.jumlah_biaya) - Number(item.jumlah_pengalihan) ) }</td>
                        <td style="width: 0px">${item.rpd ?? ""}</td>
                        <td class="text-end">${rupiah( item.jumlah_amprahan )}</td>
                        <td class="realisasi ${item.id_mak} text-end ${isTagihan ? 'text-primary' : ''}" total-tagihan="${item.jumlah_tagihan}" total-biaya="${item.jumlah_biaya}" ${isTagihan ? 'role="button"' : ''}>
                            ${ rupiah(item.jumlah_realisasi ?? ( item.jumlah_tagihan ?? '0' ) )}
                        </td>
                        <td class="text-end jumlah_pengalihan ${item.id_item_coa}">${ rupiah( item.jumlah_pengalihan ?? 0 ) }</td>
                        <td class="text-end">${ rupiah( Number(item.jumlah_biaya) - Number(realisasi ?? ( item.jumlah_tagihan ?? 0 ) ) - Number(item.jumlah_pengalihan ?? 0 ) )  }</td>
                    </tr>`)
                })
                return
            },
            error: ( err ) => {
                const message = err.responseJSON.message || "Gagal memuat data"
                return tata.error("⛔ Error", message)
            }
        })

    }
    const generateRkatLampiran = ( idunit, kodeSd, filter = null, bodyTbl, isPdf, isCombine, modified = null ) => {
        return new Promise( ( resolve ) => {
            $.ajax({
                "type": "GET",
                "url": "{{ route('rkat.lampiran.get') }}",
                data: { idunit, "sumberdana": kodeSd, filter, modified },
                success: ( res ) => {
                    const { dataMaster, sumKeg, sumIkv, sumRo, sumKro, sumSd, sumberdana, listKodeSd, parent } = res.data

                    if ( dataMaster.length == 0 ) {
                        removeLoader()
                        return tata.error("⛔ Error", "Data tidak ditemukan")
                    }
                    if ( sumberdana.length == 0 ) {
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error("⛔ Error", "Data sumberdana tidak ditemukan")
                        }
                        $(".statusError").show("slow")
                        return
                    }
                    if ( sumSd.length == 0 ) {
                        if ( !isPdf ) {
                            removeLoader()
                            return tata.error("⛔ Error", "Data sumberdana tidak ditemukan")
                        }
                        $(".statusError").show("slow")
                        return
                    }
                    const sdColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, 1); color: black; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, 1); color: darkblue")
                    const kroColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .8); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .8); color: darkblue")
                    const roColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .7); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .7); color: darkblue")
                    const ikvColor = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .6); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .5); color: darkblue")
                    const skColor  = isPdf ? "" : ( isThemed ? "background-color: rgba(0,255,255, .5); color: white; border-bottom: 1px solid gray"
                        : "background-color: rgba(0,255,255, .5); color: darkblue")

                    bodyTbl.html("")

                    const masterHtml = []
                    if ( kodeSd == "ptnbh" || kodeSd == "bptnbh" ) {
                        const globalKodeSd = kodeSd == "ptnbh" ? "41" : "42"
                        const globalNamaSd = kodeSd == "ptnbh" ? "Selain APBN" : "APBN"
                        parent.forEach( itemSd => {
                            masterHtml.push(`<tr class="sd-${itemSd.kd_sumberdana} parent-${itemSd.kd_sumberdana.substring(0,6)} fw-bold rekapSemuaUnit ${ ( idunit == 'semua_unit' && !isPdf ) ? 'trHover' : '' }" ${ ( idunit == 'semua_unit' && !isPdf ) ? "role=button" : ''} style="font-size: 15px;">
                                <td style="${sdColor}; width: 130px">${itemSd.kd_sumberdana}</td>
                                <td style="${sdColor}">${itemSd.sumberdana}</td>
                                <td style="${sdColor}" class="text-center sdCount">1</td><td style="${sdColor}" class="text-center">Keg</td>
                                <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Tahun</td>
                                <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Paket</td>
                                <td style="${sdColor}" class="totalSd total-${itemSd.kd_sumberdana} text-end"></td>
                            </tr>`)
                            if ( itemSd.kd_sumberdana.length === 8 ) {
                                dataMaster.forEach( item => {
                                    masterHtml.push(`
                                        <tr class="kro-${item.kode_ss_rkat}-${itemSd.kd_sumberdana} target-element" jenis="kro" sd="${itemSd.kd_sumberdana}">
                                            <td style="${kroColor}; width: 130px">${item.kode_ss_rkat}</td>
                                            <td style="${kroColor}">${item.sasaran_rkat}</td>
                                            <td style="${kroColor}" class="text-center kroCount">1</td><td style="${kroColor}" class="text-center">Keg</td>
                                            <td style="${kroColor}" class="text-center">1</td><td style="${kroColor}" class="text-center">Tahun</td>
                                            <td style="${kroColor}" class="text-center">1</td><td style="${kroColor}" class="text-center">Paket</td>
                                            <td style="${kroColor}" class="totalKro total-${item.kode_ss_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                    </tr>`)
                                    item.ro_rkat.forEach( itemRoRkat => {
                                        masterHtml.push(`<tr class="ro-${itemRoRkat.kode_ro_rkat}-${itemSd.kd_sumberdana} target-element" jenis="ro" sd="${itemSd.kd_sumberdana}">
                                            <td style="${roColor}">${itemRoRkat.kode_ro_rkat}</td>
                                            <td style="${roColor}">${itemRoRkat.ro_rkat}</td>
                                            <td style="${roColor}" class="text-center roCount">1</td><td style="${roColor}" class="text-center">Keg</td>
                                            <td style="${roColor}" class="text-center">1</td><td style="${roColor}" class="text-center">Tahun</td>
                                            <td style="${roColor}" class="text-center">1</td><td style="${roColor}" class="text-center">Paket</td>
                                            <td style="${roColor}" class="totalRo total-${itemRoRkat.kode_ro_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                        </tr>`)
                                        itemRoRkat.ikv_rkat.forEach( itemIkvRkat => {
                                            masterHtml.push(`<tr class="ikv-${itemIkvRkat.kode_ikv_rkat}-${itemSd.kd_sumberdana} target-element" jenis="ikv" sd="${itemSd.kd_sumberdana}">
                                                <td style="${ikvColor}">${itemIkvRkat.kode_ikv_rkat}</td>
                                                <td style="${ikvColor}">${itemIkvRkat.ikv_rkat}</td>
                                                <td style="${ikvColor}" class="text-center ikvCount">1</td><td style="${ikvColor}" class="text-center">Keg</td>
                                                <td style="${ikvColor}" class="text-center">1</td><td style="${ikvColor}" class="text-center">Tahun</td>
                                                <td style="${ikvColor}" class="text-center">1</td><td style="${ikvColor}" class="text-center">Paket</td>
                                                <td style="${ikvColor}" class="totalIkv total-${itemIkvRkat.kode_ikv_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                            </tr>`)
                                            itemIkvRkat.subkomponen_rkat.forEach( itemKegRkat => {
                                                masterHtml.push(`<tr class="keg-${itemKegRkat.kode_keg_rkat}-${itemSd.kd_sumberdana} target-element" jenis="keg" sd="${itemSd.kd_sumberdana}">
                                                    <td style="">${itemKegRkat.kode_keg_rkat}</td>
                                                    <td style="" style="width: 450px">${itemKegRkat.keg_rkat}</td>
                                                    <td class="text-center kegCount">1</td><td class="text-center">Keg</td>
                                                    <td class="text-center">1</td><td class="text-center">Tahun</td>
                                                    <td class="text-center">1</td><td class="text-center">Paket</td>
                                                    <td style="" class="totalKeg total-${itemKegRkat.kode_keg_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                                </tr>`)
                                            })
                                        })
                                    })
                                })
                            }
                        })
                        bodyTbl.html(masterHtml.join(''))
                        bodyTbl.prepend(`<tr class="sd-${globalKodeSd} fw-bold" style="font-size: 16px;">
                                <td style="${sdColor}; width: 130px">${globalKodeSd}</td>
                                <td style="${sdColor}">${globalNamaSd}</td>
                                <td style="${sdColor}" class="text-center sdCount">1</td><td style="${sdColor}" class="text-center">Keg</td>
                                <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Tahun</td>
                                <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Paket</td>
                                <td style="${sdColor}" class="totalSd total-${globalKodeSd} text-end"></td>
                            </tr>
                            `)
                    } else {
                        sumberdana.forEach( itemSd => {
                            masterHtml.push(`
                                <tr class="sd-${itemSd.kd_sumberdana} parent-${itemSd.kd_sumberdana.substring(0,6)} fw-bold rekapSemuaUnit ${ ( idunit == 'semua_unit' && !isPdf ) ? 'trHover' : '' }" ${ ( idunit == 'semua_unit' && !isPdf ) ? "role=button" : ''} style="font-size: 16px;">
                                        <td style="${sdColor}; width: 130px">${itemSd.kd_sumberdana}</td>
                                        <td style="${sdColor}">${itemSd.sumberdana}</td>
                                        <td style="${sdColor}" class="text-center sdCount">1</td><td style="${sdColor}" class="text-center">Keg</td>
                                        <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Tahun</td>
                                        <td style="${sdColor}" class="text-center">1</td><td style="${sdColor}" class="text-center">Paket</td>
                                        <td style="${sdColor}" class="totalSd total-${itemSd.kd_sumberdana} text-end"></td>
                                </tr>`)
                            dataMaster.forEach( item => {
                                masterHtml.push(`
                                    <tr class="kro-${item.kode_ss_rkat}-${itemSd.kd_sumberdana} target-element" jenis="kro" sd="${itemSd.kd_sumberdana}">
                                        <td style="${kroColor}; width: 130px">${item.kode_ss_rkat}</td>
                                        <td style="${kroColor}">${item.sasaran_rkat}</td>
                                        <td style="${kroColor}" class="text-center kroCount">1</td><td style="${kroColor}" class="text-center">Keg</td>
                                        <td style="${kroColor}" class="text-center">1</td><td style="${kroColor}" class="text-center">Tahun</td>
                                        <td style="${kroColor}" class="text-center">1</td><td style="${kroColor}" class="text-center">Paket</td>
                                        <td style="${kroColor}" class="totalKro total-${item.kode_ss_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                </tr>`)
                                item.ro_rkat.forEach( itemRoRkat => {
                                    masterHtml.push(`<tr class="ro-${itemRoRkat.kode_ro_rkat}-${itemSd.kd_sumberdana} target-element" jenis="ro" sd="${itemSd.kd_sumberdana}">
                                        <td style="${roColor}">${itemRoRkat.kode_ro_rkat}</td>
                                        <td style="${roColor}">${itemRoRkat.ro_rkat}</td>
                                        <td style="${roColor}" class="text-center roCount">1</td><td style="${roColor}" class="text-center">Keg</td>
                                        <td style="${roColor}" class="text-center">1</td><td style="${roColor}" class="text-center">Tahun</td>
                                        <td style="${roColor}" class="text-center">1</td><td style="${roColor}" class="text-center">Paket</td>
                                        <td style="${roColor}" class="totalRo total-${itemRoRkat.kode_ro_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                    </tr>`)
                                    itemRoRkat.ikv_rkat.forEach( itemIkvRkat => {
                                        masterHtml.push(`<tr class="ikv-${itemIkvRkat.kode_ikv_rkat}-${itemSd.kd_sumberdana} target-element" jenis="ikv" sd="${itemSd.kd_sumberdana}">
                                            <td style="${ikvColor}">${itemIkvRkat.kode_ikv_rkat}</td>
                                            <td style="${ikvColor}">${itemIkvRkat.ikv_rkat}</td>
                                            <td style="${ikvColor}" class="text-center ikvCount">1</td><td style="${ikvColor}" class="text-center">Keg</td>
                                            <td style="${ikvColor}" class="text-center">1</td><td style="${ikvColor}" class="text-center">Tahun</td>
                                            <td style="${ikvColor}" class="text-center">1</td><td style="${ikvColor}" class="text-center">Paket</td>
                                            <td style="${ikvColor}" class="totalIkv total-${itemIkvRkat.kode_ikv_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                        </tr>`)
                                        itemIkvRkat.subkomponen_rkat.forEach( itemKegRkat => {
                                            masterHtml.push(`<tr class="keg-${itemKegRkat.kode_keg_rkat}-${itemSd.kd_sumberdana} target-element" jenis="keg" sd="${itemSd.kd_sumberdana}">
                                                <td style="">${itemKegRkat.kode_keg_rkat}</td>
                                                <td style="" style="width: 450px">${itemKegRkat.keg_rkat}</td>
                                                <td class="text-center kegCount">1</td><td class="text-center">Keg</td>
                                                <td class="text-center">1</td><td class="text-center">Tahun</td>
                                                <td class="text-center">1</td><td class="text-center">Paket</td>
                                                <td style="" class="totalKeg total-${itemKegRkat.kode_keg_rkat}-${itemSd.kd_sumberdana} text-end"></td>
                                            </tr>`)
                                        })
                                    })
                                })
                            })
                        })
                        bodyTbl.html(masterHtml.join(''))
                    }
                    sumKeg.forEach( item => {
                        const kodeKegRkat = item.kodeKegRkat.replace(/\./g, "\\.") // escape dot
                        const foundClass  = $(`.total-${kodeKegRkat}-${item.kd_sumberdana}`)
                        foundClass.parent().find(".kegCount").text( item.jumlahKeg ?? 0 )
                        foundClass.text( rupiah( item.TOTAL_MODIF ?? item.TOTAL_KEG ) )
                    })
                    sumIkv.forEach( item => {
                        const TOTAL       = item.TOTAL_MODIF === "not-found" ? item.TOTAL_IKV : item.TOTAL_MODIF
                        const kodeIkvRkat = item.kodeIkvRkat.replace(/\./g, "\\.") // escape dot
                        const foundClass  = $(`.total-${kodeIkvRkat}-${item.kd_sumberdana}`)
                        foundClass.parent().find(".ikvCount").text( item.jumlahKeg ?? 0 )
                        foundClass.text( rupiah( TOTAL ) )
                    })
                    sumRo.forEach( item => {
                        const TOTAL       = item.TOTAL_MODIF === "not-found" ? item.TOTAL_RO : item.TOTAL_MODIF
                        const kodeRoRkat  = item.kodeRoRkat.replace(/\./g, "\\.") // escape dot
                        const foundClass  = $(`.total-${kodeRoRkat}-${item.kd_sumberdana}`)
                        foundClass.parent().find(".roCount").text( item.jumlahKeg ?? 0 )
                        foundClass.text( rupiah( TOTAL ) )
                    })
                    sumKro.forEach( item => {
                        const TOTAL       = item.TOTAL_MODIF === "not-found" ? item.TOTAL_KRO : item.TOTAL_MODIF
                        const kodeSsRkat  = item.kodeSsRkat.replace(/\./g, "\\.") // escape dot
                        const foundClass  = $(`.total-${kodeSsRkat}-${item.kd_sumberdana}`)
                        foundClass.parent().find(".kroCount").text( item.jumlahKeg ?? 0 )
                        foundClass.text( rupiah( TOTAL ) )
                    })
                    const totalSd = sumSd.reduce((acc, item) => acc + Number( item.TOTAL_MODIF === "not-found" ? item.TOTAL_SD : item.TOTAL_MODIF ), 0)
                    sumSd.forEach( item => {
                        const TOTAL      = item.TOTAL_MODIF === "not-found" ? item.TOTAL_SD : item.TOTAL_MODIF
                        const kodeSd     = item.kd_sumberdana
                        const foundClass = $(`.total-${kodeSd}`)
                        foundClass.text( rupiah( TOTAL ) )
                        $(`.totalPtnbh-${kodeSd}`).text( rupiah( TOTAL ) )
                        $(`.totalSumberdana`).text( rupiah( totalSd ?? 0 ) )
                        foundClass.parent().find(".sdCount").text( item.jumlahKeg ?? 0 )
                    })
                    if ( isCombine === false ) {
                        clearUnwantedRowLampiranRkat()
                    }
                    removeLoader()
                    resolve( res )
                }, error: ( err ) => {
                    removeLoader()
                    const message = err.responseJSON.message || "Gagal mendapatkan data"
                    return tata.error("⛔ Error", message)
                }
            })

            const clearUnwantedRowLampiranRkat = () => {
                $(".totalSd").each(function(){
                    const text = $(this).text()
                    if( text == "" || !text ){
                        $(this).closest("tr").remove()
                    }
                })
                $(".totalKro").each(function(){
                    const text = $(this).text()
                    if( text == "" || !text ){
                        $(this).closest("tr").remove()
                    }
                })
                $(".totalRo").each(function(){
                    const text = $(this).text()
                    if( text == "" || !text ){
                        $(this).closest("tr").remove()
                    }
                })
                $(".totalIkv").each(function(){
                    const text = $(this).text()
                    if( text == "" || !text ){
                        $(this).closest("tr").remove()
                    }
                })
                $(".totalKeg").each(function(){
                    const text = $(this).text()
                    if( text == "" || !text ){
                        $(this).closest("tr").remove()
                    }
                })

            }
        })
    }
</script>
