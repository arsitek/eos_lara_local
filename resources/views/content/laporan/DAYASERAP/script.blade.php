<script>
    $(document).ready(function() {
        window.dayaserap = {
            variables: {
                isPdf: false
            },
            elements: {
                table: $("#tabel-daya-serap"),
                selectDataBackup: $("select#dataBackup"),
            },
            constants: {
                ID: 0,
                TIMEOUT: 30000,
                CSRF_TOKEN: '{{ csrf_token() }}',
                ROUTES: {
                    GET_ALOKASI: "{{ route('dayaserap.getAlokasi') }}",
                    GET_ALOKASI_BACKUP: "{{ route('dayaserap.getAlokasiBackup') }}"
                },
                TATA_OPTIONS: { animate: true, duration: 5000 }
            },
            methods: {
                getAlokasiData: async () => {
                    showLoader()
                    setLoaderText("Sedang memuat data, harap menunggu ...")
                    const { methods } = window.dayaserap
                    const { TIMEOUT, ROUTES, TATA_OPTIONS } = window.dayaserap.constants
                    const response = await $.ajax({
                        url: ROUTES.GET_ALOKASI,
                        type: "GET",
                        timeout: TIMEOUT,
                    })
                    if ( response.success ) {
                        console.log( response )
                        methods.generateMappingCurrentAlokasi( response.data ).then( data => {
                            removeLoader()
                        }).catch( err => {
                            removeLoader()
                            console.error("Terjadi kesalahan saat memproses data", err)
                            return tata.error("Terjadi kesalahan saat memproses data", TATA_OPTIONS)
                        })
                    } else {
                        console.log( response)
                    }
                },
                generateMappingCurrentAlokasi: ( data ) => {
                    return new Promise( ( resolve, reject ) => {
                        try {
                            const { alokasi, realisasi } = data
                            const baseData = new Map()
                            alokasi.forEach( item => {
                                const { kd_sumberdana: kodeSd, sumberdana, unit_kerja: idunit, nama: namaUnit, pagu } = item
                                // if sd first 2 char is 41 then sd = Non_APBN else if 42 then APBN
                                const jenisSumberDana = kodeSd.startsWith("41") ? "Non APBN" : kodeSd.startsWith("42") ? "APBN" : "Lainnya"
                                const jenisMap = createOrUpdateMap( baseData, jenisSumberDana, () => ({ pagu: 0, idunit, sub: new Map() }) )
                                const unitMap  = createOrUpdateMap( jenisMap.sub, idunit, () => ({ pagu: 0, idunit, namaUnit, sub: new Map() }) )
                                const sdMap    = createOrUpdateMap( unitMap.sub, kodeSd, () => ({ pagu: 0, kodeSd, sumberdana }) );
                                [ sdMap, unitMap, jenisMap ].forEach( map => {
                                    map.pagu += Number( pagu )
                                })
                            })
                            realisasi.forEach(({ unit_kerja: idunit, TOTAL_AMPRAH, kodeSd: sd }) => {
                                if (!sd) return;
                                const jenisSumberDana = sd.startsWith("41") ? "Non APBN" : sd.startsWith("42") ? "APBN" : "Lainnya";

                                const jenisMap = baseData.get(jenisSumberDana)
                                if (!jenisMap) return;
                                const unitMap = jenisMap.sub.get(idunit)
                                if (!unitMap) return;
                                const sdMap = unitMap.sub.get(sd)
                                if (!sdMap) return;

                                const nilai = Number(TOTAL_AMPRAH);
                                sdMap.realisasi = (sdMap.realisasi ?? 0) + nilai;
                                unitMap.realisasi = (unitMap.realisasi ?? 0) + nilai;
                                jenisMap.realisasi = (jenisMap.realisasi ?? 0) + nilai;
                            });
                            window.dayaserap.methods.displayDataToTable( baseData )
                            resolve( baseData )
                        } catch ( err ) {
                            reject( err )
                        }
                    })
                },
                getBackupData: async ( idData ) => {
                    showLoader()
                    setLoaderText("Sedang memuat data, harap menunggu ...")
                    const { methods } = window.dayaserap
                    const { TIMEOUT, ROUTES, TATA_OPTIONS } = window.dayaserap.constants
                    const response = await $.ajax({
                        url: ROUTES.GET_ALOKASI_BACKUP,
                        type: "GET",
                        data: { idBackup: idData },
                        timeout: TIMEOUT,
                    })
                    if ( response.success ) {
                        console.log( response )
                        methods.generateMappingBackupAlokasi( response.data ).then( data => {
                            removeLoader()
                        }).catch( err => {
                            removeLoader()
                            console.error("Terjadi kesalahan saat memproses data", err)
                            return tata.error("Terjadi kesalahan saat memproses data", TATA_OPTIONS)
                        })
                    } else {
                        console.error("Terjadi kesalahan saat mengambil data backup", response.error)
                        return tata.error("Terjadi kesalahan saat mengambil data", TATA_OPTIONS)
                    }
                },
                generateMappingBackupAlokasi: ( data ) => {
                    return new Promise( ( resolve, reject ) => {
                        try {
                            console.log( data )
                            const baseData = new Map()
                            data.alokasi.forEach( item => {
                                const { kode_sd: sd, sumberdana, idunit, nama: namaUnit, pagu } = item
                                // if sd first 2 char is 41 then sd = Non_APBN else if 42 then APBN
                                const jenisSumberDana = sd.startsWith("41") ? "Non APBN" : sd.startsWith("42") ? "APBN" : "Lainnya"
                                const jenisMap = createOrUpdateMap( baseData, jenisSumberDana, () => ({ pagu: 0, idunit, sub: new Map(), realisasi: 0 }) )
                                const unitMap  = createOrUpdateMap( jenisMap.sub, idunit, () => ({ pagu: 0, idunit, namaUnit, sub: new Map(), realisasi: 0 }) )
                                const sdMap    = createOrUpdateMap( unitMap.sub, sd, () => ({ pagu: 0, sd, realisasi: 0, sumberdana }) );
                                [ sdMap, unitMap, jenisMap ].forEach( map => {
                                    map.pagu += Number(pagu)
                                })
                            })
                            data.realisasi.forEach(({ unit_kerja_rkt: idunit, jumlah_amprah, kd_sumberdana: kodeSd, sumberdana }) => {
                                const jenisSumberDana = kodeSd.startsWith("41") ? "Non APBN" : kodeSd.startsWith("42") ? "APBN" : "Lainnya";

                                const jenisMap = baseData.get(jenisSumberDana)
                                if (!jenisMap) return;
                                const unitMap = jenisMap.sub.get(idunit)
                                if (!unitMap) return;
                                const sdMap = unitMap.sub.get(kodeSd)
                                if (!sdMap) return;

                                const nilai = Number(jumlah_amprah);
                                [ sdMap, unitMap, jenisMap ].forEach( map => {
                                    if ( map === sdMap )
                                        map.sumberdana = sumberdana;
                                    map.realisasi = (map.realisasi ?? 0) + nilai;
                                });
                            });
                            console.log( baseData )
                            window.dayaserap.methods.displayDataToTable( baseData )
                            resolve( baseData )
                        } catch ( err ) {
                            reject( err )
                        }
                    })
                },
                displayDataToTable: ( data ) => {
                    return new Promise( ( resolve, reject ) => {
                        try {
                            const { table } = window.dayaserap.elements
                            const { isPdf } = window.dayaserap.variables
                            const jenisColor  = isPdf ? "" : "background-color: rgba(0,255,255, 1); color: darkblue"
                            table.find("tbody").empty();

                            if (data.size === 0) {
                                table.find("tbody").html(`
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <em>Tidak ada data untuk ditampilkan</em>
                                        </td>
                                    </tr>
                                `);
                                return resolve(false);
                            }
                            const masterFragment = document.createDocumentFragment();
                            data.forEach( ( jenisMap, jenisKey ) => {
                                const jenisFragment = document.createDocumentFragment()
                                const jenisRow = document.createElement("tr")
                                jenisRow.classList.add("fw-bold")
                                jenisRow.style.fontSize = "15px"
                                jenisRow.style.cssText = jenisColor
                                jenisRow.innerHTML = `
                                    <td class="text-center">${jenisKey}</td>
                                    <td class="text-end">${ rupiah(jenisMap.pagu ?? 0 ) }</td>
                                    <td class="text-end">${ rupiah(jenisMap.realisasi ?? 0 ) }</td>
                                    <td>${ window.dayaserap.methods.getPercentage(jenisMap.realisasi, jenisMap.pagu) }%</td>`
                                jenisFragment.appendChild(jenisRow)
                                table.find("tbody").append( jenisFragment )
                                jenisMap.sub.forEach( ( unitMap, unitKey ) => {
                                    const unitFragment = document.createDocumentFragment()
                                    const unitRow = document.createElement("tr")
                                    unitRow.style.fontSize = "14px"
                                    // Add unique data attribute to identify this unit row and make it clickable
                                    unitRow.classList.add("unit-row", "cursor-pointer")
                                    unitRow.setAttribute("data-unit-id", unitKey)
                                    unitRow.style.cursor = "pointer"
                                    // Add expand/collapse indicator icon
                                    unitRow.innerHTML = `
                                        <td>${unitMap.namaUnit}</td>
                                        <td class="text-end">${ rupiah(unitMap.pagu ?? 0 ) }</td>
                                        <td class="text-end">${ rupiah(unitMap.realisasi ?? 0 ) }</td>
                                        <td>${ window.dayaserap.methods.getPercentage(unitMap.realisasi, unitMap.pagu) }%</td>`
                                    unitFragment.appendChild(unitRow)
                                    table.find("tbody").append( unitFragment )

                                    // Create SD (Sumber Dana) child rows with unique class for this unit
                                    unitMap.sub.forEach( ( sdMap, sdKey ) => {
                                        const sdFragment = document.createDocumentFragment()
                                        const sdRow = document.createElement("tr")
                                        sdRow.style.fontSize = "13px"
                                        // Add class to identify this SD row belongs to which unit
                                        sdRow.classList.add("sd-child-row", `sd-child-${unitKey}`)
                                        sdRow.style.display = "none" // Initially hidden

                                        sdRow.innerHTML = `
                                            <td style="padding-left: 40px">${ sdMap.sumberdana ? sdMap.sumberdana : sdMap.sd}</td>
                                            <td class="text-end">${ rupiah(sdMap.pagu ?? 0 ) }</td>
                                            <td class="text-end">${ rupiah(sdMap.realisasi ?? 0 ) }</td>
                                            <td>${ window.dayaserap.methods.getPercentage(sdMap.realisasi, sdMap.pagu) }%</td>`
                                        sdFragment.appendChild(sdRow)
                                        table.find("tbody").append( sdFragment )
                                    } )
                                } )
                            } )
                            // Bind toggle events after table is populated
                            window.dayaserap.methods.bindToggleEvents()
                            window.dayaserap.methods.animateCurrency()
                            resolve(true)
                        } catch ( err ) {
                            console.error(`Error displaying data to table:`, err)
                            reject( err )
                        }
                    })
                },
                bindToggleEvents: () => {
                    const { table } = window.dayaserap.elements
                    table.find(".unit-row").off("click");
                    // Attach click event to all unit rows
                    table.find(".unit-row").on("click", function() {
                        const $unitRow = $(this)
                        const unitId = $unitRow.attr("data-unit-id")
                        const $childRows = $(`.sd-child-${unitId}`)
                        const $icon = $unitRow.find(".expand-icon")

                        // Check if rows are currently visible
                        const isExpanded = $childRows.first().is(":visible")

                        if (isExpanded) {
                            // Collapse: Animate out and hide
                            // Rotate icon back
                            gsap.to($icon[0], {
                                rotation: 0,
                                duration: 0.3,
                                ease: "power2.out"
                            })

                            // Animate rows out
                            gsap.to($childRows.toArray(), {
                                opacity: 0,
                                x: -20,
                                height: 0,
                                duration: 0.3,
                                stagger: 0.05,
                                ease: "power2.in",
                                onComplete: () => {
                                    $childRows.hide()
                                    // Reset properties for next animation
                                    gsap.set($childRows.toArray(), {
                                        opacity: 1,
                                        x: 0,
                                        height: "auto"
                                    })
                                }
                            })
                        } else {
                            // Expand: Show and animate in
                            // Rotate icon down
                            gsap.to($icon[0], {
                                rotation: 90,
                                duration: 0.3,
                                ease: "power2.out"
                            })

                            // Set initial state for animation
                            gsap.set($childRows.toArray(), {
                                opacity: 0,
                                x: -20,
                                display: "table-row"
                            })

                            // Animate rows in
                            gsap.to($childRows.toArray(), {
                                opacity: 1,
                                x: 0,
                                duration: 0.4,
                                stagger: 0.08,
                                ease: "power2.out"
                            })
                        }
                    })
                },
                getPercentage: (num1, num2) => {
                    const n1 = Number(num1) || 0;
                    const n2 = Number(num2) || 0;
                    if (n2 === 0) return 0;
                    const result = (n1 / n2) * 100;
                    return isNaN(result) ? 0 : result.toFixed(2);
                },
                animateCurrency: () => {
                    const { table } = window.dayaserap.elements;
                    table.find('.text-end').each(function() {
                        const element = $(this);
                        const text = element.text();

                        if (text.includes('Rp')) {
                            // Extract number from formatted currency
                            const numberStr = rupiahToNumber(text);
                            const finalValue = parseFloat(numberStr);

                            if (!isNaN(finalValue)) {
                                // Create counting animation
                                gsap.fromTo(element,
                                    { textContent: 0 },
                                    {
                                        textContent: finalValue,
                                        duration: 1.5,
                                        ease: "power2.out",
                                        delay: 0.8,
                                        onUpdate: function() {
                                            const currentValue = parseFloat(this.targets()[0].textContent);
                                            const formattedValue = new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR',
                                                minimumFractionDigits: 2
                                            }).format(currentValue);
                                            element.text(formattedValue);
                                        }
                                    }
                                );
                            }
                        }
                    });
                },
                showDataNotFound: () => {
                    const { table } = window.dayaserap.elements
                    table.find("tbody").html(`
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <em>Tidak ada data yang dapat ditampilkan</em>
                            </td>
                        </tr>
                    `);
                }
            }
        }

        function bindEvents() {
            console.log("Binding Events...")
            const { selectDataBackup } = window.dayaserap.elements
            
            // Initialize Select2 for dataBackup dropdown
            selectDataBackup.select2({
                placeholder: "Pilih Data",
                allowClear: true,
                width: '300px'
            })
            
            selectDataBackup.on("change", handleChangeSelectDataBackup)
        }

        const handleChangeSelectDataBackup = (e) => {
            const val = $(e.target).val()
            if ( val == "" || !val )
                return window.dayaserap.methods.showDataNotFound()
            if ( val == "current" )
                return window.dayaserap.methods.getAlokasiData()
            if ( val != "current" )
                return window.dayaserap.methods.getBackupData( val )
        }

        bindEvents()
    })
    </script>
