<script>
    $(document).ready(function () {
        // Namespace dipertahankan agar komponen rekap subkomponen dapat memanggil pemuat tabel sasaran.
        window.revisi = window.revisi || {}
        window.revisi.methods = window.revisi.methods || {}
        const markWarning = ( $el ) => {
            $el.css({ color: 'red', cursor: 'pointer' }).attr('role', 'button');
        }

        /**
         * Daftar kode sasaran yang ditampilkan pada tabel rekap sasaran.
         * CUSTOMIZATION POINT: ubah array ini jika master sasaran bertambah/berkurang atau ingin urutan tampilan berbeda.
         */
        const kodeSasaranRekap = ["S.01","S.02", "S.03", "S.04"]

        /**
         * Kelompok anggaran internal untuk rekap sasaran.
         * `operasional` mewakili RAB kegiatan; `sapras` mewakili RAB sarana dan prasarana.
         * Nilai ini harus sinkron dengan class CSS dan hasil normalisasi `normalizeRekapSasaranRabType`.
         */
        const rekapSasaranJenisAnggaran = ["operasional", "sapras"]

        /**
         * Menormalisasi variasi tipe RAB dari backend menjadi key internal tabel rekap.
         * Sumber data dapat berupa `jenisRab`, `rab_type`, atau `jenis_rab` dari response rekap sasaran.
         * Dampak perubahan: key return harus tetap cocok dengan `rekapSasaranJenisAnggaran` dan selector cell tabel.
         */
        const normalizeRekapSasaranRabType = ( rabType ) => {
            const normalized = String(rabType || "").toUpperCase()
            if (["OPERASIONAL", "RAB_KEGIATAN"].includes(normalized)) return "operasional"
            if (["SARANA", "PRASARANA", "RAB_PERALATAN", "RAB_GEDUNG"].includes(normalized)) return "sapras"
            if (["OPERASIONAL KEGIATAN", "OPERASIONAL"].includes(normalized)) return "operasional"
            if (["DUKUNGAN SAPRAS", "SAPRAS"].includes(normalized)) return "sapras"
            return null
        }

        /**
         * Mengubah response rekap sasaran menjadi array yang aman diproses.
         * Mendukung bentuk array maupun object hasil grouping lama agar render tetap backward-compatible.
         */
        const normalizeRekapSasaranItems = ( data ) => {
            if (Array.isArray(data)) return data
            if (!data || typeof data !== "object") return []

            return Object.entries(data).map(([kodeSs, value]) => ({
                kodeSs,
                jumlahBiaya: value?.total ?? value?.jumlahBiaya ?? value ?? 0,
                jenisRab: value?.jenisRab ?? value?.rab_type ?? value?.jenis_rab ?? null,
                ...((value && typeof value === "object") ? value : {})
            }))
        }

        const hasRekapSasaranData = ( data ) => normalizeRekapSasaranItems(data).length > 0

        /**
         * Parser angka defensif untuk nominal/persentase hasil backend maupun hasil format tampilan.
         * Mendukung format seperti `Rp1.000.000`, `1.000.000`, `-1.000.000`, `10,25%`, dan `-10.25%`.
         * Nilai invalid selalu dikembalikan sebagai 0 agar render tidak pernah menampilkan NaN.
         */
        const parseRekapSasaranNumericValue = ( value ) => {
            if (value === null || value === undefined) return 0
            if (typeof value === "number") return Number.isFinite(value) ? value : 0

            let normalized = String(value).trim()
            if (!normalized) return 0

            const isNegative = normalized.includes("-")
            normalized = normalized
                .replace(/Rp\.?/gi, "")
                .replace(/%/g, "")
                .replace(/\s/g, "")
                .replace(/-/g, "")

            if (!normalized) return 0

            const hasComma = normalized.includes(",")
            const hasDot = normalized.includes(".")
            if (hasComma && hasDot) {
                normalized = normalized.replace(/\./g, "").replace(",", ".")
            } else if (hasComma) {
                normalized = normalized.replace(",", ".")
            } else if (hasDot) {
                const parts = normalized.split(".")
                if (parts.length > 1 && parts[parts.length - 1].length === 3) {
                    normalized = parts.join("")
                }
            }

            const number = Number(normalized)
            if (!Number.isFinite(number)) return 0
            return isNegative ? -number : number
        }

        /**
         * Konversi angka defensif untuk nilai nominal dari backend.
         * Nilai null, kosong, undefined, dan NaN dipaksa menjadi 0 agar kalkulasi tidak merusak render tabel.
         */
        const toRekapSasaranNumber = ( value ) => parseRekapSasaranNumericValue(value)

        /**
         * Helper tone warna untuk nilai Selisih dan Persentase.
         * CUSTOMIZATION POINT: ubah class Bootstrap di sini jika desain warna indikator ingin diganti.
         */
        const getRekapSasaranComparisonToneClass = ( value ) => {
            const number = parseRekapSasaranNumericValue(value)
            if (number < 0) return "text-danger fw-semibold rekap-sasaran-value-negative"
            if (number > 0) return "text-success fw-semibold rekap-sasaran-value-positive"
            return ""
        }

        /**
         * Menerapkan warna positif/negatif pada cell tanpa mengubah format angka yang sudah dirender.
         * Dipakai oleh row total; row detail memakai helper class yang sama saat HTML dibentuk.
         */
        const applyRekapSasaranComparisonTone = ( $cell, value ) => {
            $cell
                .removeClass("text-danger text-success fw-semibold rekap-sasaran-value-negative rekap-sasaran-value-positive")
                .addClass(getRekapSasaranComparisonToneClass(value))
        }

        /**
         * Struktur default agregasi per kode sasaran.
         * Tambahkan properti baru di sini jika ada kelompok anggaran baru selain operasional/sapras.
         */
        const createRekapSasaranSummary = () => ({ operasional: 0, sapras: 0, total: 0 })

        /**
         * Membentuk Map total rekap sasaran per `kodeSs` dari data Semula atau Perubahan.
         * `amountKeys` menentukan prioritas field nominal karena backend semula dan menjadi memakai nama field berbeda.
         * Logic utama: normalisasi tipe RAB -> jumlahkan nominal ke bucket kelompok anggaran -> jumlahkan total sasaran.
         */
        const buildRekapSasaranSummaryMap = ( data, amountKeys = ["TOTAL", "jumlahBiaya", "total", "jumlah_biaya_revisi"] ) => {
            const map = new Map()

            normalizeRekapSasaranItems(data).forEach((item) => {
                const kodeSs = item.kodeSs ?? item.kode_ss ?? item.kodeSS
                if (!kodeSs) return

                const jenisRab  = normalizeRekapSasaranRabType(item.jenisRab ?? item.rab_type ?? item.jenis_rab)
                const amountKey = amountKeys.find((key) => item[key] !== undefined && item[key] !== null)
                const amount    = toRekapSasaranNumber(amountKey ? item[amountKey] : 0)
                const summary   = map.get(kodeSs) || createRekapSasaranSummary()

                if (jenisRab && rekapSasaranJenisAnggaran.includes(jenisRab)) {
                    summary[jenisRab] += amount
                }
                summary.total += amount
                map.set(kodeSs, summary)
            })

            return map
        }

        /**
         * Menghapus style warning range pada cell rekap sasaran sebelum render ulang.
         * Penting dipanggil saat reload/filter agar status merah dari data lama tidak tertinggal.
         */
        const clearRekapSasaranWarning = ( $el ) => {
            $el.css({ color: "", cursor: "" }).removeAttr("role title")
        }

        /**
         * Menghitung range validasi 10% dari nilai Semula.
         * Magic number `0.1` adalah batas toleransi existing; ubah di sini jika kebijakan range berubah.
         */
        const getRekapSasaranRange = ( amount ) => {
            const tenPercent = Math.round(amount * 0.1)
            return {
                min: amount - tenPercent,
                max: amount + tenPercent
            }
        }

        /**
         * Memberi warning visual jika nilai Perubahan berada di luar range minimum/maksimum.
         * Logic ini hanya visual; validasi final tetap harus berada di backend saat penyimpanan revisi.
         */
        const applyRekapSasaranRangeWarning = ( $el, value, min, max ) => {
            clearRekapSasaranWarning($el)
            if (value < min) {
                markWarning($el)
                $el.attr("title", `Di bawah range minimum ${rupiah(min)}`)
            }
            if (value > max) {
                markWarning($el)
                $el.attr("title", `Melebihi range maksimum ${rupiah(max)}`)
            }
        }

        /**
         * Menghitung Selisih dan Persentase untuk rekap sasaran maupun detail sub klasifikasi.
         * Rumus: Selisih = Semula - Perubahan; Persentase = Selisih / Semula * 100.
         * Division by zero diamankan dengan mengembalikan persentase 0 ketika Semula bernilai 0.
         */
        const calculateRekapSasaranComparison = ( semulaValue, perubahanValue ) => {
            const semula = toRekapSasaranNumber(semulaValue)
            const perubahan = toRekapSasaranNumber(perubahanValue)
            const selisih = perubahan - semula
            const persentase = semula === 0 ? 0 : (selisih / semula) * 100

            return { selisih, persentase }
        }

        /**
         * Format persentase rekap dengan 2 digit desimal agar konsisten antar row total dan detail.
         * CUSTOMIZATION POINT: ubah `toFixed(2)` jika format angka persentase ingin diubah.
         */
        const formatRekapSasaranPercentage = ( value ) => {
            const number = Number(value)
            return `${Number.isFinite(number) ? number.toFixed(2) : '0.00'}%`
        }

        /**
         * Render nilai Selisih dan Persentase ke cell target.
         * Fungsi ini sengaja dipisah agar logic perhitungan bisa dipakai ulang oleh row total dan row sub klasifikasi.
         * Warna indikator hanya membaca hasil perhitungan, tidak mengubah logic bisnis Selisih/Persentase.
         */
        const renderRekapSasaranComparison = ( $selisihCell, $persentaseCell, semulaValue, perubahanValue ) => {
            const { selisih, persentase } = calculateRekapSasaranComparison(semulaValue, perubahanValue)

            // CUSTOMIZATION POINT: ubah format text di sini jika tampilan nilai Selisih/Persentase ingin disesuaikan.
            $selisihCell.text(rupiah(selisih))
            $persentaseCell.text(formatRekapSasaranPercentage(persentase))
            applyRekapSasaranComparisonTone($selisihCell, selisih)
            applyRekapSasaranComparisonTone($persentaseCell, persentase)
        }

        /**
         * Escape teks sebelum disisipkan ke HTML detail row.
         * Penting karena nama sub klasifikasi berasal dari data master/backend dan bisa mengandung karakter HTML.
         */
        const escapeRekapSasaranText = ( value ) => String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")

        /**
         * Mengambil kode sub klasifikasi dari format key camelCase maupun snake_case.
         * Kompatibilitas ini dibutuhkan karena `perKomponen.semula` dan `perKomponen.menjadi` memakai nama field berbeda.
         */
        const getRekapSasaranSubKode = ( item ) => item?.kodeSubKlasifikasi ?? item?.kode_sub_klasifikasi ?? item?.kode_subklasifikasi ?? "-"

        /**
         * Mengambil nama sub klasifikasi dari beberapa kemungkinan nama field backend.
         * Fallback `Data tidak tersedia` menjaga tabel tetap render saat master belum lengkap.
         */
        const getRekapSasaranSubNama = ( item ) => item?.subKlasifikasi ?? item?.sub_klasifikasi ?? item?.namaSubKlasifikasi ?? "Data tidak tersedia"

        /**
         * Mengambil nominal detail sub klasifikasi berdasarkan prioritas nama field.
         * Semula biasanya memakai `TOTAL`, sedangkan Menjadi memakai `jumlahBiaya`.
         */
        const getRekapSasaranSubAmount = ( item, amountKeys ) => {
            const amountKey = amountKeys.find((key) => item?.[key] !== undefined && item?.[key] !== null)
            return toRekapSasaranNumber(amountKey ? item[amountKey] : 0)
        }

        /**
         * Struktur default row detail sub klasifikasi setelah data Semula dan Menjadi digabung.
         * Nilai yang tidak ada di salah satu sisi tetap 0 agar row tetap tampil lengkap.
         */
        const createRekapSasaranSubSummary = ( kodeSs, kodeSubKlasifikasi, subKlasifikasi ) => ({
            kodeSs,
            kodeSubKlasifikasi,
            subKlasifikasi,
            semula: 0,
            perubahan: 0
        })

        /**
         * Mengambil kode sumber dana dari bentuk response backend yang berbeda-beda.
         * Nilai fallback dipakai hanya agar tabel tetap render saat data lama belum punya metadata sumber dana.
         */
        const getRekapSasaranSumberDanaKey = ( item ) => String(item?.kd_sumberdana ?? item?.kodeSd ?? item?.kode_sd ?? item?.sd ?? "__unknown")

        /**
         * Mengambil label sumber dana untuk row group utama.
         * CUSTOMIZATION POINT: ubah format label di `renderSumberDanaRow` jika ingin tampilan kode/nama berbeda.
         */
        const getRekapSasaranSumberDanaName = ( item ) => item?.sumberdana ?? item?.nama_sumberdana ?? item?.namaSumberDana ?? "Sumber Dana tidak tersedia"

        /**
         * Mapping nama sasaran untuk row level 2.
         * CUSTOMIZATION POINT: tambahkan mapping jika master sasaran baru perlu nama statis di frontend.
         */
        const getRekapSasaranName = ( kodeSs ) => ({
            "S.01": "Talenta",
            "S.02": "Inovasi",
            "S.03": "Kontribusi/dedikasi pada masyarakat",
            "S.04": "Tata kelola berintegritas"
        })[kodeSs] || "Sasaran tidak tersedia"

        /**
         * Menggabungkan metadata sumber dana dari semua dataset response.
         * Output Map dipakai sebagai urutan render level 1 tanpa hardcode sumber dana di frontend.
         */
        const groupBySumberDana = ( ...dataSets ) => {
            const map = new Map()
            dataSets.flatMap((data) => normalizeRekapSasaranItems(data)).forEach((item) => {
                const key = getRekapSasaranSumberDanaKey(item)
                if (!map.has(key)) {
                    map.set(key, {
                        kd_sumberdana: key,
                        sumberdana: getRekapSasaranSumberDanaName(item)
                    })
                }
            })

            return new Map([...map.entries()].sort((a, b) => String(a[0]).localeCompare(String(b[0]), "id-ID", { numeric: true })))
        }

        /**
         * Mengelompokkan total Semula/Menjadi menjadi struktur sumber dana -> sasaran.
         * Data yang diproses berasal dari `dataSemula` dan `dataMenjadi` endpoint rekap sasaran.
         */
        const groupSasaranBySumberDana = ( data, amountKeys = ["TOTAL", "jumlahBiaya", "total", "jumlah_biaya_revisi"] ) => {
            const map = new Map()

            normalizeRekapSasaranItems(data).forEach((item) => {
                const kodeSs = item.kodeSs ?? item.kode_ss ?? item.kodeSS
                if (!kodeSs) return

                const sumberDanaKey = getRekapSasaranSumberDanaKey(item)
                const sumberDana = map.get(sumberDanaKey) || {
                    kd_sumberdana: sumberDanaKey,
                    sumberdana: getRekapSasaranSumberDanaName(item),
                    sasaran: new Map()
                }
                const jenisRab = normalizeRekapSasaranRabType(item.jenisRab ?? item.rab_type ?? item.jenis_rab)
                const amountKey = amountKeys.find((key) => item[key] !== undefined && item[key] !== null)
                const amount = toRekapSasaranNumber(amountKey ? item[amountKey] : 0)
                const summary = sumberDana.sasaran.get(kodeSs) || createRekapSasaranSummary()
                summary.kd_sumberdana = sumberDanaKey
                summary.sumberdana = sumberDana.sumberdana

                if (jenisRab && rekapSasaranJenisAnggaran.includes(jenisRab)) {
                    summary[jenisRab] += amount
                }
                summary.total += amount
                sumberDana.sasaran.set(kodeSs, summary)
                map.set(sumberDanaKey, sumberDana)
            })

            return map
        }

        /**
         * Menggabungkan `perKomponen.semula` dan `perKomponen.menjadi` berdasarkan sumber dana + kodeSs + kode sub klasifikasi.
         * Output: Map sumber dana -> Map sasaran -> array detail sub klasifikasi.
         * Dampak perubahan: jika key merge berubah, pastikan backend `getRekapSasaranPerKomponenSemulaMenjadi` tetap sinkron.
         */
        const mergePerKomponenData = ( perKomponen = {} ) => {
            const flatMap = new Map()
            const upsert = ( item, jenisData, amountKeys ) => {
                const kodeSs = item?.kodeSs ?? item?.kode_ss ?? item?.kodeSS
                const kodeSubKlasifikasi = getRekapSasaranSubKode(item)
                if (!kodeSs || !kodeSubKlasifikasi || kodeSubKlasifikasi === "-") return

                const sumberDanaKey = getRekapSasaranSumberDanaKey(item)
                const key = `${sumberDanaKey}||${kodeSs}||${kodeSubKlasifikasi}`
                const current = flatMap.get(key) || {
                    ...createRekapSasaranSubSummary(kodeSs, kodeSubKlasifikasi, getRekapSasaranSubNama(item)),
                    kd_sumberdana: sumberDanaKey,
                    sumberdana: getRekapSasaranSumberDanaName(item)
                }
                const subName = getRekapSasaranSubNama(item)
                if (subName && subName !== "Data tidak tersedia") current.subKlasifikasi = subName
                current[jenisData] += getRekapSasaranSubAmount(item, amountKeys)
                flatMap.set(key, current)
            }

            normalizeRekapSasaranItems(perKomponen?.semula).forEach((item) => upsert(item, "semula", ["TOTAL", "jumlahBiaya", "total"]))
            normalizeRekapSasaranItems(perKomponen?.menjadi).forEach((item) => upsert(item, "perubahan", ["jumlahBiaya", "TOTAL", "total"]))

            const grouped = new Map()
            flatMap.forEach((item) => {
                const sumberDana = grouped.get(item.kd_sumberdana) || {
                    kd_sumberdana: item.kd_sumberdana,
                    sumberdana: item.sumberdana,
                    sasaran: new Map()
                }
                const rows = sumberDana.sasaran.get(item.kodeSs) || []
                rows.push(item)
                sumberDana.sasaran.set(item.kodeSs, rows)
                grouped.set(item.kd_sumberdana, sumberDana)
            })
            grouped.forEach((sumberDana) => {
                sumberDana.sasaran.forEach((rows) => rows.sort((a, b) => String(a.kodeSubKlasifikasi).localeCompare(String(b.kodeSubKlasifikasi), "id-ID", { numeric: true })))
            })

            return grouped
        }

        /**
         * Membentuk metadata warning range minimum/maksimum untuk cell Perubahan.
         * Logic validasi tidak berubah: hanya memberi indikator visual jika nilai keluar dari range 10% Semula.
         */
        const getRekapSasaranRangeWarning = ( value, min, max ) => {
            if (value < min) return {
                className: "text-danger fw-semibold",
                attrs: `title="Di bawah range minimum ${escapeRekapSasaranText(rupiah(min))}" role="button"`
            }
            if (value > max) return {
                className: "text-danger fw-semibold",
                attrs: `title="Melebihi range maksimum ${escapeRekapSasaranText(rupiah(max))}" role="button"`
            }
            return { className: "", attrs: "" }
        }

        /**
         * Menghitung total level sumber dana dari seluruh sasaran di bawahnya.
         * Data sumbernya adalah summary Semula/Menjadi yang sudah dikelompokkan per sumber dana.
         */
        const calculateRekapSumberDanaTotals = ( semulaSasaran = new Map(), perubahanSasaran = new Map() ) => {
            const sasaranKeys = new Set([...semulaSasaran.keys(), ...perubahanSasaran.keys()])
            return [...sasaranKeys].reduce((total, kodeSs) => {
                total.semula += toRekapSasaranNumber(semulaSasaran.get(kodeSs)?.total)
                total.perubahan += toRekapSasaranNumber(perubahanSasaran.get(kodeSs)?.total)
                return total
            }, { semula: 0, perubahan: 0 })
        }

        /**
         * Render row level 1: sumber dana beserta total Semula/Perubahan/Selisih/Persentase.
         * CUSTOMIZATION POINT: ubah class table/indentasi di sini jika tampilan group utama ingin disesuaikan.
         */
        const renderSumberDanaRow = ( sumberDana, totals = { semula: 0, perubahan: 0 } ) => {
            const kodeSd = escapeRekapSasaranText(sumberDana?.kd_sumberdana || "-")
            const namaSd = escapeRekapSasaranText(sumberDana?.sumberdana || "Sumber Dana tidak tersedia")
            const { selisih, persentase } = calculateRekapSasaranComparison(totals.semula, totals.perubahan)
            const totalRange = getRekapSasaranRange(totals.semula)
            const perubahanWarning = getRekapSasaranRangeWarning(totals.perubahan, totalRange.min, totalRange.max)
            const selisihToneClass = getRekapSasaranComparisonToneClass(selisih)
            const persentaseToneClass = getRekapSasaranComparisonToneClass(persentase)

            return `<tr class="rekap-sasaran-sumberdana-row table-primary" data-sumberdana="${kodeSd}" style="cursor: pointer">
                <td colspan="2" class="fw-bold">${kodeSd} - ${namaSd}</td>
                <td class="fw-bold text-end">${rupiah(totals.semula)}</td>
                <td class="fw-bold text-end ${perubahanWarning.className}" ${perubahanWarning.attrs}>${rupiah(totals.perubahan)}</td>
                <td class="fw-bold text-end selisih-total ${selisihToneClass}">${rupiah(selisih)}</td>
                <td class="fw-bold text-end persentase-total ${persentaseToneClass}">${formatRekapSasaranPercentage(persentase)}</td>
            </tr>`
        }

        /**
         * Render row level 2: sasaran per sumber dana.
         * Nilai Semula/Perubahan/Selisih/Persentase tetap dihitung oleh helper existing agar business logic tidak berubah.
         */
        const renderSasaranRow = ( kodeSs, semula = createRekapSasaranSummary(), perubahan = createRekapSasaranSummary(), sumberDanaKey = "" ) => {
            const { selisih, persentase } = calculateRekapSasaranComparison(semula.total, perubahan.total)
            const totalRange = getRekapSasaranRange(semula.total)
            const perubahanWarning = getRekapSasaranRangeWarning(perubahan.total, totalRange.min, totalRange.max)
            const selisihToneClass = getRekapSasaranComparisonToneClass(selisih)
            const persentaseToneClass = getRekapSasaranComparisonToneClass(persentase)

            return `<tr class="rekap-sasaran-sasaran-row" data-sumberdana="${escapeRekapSasaranText(semula.kd_sumberdana ?? perubahan.kd_sumberdana ?? sumberDanaKey)}" data-kode-ss="${escapeRekapSasaranText(kodeSs)}" style="cursor: pointer">
                <td class="fw-bold ps-5">${escapeRekapSasaranText(kodeSs)}</td>
                <td class="fw-bold">${escapeRekapSasaranText(getRekapSasaranName(kodeSs))}</td>
                <td class="fw-bold text-end">${rupiah(semula.total)}</td>
                <td class="fw-bold text-end ${perubahanWarning.className}" ${perubahanWarning.attrs}>${rupiah(perubahan.total)}</td>
                <td class="fw-bold text-end selisih-total ${selisihToneClass}">${rupiah(selisih)}</td>
                <td class="fw-bold text-end persentase-total ${persentaseToneClass}">${formatRekapSasaranPercentage(persentase)}</td>
            </tr>`
        }

        /**
         * Render row level 3: per komponen/sub klasifikasi di bawah sasaran.
         * CUSTOMIZATION POINT: ubah label/indentasi detail di sini jika format sub klasifikasi perlu dikustomisasi.
         */
        const renderPerKomponenRow = ( item ) => {
            const { selisih, persentase } = calculateRekapSasaranComparison(item.semula, item.perubahan)
            const selisihToneClass = getRekapSasaranComparisonToneClass(selisih)
            const persentaseToneClass = getRekapSasaranComparisonToneClass(persentase)

            return `<tr class="rekap-sasaran-detail-row" data-kode-ss="${escapeRekapSasaranText(item.kodeSs)}" style="font-size: 12px">
                <td class="ps-9">${escapeRekapSasaranText(item.kodeSubKlasifikasi)}</td>
                <td>${escapeRekapSasaranText(item.subKlasifikasi || "Data tidak tersedia")}</td>
                <td class="text-end">${rupiah(item.semula)}</td>
                <td class="text-end">${rupiah(item.perubahan)}</td>
                <td class="text-end rekap-sasaran-selisih ${selisihToneClass}">${rupiah(selisih)}</td>
                <td class="text-end rekap-sasaran-persentase ${persentaseToneClass}">${formatRekapSasaranPercentage(persentase)}</td>
            </tr>`
        }

        /**
         * Function render utama tabel rekap sasaran dengan struktur hierarki:
         * Sumber Dana -> Sasaran -> Per Komponen/Sub Klasifikasi.
         * Filter, request, dan rumus existing tidak diubah; function ini hanya mengubah cara data divisualisasikan.
         */
        const renderRekapSasaranTable = ( dataSemula = [], dataMenjadi = [], kodeSasaran = kodeSasaranRekap, perKomponen = {} ) => {
            const $tbody = $("#tabel-rekap-sasaran tbody")
            const sumberDanaMap = groupBySumberDana(dataSemula, dataMenjadi, perKomponen?.semula, perKomponen?.menjadi)
            const semulaMap = groupSasaranBySumberDana(dataSemula, ["TOTAL", "jumlahBiaya", "total"])
            const perubahanMap = groupSasaranBySumberDana(dataMenjadi, ["jumlahBiaya", "TOTAL", "total"])
            const perKomponenMap = mergePerKomponenData(perKomponen)
            const rows = []

            $tbody.empty()

            if (sumberDanaMap.size === 0) {
                $tbody.html(`<tr><td colspan="6" class="text-center text-muted">Data rekap sasaran tidak tersedia.</td></tr>`)
                // animateRekapSasaranTableRender()
                return
            }

            sumberDanaMap.forEach((sumberDana, sumberDanaKey) => {
                const semulaSasaran = semulaMap.get(sumberDanaKey)?.sasaran || new Map()
                const perubahanSasaran = perubahanMap.get(sumberDanaKey)?.sasaran || new Map()
                const detailSasaran = perKomponenMap.get(sumberDanaKey)?.sasaran || new Map()
                const sumberDanaTotals = calculateRekapSumberDanaTotals(semulaSasaran, perubahanSasaran)

                rows.push(renderSumberDanaRow(sumberDana, sumberDanaTotals))
                const sasaranKeys = new Set([
                    ...kodeSasaran,
                    ...semulaSasaran.keys(),
                    ...perubahanSasaran.keys(),
                    ...detailSasaran.keys()
                ])

                sasaranKeys.forEach((kodeSs) => {
                    const semula = semulaSasaran.get(kodeSs) || createRekapSasaranSummary()
                    const perubahan = perubahanSasaran.get(kodeSs) || createRekapSasaranSummary()
                    rows.push(renderSasaranRow(kodeSs, semula, perubahan, sumberDanaKey))
                    ;(detailSasaran.get(kodeSs) || []).forEach((item) => rows.push(renderPerKomponenRow(item)))
                })
            })

            $tbody.html(rows.join(""))
            // animateRekapSasaranTableRender()
        }

        /**
         * Mencari row target di tabel rekap subkomponen berdasarkan atribut data.
         * Dipakai oleh fitur click-to-scroll dari row rekap sasaran ke row subkomponen yang setara.
         */
        const findRekapSubkomponenTargetRow = ( sumberDana, kodeSs = null ) => {
            const $rows = $("#tabel-rekap-subkomponen tbody tr")
            return $rows.filter((_, row) => {
                const $row = $(row)
                const isSameSumberDana = String($row.data("sumberdana") ?? "") === String(sumberDana ?? "")
                const isSameSasaran = kodeSs === null || String($row.data("kode-ss") ?? "") === String(kodeSs ?? "")
                return isSameSumberDana && isSameSasaran
            }).first()
        }

        /**
         * Smooth scroll dan highlight sementara pada row target.
         * CUSTOMIZATION POINT: ubah warna/durasi highlight jika ingin efek navigasi berbeda.
         */
        const scrollToRekapSubkomponenRow = ( $target ) => {
            if (!$target?.length) return

            $target.get(0).scrollIntoView({ behavior: "smooth", block: "center" })
            $target.stop(true, true)
                .css("box-shadow", "inset 0 0 0 9999px rgba(255, 243, 205, 0.85)")
                .delay(1200)
                .animate({ opacity: 1 }, 200, function() {
                    $(this).css("box-shadow", "")
                })
        }

        /**
         * Delegated event listener agar click-to-scroll tetap aktif setelah tabel rekap sasaran dirender ulang.
         * Row sumber dana menuju row sumber dana yang sama; row sasaran menuju row sasaran pada sumber dana yang sama.
         */
        $(document).on("click", "#tabel-rekap-sasaran tbody tr.rekap-sasaran-sumberdana-row, #tabel-rekap-sasaran tbody tr.rekap-sasaran-sasaran-row", function() {
            const $row = $(this)
            const sumberDana = $row.data("sumberdana")
            const kodeSs = $row.hasClass("rekap-sasaran-sasaran-row") ? $row.data("kode-ss") : null
            const $target = findRekapSubkomponenTargetRow(sumberDana, kodeSs)

            scrollToRekapSubkomponenRow($target)
        })

        /**
         * AJAX request untuk memuat rekap sasaran berdasarkan shared filter unit kerja dan sumber dana.
         * Response route `revisi.sasaran.get.rekapSasaran` harus menyediakan `dataSemula`, `dataMenjadi`, dan `perKomponen`.
         * Function ini dipanggil oleh modul REKAPSUBKOMPONEN saat tombol Tampilkan/reload filter digunakan.
         */
        const muatRekapSasaran = ( idunitSasaran = [], kodeSdSasaran = [], kodeSasaran = kodeSasaranRekap ) => {
            return new Promise( ( resolve, reject ) => {
                $.ajax({
                    type: "GET", url: "{{ route('revisi.sasaran.get.rekapSasaran') }}",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        idunit: idunitSasaran,
                        kodeSd: kodeSdSasaran,
                        unitkerja: idunitSasaran,
                        sumberdana: kodeSdSasaran
                    },
                    success: ( res ) => {
                        const { dataSemula = [], dataMenjadi = [], perKomponen } = res.data || {}
                        if ( !hasRekapSasaranData(dataSemula) )
                            tata.warn("Perhatian", "Data sasaran semula tidak ditemukan.", { duration: 5000, animate: "slide" })
                        if ( !hasRekapSasaranData(dataMenjadi) )
                            tata.warn("Perhatian", "Data sasaran menjadi tidak ditemukan.", { duration: 5000, animate: "slide" })
                        
                        renderRekapSasaranTable(dataSemula, dataMenjadi, kodeSasaran, perKomponen)
                        resolve(res)
                    },
                    error: ( xhr ) => {
                        const message = xhr.responseJSON?.message
                        tata.error(" Error", message || "Terjadi kesalahan sistem", { duration: 5000, animate: "slide" })
                        reject(xhr)
                    }
                })
            })
        }
        window.revisi.methods.muatRekapSasaran = muatRekapSasaran
    })
</script>
