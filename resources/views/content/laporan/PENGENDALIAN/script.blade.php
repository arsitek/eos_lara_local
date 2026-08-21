<script>
    $(document).ready(function() {
        window.pengendalian = {
            elements: {
                tblRow: $('#tabel-pengendalian tbody tr'),
                modalHistory: $('#modal-lembar-kendali'),
                tblRiwayatModalLembarKendali: $('#tabel-riwayat-modal-lembar-kendali'),
            },
            constants: {
                ID: 0,
                JENIS_RAB: null,
                TIMEOUT: 10000,
                CSRF_TOKEN: '{{ csrf_token() }}',
                ROUTES: {
                    GET_HISTORI_PENGENDALIAN: "{{ route('lembarpengendalian.getHistories') }}",
                }
            },
            methods: {
                fetchHistories: async function() {
                    const { ID, JENIS_RAB, TIMEOUT, CSRF_TOKEN, ROUTES } = window.pengendalian.constants;
                    const { tblRiwayatModalLembarKendali } = window.pengendalian.elements;

                    try {
                        // Use plain object for GET request, not FormData
                        const requestData = {
                            id: ID,
                            jenis_rab: JENIS_RAB
                        };

                        const response = await $.ajax({
                            url: ROUTES.GET_HISTORI_PENGENDALIAN,
                            method: 'GET',
                            data: requestData,
                            timeout: TIMEOUT,
                        });

                        if ( response.success ) {
                            const histories = response.data;
                            const tbody = tblRiwayatModalLembarKendali.find('tbody');
                            // Clear existing content
                            tbody.empty();

                            if ( histories.length === 0 ) {
                                tbody.append('<tr><td colspan="5" class="text-center">Tidak ada riwayat tersedia</td></tr>');
                            } else {
                                histories.forEach((history, index) => {
                                    const totalBayar = parseFloat(history.TOTAL_AMPRAH) + parseFloat(history.TOTAL_REALISASI);
                                    const genereateStyle = totalBayar > parseFloat(history.TOTAL) ? 'color: red;' : '';
                                    const row = `
                                        <tr style="${genereateStyle}">
                                            <td>${index + 1}</td>
                                            <td>${history.keterangan || '-'}</td>
                                            <td class="text-end">${rupiah(history.TOTAL || 0)}</td>
                                            <td class="text-end">${rupiah(history.TOTAL_AMPRAH || 0)}</td>
                                            <td class="text-end">${rupiah(history.TOTAL_REALISASI || 0)}</td>
                                        </tr>
                                    `;
                                    tbody.append(row);
                                });
                            }
                        } else {
                            return tata.error("⛔ Error", response.message || "Gagal mendapatkan riwayat", { animate: "slide" });
                        }

                    } catch (error) {
                        console.error("Error fetching histories:", error);
                        return tata.error("⛔ Error", "Terjadi kesalahan riwayat", { animate: "slide" });
                    }
                }
            }
        };

        function bindEvent(){
            const { tblRow } = window.pengendalian.elements;
            const { modalHistory } = window.pengendalian.elements;

            tblRow.on("click", handleClickTblRow);

            // Bind close button event
            $('#close-modal-lembar-kendali').on('click', function() {
                modalHistory.modal('hide');
            });
        }

        const handleClickTblRow = (e) => {
            const { modalHistory, tblRiwayatModalLembarKendali } = window.pengendalian.elements;

            // Only clear the table body, not the entire modal body
            tblRiwayatModalLembarKendali.find('tbody').empty();

            modalHistory.modal('show');
            window.pengendalian.constants.ID = $(e.currentTarget).attr("key");
            window.pengendalian.constants.JENIS_RAB = $(e.currentTarget).attr("jenis");
            window.pengendalian.methods.fetchHistories();
        }

        bindEvent();
        // Define currency animation
        function animateCurrency($element) {
            $('.text-end').each(function() {
                const element = $(this);
                const text = element.text();

                if (text.includes('Rp')) {
                    // Extract number from formatted currency
                    const numberStr = text.replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
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
        }

        // Initialize DataTable with animation callbacks
        const table = $('#tabel-pengendalian').DataTable({
            "rowsGroup": [1,2],
            "initComplete": function(settings, json) {
                // Animate table fade in after initialization
                gsap.fromTo("#tabel-pengendalian",
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.8, ease: "power2.out" }
                );

                // Animate rows staggered
                gsap.fromTo("#tabel-pengendalian tbody tr",
                    { opacity: 0, x: -20 },
                    {
                        opacity: 1,
                        x: 0,
                        duration: 0.6,
                        stagger: 0.1,
                        ease: "power2.out",
                        delay: 0.3
                    }
                );
            },
            "drawCallback": function(settings) {
                // Animate new rows when table is redrawn (pagination, search, etc.)
                gsap.fromTo("#tabel-pengendalian tbody tr",
                    { opacity: 0, scale: 0.9 },
                    {
                        opacity: 1,
                        scale: 1,
                        duration: 0.4,
                        stagger: 0.05,
                        ease: "back.out(1.7)"
                    }
                );
            }
        });

        // Add hover animations for table rows
        $('#tabel-pengendalian tbody').on('mouseenter', 'tr', function() {
            gsap.to($(this), {
                scale: 1.02,
                duration: 0.3,
                ease: "power2.out"
            });
        }).on('mouseleave', 'tr', function() {
            gsap.to($(this), {
                backgroundColor: "transparent",
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });

        // Animate card header
        gsap.fromTo(".card-header",
            { opacity: 0, y: -20 },
            { opacity: 1, y: 0, duration: 0.6, ease: "power2.out" }
        );
        animateCurrency($('.text-end'));
    });
</script>
