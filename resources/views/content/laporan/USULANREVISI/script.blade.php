<script>
$(document).ready(function() {
    window.laporan = window.laporan || {};
    window.laporan.usulanRevisi = {
        config: {
            TIMEOUT: 30000,
            TATA_OPTIONS: { animate: "slide", duration: 5000 },
            ROUTES: {
                GET_DATA_USULAN_REVISI: "{{ route('laporan.usulanrevisi.data') }}",
            },
            INDENT_LEVEL: {
                SD: 0,
                SS: 20,
                IKK: 40,
                DETAIL: 60
            }
        },
        cache: {
            dataPosturAnggaran: null,
            dataJenisBelanja: null,
            isPdf: typeof isPdfView !== 'undefined' ? isPdfView : false, // Check if PDF mode
            showDetailedSD: null, // Toggle for showing SD level in jenis grouping mode (null = will be initialized)
            rawDataExisting: null, // Store raw data for re-rendering
            rawDataUsulan: null,   // Store raw data for re-rendering
            pdfKodeSd: null,       // Store kodeSd for PDF mode to determine jenis grouping
        },
        dom: {
            statusLoading: $(".statusLoading"),
            statusError: $(".statusError"),
            tabelUsulanRevisi: $("#tabel-usulan-revisi"),
            btnCariUsulanRevisi: $("button#cari-usulan-revisi"),
            selectFilterData: $("select#filter-tampilan-usulan-revisi")
        },
        helpers: {
            /**
             * Menghitung persentase perubahan antara nominal dan revisi
             * @param {Object} mapItem - Object dengan property nominal dan nominalRevisi
             * @returns {String} Persentase dalam format "XX.XX%"
             */
            calculatePercentage: (mapItem) => {
                if (mapItem.nominal === 0) {
                    return '0%';
                }
                const percentage = ((mapItem.nominalRevisi - mapItem.nominal) / mapItem.nominal) * 100;
                return `${percentage.toFixed(2)}%`;
            },

            /**
             * Menambahkan persentase ke semua level hierarki secara rekursif
             * @param {Map} baseDataMap - Map data hierarki
             * @param {Number} currentLevel - Level hierarki saat ini (default: 1)
             */
            addPercentageToAllLevels: (baseDataMap, currentLevel = 1) => {
                const { calculatePercentage, addPercentageToAllLevels } = window.laporan.usulanRevisi.helpers;
                
                baseDataMap.forEach((map) => {
                    map.persentase = calculatePercentage(map);
                    
                    // Rekursif untuk semua sub-level yang ada
                    if (map.sub && map.sub.size > 0) {
                        addPercentageToAllLevels(map.sub, currentLevel + 1);
                    }
                });
            },

            /**
             * Membuat baris HTML untuk tabel
             * @param {String} text - Teks yang ditampilkan
             * @param {Number} nominal - Nilai existing
             * @param {Number} nominalRevisi - Nilai usulan
             * @param {String} persentase - Persentase perubahan
             * @param {Number} indent - Level indentasi (dalam px)
             * @param {Boolean} isBold - Apakah teks harus bold
             * @returns {String} HTML string
             */
            createTableRow: (text, nominal, nominalRevisi, persentase, indent = 0, isBold = false) => {
                const isNotFoundText = /Data tidak ditemukan/.test(text);
                const rowClasses = isBold ? ['fw-bold'] : [];
                const rowStyles = [];
                if (isBold) rowStyles.push('font-size: 14px;');
                if (isNotFoundText) rowStyles.push('color: #d43535;');
                const classAttr = rowClasses.length ? `class="${rowClasses.join(' ')}"` : '';
                const styleAttr = rowStyles.length ? `style="${rowStyles.join(' ')}"` : '';
                const rowAttributes = [classAttr, styleAttr].filter(Boolean).join(' ');
                const rowAttrString = rowAttributes ? ` ${rowAttributes}` : '';
                const paddingStyle = indent > 0 ? `style="padding-left: ${indent}px;"` : '';
                
                return `<tr${rowAttrString}>
                    <td ${paddingStyle}>${text}</td>
                    <td>${rupiah(nominal)}</td>
                    <td class="nilai-usulan">${rupiah(nominalRevisi)}</td>
                    <td class="persentase">${persentase}</td>
                </tr>`;
            },

            /**
             * Membuat baris HTML clickable untuk toggle jenis
             * @param {String} text - Teks yang ditampilkan
             * @param {Number} nominal - Nilai existing
             * @param {Number} nominalRevisi - Nilai usulan
             * @param {String} persentase - Persentase perubahan
             * @param {String} jenisKey - Key jenis (APBN/SELAIN APBN)
             * @param {Boolean} isExpanded - Status expanded/collapsed
             * @returns {String} HTML string
             */
            createJenisRow: (text, nominal, nominalRevisi, persentase, jenisKey, isExpanded) => {
                const isPdfMode   = window.laporan.usulanRevisi.cache.isPdf;
                let icon          = isExpanded ? '▼' : '▶';
                const rowClass    = isExpanded ? 'jenis-row-expanded' : 'jenis-row-collapsed';
                const cursorStyle = isPdfMode ? 'default' : 'pointer';
                const toggleClass = isPdfMode ? '' : 'jenis-row-toggle';
                
                if ( isPdfMode )
                    icon = '';

                const isNotFoundText = /Data tidak ditemukan/.test(text);
                const rowStyles = ['font-size: 14px;'];
                if (isNotFoundText) rowStyles.push('color: #d43535;');
                const styleAttr = `style="${rowStyles.join(' ')}"`;
                
                return `<tr class="fw-bold jenis-row" ${styleAttr}>
                    <td class="${toggleClass} ${rowClass}" data-jenis="${jenisKey}" style="cursor: ${cursorStyle}; user-select: none;">
                        <span class="jenis-icon">${icon}</span> ${text}
                    </td>
                    <td>${rupiah(nominal)}</td>
                    <td class="nilai-usulan">${rupiah(nominalRevisi)}</td>
                    <td class="persentase">${persentase}</td>
                </tr>`;
            },

            /**
             * Menghitung nilai revisi dari item
             * @param {Object} item - Item data dari server
             * @returns {Number} Nilai revisi
             */
            calculateRevisionValue: (item) => {
                const totalRevisi = Number(item.TOTAL_REVISI);
                return totalRevisi > 0 ? totalRevisi : Number(item.jumlah_biaya);
            }
        },
        dataProcessor: {
            /**
             * Membuat atau update node Map dengan struktur standar
             * @param {Map} parentMap - Map parent
             * @param {String} key - Key untuk node
             * @param {Function} createNode - Function untuk membuat node baru
             * @returns {Object} Node yang dibuat/diupdate
             */
            createOrUpdateNode: (parentMap, key, createNode) => {
                if (!parentMap.has(key)) {
                    parentMap.set(key, createNode());
                } else {
                    // Ensure descriptive properties are preserved when node already exists
                    const existingNode = parentMap.get(key);
                    const newNode = createNode();
                    
                    // Merge properties, preserving sub, nominal, nominalRevisi from existing
                    // but updating descriptive fields (sumberdana, ss, ikk, etc.) if they're missing
                    Object.keys(newNode).forEach(prop => {
                        if (prop !== 'sub' && prop !== 'nominal' && prop !== 'nominalRevisi' && prop !== 'persentase') {
                            if (!existingNode[prop]) {
                                existingNode[prop] = newNode[prop];
                            }
                        }
                    });
                }
                return parentMap.get(key);
            },

            /**
             * Membuat struktur node standar untuk Map
             * @param {Object} additionalData - Data tambahan untuk node
             * @returns {Object} Node dengan struktur standar
             */
            createNodeStructure: (additionalData = {}) => ({
                sub: new Map(),
                nominal: 0,
                nominalRevisi: 0,
                ...additionalData
            }),

            /**
             * Update nominal untuk hierarki node
             * @param {Array} nodes - Array node yang akan diupdate
             * @param {Number} value - Nilai yang ditambahkan
             */
            updateNominalHierarchy: (nodes, value) => {
                nodes.forEach(node => {
                    node.nominal += value;
                });
            },

            /**
             * Update nominal revisi untuk hierarki node
             * @param {Array} nodes - Array node yang akan diupdate
             * @param {Number} value - Nilai yang ditambahkan
             */
            updateRevisiHierarchy: (nodes, value) => {
                nodes.forEach(node => {
                    node.nominalRevisi += value;
                });
            },

            /**
             * Memproses hierarki data dengan mode yang berbeda
             * @param {Map} baseDataMap - Map dasar
             * @param {Object} item - Item data
             * @param {String} detailKey - Key untuk level detail
             * @param {Object} detailData - Data untuk level detail
             * @param {Boolean} useJenisGrouping - Apakah menggunakan grouping berdasarkan jenis APBN/SELAIN APBN
             * @param {Boolean} showDetailedSD - Apakah menampilkan SD level dalam jenis grouping mode
             * @returns {Array} Array berisi semua node dalam hierarki
             */
            processFourLevelHierarchy: (baseDataMap, item, detailKey, detailData, useJenisGrouping = false, showDetailedSD = false) => {
                const { createOrUpdateNode, createNodeStructure } = window.laporan.usulanRevisi.dataProcessor;
                
                let hierarchy;
                
                if (useJenisGrouping) {
                    // Jenis Grouping Mode
                    let jenisSdMap;
                    if (item.kd_sumberdana.startsWith('41')) {
                        jenisSdMap = createOrUpdateNode(baseDataMap, 'SELAIN APBN', () => 
                            createNodeStructure({ sumberdana: 'Sumber Dana Selain APBN' })
                        );
                    } else {
                        jenisSdMap = createOrUpdateNode(baseDataMap, 'APBN', () => 
                            createNodeStructure({ sumberdana: 'Sumber Dana APBN' })
                        );
                    }

                    if (showDetailedSD) {
                        // Detailed Mode: Show SD level (Jenis > SD > KRO > RO > Detail)
                        const sdMap = createOrUpdateNode(jenisSdMap.sub, item.kd_sumberdana, () => 
                            createNodeStructure({ sumberdana: item.sumberdana })
                        );
                        
                        const kroMap = createOrUpdateNode(sdMap.sub, item.kode_ss, () => 
                            createNodeStructure({ ss: item.ss })
                        );
                        
                        const roMap = createOrUpdateNode(kroMap.sub, item.kode_ikk, () => 
                            createNodeStructure({ ikk: item.ikk })
                        );
                        
                        const detailMap = createOrUpdateNode(roMap.sub, item[detailKey], () => 
                            createNodeStructure(detailData)
                        );
                        
                        hierarchy = [baseDataMap, jenisSdMap, sdMap, kroMap, roMap, detailMap];
                    } else {
                        // Compact Mode: Skip SD level (Jenis > KRO > RO > Detail)
                        const kroMap = createOrUpdateNode(jenisSdMap.sub, item.kode_ss, () => 
                            createNodeStructure({ ss: item.ss })
                        );
                        
                        const roMap = createOrUpdateNode(kroMap.sub, item.kode_ikk, () => 
                            createNodeStructure({ ikk: item.ikk })
                        );
                        
                        const detailMap = createOrUpdateNode(roMap.sub, item[detailKey], () => 
                            createNodeStructure(detailData)
                        );
                        
                        hierarchy = [baseDataMap, jenisSdMap, kroMap, roMap, detailMap];
                    }
                } else {
                    // Default 4-level hierarchy (SD > SS > IKK > Detail)
                    const sdMap = createOrUpdateNode(baseDataMap, item.kd_sumberdana, () => 
                        createNodeStructure({ sumberdana: item.sumberdana })
                    );
                    
                    const kroMap = createOrUpdateNode(sdMap.sub, item.kode_ss, () => 
                        createNodeStructure({ ss: item.ss })
                    );
                    
                    const roMap = createOrUpdateNode(kroMap.sub, item.kode_ikk, () => 
                        createNodeStructure({ ikk: item.ikk })
                    );
                    
                    const detailMap = createOrUpdateNode(roMap.sub, item[detailKey], () => 
                        createNodeStructure(detailData)
                    );
                    
                    hierarchy = [baseDataMap, sdMap, kroMap, roMap, detailMap];
                }
                
                return hierarchy;
            },

            /**
             * Determine if jenis grouping should be used based on user selection
             * @returns {Boolean} True if user selected "APBN" or "SELAIN APBN"
             */
            shouldUseJenisGrouping: () => {
                const { isPdf, pdfKodeSd } = window.laporan.usulanRevisi.cache;
                
                // In PDF mode, check the pdfKodeSd parameter from cache
                if (isPdf) {
                    const url = new URL(window.location.href);
                    const pdfKodeSd = url.searchParams.get('parentSd');
                    if (pdfKodeSd) {
                        // pdfKodeSd can be a string or array from Laravel
                        const kodeSdStr = String(pdfKodeSd);
                        const result = kodeSdStr.startsWith('APBN') || kodeSdStr.startsWith('SELAIN APBN');
                        return result;
                    }
                    return true;
                }
                
                // In regular mode, check DOM selection
                const highestParent = window.laporan.usulanRevisi.business.getHighestSumberdanaParent();
                const result = highestParent === 'APBN' || highestParent === 'SELAIN APBN' || 
                       highestParent === 'Sumber Dana APBN' || highestParent === 'Sumber Dana Selain APBN';
                return result;
            }
        },
        api: {
            /**
             * Mengambil data dari server
             * @param {Array} idunit - Array ID unit kerja
             * @param {Array} kodeSd - Array kode sumber dana
             * @param {Array} idBackup - Array ID backup/riwayat
             * @returns {Promise} Response dari server
             */
            fetchData: async (idunit, kodeSd, idBackup) => {
                const { TIMEOUT, ROUTES } = window.laporan.usulanRevisi.config;
                
                return await $.ajax({
                    url: ROUTES.GET_DATA_USULAN_REVISI,
                    method: "GET",
                    timeout: TIMEOUT,
                    data: { idunit, kodeSd, idBackup },
                });
            }
        },
        business: {
            /**
             * Membangun struktur data postur anggaran (SD > SS > IKK > IKV)
             * @param {Object} data - Object berisi dataExisting dan dataUsulan
             * @returns {Promise<Map>} Map data yang sudah diproses
             */
            buildDataPosturAnggaran: async (data) => {
                try {
                    const baseDataMap = new Map();
                    baseDataMap.nominal = 0;
                    baseDataMap.nominalRevisi = 0;
                    const { dataExisting, dataUsulan } = data;
                    const { processFourLevelHierarchy, updateNominalHierarchy, updateRevisiHierarchy, shouldUseJenisGrouping } = 
                        window.laporan.usulanRevisi.dataProcessor;
                    const { calculateRevisionValue } = window.laporan.usulanRevisi.helpers;
                    const { getHighestSumberdanaParent } = window.laporan.usulanRevisi.business;

                    // Determine if we should use jenis grouping based on user selection
                    const useJenisGrouping = shouldUseJenisGrouping();
                    // Use cached showDetailedSD value for toggling
                    const showDetailedSD = window.laporan.usulanRevisi.cache.showDetailedSD;
                    // Proses data existing
                    dataExisting.forEach(item => {
                        const hierarchy = processFourLevelHierarchy(
                            baseDataMap, 
                            item, 
                            'kode_ikv', 
                            { ikv: item.ikv },
                            useJenisGrouping,
                            showDetailedSD
                        );
                        updateNominalHierarchy(hierarchy, Number(item.jumlah_biaya));
                    });

                    // Proses data usulan revisi
                    dataUsulan.forEach(item => {
                        const hierarchy = processFourLevelHierarchy(
                            baseDataMap, 
                            item, 
                            'kode_ikv', 
                            { ikv: item.ikv },
                            useJenisGrouping,
                            showDetailedSD
                        );
                        const nilaiRevisi = calculateRevisionValue(item);
                        // Update semua level termasuk baseDataMap
                        updateRevisiHierarchy(hierarchy.slice(1), nilaiRevisi);
                        // Update baseDataMap total
                        baseDataMap.nominalRevisi += nilaiRevisi;
                    });
                    
                    const { addPercentageToAllLevels, calculatePercentage } = window.laporan.usulanRevisi.helpers;
                    addPercentageToAllLevels(baseDataMap);
                    // Hitung persentase untuk baseDataMap
                    baseDataMap.persentase = calculatePercentage(baseDataMap);
                    window.laporan.usulanRevisi.cache.dataPosturAnggaran = baseDataMap;
                    return baseDataMap;    
                } catch (e) {
                    console.error('Error buildDataPosturAnggaran:', e);
                    throw new Error("Gagal membangun data postur anggaran");
                }
            },
            /**
             * Membangun struktur data jenis belanja (SD > SS > IKK > COA)
             * @param {Object} data - Object berisi dataExisting dan dataUsulan
             * @returns {Promise<Map>} Map data yang sudah diproses
             */
            buildDataJenisBelanja: async (data) => {
                try {
                    const baseDataMap = new Map();
                    baseDataMap.nominal = 0;
                    baseDataMap.nominalRevisi = 0;
                    const { dataExisting, dataUsulan } = data;
                    const { processFourLevelHierarchy, updateNominalHierarchy, updateRevisiHierarchy, shouldUseJenisGrouping } = 
                        window.laporan.usulanRevisi.dataProcessor;
                    const { calculateRevisionValue } = window.laporan.usulanRevisi.helpers;
                    const { getHighestSumberdanaParent } = window.laporan.usulanRevisi.business;
                    // Determine if we should use jenis grouping based on user selection
                    const useJenisGrouping = shouldUseJenisGrouping();
                    // Use cached showDetailedSD value for toggling
                    const showDetailedSD = window.laporan.usulanRevisi.cache.showDetailedSD;
                    // Proses data existing
                    dataExisting.forEach(item => {
                        if (item.id_jenis_belanja == null) return;
                        
                        const hierarchy = processFourLevelHierarchy(
                            baseDataMap, 
                            item, 
                            'id_jenis_belanja', 
                            { jenis_belanja: item.jenis_belanja },
                            useJenisGrouping,
                            showDetailedSD
                        );
                        updateNominalHierarchy(hierarchy, Number(item.jumlah_biaya));
                    });

                    // Proses data usulan revisi
                    dataUsulan.forEach(item => {
                        if (item.id_jenis_belanja == null) return;
                        
                        const hierarchy = processFourLevelHierarchy(
                            baseDataMap, 
                            item, 
                            'id_jenis_belanja', 
                            { jenis_belanja: item.jenis_belanja },
                            useJenisGrouping,
                            showDetailedSD
                        );
                        const nilaiRevisi = calculateRevisionValue(item);
                        // Update semua level termasuk baseDataMap
                        updateRevisiHierarchy(hierarchy.slice(1), nilaiRevisi);
                        // Update baseDataMap total
                        baseDataMap.nominalRevisi += nilaiRevisi;
                    });
                    
                    const { addPercentageToAllLevels, calculatePercentage } = window.laporan.usulanRevisi.helpers;
                    addPercentageToAllLevels(baseDataMap);
                    // Hitung persentase untuk baseDataMap
                    baseDataMap.persentase = calculatePercentage(baseDataMap);
                    
                    window.laporan.usulanRevisi.cache.dataJenisBelanja = baseDataMap;
                    return baseDataMap;
                    
                } catch (e) {
                    console.error('Error buildDataJenisBelanja:', e);
                    throw new Error("Gagal membangun data jenis belanja");
                }
            },
            getHighestSumberdanaParent: () => {
                // check if url contain pdf word
                const url = new URL(window.location.href);
                const isPdf = url.pathname.includes('/pdf/');
                const $selectedSumberdana = $(".sumberdanaOption.selected, .selectable-header.selected[data-jenis='sumberdana']");
                if ($selectedSumberdana.length === 0 && isPdf === false) return null;
                        
                let highestParent = null;
                let highestLevel = Infinity;
                        
                $selectedSumberdana.each(function() {
                    const $selected = $(this);
                    const $parentGroup = $selected.closest('.option-group');
                            
                    // Count parent levels (lower number = higher in hierarchy)
                    const parentCount = $selected.parents('.option-group').length;
                            
                    if (parentCount < highestLevel) {
                        highestLevel = parentCount;
                        highestParent = $selected.data("text") || $selected.find('span:not(.checkmark):not(.toggle-icon)').first().text();
                    }
                });
                if ( isPdf )
                    return url.searchParams.get('parentSd');
                return highestParent;
            },
            
            /**
             * Initialize showDetailedSD state based on context (main view or PDF view)
             * For main view: Check if parent is APBN/SELAIN APBN (if so, start collapsed)
             * For PDF view: Check parentSd parameter (if APBN/SELAIN APBN, start collapsed)
             * @returns {Boolean} Initial state for showDetailedSD
             */
            initializeShowDetailedSD: () => {
                const { isPdf } = window.laporan.usulanRevisi.cache;
                const { getHighestSumberdanaParent } = window.laporan.usulanRevisi.business;
                
                if (isPdf) {
                    // PDF Mode: Check parentSd parameter from URL
                    const url = new URL(window.location.href);
                    const parentSd = url.searchParams.get('parentSd');
                    
                    if (parentSd) {
                        // If parentSd is APBN or SELAIN APBN, start collapsed (false)
                        const isJenisGrouping = parentSd === 'APBN' || 
                                               parentSd === 'SELAIN APBN' || 
                                               parentSd === 'Sumber Dana APBN' || 
                                               parentSd === 'Sumber Dana Selain APBN';
                        return !isJenisGrouping; // If jenis grouping, start collapsed (false), else expanded (true)
                    }
                    return true; // Default to expanded if no parentSd
                } else {
                    // Main View: Check highest selected parent
                    const highestParent = getHighestSumberdanaParent();
                    
                    if (highestParent) {
                        // If highest parent is APBN or SELAIN APBN, start collapsed (false)
                        const isJenisGrouping = highestParent === 'APBN' || 
                                               highestParent === 'SELAIN APBN' || 
                                               highestParent === 'Sumber Dana APBN' || 
                                               highestParent === 'Sumber Dana Selain APBN';
                        return !isJenisGrouping; // If jenis grouping, start collapsed (false), else expanded (true)
                    }
                    return true; // Default to expanded
                }
            },
        },
        view: {
            /**
             * Render hierarki data ke dalam baris-baris tabel
             * @param {Map} data - Map data hierarki
             * @param {String} detailField - Field untuk level detail ('ikv' atau 'jenis_belanja')
             * @returns {Array} Array baris HTML
             */
            renderHierarchy: (data, detailField) => {
                const rows = [];
                const { createTableRow, createJenisRow } = window.laporan.usulanRevisi.helpers;
                const { INDENT_LEVEL } = window.laporan.usulanRevisi.config;
                const { shouldUseJenisGrouping } = window.laporan.usulanRevisi.dataProcessor;
                const isPdfMode = window.laporan.usulanRevisi.cache.isPdf;
                const { getHighestSumberdanaParent } = window.laporan.usulanRevisi.business;
                // Use cached showDetailedSD value for toggling
                const showDetailedSD = window.laporan.usulanRevisi.cache.showDetailedSD;
                // Determine if we're using jenis grouping
                const useJenisGrouping = shouldUseJenisGrouping();
                
                // Tampilkan total dari semua sumber dana
                rows.push(createTableRow(
                    `TOTAL`,
                    data.nominal || 0,
                    data.nominalRevisi || 0,
                    data.persentase || '0%',
                    INDENT_LEVEL.SD,
                    true
                ));
                
                data.forEach((jenisOrSdMap, jenisOrSdKey) => {
                    if (useJenisGrouping) {
                        // Jenis Grouping Mode
                        // Level 1: Jenis (APBN / SELAIN APBN) - Clickable row
                        rows.push(createJenisRow(
                            jenisOrSdMap.sumberdana ?? 'Data tidak ditemukan',
                            jenisOrSdMap.nominal,
                            jenisOrSdMap.nominalRevisi,
                            jenisOrSdMap.persentase,
                            jenisOrSdKey,
                            showDetailedSD
                        ));
                        
                        if (showDetailedSD) {
                            // Detailed Mode: Show SD level (Jenis > SD > KRO > RO > Detail)
                            jenisOrSdMap.sub.forEach((sdMap, sdKey) => {
                                // Level 2: Sumber Dana
                                rows.push(createTableRow(
                                    `${sdKey} - ${sdMap.sumberdana}`,
                                    sdMap.nominal,
                                    sdMap.nominalRevisi,
                                    sdMap.persentase,
                                    INDENT_LEVEL.SS,
                                    true
                                ));
                                $(`.total-${sdKey}`).text(rupiah(sdMap.nominal));
                                
                                sdMap.sub.forEach((kroMap, kroKey) => {
                                    // Level 3: SS/Kegiatan (KRO)
                                    const key = !kroKey ? 'Data tidak ditemukan' : `${kroKey} - ${kroMap.ss}`
                                    rows.push(createTableRow(
                                        key,
                                        kroMap.nominal,
                                        kroMap.nominalRevisi,
                                        kroMap.persentase,
                                        INDENT_LEVEL.IKK
                                    ));
                                    
                                    kroMap.sub.forEach((roMap, roKey) => {
                                        // Level 4: IKK/Hasil (RO)
                                        const key = !roKey ? 'Data tidak ditemukan' : `${roKey} - ${roMap.ikk}`;
                                        rows.push(createTableRow(
                                            key,
                                            roMap.nominal,
                                            roMap.nominalRevisi,
                                            roMap.persentase,
                                            INDENT_LEVEL.DETAIL
                                        ));
                                        
                                        roMap.sub.forEach((detailMap, detailKey) => {
                                            // Level 5: Detail (IKV atau Jenis Belanja / Komponen)
                                            const key = !detailKey ? 'Data tidak ditemukan' : `${detailKey} - ${detailMap[detailField]}`;
                                            rows.push(createTableRow(
                                                key,
                                                detailMap.nominal,
                                                detailMap.nominalRevisi,
                                                detailMap.persentase,
                                                INDENT_LEVEL.DETAIL + 20
                                            ));
                                        });
                                    });
                                });
                            });
                        } else {
                            
                            // Compact Mode: Skip SD level (Jenis > KRO > RO > Detail)
                            jenisOrSdMap.sub.forEach((kroMap, kroKey) => {
                                // Level 2: SS/Kegiatan (KRO)
                                const key = !kroKey ? 'Data tidak ditemukan' : `${kroKey} - ${kroMap.ss}`;
                                rows.push(createTableRow(
                                    key,
                                    kroMap.nominal,
                                    kroMap.nominalRevisi,
                                    kroMap.persentase,
                                    INDENT_LEVEL.SS
                                ));
                                
                                kroMap.sub.forEach((roMap, roKey) => {
                                    // Level 3: IKK/Hasil (RO)
                                    const key = !roKey ? 'Data tidak ditemukan' : `${roKey} - ${roMap.ikk}`;
                                    rows.push(createTableRow(
                                        key,
                                        roMap.nominal,
                                        roMap.nominalRevisi,
                                        roMap.persentase,
                                        INDENT_LEVEL.IKK
                                    ));
                                    
                                    roMap.sub.forEach((detailMap, detailKey) => {
                                        // Level 4: Detail (IKV atau Jenis Belanja / Komponen)
                                        const key = !detailKey ? 'Data tidak ditemukan' : `${detailKey} - ${detailMap[detailField]}`;
                                        rows.push(createTableRow(
                                            key,
                                            detailMap.nominal,
                                            detailMap.nominalRevisi,
                                            detailMap.persentase,
                                            INDENT_LEVEL.DETAIL
                                        ));
                                    });
                                });
                            });
                        }
                    } else {
                        // 4-Level Hierarchy (Default): SD > SS > IKK > Detail
                        // Level 1: Sumber Dana
                        const sdMap = jenisOrSdMap;
                        const sdKey = jenisOrSdKey;
                        
                        rows.push(createTableRow(
                            `${sdKey} - ${sdMap.sumberdana}`,
                            sdMap.nominal,
                            sdMap.nominalRevisi,
                            sdMap.persentase,
                            INDENT_LEVEL.SD,
                            true
                        ));
                        $(`.total-${sdKey}`).text(rupiah(sdMap.nominal));
                        
                        sdMap.sub.forEach((kroMap, kroKey) => {
                            // Level 2: SS/Kegiatan
                            const key = !kroKey ? 'Data tidak ditemukan' : `${kroKey} - ${kroMap.ss}`;
                            rows.push(createTableRow(
                                key,
                                kroMap.nominal,
                                kroMap.nominalRevisi,
                                kroMap.persentase,
                                INDENT_LEVEL.SS
                            ));
                            
                            kroMap.sub.forEach((roMap, roKey) => {
                                // Level 3: IKK/Hasil
                                const key = !roKey ? 'Data tidak ditemukan' : `${roKey} - ${roMap.ikk}`;
                                rows.push(createTableRow(
                                    key,
                                    roMap.nominal,
                                    roMap.nominalRevisi,
                                    roMap.persentase,
                                    INDENT_LEVEL.IKK
                                ));
                                
                                roMap.sub.forEach((detailMap, detailKey) => {
                                    // Level 4: Detail (IKV atau Jenis Belanja)
                                    const key = !detailKey ? 'Data tidak ditemukan' : `${detailKey} - ${detailMap[detailField]}`;
                                    rows.push(createTableRow(
                                        key,
                                        detailMap.nominal,
                                        detailMap.nominalRevisi,
                                        detailMap.persentase,
                                        INDENT_LEVEL.DETAIL
                                    ));
                                });
                            });
                        });
                    }
                });
                
                return rows;
            },

            /**
             * Menampilkan data postur anggaran ke tabel
             * @param {Map} data - Map data yang sudah diproses
             */
            showDataPosturAnggaran: async (data) => {
                try {
                    const { tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                    const { renderHierarchy, animateTableRows } = window.laporan.usulanRevisi.view;
                    
                    const rows = renderHierarchy(data, 'ikv');
                    $(`.totalSumberdana`).text(rupiah(data.nominal));
                    tabelUsulanRevisi.find("tbody").append(rows.join(""));
                    
                    // Animate rows on first load
                    animateTableRows();
                    
                } catch (e) {
                    console.error('Error showDataPosturAnggaran:', e);
                    throw new Error("Gagal menampilkan data postur anggaran");
                }
            },
            /**
             * Menampilkan data jenis belanja ke tabel
             * @param {Map} data - Map data yang sudah diproses
             */
            showDataJenisBelanja: async (data) => {
                try {
                    const { tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                    const { renderHierarchy, animateTableRows } = window.laporan.usulanRevisi.view;
                    
                    const rows = renderHierarchy(data, 'jenis_belanja');
                    tabelUsulanRevisi.find("tbody").append(rows.join(""));
                    
                    // Animate rows on first load
                    animateTableRows();
                    
                } catch (e) {
                    console.error('Error showDataJenisBelanja:', e);
                    throw new Error("Gagal menampilkan data jenis belanja");
                }
            },

            /**
             * Clear tabel dan reset tampilan
             */
            clearTable: () => {
                const { tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                tabelUsulanRevisi.find("tbody").empty();
            },

            /**
             * Animate table rows on initial load (reusable)
             */
            animateTableRows: () => {
                if (typeof gsap !== 'undefined') {
                    const { tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                    const $tbody = tabelUsulanRevisi.find("tbody");
                    const rows = $tbody.find('tr');
                    
                    // Initial state
                    gsap.set(rows, { opacity: 0, x: -30 });
                    
                    // Animate in with stagger
                    gsap.to(rows, {
                        opacity: 1,
                        x: 0,
                        duration: 0.4,
                        stagger: 0.02,
                        ease: "power2.out"
                    });
                }
            },

            /**
             * Toggle detailed SD view and refresh table with GSAP animation
             * Reusable function for toggling between compact and detailed modes
             */
            toggleDetailedSDView: async () => {
                try {
                    const { cache } = window.laporan.usulanRevisi;
                    const { processDataByFilter } = window.laporan.usulanRevisi.operations;
                    const { selectFilterData, tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                    
                    // Toggle the state
                    cache.showDetailedSD = !cache.showDetailedSD;
                    
                    // Get current filter and cached data
                    const filterTampilan = selectFilterData.val();
                    const $tbody = tabelUsulanRevisi.find("tbody");
                    
                    // GSAP Animation: Fade out old content
                    if (typeof gsap !== 'undefined') {
                        await gsap.to($tbody[0], {
                            opacity: 0,
                            y: -20,
                            duration: 0.3,
                            ease: "power2.in"
                        });
                    }
                    
                    // Process and re-render data
                    if (filterTampilan === "postur") {
                        const responseData = {
                            dataExisting: cache.rawDataExisting || [],
                            dataUsulan: cache.rawDataUsulan || []
                        };
                        await processDataByFilter(filterTampilan, responseData);
                    } else if (filterTampilan === "coa") {
                        const responseData = {
                            dataExisting: cache.rawDataExisting || [],
                            dataUsulan: cache.rawDataUsulan || []
                        };
                        await processDataByFilter(filterTampilan, responseData);
                    }
                    
                    // GSAP Animation: Fade in new content
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo($tbody[0], 
                            { opacity: 0, y: 20 },
                            { 
                                opacity: 1, 
                                y: 0,
                                duration: 0.4,
                                ease: "power2.out"
                            }
                        );
                        
                        // Animate individual rows with stagger effect
                        const rows = $tbody.find('tr');
                        gsap.fromTo(rows, 
                            { opacity: 0, x: -30 },
                            {
                                opacity: 1,
                                x: 0,
                                duration: 0.3,
                                stagger: 0.02,
                                ease: "power2.out",
                                delay: 0.1
                            }
                        );
                    }
                } catch (error) {
                    console.error('Error toggleDetailedSDView:', error);
                }
            },

            /**
             * Setup click handler for jenis row toggle with GSAP animation
             */
            setupJenisRowToggleHandler: () => {
                const { tabelUsulanRevisi } = window.laporan.usulanRevisi.dom;
                const { toggleDetailedSDView } = window.laporan.usulanRevisi.view;
                
                // Use event delegation for dynamically added rows
                tabelUsulanRevisi.on('click', '.jenis-row-toggle', function(e) {
                    e.stopPropagation();
                    
                    const $clickedCell = $(this);
                    const $icon = $clickedCell.find('.jenis-icon');
                    
                    // Animate the clicked row background
                    if (typeof gsap !== 'undefined') {
                        // Icon rotation animation
                        const isExpanded = $clickedCell.hasClass('jenis-row-expanded');
                        const rotation = isExpanded ? 0 : 90; // Rotate from ▼ to ▶
                        
                        gsap.to($icon[0], {
                            rotation: rotation,
                            duration: 0.3,
                            ease: "power2.inOut"
                        });
                        
                        // Cell highlight effect
                        gsap.fromTo($clickedCell[0],
                            { backgroundColor: 'rgba(13, 110, 253, 0.15)' },
                            { 
                                backgroundColor: 'transparent',
                                duration: 0.6,
                                ease: "power2.out"
                            }
                        );
                    }
                    
                    // Toggle the view
                    toggleDetailedSDView();
                });
            }
        },
        validation: {
            /**
             * Validasi input form
             * @param {Object} inputs - Object berisi input yang akan divalidasi
             * @returns {Object} { isValid, message }
             */
            validateInputs: (inputs) => {
                const { idunit, kodeSd, idBackup, filterTampilan } = inputs;
                const { TATA_OPTIONS } = window.laporan.usulanRevisi.config;

                if (!idunit || idunit.length === 0) {
                    return { 
                        isValid: false, 
                        message: "Silahkan pilih unit kerja terlebih dahulu" 
                    };
                }
                
                if (!kodeSd || kodeSd.length === 0) {
                    return { 
                        isValid: false, 
                        message: "Silahkan pilih sumber dana terlebih dahulu" 
                    };
                }
                
                if (!idBackup || idBackup.length === 0) {
                    return { 
                        isValid: false, 
                        message: "Silahkan pilih riwayat usulan revisi terlebih dahulu" 
                    };
                }
                
                if (!filterTampilan) {
                    return { 
                        isValid: false, 
                        message: "Silahkan pilih filter tampilan terlebih dahulu",
                        focusElement: window.laporan.usulanRevisi.dom.selectFilterData
                    };
                }

                return { isValid: true };
            }
        },
        operations: {
            /**
             * Proses data berdasarkan filter yang dipilih
             * @param {String} filterTampilan - Tipe filter ('postur' atau 'coa')
             * @param {Object} responseData - Data dari server
             */
            processDataByFilter: async (filterTampilan, responseData) => {
                const { buildDataPosturAnggaran, buildDataJenisBelanja } = window.laporan.usulanRevisi.business;
                const { showDataPosturAnggaran, showDataJenisBelanja, clearTable } = window.laporan.usulanRevisi.view;
                const { cache } = window.laporan.usulanRevisi;

                // Store raw data in cache for re-rendering when toggling
                cache.rawDataExisting = responseData.dataExisting;
                cache.rawDataUsulan = responseData.dataUsulan;

                clearTable();

                if (filterTampilan === "postur") {
                    await buildDataPosturAnggaran(responseData);
                    await showDataPosturAnggaran(cache.dataPosturAnggaran);
                } else if (filterTampilan === "coa") {
                    await buildDataJenisBelanja(responseData);
                    await showDataJenisBelanja(cache.dataJenisBelanja);
                }
            },
            generatePdf: async () => {
                const url = new URL(window.location.href);
                const isPdf = url.toString().includes("pdf");
                if (!isPdf) return;

                try {
                    window.laporan.usulanRevisi.cache.isPdf = true;
                    
                    const idunit = "{{ $idunit }}";
                    const kodeSd = "{{ $kodeSd }}";
                    const idBackup = "{{ $idBackup }}";
                    const filterTampilan = "{{ $filterTampilan }}";
                    
                    // Store kodeSd in cache for PDF mode to determine jenis grouping
                    window.laporan.usulanRevisi.cache.pdfKodeSd = kodeSd;

                    // Initialize showDetailedSD based on context (PDF view logic)
                    const { initializeShowDetailedSD } = window.laporan.usulanRevisi.business;
                    window.laporan.usulanRevisi.cache.showDetailedSD = initializeShowDetailedSD();

                    const { fetchData } = window.laporan.usulanRevisi.api;
                    const { processDataByFilter } = window.laporan.usulanRevisi.operations;
                    const { statusError, statusLoading } = window.laporan.usulanRevisi.dom;
                    statusLoading.show();
                    const response = await fetchData(idunit, kodeSd, idBackup);
                    
                    if (response.success) {
                        await processDataByFilter(filterTampilan, response.data);
                        statusLoading.hide();
                    } else {
                        console.error("Gagal mengambil data dari server");
                        statusError.show();
                        statusLoading.hide();
                    }
                } catch (error) {
                    console.error('Error generatePdf:', error);
                    statusError.show();
                    statusLoading.hide();
                }
            }
        },
        handlers: {
            /**
             * Handler untuk tombol Cari
             */
            handleCariUsulanRevisi: async (e) => {
                const $btn = $(e.target);
                const btnHtml = $btn.html();
                
                try {
                    const { getHighestSumberdanaParent } = window.laporan.usulanRevisi.business;
                    // Ambil input dari form
                    const inputs = {
                        idunit: $(".unitkerjaOption.selected").map((_, el) => $(el).data("value")).get(),
                        kodeSd: $(".sumberdanaOption.selected").map((_, el) => $(el).data("value")).get(),
                        idBackup: $(".riwayatOption.selected").map((_, el) => $(el).data("value")).get(),
                        filterTampilan: window.laporan.usulanRevisi.dom.selectFilterData.val()
                    };    
                    const highestSumberdanaText = getHighestSumberdanaParent();
                    // Validasi input
                    const { validateInputs } = window.laporan.usulanRevisi.validation;
                    const validation = validateInputs(inputs);
                    
                    if (!validation.isValid) {
                        const { TATA_OPTIONS } = window.laporan.usulanRevisi.config;
                        if (validation.focusElement) validation.focusElement.focus();
                        return tata.error("Error", validation.message, TATA_OPTIONS);
                    }

                    // Initialize showDetailedSD based on context (main view logic)
                    const { initializeShowDetailedSD } = window.laporan.usulanRevisi.business;
                    window.laporan.usulanRevisi.cache.showDetailedSD = initializeShowDetailedSD();

                    // Tampilkan loading state
                    $btn.prop("disabled", true)
                        .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses data...');
                    
                    // Fetch dan proses data
                    const { fetchData } = window.laporan.usulanRevisi.api;
                    const { processDataByFilter } = window.laporan.usulanRevisi.operations;
                    const { TATA_OPTIONS } = window.laporan.usulanRevisi.config;
                    
                    const response = await fetchData(inputs.idunit, inputs.kodeSd, inputs.idBackup);
                    
                    if (response.success) {
                        await processDataByFilter(inputs.filterTampilan, response.data);
                    } else {
                        tata.error("Error", response.message || "Gagal mengambil data dari server", TATA_OPTIONS);
                    }
                    
                } catch (error) {
                    console.error("Error handleCariUsulanRevisi:", error);
                    const { TATA_OPTIONS } = window.laporan.usulanRevisi.config;
                    tata.error("Error", error.message || "Terjadi kesalahan pada sistem", TATA_OPTIONS);
                } finally {
                    // Kembalikan tombol ke kondisi semula
                    $btn.prop("disabled", false).html(btnHtml);
                }
            }
        },
        init: () => {
            const { btnCariUsulanRevisi } = window.laporan.usulanRevisi.dom;
            const { handleCariUsulanRevisi } = window.laporan.usulanRevisi.handlers;
            const { generatePdf } = window.laporan.usulanRevisi.operations;
            const { setupJenisRowToggleHandler } = window.laporan.usulanRevisi.view;
            const { isPdf } = window.laporan.usulanRevisi.cache;
            
            // Bind events
            btnCariUsulanRevisi.on("click", handleCariUsulanRevisi);
            
            // Setup toggle handler for jenis row clicks (only in non-PDF mode)
            if (!isPdf) {
                setupJenisRowToggleHandler();
            }
            
            // Auto-load untuk PDF mode
            generatePdf();
        }
    };

    // Initialize module
    window.laporan.usulanRevisi.init();
});
</script>
