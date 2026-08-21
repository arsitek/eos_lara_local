<script>
    $(document).ready(function (e) {
        window.laporan = window.laporan || {};
        window.laporan.kegiatan = {
            constants: {
                ROUTES: {
                    GET_DATA: "{{ route('perkinReport.getData') }}",
                    PDF: "{{ route('perkinReport.pdf') }}",
                }
            },
            elements: {
                btnCari: $('button#btn-cari'),
                btnPdf: $('button#btn-pdf'),
                tabelProgramKegiatan: $('table#tabel-program-kegiatan'),
            },
            state: {
                activeRequest: null,
                requestSequence: 0,
                renderVersion: 0,
            },
            methods: {
                init: () => {
                    const { btnCari, btnPdf, tabelProgramKegiatan } = laporan.kegiatan.elements;
                    const { methods } = laporan.kegiatan;

                    if (!tabelProgramKegiatan.length) return;

                    btnCari.on('click', methods.handleOnClickBtnCari);
                    btnPdf.on('click', methods.handleOnClickBtnPdf);
                    methods.initPercentageTooltip();
                    methods.animateCalculatedMoney(tabelProgramKegiatan.find('.jumlah-biaya-revisi'));

                    if ($('body').data('perkin-auto-load')) {
                        methods.handleOnClickBtnCari();
                    }
                },
                getSelectedUnitIds: () => {
                    const selectedIds = $(".unitkerjaOption.selected")
                        .map((_, element) => $(element).data("value"))
                        .get()
                        .filter(value => value && value !== "X");

                    if (selectedIds.length) return selectedIds;

                    const urlIdunit = new URLSearchParams(window.location.search).get('idunit');
                    const pageIdunit = $('body').data('idunit');
                    const idunit = urlIdunit || pageIdunit;

                    return idunit ? [idunit] : [];
                },
                createOrUpdateMap: (map, node, createNode) => {
                    if (!map.has(node)) map.set(node, createNode());
                    return map.get(node);
                },
                initPercentageTooltip: () => {
                    $(document)
                        .off('mouseenter.perkinTooltip focusin.perkinTooltip', '.perkin-percentage-trigger')
                        .on('mouseenter.perkinTooltip focusin.perkinTooltip', '.perkin-percentage-trigger', function() {
                            laporan.kegiatan.methods.adjustPercentageTooltip($(this));
                        });
                },
                adjustPercentageTooltip: ($trigger) => {
                    const $tooltip = $trigger.find('.perkin-percentage-tooltip');
                    if (!$tooltip.length) return;

                    // Tooltip persentase digeser ke kiri jika posisi default melewati sisi kanan layar.
                    $trigger.removeClass('tooltip-left');
                    requestAnimationFrame(() => {
                        const tooltipRect = $tooltip[0].getBoundingClientRect();
                        const tableContainer = $trigger.closest('.table-responsive')[0];
                        const containerRight = tableContainer
                            ? tableContainer.getBoundingClientRect().right - 12
                            : window.innerWidth - 12;
                        const rightBoundary = Math.min(window.innerWidth - 12, containerRight);

                        if (tooltipRect.right > rightBoundary) {
                            $trigger.addClass('tooltip-left');
                        }
                    });
                },
                buildHierarchy: (data) => {
                    if (!data) return;
                    const hierarchy = new Map();
                    const { methods } = laporan.kegiatan;
                    data.forEach((item, index) => {
                        const {
                            kode_ss: kodeSs,
                            ss,
                            kode_ikk_dirjen: kodeIkkDirjen,
                            kode_ikk_sekjen: kodeIkkSekjen,
                            kode_ikv_usk: kodeIkvUsk,
                            ikv_usk: ikvUsk,
                            sub_judul: subJudul,
                            jumlah_biaya_revisi: jumlahBiayaRevisi,
                            id_rekat: idRekat,
                            pagu
                        } = item;
                        const nilaiRevisi = Number(jumlahBiayaRevisi) || 0;

                        const readyDisplayMap = methods.createOrUpdateMap( hierarchy, kodeSs, () => ({
                            ss,
                            pagu,
                            jumlahBiayaRevisi: 0,
                            subJudulMap: new Map()
                        }));

                        // Key sub judul ikut kode IK karena satu id_rekat bisa terkait beberapa IK berbeda.
                        const subJudulBaseKey = idRekat || `${kodeSs || 'tanpa-sasaran'}-${subJudul || 'tanpa-sub-judul'}-${index}`;
                        const subJudulKey = [subJudulBaseKey, kodeIkkSekjen || '-', kodeIkkDirjen || '-', kodeIkvUsk || '-'].join('|');
                        const subJudulNode = methods.createOrUpdateMap(readyDisplayMap.subJudulMap, subJudulKey, () => ({
                            idRekat,
                            subJudul,
                            kodeIkkDirjen,
                            kodeIkkSekjen,
                            kodeIkvUsk,
                            ikvUsk,
                            jumlahBiayaRevisi: 0
                        }));

                        readyDisplayMap.jumlahBiayaRevisi += nilaiRevisi;
                        subJudulNode.jumlahBiayaRevisi += nilaiRevisi;
                    });
                    console.log("Hierarchy built successfully:", hierarchy);
                    methods.renderHierarchy(hierarchy);
                },
                formatNumber: (value) => {
                    return new Intl.NumberFormat('id-ID').format(Number(value) || 0)
                },                parseMoneyValue: (value) => {
                    return Number(String(value ?? '').replace(/[^0-9-]/g, '')) || 0;
                },
                formatMoneyValue: (value) => {
                    if (typeof rupiah === 'function') return rupiah(value || 0);
                    return `Rp${new Intl.NumberFormat('id-ID').format(Number(value) || 0)}`;
                },
                animateCalculatedMoney: ($elements) => {
                    if (!$elements || !$elements.length) return;

                    const renderVersion = laporan.kegiatan.state.renderVersion;
                    $elements.each(function() {
                        const $element = $(this);
                        const targetValue = laporan.kegiatan.methods.parseMoneyValue($element.data('value') ?? $element.text());

                        if (!targetValue) {
                            $element.text('-');
                            return;
                        }

                        if (typeof gsap === 'undefined') {
                            $element.text(laporan.kegiatan.methods.formatMoneyValue(targetValue));
                            return;
                        }

                        const counter = { value: 0 };
                        gsap.to(counter, {
                            value: targetValue,
                            duration: .75,
                            ease: 'power2.out',
                            onUpdate: () => {
                                if (renderVersion !== laporan.kegiatan.state.renderVersion) return;
                                $element.text(laporan.kegiatan.methods.formatMoneyValue(Math.round(counter.value)));
                            },
                        });
                    });
                },
                setCalculatedMoney: ($element, value) => {
                    const numericValue = Number(value) || 0;
                    $element.attr('data-value', numericValue).data('value', numericValue);
                    laporan.kegiatan.methods.animateCalculatedMoney($element);
                },
                setPercentage: ($element, anggaran, pagu) => {
                    const numericAnggaran = Number(anggaran) || 0;
                    const numericPagu = Number(pagu) || 0;
                    const percentage = ( (numericAnggaran / (numericPagu || 1)) * 100).toFixed(2);
                    const $trigger = $('<span>', {
                        class: 'perkin-percentage-trigger',
                        tabindex: 0,
                    });
                    const $tooltip = $('<span>', {
                        class: 'perkin-percentage-tooltip',
                    });

                    // Tooltip menampilkan dasar perhitungan agar nilai persentase mudah diverifikasi.
                    $trigger.append($('<span>').text(`${percentage}%`));
                    $tooltip.append(
                        $('<span>').text(`Anggaran: ${laporan.kegiatan.methods.formatMoneyValue(numericAnggaran)}`),
                        $('<span>').text(`Pagu: ${laporan.kegiatan.methods.formatMoneyValue(numericPagu)}`)
                    );
                    $element.empty().append($trigger.append($tooltip));
                },
                resetTable: () => {
                    const { tabelProgramKegiatan } = laporan.kegiatan.elements;
                    const { state } = laporan.kegiatan;

                    // Versi render dinaikkan agar animasi dari pencarian sebelumnya berhenti memperbarui tabel.
                    state.renderVersion += 1;

                    if ($.fn.DataTable && $.fn.DataTable.isDataTable(tabelProgramKegiatan[0])) {
                        tabelProgramKegiatan.DataTable().destroy();
                    }

                    tabelProgramKegiatan.find('tbody tr.ikv-clone').remove();
                    tabelProgramKegiatan
                        .find('tbody .baseline-awal, tbody .target-akhir, tbody .subjudul, tbody .jumlah-biaya-revisi, tbody .persentase')
                        .text('-');
                    tabelProgramKegiatan.find('tbody .jumlah-biaya-revisi').removeAttr('data-value').removeData('value');
                },
                handleOnClickBtnPdf: () => {
                    const { PDF } = laporan.kegiatan.constants.ROUTES;
                    const idunit = laporan.kegiatan.methods.getSelectedUnitIds()[0];

                    if (!idunit) {
                        const message = "Harap memilih unit kerja terlebih dahulu";
                        if (typeof tata !== 'undefined') {
                            return tata.warn("⚠️ Perhatian", message, { duration: 3000, animate: "slide" });
                        }
                        return window.alert(message);
                    }

                    // Unit terpilih diteruskan melalui URL agar halaman PDF memuat data yang sama.
                    const pdfUrl = new URL(PDF, window.location.origin);
                    pdfUrl.searchParams.set('idunit', idunit);
                    window.location.href = pdfUrl.toString();
                },
                handleOnClickBtnCari: () => {
                    const { ROUTES } = laporan.kegiatan.constants;
                    const { methods } = laporan.kegiatan;
                    const { state } = laporan.kegiatan;
                    const idunit = methods.getSelectedUnitIds();

                    state.requestSequence += 1;
                    const requestSequence = state.requestSequence;

                    // Request lama dibatalkan dan tabel dikosongkan sebelum memuat unit kerja yang baru.
                    if (state.activeRequest) state.activeRequest.abort();
                    methods.resetTable();

                    state.activeRequest = $.ajax({
                        url: ROUTES.GET_DATA,
                        method: 'GET',
                        data: {idunit},
                        success: (res) => {
                            if (requestSequence !== state.requestSequence) return;

                            const data = Array.isArray(res.data) ? res.data : [];
                            console.log("Data fetched successfully:", data);

                            const groupedData = data.reduce((groups, item) => {
                                const kodeIkv = String(item.kode_ikv);
                                if (!groups[kodeIkv]) groups[kodeIkv] = [];
                                groups[kodeIkv].push(item);
                                return groups;
                            }, {});

                            let totalParent = {}
                            Object.entries(groupedData).forEach(([kodeIkv, subJudulList]) => {
                                // Ambil satu baris template/original berdasarkan kode IKV
                                const ikvRow = laporan.kegiatan.elements.tabelProgramKegiatan.find(".ikv-row").filter(function () {
                                    return String($(this).data("ikv")) === kodeIkv;
                                }).first();
                                if (!ikvRow.length) return;


                                let lastRow = ikvRow;
                                subJudulList.forEach((item, index) => {
                                    const { baseline_awal, target_akhir, sub_judul, jumlah_biaya_revisi } = item;

                                    // Data pertama menggunakan baris asli
                                    // Data selanjutnya menggunakan clone
                                    const currentRow = index === 0 ? ikvRow : ikvRow.clone(false, false);
                                    if (index > 0) {
                                        currentRow.removeAttr("id").addClass("ikv-clone").attr("data-parent-ikv", kodeIkv).insertAfter(lastRow);
                                        lastRow = currentRow;
                                    }

                                    currentRow.find(".baseline-awal").text(baseline_awal ?? "");
                                    currentRow.find(".target-akhir").text(target_akhir ?? "");
                                    currentRow.find(".subjudul").text(sub_judul ?? "");
                                    methods.setCalculatedMoney(currentRow.find(".jumlah-biaya-revisi"), jumlah_biaya_revisi ?? 0);
                                    methods.setPercentage(currentRow.find(".persentase"), jumlah_biaya_revisi, item.pagu);

                                    const kodeIkvKey = String(kodeIkv ?? "").substring(0, 8);
                                    totalParent[kodeIkvKey] = (totalParent[kodeIkvKey] || 0) + (Number(jumlah_biaya_revisi) || 0);
                                    // add pagu to totalParent, but dont sum, just set it once
                                    if (!totalParent.hasOwnProperty(`${kodeIkvKey}_pagu`)) {
                                        totalParent[`${kodeIkvKey}_pagu`] = Number(item.pagu);
                                    }
                                });
                            });

                            // Total parent dirender setelah seluruh kelompok selesai agar tidak memakai nilai parsial.
                            Object.entries(totalParent).forEach(([kodeIkvKey, totalBiaya]) => {
                                if (kodeIkvKey.endsWith('_pagu')) return;

                                const totalRow = $(`tr.parent-row[data-ikv^="${kodeIkvKey}"]`).first();
                                if (totalRow.length) {
                                    const pagu = totalParent[`${kodeIkvKey}_pagu`] || 0;
                                    methods.setCalculatedMoney(totalRow.find(".jumlah-biaya-revisi"), totalBiaya);
                                    methods.setPercentage(totalRow.find(".persentase"), totalBiaya, pagu);
                                }
                            });

                            if (data.length) {
                                const totalAnggaran = data.reduce((total, item) => total + (Number(item.jumlah_biaya_revisi) || 0), 0);
                                const totalPagu = Number(data[0].pagu) || 0;
                                const { tabelProgramKegiatan } = laporan.kegiatan.elements;

                                methods.setCalculatedMoney(tabelProgramKegiatan.find('.total-anggaran-keseluruhan'), totalAnggaran);
                                methods.setCalculatedMoney($('.perkin-header-total-anggaran'), totalAnggaran);
                                methods.setPercentage(tabelProgramKegiatan.find('.total-persentase-keseluruhan'), totalAnggaran, totalPagu);
                            }

                            console.log("Total biaya per parent IKV:", totalParent);
                            const { tabelProgramKegiatan } = laporan.kegiatan.elements;
                            const isPdfPage = $('body').data('perkin-auto-load') === true;

                            // PDF dan index memakai satu mekanisme penggabungan baris agar struktur tabel tidak rusak.
                            if (isPdfPage && $.fn.rowspanizer) {
                                tabelProgramKegiatan.rowspanizer({});
                            } else if ($.fn.DataTable) {
                                tabelProgramKegiatan.DataTable({
                                    "paging": false,
                                    "rowsGroup": [0, 1, 2, 3, 4],
                                    // disable ordering on all columns
                                    "ordering": false,
                                });
                            }
                        },
                        error: (xhr, status, error) => {
                            if (requestSequence !== state.requestSequence || status === 'abort') return;
                            console.error("Error fetching data:", error);
                        },
                        complete: () => {
                            if (requestSequence === state.requestSequence) {
                                state.activeRequest = null;
                            }
                        }
                    });
                }
            },
        }
        window.laporan.kegiatan.methods.init();
    })
</script>
