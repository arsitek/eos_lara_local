<script type="text/javascript">
$( document ).ready( function () {
    window.laporan = {}
    window.laporan.rka = {}
    window.laporan.rka.methods = {}
    const bodyTblUnit = $(".body-tbl-unit")
    window.laporan.rka.methods = {
        createOrUpdateObject: ( object, key, createNode ) => {
            if ( !object[key] ) object[key] = createNode()
            return object[key]
        },
        createRows: ({ kode, label, total, totalAmprahan, totalRealisasi }, cssClass, customStyle, addOns = '', isPdf = false, isHeader = false ) => {
            const realisasi = totalAmprahan + totalRealisasi;
            const sisa      = total - realisasi;
            customStyle     = ( isPdf === false && customStyle != '' ) ? customStyle : '';
            if ( isHeader === true ) {
                return `<tr class="${cssClass}" style="${customStyle || ''}">
                    <td style="text-align: center; font-size: 14px" colspan="3">Total</td>
                    <td class="total text-end">${total}</td>
                    <td style="width: 50px;"></td>
                    <td style="width: 50px;" class="text-end total_proses">${ rupiah(totalAmprahan) }</td>
                    <td style="width: 50px;" class="text-end total_real">${ rupiah(totalRealisasi) }</td>
                    <td style="width: 50px;" class="text-end"></td>
                    <td style="width: 50px;" class="text-end total_sisa">${ rupiah(sisa) }</td>
                </tr>`
            }
            return `
                <tr class="${cssClass}" style="${customStyle || ''}">
                <td style="width:200px">${kode}</td><td style="width:100px;">${label}</td><td></td>
                <td class="total-sd text-end">${rupiah(total)}</td>
                <td style="width:50px;"></td>
                <td style="width:50px;" class="text-end">${rupiah(totalAmprahan)}</td>
                <td style="width:50px;" class="text-end">${rupiah(totalRealisasi)}</td>
                <td style="width:50px;" class="text-end"></td>
                <td style="width:50px;" class="text-end">${rupiah(sisa)}</td>
                </tr>`;
        },
        getBaseData: ( idunit, kodeSd, filter, backup ) => {
            return new Promise( ( resolve, reject ) => {
                $.ajax({
                    url: "{{ route('rktReportUnit.getBaseData') }}",
                    data: { idunit, kodeSd, filter, backup },
                    success: ( res ) => resolve( res ),
                    error: ( err ) => {
                        const message = err.responseJSON?.message || "Terjadi kesalahan saat mengambil data base RKA";
                        reject( message )
                    }
                })
            })
        },
        generateRkaFinal: ( data, isPdf = false) => {
            if ( data === null || data.length === 0 ) {
                bodyTblUnit.empty().append('<tr><td colspan="9" class="text-center">Tidak ada data yang ditemukan</td></tr>');
                removeLoader();
                return
            }
            // buildData
            const basedata = { sub: {}, total: 0, totalRealisasi: 0, totalAmprahan: 0 }
            data.forEach( item => {
                const { kd_sumberdana: kodeSd, sumberdana, kode_ss: kodeSs, ss, kode_ikk: kodeIkk, ikk, kode_ikv: kodeIkv, ikv, kode_keg: kodeKeg, rincian_kegiatan: rincianKeg,
                    unit_kerja_rkt: idunit, nama_unit: namaUnit, id_paket: idPaket, id_rekat: idRekat, id_mak_paket: idMakPaket, judul_paket: judulPaket, sub_judul: subJudul,
                    total_paket: totalPaket, jumlah_biaya: jumlahBiaya, TOTAL_AMPRAH: totalAmprah, TOTAL_REALISASI: totalRealisasi
                } = item
                const sumberdanaObj = window.laporan.rka.methods.createOrUpdateObject( basedata.sub, kodeSd, () => ({ sub: {}, kodeSd, sumberdana, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                const sasaranObj    = window.laporan.rka.methods.createOrUpdateObject( sumberdanaObj.sub, kodeSs, () => ({ sub: {}, kodeSs, ss, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                const ikuObj        = window.laporan.rka.methods.createOrUpdateObject( sasaranObj.sub, kodeIkk, () => ({ sub: {}, kodeIkk, ikk, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                const ikvObj        = window.laporan.rka.methods.createOrUpdateObject( ikuObj.sub, kodeIkv, () => ({ sub: {}, kodeIkv, ikv, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                const kegObj        = window.laporan.rka.methods.createOrUpdateObject( ikvObj.sub, kodeKeg, () => ({ sub: {}, kodeKeg, rincianKeg, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                const unitObj       = window.laporan.rka.methods.createOrUpdateObject( kegObj.sub, idunit, () => ({ sub: {}, idunit, namaUnit, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                if ( judulPaket ) {
                    const rekatObj = window.laporan.rka.methods.createOrUpdateObject( unitObj.sub, idPaket, () => ({ sub: {}, idPaket, idMakPaket, judulPaket, subJudul, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                    rekatObj.total = Number(totalPaket)
                    rekatObj.totalRealisasi += Number(totalRealisasi)
                    rekatObj.totalAmprahan += Number(totalAmprah)
                } else {
                    const rekatObj = window.laporan.rka.methods.createOrUpdateObject( unitObj.sub, idRekat, () => ({ sub: {}, idRekat, judulPaket, subJudul, total: 0, totalRealisasi: 0, totalAmprahan: 0 }) )
                    rekatObj.total += Number(jumlahBiaya)
                    rekatObj.totalRealisasi += Number(totalRealisasi)
                    rekatObj.totalAmprahan += Number(totalAmprah)
                }
                // Update totals
                [ basedata, sumberdanaObj, sasaranObj, ikuObj, ikvObj, kegObj, unitObj].forEach(obj => {
                    obj.total           += Number(jumlahBiaya)
                    obj.totalRealisasi  += Number(totalRealisasi)
                    obj.totalAmprahan   += Number(totalAmprah)
                });
            })

            // Generate HTML
            const rows = [];
            rows.push(window.laporan.rka.methods.createRows({
                kode: '',
                label: 'Total',
                total: basedata.total,
                totalAmprahan: basedata.totalAmprahan,
                totalRealisasi: basedata.totalRealisasi
            }, 'fw-bold headerTotal', 'background-color: rgba(0,255,255, 1); color: darkblue', '', isPdf, true));
            Object.values(basedata.sub).forEach(itemSd => {
                // sumber dana row
                $(`.total-${itemSd.kodeSd}`).text( rupiah(itemSd.total) )

                rows.push(window.laporan.rka.methods.createRows({
                    kode: itemSd.kodeSd,
                    label: itemSd.sumberdana,
                    total: itemSd.total,
                    totalAmprahan: itemSd.totalAmprahan,
                    totalRealisasi: itemSd.totalRealisasi
                }, 'fw-bold headerSumberdana', 'background-color: rgba(0,255,255, 1); color: darkblue', '', isPdf, 'false'));

                Object.values(itemSd.sub).forEach(itemSs => {
                    // sasaran row
                    rows.push(window.laporan.rka.methods.createRows({
                        kode: itemSs.kodeSs,
                        label: itemSs.ss,
                        total: itemSs.total,
                        totalAmprahan: itemSs.totalAmprahan,
                        totalRealisasi: itemSs.totalRealisasi
                    }, 'headerSasaran', 'background-color: rgba(0,255,255, .8); color: darkblue', '', isPdf, 'false'));

                    Object.values(itemSs.sub).forEach(itemIkk => {
                        // iku row
                        rows.push(window.laporan.rka.methods.createRows({
                            kode: itemIkk.kodeIkk,
                            label: itemIkk.ikk,
                            total: itemIkk.total,
                            totalAmprahan: itemIkk.totalAmprahan,
                            totalRealisasi: itemIkk.totalRealisasi
                        }, 'headerIKU', 'background-color: rgba(0,255,255, .7); color: darkblue', '', isPdf, 'false'));

                        Object.values(itemIkk.sub).forEach(itemIkv => {
                            // ikv row
                            rows.push(window.laporan.rka.methods.createRows({
                                kode: itemIkv.kodeIkv,
                                label: itemIkv.ikv,
                                total: itemIkv.total,
                                totalAmprahan: itemIkv.totalAmprahan,
                                totalRealisasi: itemIkv.totalRealisasi
                            }, 'headerIKV', 'background-color: rgba(0,255,255, .6); color: darkblue', '', isPdf, 'false'));

                            Object.values(itemIkv.sub).forEach(itemKeg => {
                                // keg row
                                rows.push(window.laporan.rka.methods.createRows({
                                    kode: itemKeg.kodeKeg,
                                    label: itemKeg.rincianKeg,
                                    total: itemKeg.total,
                                    totalAmprahan: itemKeg.totalAmprahan,
                                    totalRealisasi: itemKeg.totalRealisasi
                                }, 'headerKegiatan', 'background-color: rgba(0,255,255, .5); color: darkblue', '', isPdf, 'false'));

                                Object.values(itemKeg.sub).forEach(itemUnit => {
                                    // unit kerja row
                                    rows.push(window.laporan.rka.methods.createRows({
                                        kode: itemUnit.idunit,
                                        label: itemUnit.namaUnit,
                                        total: itemUnit.total,
                                        totalAmprahan: itemUnit.totalAmprahan,
                                        totalRealisasi: itemUnit.totalRealisasi
                                    }, 'headerUnitKerja', 'background-color: rgba(0,255,255, .4); color: #FF8355', '', isPdf, 'false'));

                                    Object.values(itemUnit.sub).forEach(itemPaket => {
                                        // paket rekat row
                                        const isPaket = itemPaket.judulPaket ? true : false;
                                        const addOns = ( isPaket && !isPdf ) ? `<span class="badge badge-custom rounded-pill text-white px-3 py-2 shadow">Paket</span>`
                                            : ( isPaket && isPdf ) ? ' - ( Paket ) ' : '';
                                        rows.push(window.laporan.rka.methods.createRows({
                                            kode: itemPaket.idMakPaket || itemPaket.idRekat,
                                            label: itemPaket.judulPaket || itemPaket.subJudul,
                                            total: itemPaket.total,
                                            totalAmprahan: itemPaket.totalAmprahan,
                                            totalRealisasi: itemPaket.totalRealisasi
                                        }, 'fw-bold paket-rekat', '', addOns, isPdf, 'false'
                                        ));
                                    });
                                });
                            });
                        });
                    });
                });
            })
            bodyTblUnit.empty().append(rows.join(''))
            $(`.total`).text( rupiah( basedata.total ) )
            removeLoader()
        }
    }

})
</script>
