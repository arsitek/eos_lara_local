<script type="text/javascript">
    $(document).ready(function () {
        window.laporan = {}
        window.laporan.dayaSerap = {}
        window.laporan.tahun = "{{ $tahunAngka }}"
        $(".s").select2()
        $(".alokasi-anggaran").text( rupiah($(".alokasi-anggaran").text()) )
        $(".anggaran-terpetakan").text( rupiah($(".anggaran-terpetakan").text()) )
        const tabelRkaCoa = $("#tabel-rka-coa")

        $(".btn-download-pdf").on("click", function(){
            const pdf = new jsPDF('landscape')
            const rowCount = $("#tabel-rka-coa tbody tr").length
            if ( rowCount == 0 )
                return tata.warn('Perhatian ⚠️', 'Maaf, tidak ada data yang dapat diunduh', { duration: 3000, animate: "slide" })

            // Add content to the PDF from the table
            pdf.autoTable({ html: '#tabel-rka-coa' ,
                headStyles :{fillColor : [ 133, 132, 132 ], textColor: [43, 43, 43]  },
                columnStyles: {
                    0: { cellWidth: 40 },
                    1: { cellWidth: 100 },
                },
            })
            const today = getCurrentDate()
            // Save or open the PDF
            pdf.save(`Rekap Daya Serap-${today}.pdf`)
        })
        $(".btn-download-xlsx").on("click", function(){
            const filename = `Rekap Daya Serap.xlsx`
            const rowCount = $("#tabel-rka-coa tbody tr").length
            if ( rowCount == 0 )
                return tata.warn('Perhatian ⚠️', 'Maaf, tidak ada data yang dapat diunduh', { duration: 3000, animate: "slide" })

            const wb = XLSX.utils.table_to_book(document.getElementById('tabel-rka-coa'), {sheet:"Sheet JS"});
            return XLSX.writeFile(wb, filename);
        })

        $(document).on("click", "#btn-filter-unitkerja", function(){
            const unitkerja  = $("select[name=unitkerja]").val()
            const sumberdana = $("select[name=sumberdana]").val()
            if ( unitkerja == "" || sumberdana == "" ) {
                return tata.warn('Perhatian ⚠️', 'Harap memilih unitkerja dan sumberdana', {
                    duration: 3000,
                    animate: "slide"
                })
            }
            $.ajax({
                type: "POST",
                url: "{{ route('rka.coa.getRka') }}",
                data: { "_token": "{{ csrf_token() }}", idunit: unitkerja, kd_sumberdana: sumberdana },
                beforeSend: function(){
                    showLoading("Sedang memuat data RKA...")
                },
                success: ( res ) => {
                    hideLoading()
                    const { message, data } = res.data
                    if ( data.length == 0 ) {
                        return tata.warn('Perhatian ⚠️', 'Maaf, data tidak ditemukan', { duration: 3000, animate: "slide" })
                    }
                    window.laporan.dayaSerap.methods.buildBaseMap( data ).then( () => {
                        window.laporan.dayaSerap.methods.generateTable()
                    }).catch( err => {
                        console.error(err)
                        return tata.error('⛔ Error', 'Gagal mendapatkan data',{ duration: 3000, animate: "slide" })
                    })
                    return tata.success('✅ Sukses', 'Berhasil mendapatkan data daya serap',{ duration: 3000, animate: "slide" })
                },
                error: ( err ) => {
                    return tata.error('⛔ Error', 'Gagal mendapatkan data daya serap',{ duration: 3000, animate: "slide" })
                },
            })
        })
        window.laporan.dayaSerap.methods = {
            buildBaseMap: ( data ) => {
                return new Promise( ( resolve, reject ) => {
                    const baseData = new Map();
                    baseData.total = 0;
                    baseData.totalRealisasi = 0;
                    baseData.totalAmprah = 0;

                    data.forEach( item => {
                        const { kd_sumberdana: kodeSd, sumberdana, kode_ss: kodeSs, ss, kode_ikk: kodeIkk, ikk, kode_ikv: kodeIkv, ikv,
                            kode_keg: kodeKeg, rincian_kegiatan: rincianKeg, id_rekat: idRekat, sub_judul: subJudul, unit_kerja_rkt: idunit, nama_unit: namaUnit,
                            id_jenis_belanja: idCoa, jenis_belanja: coa, TOTAL_AMPRAH: totalAmprah, TOTAL_REALISASI: totalRealisasi, jumlah_biaya: total
                        } = item
                        const sdMap = createOrUpdateMap( baseData, kodeSd, () => ({ total: 0, totalRealisasi: 0, totalAmprah: 0, sumberdana, ss: new Map() }) )
                        const ssMap = createOrUpdateMap( sdMap.ss, kodeSs, () => ({ total: 0, totalRealisasi: 0, totalAmprah: 0, ss, ro: new Map() }) )
                        const ikkMap = createOrUpdateMap( ssMap.ro, kodeIkk, () => ({ total: 0, totalRealisasi: 0, totalAmprah: 0, ikk, ikv: new Map() }) )
                        const ikvMap = createOrUpdateMap( ikkMap.ikv, kodeIkv, () => ({ total: 0, totalRealisasi: 0, totalAmprah: 0, ikv, keg: new Map() }) )
                        const kegMap = createOrUpdateMap( ikvMap.keg, kodeKeg, () => ({ total: 0, totalRealisasi: 0, totalAmprah: 0, rincianKeg, unit: new Map() }) )
                        const unitMap = createOrUpdateMap( kegMap.unit, idunit, () => ({ namaUnit, total: 0, totalRealisasi: 0, totalAmprah: 0, rekat: new Map() }) )
                        const rekatMap = createOrUpdateMap( unitMap.rekat, idRekat, () => ({ subJudul, total: 0, totalRealisasi: 0, totalAmprah: 0, coa: new Map() }) )
                        const coaMap = createOrUpdateMap( rekatMap.coa, idCoa, () => ({ coa, total: 0, totalRealisasi: 0, totalAmprah: 0 }) )

                        // Add the actual values from the data to each level
                        const numTotal = parseFloat(total) || 0;
                        const numTotalRealisasi = parseFloat(totalRealisasi) || 0;
                        const numTotalAmprah = parseFloat(totalAmprah) || 0;

                        [ baseData, sdMap, ssMap, ikkMap, ikvMap, kegMap, unitMap, rekatMap, coaMap ].forEach( item => {
                            item.total += numTotal
                            item.totalRealisasi += numTotalRealisasi
                            item.totalAmprah += numTotalAmprah
                        })
                    })
                    window.laporan.dayaSerap.methods.baseData = baseData
                    resolve()
                })
            },
            generateHTMLRows: ( code, codeDesc, total, totalRealisasi ) => {
                return `<tr>
                    <td>${code ?? '-'}</td>
                    <td>${codeDesc ?? 'Data tidak ditemukan'}</td>
                    <td>${rupiah(total)}</td>
                    <td>${rupiah(totalRealisasi)}</td>
                    <td>${rupiah( total - totalRealisasi )}</td>
                    </tr>`;
            },
            generateHeaderRows: ( code, total, totalRealisasi ) => {
                return `<tr>
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td>${rupiah(total)}</td>
                    <td>${rupiah(totalRealisasi)}</td>
                    <td>${rupiah( total - totalRealisasi )}</td>
                    </tr>`;
            },
            generateTable: () => {
                const baseData = window.laporan.dayaSerap.methods.baseData
                const rows = []
                // Create a DocumentFragment to batch DOM updates
                const fragment = document.createDocumentFragment();
                
                const totalRow = Object.assign( document.createElement('tr'), {
                    className: 'fw-bold totalRow',
                    style: "font-size: 16px",
                    innerHTML: window.laporan.dayaSerap.methods.generateHeaderRows(
                        'TOTAL', baseData.total, baseData.totalRealisasi + baseData.totalAmprah
                    )
                } )
                fragment.appendChild(totalRow)
                // Iterate through the baseData map
                const tahun = window.laporan.tahun
                baseData.forEach( (sd, kodeSd) => {
                    const totalRealisasi = sd.totalRealisasi + sd.totalAmprah
                    // create a row for each sumberdana and give style
                    const tr = Object.assign( document.createElement('tr'), { 
                        className: 'fw-bold sdRow', 
                        style: "font-size: 15px", 
                    });
                    Object.assign( tr.dataset, { kodeSd });

                    tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                        kodeSd, sd.sumberdana, sd.total, totalRealisasi
                    );
                    fragment.appendChild(tr);

                    sd.ss.forEach( (ss, kodeSs) => {
                        const totalRealisasi = ss.totalRealisasi + ss.totalAmprah
                        const tr = Object.assign( document.createElement('tr'), { className: 'ssRow' });
                        Object.assign( tr.dataset, { kodeSs });

                        tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                            kodeSs, ss.ss, ss.total, totalRealisasi
                        );
                        fragment.appendChild(tr);

                        ss.ro.forEach( (ikk, kodeIkk) => {
                            const totalRealisasi = ikk.totalRealisasi + ikk.totalAmprah
                            const tr = Object.assign( document.createElement('tr'), { className: 'ikkRow' });
                            Object.assign( tr.dataset, { kodeIkk });

                            tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                kodeIkk, ikk.ikk, ikk.total, totalRealisasi
                            );
                            fragment.appendChild(tr);

                            ikk.ikv.forEach( (ikv, kodeIkv) => {
                                const totalRealisasi = ikv.totalRealisasi + ikv.totalAmprah
                                const tr = Object.assign( document.createElement('tr'), { className: 'ikvRow' });
                                Object.assign( tr.dataset, { kodeIkv });

                                tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                    kodeIkv, ikv.ikv, ikv.total, totalRealisasi
                                );
                                fragment.appendChild(tr);

                                ikv.keg.forEach( (keg, kodeKeg) => {
                                    const totalRealisasi = keg.totalRealisasi + keg.totalAmprah
                                    const tr = Object.assign( document.createElement('tr'), { className: 'kegRow' });
                                    Object.assign( tr.dataset, { kodeKeg });

                                    tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                        kodeKeg, keg.rincianKeg, keg.total, totalRealisasi
                                    );
                                    fragment.appendChild(tr);

                                    keg.unit.forEach( (unit, idunit) => {
                                        const totalRealisasi = unit.totalRealisasi + unit.totalAmprah
                                        const tr = Object.assign( document.createElement('tr'), { className: 'unitRow' });
                                        Object.assign( tr.dataset, { idunit });

                                        tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                            idunit, unit.namaUnit, unit.total, totalRealisasi
                                        );
                                        fragment.appendChild(tr);

                                        unit.rekat.forEach( (rekat, idRekat) => {
                                            const totalRealisasi = rekat.totalRealisasi + rekat.totalAmprah
                                            const tr = Object.assign( document.createElement('tr'), { className: 'rekatRow fw-bold' });
                                            Object.assign( tr.dataset, { idRekat });

                                            tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                                idRekat, rekat.subJudul, rekat.total, totalRealisasi
                                            );
                                            fragment.appendChild(tr);

                                            rekat.coa.forEach( (coa, idCoa) => {
                                                const totalRealisasi = coa.totalRealisasi + coa.totalAmprah
                                                const tr = Object.assign( document.createElement('tr'), { className: 'coaRow' });
                                                Object.assign( tr.dataset, { idCoa });
                                                const kodeKegPartSafe = parseFloat(tahun) >= 2026 ? `${safeText(kodeIkv)}.<br>${safeText(kodeKeg)}` : (kodeKeg && kodeKeg.length >= 11) ? `${kodeKeg.substring(3, 11)}<br>` : '-'
                                                const mak      = `${kodeSd}.${kodeSs ?? '-'}.${kodeKegPartSafe}.${idunit}.${idRekat}.${safeText(idCoa, '-')}`

                                                tr.innerHTML = window.laporan.dayaSerap.methods.generateHTMLRows(
                                                    mak, coa.coa, coa.total, totalRealisasi
                                                );
                                                fragment.appendChild(tr);
                                            })
                                        })
                                    })
                                })
                            })
                        })
                    });
                });

                // Assuming you have a tbody with id 'tbody-rka-coa'
                const tbody = document.getElementById('tabel-rka-coa').querySelector('tbody');
                if (tbody) {
                    tbody.innerHTML = ''; // Clear existing rows
                    tbody.appendChild(fragment);
                }
            },
        }

        const showLoading = ( msg ) => {
            $(".loading-div").addClass("d-flex")
            $(".loading-div").show()
            $(".loading-msg").text(msg)
        }
        const hideLoading = () => {
            $(".loading-div").removeClass("d-flex")
            $(".loading-div").hide()
        }
    })
</script>
