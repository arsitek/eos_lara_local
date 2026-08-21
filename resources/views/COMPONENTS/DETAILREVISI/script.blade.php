<script>
$(document).ready(function() {
    window.components = {
        elements: {
            metadata: $('h4#metadata'),
            jumlahBiayaDetail: 'td.jumlahBiayaDetail', // this element is added dynamically
            modalDetailRevisi: $('#modal-detail-revisi'),
            closeModalDetailRevisi: $('#close-modal-detail-revisi'),
            tableDetailRevisiSemula: $('#tabel-detail-revisi-semula'),
            tableDetailRevisiMenjadi: $('#tabel-detail-revisi-menjadi'),
        },
        constant: {
            ROUTES: {
                GET_DETAIL_REVISI: "{{ route('revisi.getDetailRevisi') }}",
            },
        },
        methods: {
            getData: async ( idSm ) => {
                const { methods } = window.components;
                const { ROUTES } = window.components.constant;
                try {
                    const response = await $.ajax({
                        url: ROUTES.GET_DETAIL_REVISI,
                        method: "GET",
                        data: { idSm }
                    });
                    if ( response.success ) {
                        const { created_at: createdAt, updated_at: updatedAt } = response.data || {};
                        let metadata = "";
                        if (createdAt || updatedAt) {
                            // pick the newest date
                            const isUpdated = updatedAt && (!createdAt || updatedAt > createdAt);
                            const date = new Date(isUpdated ? updatedAt : createdAt);

                            // format date as dd-mm-yyyy
                            const formattedDate = [
                                String(date.getDate()).padStart(2, "0"),
                                String(date.getMonth() + 1).padStart(2, "0"),
                                date.getFullYear()
                            ].join("-");

                            metadata = isUpdated ? `Terakhir di validasi pada ${formattedDate}` : `Dibuat pada ${formattedDate}`;
                        }
                        methods.displayDataToTable( response.data, metadata );
                    } else {
                        throw new Error( response.message || "Gagal mendapatkan data detail revisi." );
                    }
                } catch ( e ) {
                    console.error(e)
                }
            },
            displayDataToTable: (data, metadata) => {
                const { elements } = window.components;
                if (!data) return;
                const {
                    jenis_rab, jenis_revisi, jenis_validasi, jumlah_semula, jumlah_menjadi,
                    spek_semula_json, spek_menjadi_json, id
                } = data;

                // Clear existing data
                elements.tableDetailRevisiSemula.find("tbody").empty();
                elements.tableDetailRevisiMenjadi.find("tbody").empty();
                const safeParse = (payload) => {
                    if (!payload) return {};
                    try {
                        const parsed = JSON.parse(payload);
                        return (parsed && typeof parsed === 'object') ? parsed : {};
                    } catch (error) {
                        console.warn('Failed to parse spec payload', error);
                        return {};
                    }
                };
                const formatValue = (value, fallback = '-') => {
                    return (value === null || typeof value === 'undefined' || value === '') ? fallback : value;
                };
                const escapeHtml = (unsafe) => {
                    if (unsafe === null || typeof unsafe === 'undefined') return '';
                    return String(unsafe)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
                const display = (parts = []) => {
                    const cleaned = parts.map((part) => formatValue(part, '-'));
                    return cleaned.join(' ');
                };

                const dataSemula = safeParse(spek_semula_json);
                const dataMenjadi = safeParse(spek_menjadi_json);

                const spekS = []
                const spekM = []
                if ( jenis_rab == "SARANA" ) {
                    spekS.push(`<span>${escapeHtml(display([dataSemula.kuantitas, dataSemula.satuan]))}</span><br>`)
                    spekM.push(`<span>${escapeHtml(display([dataMenjadi.kuantitas, dataMenjadi.satuan]))}</span><br>`)
                } if ( jenis_rab == "OPERASIONAL" ) {
                    spekS.push(`
                        <span>${escapeHtml(display([
                            dataSemula.kuantitas,
                            dataSemula.satuan_kuantitas,
                            'X',
                            dataSemula.durasi,
                            dataSemula.satuan_durasi,
                            'X',
                            dataSemula.kegiatan,
                            dataSemula.satuan_kegiatan
                        ]))}</span><br>
                    `)
                    spekM.push(`
                        <span>${escapeHtml(display([
                            dataMenjadi.kuantitas,
                            dataMenjadi.satuan_kuantitas,
                            'X',
                            dataMenjadi.durasi,
                            dataMenjadi.satuan_durasi,
                            'X',
                            dataMenjadi.kegiatan,
                            dataMenjadi.satuan_kegiatan
                        ]))}</span><br>
                    `)
                } if ( jenis_rab == "PRASARANA" ) {
                    spekS.push(`<span>1 Paket</span><br>`)
                    spekM.push(`<span>1 Paket</span><br>`)
                }
                // Build and append table rows
                const rowSemula = `<tr>
                    <td>${spekS.join('')}</td>
                    <td class="jumlahBiayaDetail" data-idsm="${id}">${rupiah(jumlah_semula || 0)}</td>
                </tr>`;

                const rowMenjadi = `<tr>
                    <td>${spekM.join('')}</td>
                    <td class="jumlahBiayaDetail" data-idsm="${id}">${rupiah(jumlah_menjadi || 0)}</td>
                </tr>`;
                elements.metadata.text( metadata );
                elements.tableDetailRevisiSemula.find("tbody").append(rowSemula);
                elements.tableDetailRevisiMenjadi.find("tbody").append(rowMenjadi);
            },
            bindFunctions: () => {
                const { elements, methods } = window.components;
                elements.closeModalDetailRevisi.on('click', closeModalDetailRevisi);
                $(document).on('click', 'td.jumlahBiayaDetail', handleOnClickJumlahBiayaDetail);
            }
        }
    }
    const closeModalDetailRevisi = () => {
        const { elements } = window.components;
        elements.modalDetailRevisi.modal('hide');
    }
    const handleOnClickJumlahBiayaDetail = (event) => {
        const { elements, methods } = window.components;
        const target = $(event.currentTarget);
        const idSm = target.data('idsm');
        methods.getData( idSm );
        elements.modalDetailRevisi.modal('show');
    }
    window.components.methods.bindFunctions();
});
</script>
