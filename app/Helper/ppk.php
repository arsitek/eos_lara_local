<?php
use Illuminate\Support\Facades\DB;
use App\Models\Komitmen;

    function getDataOperasionalPPKNull( $kd_unit_kerja, $kd_sumber_dana, $tahun ) {
        $data = [];
        $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
            tb_rabkegiatan.id_jenis_belanja,
            tb_rabkegiatan.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                tb_rabkegiatan.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabkegiatan.rpd,
            CASE
                WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabkegiatan.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabkegiatan.jenis_belanja
            END AS nama_coa,
            tb_rabkegiatan.id AS id_item_coa,
            CONCAT(
                tb_rabkegiatan.kebutuhan_kegiatan,
                CASE
                    WHEN (
                        tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
            tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
            CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
            tb_rabkegiatan.satuan_durasi AS satuan_durasi,
            CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
            tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
            CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
            CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabkegiatan.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
                tb_rekat.tahun = 'Definitif_$tahun'
                AND tb_rabkegiatan.id_mak IS NOT NULL
                AND (
                tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                    and
                tb_rabkegiatan.jenis_belanja is not null
                )
                AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                AND tb_rabkegiatan.jumlah_biaya != 0
                AND tb_rabkegiatan.jumlah_biaya <= 400000000
                AND (tb_unit_api.nama  like '%Fakultas%'
                or tb_unit_api.nama like '%Sekolah%'
                or tb_unit_api.idunit = '1040301'
                or tb_unit_api.idunit = '1040302'
                or tb_unit_api.idunit = '10501')
                AND tb_rekat.sd != '4100'
                AND tb_rekat.sd = '$kd_sumber_dana'
                AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                ),
            ppk AS (
            SELECT
                k.id,k.is_active, k.nip, k.nama_pejabat,
                ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                k.maksimal_pengeluaran, k.minimal_pengeluaran
            FROM tb_komitmen k
            LEFT JOIN tb_relasi_unit ru
                ON ru.id_komitmen = k.id
            LEFT JOIN tb_relasi_sumberdana rs
                ON rs.id_komitmen = k.id
            JOIN tb_sumberdana sd
            ON sd.id = rs.id_sumberdana
            WHERE k.is_active = 'true'
            and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
            rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
            rkt.kd_komponen, rkt.komponen,
            rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
            rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
            rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
            rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
            rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
            rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
            ppk.nip as nip_pejabat, ppk.nama_pejabat
            from data_rkat rkt
            left join ppk on ppk.kd_sumberdana = rkt.kd_sumber_dana
            and ppk.idunit = rkt.kd_unit_kerja
            WHERE (
                ppk.maksimal_pengeluaran = 400000000
                OR
                (
                ppk.maksimal_pengeluaran = 0 and ppk.minimal_pengeluaran = 0
                AND CASE
                WHEN NOT EXISTS (
                    SELECT 1
                    FROM ppk ppk_inner
                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk_inner.idunit = rkt.kd_unit_kerja
                    AND ppk_inner.maksimal_pengeluaran = 400000000
                )
        )) AND ppk.nama_pejabat is null");
        $data = array_merge($data, $fakultasLimit);
        $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
            tb_rabkegiatan.id_jenis_belanja,
            tb_rabkegiatan.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                tb_rabkegiatan.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabkegiatan.rpd as rpd,
            CASE
                    WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.coa
                    WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.coa
                    ELSE tb_rabkegiatan.id_jenis_belanja
                END AS kd_coa,
                CASE
                    WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.nama
                    WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.nama
                    ELSE tb_rabkegiatan.jenis_belanja
                END AS nama_coa,
            tb_rabkegiatan.id AS id_item_coa,
            CONCAT(
                tb_rabkegiatan.kebutuhan_kegiatan,
                CASE
                    WHEN (
                        tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                        tb_rabkegiatan.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
            tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
            CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
            tb_rabkegiatan.satuan_durasi AS satuan_durasi,
            CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
            tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
            CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
            CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabkegiatan.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
            left join tb_kodefikasi_jenisbelanja
                on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
            left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabkegiatan.id_mak IS NOT NULL
            AND (
            tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                and
            tb_rabkegiatan.jenis_belanja is not null
            )
            AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
            AND tb_rabkegiatan.jumlah_biaya != 0
            AND tb_rabkegiatan.jumlah_biaya > 400000000
            AND (tb_unit_api.nama  like '%Fakultas%'
            or tb_unit_api.nama like '%Sekolah%'
            or tb_unit_api.idunit = '1040301'
            or tb_unit_api.idunit = '1040302'
            or tb_unit_api.idunit = '10501')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
                SELECT
                    k.id,k.is_active, k.nip, k.nama_pejabat,
                    ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                    k.maksimal_pengeluaran, k.minimal_pengeluaran, rc.coa_parent
                FROM tb_komitmen k
                LEFT JOIN tb_relasi_unit ru
                    ON ru.id_komitmen = k.id
                LEFT JOIN tb_relasi_sumberdana rs
                    ON rs.id_komitmen = k.id
                left join tb_relasi_coa rc
                    on rc.id_komitmen = k.id
                JOIN tb_sumberdana sd
                ON sd.id = rs.id_sumberdana
                WHERE k.is_active = 'true'
                and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat
            FROM data_rkat rkt
            LEFT JOIN ppk ON
                ppk.kd_sumberdana = rkt.kd_sumber_dana
                and ppk.idunit = rkt.kd_unit_kerja
                WHERE (
                    ppk.maksimal_pengeluaran = 400000000
                    AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                    OR (
                        ppk.maksimal_pengeluaran = 0
                        AND ppk.minimal_pengeluaran = 0
                        AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                        AND CASE
                            WHEN NOT EXISTS (
                                    SELECT 1
                                    FROM ppk ppk_inner
                                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                        AND ppk_inner.idunit = rkt.kd_unit_kerja
                                        AND ppk_inner.maksimal_pengeluaran = 400000000
                                        AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                )
        )) AND ppk.nama_pejabat is null");
        $data = array_merge($data, $fakultasNonLimit);
        $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                        tb_rabkegiatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabkegiatan.rpd,
                    CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                    tb_rabkegiatan.id AS id_item_coa,
                    CONCAT(
                        tb_rabkegiatan.kebutuhan_kegiatan,
                        CASE
                            WHEN (
                                tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                    ) AS item_coa,
                    CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                    CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                    tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                    CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                    tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                    CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabkegiatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    left join tb_kodefikasi_jenisbelanja
                        on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                    left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabkegiatan.id_mak IS NOT NULL
                    AND (
                    tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                        and
                    tb_rabkegiatan.jenis_belanja is not null
                    )
                    AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya != 0
                    AND tb_rabkegiatan.jumlah_biaya <= 400000000
                    AND (tb_unit_api.nama not like '%Fakultas%'
                    AND tb_unit_api.nama not like '%Sekolah%'
                    AND tb_unit_api.idunit != '1040301'
                    AND tb_unit_api.idunit != '1040302'
                    AND tb_unit_api.idunit != '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    from data_rkat rkt
                    left JOIN ppk ON
                    ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        WHERE (
                            ppk.maksimal_pengeluaran = 0
                            AND ppk.minimal_pengeluaran = 0
                            AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 400000000
                                and ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                AND CASE
                                WHEN NOT EXISTS (
                                        SELECT 1
                                        FROM ppk ppk_inner
                                        WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                            AND ppk_inner.idunit = rkt.kd_unit_kerja
                                            AND ppk_inner.maksimal_pengeluaran = 0
                                            AND ppk_inner.minimal_pengeluaran = 0
                                            AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                    )
        )) AND ppk.nama_pejabat is null");
        $data = array_merge($data, $nonFakultasLimit);
        $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                        tb_rabkegiatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabkegiatan.rpd,
                    CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                    tb_rabkegiatan.id AS id_item_coa,
                    CONCAT(
                        tb_rabkegiatan.kebutuhan_kegiatan,
                        CASE
                            WHEN (
                                tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                    ) AS item_coa,
                    CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                    CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                    tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                    CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                    tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                    CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabkegiatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    left join tb_kodefikasi_jenisbelanja
                        on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                    left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabkegiatan.id_mak IS NOT NULL
                    AND (
                    tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                        and
                    tb_rabkegiatan.jenis_belanja is not null
                    )
                    AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya != 0
                    AND tb_rabkegiatan.jumlah_biaya > 400000000
                    AND (tb_unit_api.nama not like '%Fakultas%'
                        AND tb_unit_api.nama not like '%Sekolah%'
                        AND tb_unit_api.idunit != '1040301'
                        AND tb_unit_api.idunit != '1040302'
                        AND tb_unit_api.idunit != '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran, rc.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa rc
                            on rc.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    FROM data_rkat rkt
                    LEFT JOIN ppk ON
                        ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        WHERE (
                            ppk.maksimal_pengeluaran = 0
                            AND ppk.minimal_pengeluaran = 0
                            AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 400000000
                                and ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                AND
                                CASE
                                    WHEN NOT EXISTS (
                                            SELECT 1
                                            FROM ppk ppk_inner
                                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.maksimal_pengeluaran = 0
                                                AND ppk_inner.minimal_pengeluaran = 0
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                        )
        )) AND ppk.nama_pejabat IS NULL");
        $data = array_merge($data, $nonFakultasNonLimit);
        return $data;
    }
    function getDataPrasaranaPPKNull( $kd_unit_kerja, $kd_sumber_dana, $tahun ) {
        $data = [];
        $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS  (SELECT
                tb_rabperalatan.id_jenis_belanja,
                tb_rabperalatan.id_mak,
                CONCAT(
                    tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                    RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                    '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                ) AS mak,
                tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                tb_rekat.rincian_komponen AS subkomponen,
                tb_rekat.unit_kerja AS kd_unit_kerja,
                tb_unit_api.nama AS unit_kerja,
                tb_rekat.id AS id_rekat,
                tb_rekat.sub_judul AS keg,
                tb_rabperalatan.rpd,
                CASE
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.coa
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.coa
                    ELSE tb_rabperalatan.id_jenis_belanja
                END AS kd_coa,
                CASE
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.nama
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.nama
                    ELSE tb_rabperalatan.jenis_belanja
                END AS nama_coa,
                tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                    CASE
                        WHEN (
                            tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                            tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                            tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                            tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                            tb_rabperalatan.verifikasi_aset != 'Setuju'
                        ) THEN '*'
                        ELSE ''
                    END
                    ) AS item_coa,
                CASE
                    WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                    THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                    ELSE CONCAT('[]')
                END AS status_produk,
                CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                tb_rabperalatan.satuan,
                CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                tb_rabperalatan.is_deleted
                FROM tb_rekat
                INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                LEFT JOIN tb_kodefikasi_jenisbelanja
                    ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabperalatan.id_mak IS NOT NULL
                    AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                    AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya != 0
                    AND tb_rabperalatan.jumlah_biaya <= 400000000
                    AND (tb_unit_api.nama LIKE '%Fakultas%'
                        or tb_unit_api.nama LIKE '%Sekolah%'
                        or tb_unit_api.idunit = '1040301'
                        or tb_unit_api.idunit = '1040302'
                        or tb_unit_api.idunit = '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                    rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                    rkt.kd_komponen, rkt.komponen,
                    rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                    rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                    rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                    rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                    rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                    ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                    LEFT JOIN ppk ON
                    ppk.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk.idunit = rkt.kd_unit_kerja
                    and (
                        ppk.maksimal_pengeluaran = 400000000
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        OR
                        (
                        ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        AND
                        CASE
                            WHEN NOT EXISTS (
                                    SELECT 1
                                    FROM ppk ppk_inner
                                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                        AND ppk_inner.idunit = rkt.kd_unit_kerja
                                        AND ppk_inner.maksimal_pengeluaran = 400000000
                                        AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                )
                            THEN 1
                            ELSE 0
        END = 1 )) WHERE ppk.nama_pejabat is null");
        $data = array_merge($data, $fakultasLimit);
        $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                        CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                        FROM tb_rekat
                        INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                        INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                        INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                        LEFT JOIN tb_kodefikasi_jenisbelanja
                            ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                        LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                        WHERE
                            tb_rekat.tahun = 'Definitif_$tahun'
                            AND tb_rabperalatan.id_mak IS NOT NULL
                            AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                            AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                            AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                            AND tb_rabperalatan.jumlah_biaya != 0
                            AND tb_rabperalatan.jumlah_biaya > 400000000
                            AND (tb_unit_api.nama  like '%Fakultas%'
                            or tb_unit_api.nama like '%Sekolah%'
                            or tb_unit_api.idunit = '1040301'
                            or tb_unit_api.idunit = '1040302'
                            or tb_unit_api.idunit = '10501')
                            AND tb_rekat.sd != '4100'
                            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                            AND tb_rekat.sd = '$kd_sumber_dana'
                            ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT rkt.id_jenis_belanja,rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    FROM data_rkat rkt
                    LEFT JOIN ppk ON
                        ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        AND (
                            ppk.minimal_pengeluaran = 400000000
                            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                AND
                                CASE
                                    WHEN NOT EXISTS (
                                            SELECT 1
                                            FROM ppk ppk_inner
                                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.minimal_pengeluaran = 400000000
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                        )
                                    THEN 1
                                    ELSE 0
                                END = 1
        )) where ppk.nama_pejabat IS NULL");
        $data = array_merge($data, $fakultasNonLimit);
        $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                        CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    LEFT JOIN tb_kodefikasi_jenisbelanja
                        ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                    LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                        tb_rekat.tahun = 'Definitif_$tahun'
                        AND tb_rabperalatan.id_mak IS NOT NULL
                        AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                        AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                        AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                        AND tb_rabperalatan.jumlah_biaya != 0
                        AND tb_rabperalatan.jumlah_biaya <= 400000000
                        AND (tb_unit_api.nama not LIKE '%Fakultas%'
                            and tb_unit_api.nama not LIKE '%Sekolah%'
                            and tb_unit_api.idunit != '1040301'
                            and tb_unit_api.idunit != '1040302'
                            and tb_unit_api.idunit != '10501')
                        AND tb_rekat.sd != '4100'
                        AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                        AND tb_rekat.sd = '$kd_sumber_dana'
                        ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT rkt.id_jenis_belanja,rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                    left JOIN ppk ON
                    ppk.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk.idunit = rkt.kd_unit_kerja
                    and (
                        ppk.maksimal_pengeluaran = 0
                        AND ppk.minimal_pengeluaran = 0
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        OR
                        (
                        ppk.maksimal_pengeluaran = 400000000
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        AND
                        CASE
                            WHEN NOT EXISTS (
                                    SELECT 1
                                    FROM ppk ppk_inner
                                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                    AND ppk_inner.idunit = rkt.kd_unit_kerja
                                    and ppk_inner.maksimal_pengeluaran = 0
                                    AND ppk_inner.minimal_pengeluaran = 0
                                    AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                )
                            THEN 1
                            ELSE 0
                        END = 1
        )) WHERE ppk.nama_pejabat IS NULL");
        $data = array_merge($data, $nonFakultasLimit);
        $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS  (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                            CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    LEFT JOIN tb_kodefikasi_jenisbelanja
                        ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                    LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabperalatan.id_mak IS NOT NULL
                    AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                    AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya != 0
                    AND tb_rabperalatan.jumlah_biaya > 400000000
                    AND (tb_unit_api.nama not LIKE '%Fakultas%'
                    and tb_unit_api.nama not LIKE '%Sekolah%'
                    and tb_unit_api.idunit != '1040301'
                    and tb_unit_api.idunit != '1040302'
                    and tb_unit_api.idunit != '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                                    FROM tb_komitmen k
                                    LEFT JOIN tb_relasi_unit ru
                                        ON ru.id_komitmen = k.id
                                    LEFT JOIN tb_relasi_sumberdana rs
                                        ON rs.id_komitmen = k.id
                                    left join tb_relasi_coa c
                                        on c.id_komitmen = k.id
                                    JOIN tb_sumberdana sd
                                    ON sd.id = rs.id_sumberdana
                                    WHERE k.is_active = 'true'
                                    and k.jenis = 'PPK')
                    SELECT rkt.id_jenis_belanja,rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                                rkt.kd_komponen, rkt.komponen,
                                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                                rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                            LEFT JOIN ppk ON
                            ppk.kd_sumberdana = rkt.kd_sumber_dana
                            and ppk.idunit = rkt.kd_unit_kerja
                            AND (
                                ppk.minimal_pengeluaran = 400000000
                                    and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                OR (
                                    ppk.maksimal_pengeluaran = 0
                                        AND ppk.minimal_pengeluaran = 0
                                    and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                    AND
                                    CASE
                                        WHEN NOT EXISTS (
                                                SELECT 1
                                                FROM ppk ppk_inner
                                                WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.minimal_pengeluaran = 400000000
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                            )
                                        THEN 1
                                        ELSE 0
                                    END = 1
                                )
        ) where ppk.nama_pejabat IS NULL");
        $data = array_merge($data, $nonFakultasNonLimit);
        return $data;
    }
    function getDataSaranaPPKNull( $kd_unit_kerja, $kd_sumber_dana, $tahun ){
        $data = [];
        $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
            tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
            RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
            '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(  COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
            CASE
                WHEN (
                    tb_rabgedung.verifikasi_tim != 'Setuju' OR
                    tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                    tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                    tb_rabgedung.verifikasi_keu != 'Setuju' OR
                    tb_rabgedung.verifikasi_aset != 'Setuju'
                ) THEN '*'
                ELSE ''
            END ) AS item_coa,
            CONCAT(
            '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
            ']') AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
                CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
                tb_rabgedung.satuan,
                CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
                CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                tb_rabgedung.is_deleted
                FROM tb_rekat
                INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
                INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
                LEFT JOIN tb_kodefikasi_jenisbelanja
                    ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai != 0
                    AND tb_rabgedung.id_mak IS NOT NULL
                    AND (
                        tb_rabgedung.id_jenis_belanja IS NOT NULL
                        and
                        tb_rabgedung.jenis_belanja is not null
                    )
                    AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai <= 400000000
                    AND (tb_unit_api.nama  like '%Fakultas%'
                    or tb_unit_api.nama like '%Sekolah%'
                    or tb_unit_api.idunit = '1040301'
                    or tb_unit_api.idunit = '1040302')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
                SELECT
                    k.id,k.is_active, k.nip, k.nama_pejabat,
                    ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                    k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                FROM tb_komitmen k
                LEFT JOIN tb_relasi_unit ru
                    ON ru.id_komitmen = k.id
                LEFT JOIN tb_relasi_sumberdana rs
                    ON rs.id_komitmen = k.id
                left join tb_relasi_coa c
                    on c.id_komitmen = k.id
                JOIN tb_sumberdana sd
                ON sd.id = rs.id_sumberdana
                WHERE k.is_active = 'true'
                and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            AND (
                ppk.maksimal_pengeluaran = 400000000
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                OR
                (
                ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                AND ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                AND
                CASE
                    WHEN NOT EXISTS (
                            SELECT 1
                            FROM ppk ppk_inner
                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                            AND ppk_inner.idunit = rkt.kd_unit_kerja
                            AND ppk_inner.maksimal_pengeluaran = 400000000
                            AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                    )
                    THEN 1
                    ELSE 0
                END = 1
            )
        ) WHERE ppk.nama_pejabat IS null");
        $data = array_merge($data, $fakultasLimit);
        $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai > 400000000
            AND (tb_unit_api.nama  like '%Fakultas%'
                or tb_unit_api.nama like '%Sekolah%'
                or tb_unit_api.idunit = '1040301'
                or tb_unit_api.idunit = '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
            SELECT
                k.id,k.is_active, k.nip, k.nama_pejabat,
                ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
            FROM tb_komitmen k
            LEFT JOIN tb_relasi_unit ru
                ON ru.id_komitmen = k.id
            LEFT JOIN tb_relasi_sumberdana rs
                ON rs.id_komitmen = k.id
            left join tb_relasi_coa c
                on c.id_komitmen = k.id
            JOIN tb_sumberdana sd
            ON sd.id = rs.id_sumberdana
            WHERE k.is_active = 'true'
            and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
            rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
            rkt.kd_komponen, rkt.komponen,
            rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
            rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
            rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
            rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
            rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
            rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
            ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            AND (
            ppk.minimal_pengeluaran = 400000000
            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            OR
            (
            ppk.maksimal_pengeluaran = 0
                AND ppk.minimal_pengeluaran = 0
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            AND
            CASE
                WHEN NOT EXISTS (
                    SELECT 1
                    FROM ppk ppk_inner
                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk_inner.idunit = rkt.kd_unit_kerja
                    AND ppk_inner.minimal_pengeluaran = 400000000
                    AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                )
                THEN 1
                ELSE 0
            END = 1
        )) where ppk.nama_pejabat IS null");
        $data = array_merge($data, $fakultasNonLimit);
        $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai <= 400000000
            AND (tb_unit_api.nama not like '%Fakultas%'
                and tb_unit_api.nama not like '%Sekolah%'
                and tb_unit_api.idunit != '1040301'
                and tb_unit_api.idunit != '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
                SELECT
                    k.id,k.is_active, k.nip, k.nama_pejabat,
                    ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                    k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                FROM tb_komitmen k
                LEFT JOIN tb_relasi_unit ru
                    ON ru.id_komitmen = k.id
                LEFT JOIN tb_relasi_sumberdana rs
                    ON rs.id_komitmen = k.id
                left join tb_relasi_coa c
                    on c.id_komitmen = k.id
                JOIN tb_sumberdana sd
                ON sd.id = rs.id_sumberdana
                WHERE k.is_active = 'true'
                and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            AND (
            (ppk.maksimal_pengeluaran = 400000000 AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6))
            OR
            (
                ppk.maksimal_pengeluaran = 0
                AND ppk.minimal_pengeluaran = 0
                AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                AND
                CASE
                    WHEN NOT EXISTS (
                            SELECT 1
                            FROM ppk ppk_inner
                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                AND ppk_inner.maksimal_pengeluaran = 400000000
                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                        )
                    THEN 1
                    ELSE 0
                END = 1
        )) WHERE ppk.nama_pejabat IS null");
        $data = array_merge($data, $nonFakultasLimit);
        $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_2024'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai > 400000000
            AND (tb_unit_api.nama not like '%Fakultas%'
                and tb_unit_api.nama not like '%Sekolah%'
                and tb_unit_api.idunit != '1040301'
                and tb_unit_api.idunit != '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
            SELECT
                k.id,k.is_active, k.nip, k.nama_pejabat,
                ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
            FROM tb_komitmen k
            LEFT JOIN tb_relasi_unit ru
                ON ru.id_komitmen = k.id
            LEFT JOIN tb_relasi_sumberdana rs
                ON rs.id_komitmen = k.id
            left join tb_relasi_coa c
                on c.id_komitmen = k.id
            JOIN tb_sumberdana sd
            ON sd.id = rs.id_sumberdana
            WHERE k.is_active = 'true'
            and k.jenis = 'PPK'
            )
            SELECT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            AND (
            ppk.minimal_pengeluaran = 400000000
            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            OR
            (
            ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            AND
            CASE
            WHEN NOT EXISTS (
                    SELECT 1
                    FROM ppk ppk_inner
                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                        AND ppk_inner.idunit = rkt.kd_unit_kerja
                        AND ppk_inner.minimal_pengeluaran = 400000000
                        AND ppk_inner.coa_parent = SUBSTRING(rkt.kd_coa, 1, 6)
                )
            THEN 1
            ELSE 0
        END = 1 )) where ppk.nama_pejabat IS null");
        $data = array_merge($data, $nonFakultasNonLimit);
        return $data;
    }
    function getDataOperasionalPPK( $kd_unit_kerja, $kd_sumber_dana, $tahun, $null = false ) {
        $data = [];
                $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                        tb_rabkegiatan.id_jenis_belanja,
                        tb_rabkegiatan.id_mak,
                        CONCAT(
                            tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                            RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                            '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                            tb_rabkegiatan.id
                        ) AS mak,
                        tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                        tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                        MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                        MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                        RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                        MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                        RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                        tb_rekat.rincian_komponen AS subkomponen,
                        tb_rekat.unit_kerja AS kd_unit_kerja,
                        tb_unit_api.nama AS unit_kerja,
                        tb_rekat.id AS id_rekat,
                        tb_rekat.sub_judul AS keg,
                        tb_rabkegiatan.rpd as rpd,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                        tb_rabkegiatan.id AS id_item_coa,
                        CONCAT(
                            tb_rabkegiatan.kebutuhan_kegiatan,
                            CASE
                                WHEN (
                                    tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                    tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                    tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                    tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                    tb_rabkegiatan.verifikasi_aset != 'Setuju'
                                ) THEN '*'
                                ELSE ''
                            END
                        ) AS item_coa,
                        CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                        tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                        CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                        tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                        CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                        tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                        CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                        CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                        CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                        tb_rabkegiatan.is_deleted
                        FROM tb_rekat
                        INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                        INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                        INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                        LEFT JOIN tb_kodefikasi_jenisbelanja
                            ON tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                        LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                        WHERE
                            tb_rekat.tahun = 'Definitif_$tahun'
                            AND tb_rabkegiatan.id_mak IS NOT NULL
                            AND (
                            tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                                and
                            tb_rabkegiatan.jenis_belanja is not null
                            )
                            AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                            AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                            AND tb_rabkegiatan.jumlah_biaya != 0
                            AND tb_rabkegiatan.jumlah_biaya <= 400000000
                            AND (tb_unit_api.nama  like '%Fakultas%'
                            or tb_unit_api.nama like '%Sekolah%'
                            or tb_unit_api.idunit = '1040301'
                            or tb_unit_api.idunit = '1040302'
                            or tb_unit_api.idunit = '10501')
                            AND tb_rekat.sd != '4100'
                            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                            AND tb_rekat.sd = '$kd_sumber_dana'
                            ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    from data_rkat rkt
                    left join ppk on ppk.kd_sumberdana = rkt.kd_sumber_dana
                    and ppk.idunit = rkt.kd_unit_kerja
                    AND (
                        ppk.maksimal_pengeluaran = 400000000
                        OR
                        (
                        ppk.maksimal_pengeluaran = 0 and ppk.minimal_pengeluaran = 0
                        AND NOT EXISTS (
                            SELECT 1
                            FROM ppk ppk_inner
                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                            AND ppk_inner.idunit = rkt.kd_unit_kerja
                            AND ppk_inner.maksimal_pengeluaran = 400000000
                        )
                ))");
                $data = array_merge($data, $fakultasLimit);
                $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                        tb_rabkegiatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabkegiatan.rpd as rpd,
                    CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                    tb_rabkegiatan.id AS id_item_coa,
                    CONCAT(
                        tb_rabkegiatan.kebutuhan_kegiatan,
                        CASE
                            WHEN (
                                tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                    ) AS item_coa,
                    CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                    CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                    tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                    CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                    tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                    CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabkegiatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    left join tb_kodefikasi_jenisbelanja
                        on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                    left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabkegiatan.id_mak IS NOT NULL
                    AND (
                    tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                        and
                    tb_rabkegiatan.jenis_belanja is not null
                    )
                    AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya != 0
                    AND tb_rabkegiatan.jumlah_biaya > 400000000
                    AND (tb_unit_api.nama  like '%Fakultas%'
                    or tb_unit_api.nama like '%Sekolah%'
                    or tb_unit_api.idunit = '1040301'
                    or tb_unit_api.idunit = '1040302'
                    or tb_unit_api.idunit = '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran, rc.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa rc
                            on rc.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    FROM data_rkat rkt
                    LEFT JOIN ppk ON
                        ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        AND (
                            ppk.maksimal_pengeluaran = 400000000
                            AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 0
                                AND ppk.minimal_pengeluaran = 0
                                AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                AND CASE
                                    WHEN NOT EXISTS (
                                            SELECT 1
                                            FROM ppk ppk_inner
                                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.maksimal_pengeluaran = 400000000
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                        )
                                    THEN 1
                                    ELSE 0
                                END = 1
                ))");
                $data = array_merge($data, $fakultasNonLimit);
                $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                        tb_rabkegiatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabkegiatan.rpd,
                    CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                    tb_rabkegiatan.id AS id_item_coa,
                    CONCAT(
                        tb_rabkegiatan.kebutuhan_kegiatan,
                        CASE
                            WHEN (
                                tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                    ) AS item_coa,
                    CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                    CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                    tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                    CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                    tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                    CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabkegiatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    left join tb_kodefikasi_jenisbelanja
                        on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                    left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabkegiatan.id_mak IS NOT NULL
                    AND (
                    tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                        and
                    tb_rabkegiatan.jenis_belanja is not null
                    )
                    AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya != 0
                    AND tb_rabkegiatan.jumlah_biaya <= 400000000
                    AND (tb_unit_api.nama not like '%Fakultas%'
                    AND tb_unit_api.nama not like '%Sekolah%'
                    AND tb_unit_api.idunit != '1040301'
                    AND tb_unit_api.idunit != '1040302'
                    AND tb_unit_api.idunit != '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    from data_rkat rkt
                    left JOIN ppk ON
                        ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        AND (
                            ppk.maksimal_pengeluaran = 0
                            AND ppk.minimal_pengeluaran = 0
                            AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 400000000
                                and ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                AND CASE
                                WHEN NOT EXISTS (
                                        SELECT 1
                                        FROM ppk ppk_inner
                                        WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                            AND ppk_inner.idunit = rkt.kd_unit_kerja
                                            AND ppk_inner.maksimal_pengeluaran = 0
                                            AND ppk_inner.minimal_pengeluaran = 0
                                            AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                    )
                                THEN 1
                                ELSE 0
                            END = 1
                ))");
                $data = array_merge($data, $nonFakultasLimit);
                $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabkegiatan.id_jenis_belanja, '.',
                        tb_rabkegiatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabkegiatan.rpd,
                    CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.coa
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.coa
                            ELSE tb_rabkegiatan.id_jenis_belanja
                        END AS kd_coa,
                        CASE
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5159%'
                                THEN apicoa.nama
                            WHEN tb_rabkegiatan.id_jenis_belanja like '%5259%'
                                THEN apicoa.nama
                            ELSE tb_rabkegiatan.jenis_belanja
                        END AS nama_coa,
                    tb_rabkegiatan.id AS id_item_coa,
                    CONCAT(
                        tb_rabkegiatan.kebutuhan_kegiatan,
                        CASE
                            WHEN (
                                tb_rabkegiatan.verifikasi_tim != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_keu != 'Setuju' OR
                                tb_rabkegiatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                    ) AS item_coa,
                    CAST(tb_rabkegiatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabkegiatan.satuan_kuantitas AS satuan_kuantitas,
                    CAST(tb_rabkegiatan.durasi AS SIGNED) AS durasi,
                    tb_rabkegiatan.satuan_durasi AS satuan_durasi,
                    CAST(tb_rabkegiatan.kegiatan AS SIGNED) AS volume,
                    tb_rabkegiatan.satuan_kegiatan AS satuan_volume,
                    CAST(tb_rabkegiatan.biaya_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabkegiatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabkegiatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    left join tb_kodefikasi_jenisbelanja
                        on tb_kodefikasi_jenisbelanja.akun = tb_rabkegiatan.id_jenis_belanja
                    left join tb_api_coa apicoa on tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabkegiatan.id_mak IS NOT NULL
                    AND (
                    tb_rabkegiatan.id_jenis_belanja IS NOT NULL
                        and
                    tb_rabkegiatan.jenis_belanja is not null
                    )
                    AND tb_rabkegiatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya IS NOT NULL
                    AND tb_rabkegiatan.jumlah_biaya != 0
                    AND tb_rabkegiatan.jumlah_biaya > 400000000
                    AND (tb_unit_api.nama not like '%Fakultas%'
                        AND tb_unit_api.nama not like '%Sekolah%'
                        AND tb_unit_api.idunit != '1040301'
                        AND tb_unit_api.idunit != '1040302'
                        AND tb_unit_api.idunit != '10501')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran, rc.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa rc
                            on rc.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.kuantitas, rkt.satuan_kuantitas, rkt.durasi, rkt.satuan_durasi,
                        rkt.volume, rkt.satuan_volume, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    FROM data_rkat rkt
                    LEFT JOIN ppk ON ppk.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk.idunit = rkt.kd_unit_kerja
                    AND (
					    ( ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
						AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6))
					    OR
					    ( ppk.maksimal_pengeluaran = 400000000
					    AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
					    AND NOT EXISTS (
					        SELECT 1
					        FROM ppk ppk_inner
					        WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
					            AND ppk_inner.idunit = rkt.kd_unit_kerja
			                    AND ppk_inner.maksimal_pengeluaran = 0
					            AND ppk_inner.minimal_pengeluaran = 0
					            AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
					        )
				) )");
                $data = array_merge($data, $nonFakultasNonLimit);
                $withBpp = [];
                foreach ( $data as $item ) {
                    $bpp = Komitmen::whereHas('unitKerja', function ($query) use ($item) {
                        $query->where("idunit", "=", $item->kd_unit_kerja);
                    })->whereHas('sumberDana.sumberdana', function ($query) use ($item) {
                        $query->where("kd_sumberdana", "=", $item->kd_sumber_dana);
                    })->where("jenis", "BPP")->select("nip", "nama_pejabat")->get()->toArray();
                    !empty($bpp) ?
                        $withBpp[] = array_merge((array)$item, [
                            "nip_bpp" => $bpp['0']['nip'],
                            "nama_bpp" => $bpp['0']['nama_pejabat']]) :
                        $withBpp[] = array_merge((array)$item, [
                            "nip_bpp" => '',
                            "nama_bpp" => '']);
                }
        return $withBpp;
    }
    function getDataSaranaPPK( $kd_unit_kerja, $kd_sumber_dana, $tahun, $null = false ) {
        $data = [];
        $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS  (SELECT
                tb_rabperalatan.id_jenis_belanja,
                tb_rabperalatan.id_mak,
                CONCAT(
                    tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                    RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                    '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                ) AS mak,
                tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                tb_rekat.rincian_komponen AS subkomponen,
                tb_rekat.unit_kerja AS kd_unit_kerja,
                tb_unit_api.nama AS unit_kerja,
                tb_rekat.id AS id_rekat,
                tb_rekat.sub_judul AS keg,
                tb_rabperalatan.rpd,
                CASE
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.coa
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.coa
                    ELSE tb_rabperalatan.id_jenis_belanja
                END AS kd_coa,
                CASE
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                        THEN apicoa.nama
                    WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                        THEN apicoa.nama
                    ELSE tb_rabperalatan.jenis_belanja
                END AS nama_coa,
                tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                    CASE
                        WHEN (
                            tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                            tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                            tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                            tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                            tb_rabperalatan.verifikasi_aset != 'Setuju'
                        ) THEN '*'
                        ELSE ''
                    END
                    ) AS item_coa,
                CASE
                    WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                    THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                    ELSE CONCAT('[]')
                END AS status_produk,
                CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                tb_rabperalatan.satuan,
                CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                tb_rabperalatan.is_deleted
                FROM tb_rekat
                INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                LEFT JOIN tb_kodefikasi_jenisbelanja
                    ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabperalatan.id_mak IS NOT NULL
                    AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                    AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya != 0
                    AND tb_rabperalatan.jumlah_biaya <= 400000000
                    AND (tb_unit_api.nama LIKE '%Fakultas%'
                        or tb_unit_api.nama LIKE '%Sekolah%'
                        or tb_unit_api.idunit = '1040301'
                        or tb_unit_api.idunit = '1040302')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                    rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                    rkt.kd_komponen, rkt.komponen,
                    rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                    rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                    rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                    rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                    rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                    ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                    LEFT JOIN ppk ON
                    ppk.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk.idunit = rkt.kd_unit_kerja
                    WHERE (
                        ppk.maksimal_pengeluaran = 400000000
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        OR
                        (
                        ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        AND NOT EXISTS (
                                    SELECT 1
                                    FROM ppk ppk_inner
                                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                        AND ppk_inner.idunit = rkt.kd_unit_kerja
                                        AND ppk_inner.maksimal_pengeluaran = 400000000
                                        AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                )
        ))");
        $data = array_merge($data, $fakultasLimit);
        $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                        CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                        FROM tb_rekat
                        INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                        INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                        INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                        LEFT JOIN tb_kodefikasi_jenisbelanja
                            ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                        LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                        WHERE
                            tb_rekat.tahun = 'Definitif_$tahun'
                            AND tb_rabperalatan.id_mak IS NOT NULL
                            AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                            AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                            AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                            AND tb_rabperalatan.jumlah_biaya != 0
                            AND tb_rabperalatan.jumlah_biaya > 400000000
                            AND (tb_unit_api.nama  like '%Fakultas%'
                            or tb_unit_api.nama like '%Sekolah%'
                            or tb_unit_api.idunit = '1040301'
                            or tb_unit_api.idunit = '1040302')
                            AND tb_rekat.sd != '4100'
                            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                            AND tb_rekat.sd = '$kd_sumber_dana'
                            ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat
                    FROM data_rkat rkt
                    LEFT JOIN ppk ON
                        ppk.kd_sumberdana = rkt.kd_sumber_dana
                        and ppk.idunit = rkt.kd_unit_kerja
                        WHERE (
                            ppk.minimal_pengeluaran = 400000000
                            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                            OR (
                                ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                AND NOT EXISTS (
                                            SELECT 1
                                            FROM ppk ppk_inner
                                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.minimal_pengeluaran = 400000000
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                        )
        ))");
        $data = array_merge($data, $fakultasNonLimit);
        $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                        CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    LEFT JOIN tb_kodefikasi_jenisbelanja
                        ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                    LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                        tb_rekat.tahun = 'Definitif_$tahun'
                        AND tb_rabperalatan.id_mak IS NOT NULL
                        AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                        AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                        AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                        AND tb_rabperalatan.jumlah_biaya != 0
                        AND tb_rabperalatan.jumlah_biaya <= 400000000
                        AND (tb_unit_api.nama not LIKE '%Fakultas%'
                            and tb_unit_api.nama not LIKE '%Sekolah%'
                            and tb_unit_api.idunit != '1040301'
                            and tb_unit_api.idunit != '1040302')
                        AND tb_rekat.sd != '4100'
                        AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                        AND tb_rekat.sd = '$kd_sumber_dana'
                        ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                        FROM tb_komitmen k
                        LEFT JOIN tb_relasi_unit ru
                            ON ru.id_komitmen = k.id
                        LEFT JOIN tb_relasi_sumberdana rs
                            ON rs.id_komitmen = k.id
                        left join tb_relasi_coa c
                            on c.id_komitmen = k.id
                        JOIN tb_sumberdana sd
                        ON sd.id = rs.id_sumberdana
                        WHERE k.is_active = 'true'
                        and k.jenis = 'PPK'
                    )
                    SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                        rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                        rkt.kd_komponen, rkt.komponen,
                        rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                        rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                        rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                        rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                        rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                        ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                    left JOIN ppk ON
                    ppk.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk.idunit = rkt.kd_unit_kerja
                    WHERE (
                        ppk.maksimal_pengeluaran = 0
                        AND ppk.minimal_pengeluaran = 0
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        OR
                        (
                        ppk.maksimal_pengeluaran = 400000000
                        and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                        AND NOT EXISTS (
                                    SELECT 1
                                    FROM ppk ppk_inner
                                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                    AND ppk_inner.idunit = rkt.kd_unit_kerja
                                    and ppk_inner.maksimal_pengeluaran = 0
                                    AND ppk_inner.minimal_pengeluaran = 0
                                    AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                )
        ))");
        $data = array_merge($data, $nonFakultasLimit);
        $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS  (SELECT
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.id_mak,
                    CONCAT(
                        tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                        RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                        '.', tb_rekat.id, '.', tb_rabperalatan.id_jenis_belanja, '.', tb_rabperalatan.id
                    ) AS mak,
                    tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
                    tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
                    MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
                    RIGHT(tb_rekat.kd_keg,1) AS kd_komponen,
                    MID(tb_rekat.rincian_kegiatan,12) AS komponen,
                    RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
                    tb_rekat.rincian_komponen AS subkomponen,
                    tb_rekat.unit_kerja AS kd_unit_kerja,
                    tb_unit_api.nama AS unit_kerja,
                    tb_rekat.id AS id_rekat,
                    tb_rekat.sub_judul AS keg,
                    tb_rabperalatan.rpd,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.coa
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.coa
                        ELSE tb_rabperalatan.id_jenis_belanja
                    END AS kd_coa,
                    CASE
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5159%'
                            THEN apicoa.nama
                        WHEN tb_rabperalatan.id_jenis_belanja like '%5259%'
                            THEN apicoa.nama
                        ELSE tb_rabperalatan.jenis_belanja
                    END AS nama_coa,
                    tb_rabperalatan.id AS id_item_coa,
                    CONCAT(
                        COALESCE(tb_rabperalatan.kebutuhan_kegiatan, ''), ', ',
                        COALESCE(tb_rabperalatan.merk, ''), ', ',
                        COALESCE(tb_rabperalatan.type, ''),
                            CASE
                            WHEN (
                                tb_rabperalatan.verifikasi_tim != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_unit != 'Setuju' OR
                                tb_rabperalatan.verifikasi_pimpinan_univ != 'Setuju' OR
                                tb_rabperalatan.verifikasi_keu != 'Setuju' OR
                                tb_rabperalatan.verifikasi_aset != 'Setuju'
                            ) THEN '*'
                            ELSE ''
                        END
                        ) AS item_coa,
                    CASE
                        WHEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog) IS NOT NULL
                        THEN CONCAT(tb_rabperalatan.status_produk, ', ', tb_rabperalatan.eCatalog)
                        ELSE CONCAT('[]')
                    END AS status_produk,
                    CAST(tb_rabperalatan.kuantitas AS SIGNED) AS kuantitas,
                    tb_rabperalatan.satuan,
                    CAST(tb_rabperalatan.harga_satuan AS SIGNED) AS biaya_satuan_kegiatan,
                    CAST(tb_rabperalatan.jumlah_biaya AS SIGNED) AS total_biaya_kegiatan,
                    CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                    tb_rabperalatan.is_deleted
                    FROM tb_rekat
                    INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                    INNER JOIN tb_sumberdana ON tb_sumberdana.kd_sumberdana = tb_rekat.sd
                    LEFT JOIN tb_kodefikasi_jenisbelanja
                        ON tb_kodefikasi_jenisbelanja.akun = tb_rabperalatan.id_jenis_belanja
                    LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                    WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabperalatan.id_mak IS NOT NULL
                    AND tb_rabperalatan.id_jenis_belanja IS NOT NULL
                    AND tb_rabperalatan.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya IS NOT NULL
                    AND tb_rabperalatan.jumlah_biaya != 0
                    AND tb_rabperalatan.jumlah_biaya > 400000000
                    AND (tb_unit_api.nama not LIKE '%Fakultas%'
                    and tb_unit_api.nama not LIKE '%Sekolah%'
                    and tb_unit_api.idunit != '1040301'
                    and tb_unit_api.idunit != '1040302')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
                    ),
                    ppk AS (
                        SELECT
                            k.id,k.is_active, k.nip, k.nama_pejabat,
                            ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                            k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                                    FROM tb_komitmen k
                                    LEFT JOIN tb_relasi_unit ru
                                        ON ru.id_komitmen = k.id
                                    LEFT JOIN tb_relasi_sumberdana rs
                                        ON rs.id_komitmen = k.id
                                    left join tb_relasi_coa c
                                        on c.id_komitmen = k.id
                                    JOIN tb_sumberdana sd
                                    ON sd.id = rs.id_sumberdana
                                    WHERE k.is_active = 'true'
                                    and k.jenis = 'PPK')
                SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                                rkt.kd_komponen, rkt.komponen,
                                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                                rkt.status_produk, rkt.kuantitas, rkt.satuan, rkt.biaya_satuan_kegiatan,
                                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
                            LEFT JOIN ppk ON
                            ppk.kd_sumberdana = rkt.kd_sumber_dana
                            and ppk.idunit = rkt.kd_unit_kerja
                            WHERE (
                                ppk.minimal_pengeluaran = 400000000
                                    and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                OR (
                                    ppk.maksimal_pengeluaran = 0
                                        AND ppk.minimal_pengeluaran = 0
                                    and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                                    AND NOT EXISTS (
                                                SELECT 1
                                                FROM ppk ppk_inner
                                                WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                                AND ppk_inner.minimal_pengeluaran = 400000000
                                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                                            )
        ))");
        $data = array_merge($data, $nonFakultasNonLimit);
        $withBpp = [];
        foreach ( $data as $item ) {
            $bpp = Komitmen::whereHas('unitKerja', function ($query) use ($item) {
                $query->where("idunit", "=", $item->kd_unit_kerja);
            })->whereHas('sumberDana.sumberdana', function ($query) use ($item) {
                $query->where("kd_sumberdana", "=", $item->kd_sumber_dana);
            })->where("jenis", "BPP")->select("nip", "nama_pejabat")->get()->toArray();
            !empty($bpp) ?
                $withBpp[] = array_merge((array)$item, [
                    "nip_bpp" => $bpp['0']['nip'],
                    "nama_bpp" => $bpp['0']['nama_pejabat']]) :
                $withBpp[] = array_merge((array)$item, [
                    "nip_bpp" => '',
                    "nama_bpp" => '']);
        }
        return $withBpp;
    }
    function getDataPrasaranaPPK( $kd_unit_kerja, $kd_sumber_dana, $tahun, $null = false ){
        $data = [];
        $fakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
            tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
            RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
            '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(  COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
            CASE
                WHEN (
                    tb_rabgedung.verifikasi_tim != 'Setuju' OR
                    tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                    tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                    tb_rabgedung.verifikasi_keu != 'Setuju' OR
                    tb_rabgedung.verifikasi_aset != 'Setuju'
                ) THEN '*'
                ELSE ''
            END ) AS item_coa,
            CONCAT(
            '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
            ']') AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
                CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
                tb_rabgedung.satuan,
                CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
                CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
                tb_rabgedung.is_deleted
                FROM tb_rekat
                INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
                INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
                INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
                LEFT JOIN tb_kodefikasi_jenisbelanja
                    ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
                WHERE
                    tb_rekat.tahun = 'Definitif_$tahun'
                    AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai != 0
                    AND tb_rabgedung.id_mak IS NOT NULL
                    AND (
                        tb_rabgedung.id_jenis_belanja IS NOT NULL
                        and
                        tb_rabgedung.jenis_belanja is not null
                    )
                    AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
                    AND tb_rabgedung.jumlah_nilai <= 400000000
                    AND (tb_unit_api.nama  like '%Fakultas%'
                    or tb_unit_api.nama like '%Sekolah%'
                    or tb_unit_api.idunit = '1040301'
                    or tb_unit_api.idunit = '1040302')
                    AND tb_rekat.sd != '4100'
                    AND tb_rekat.unit_kerja = '$kd_unit_kerja'
                    AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
                SELECT
                    k.id,k.is_active, k.nip, k.nama_pejabat,
                    ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                    k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                FROM tb_komitmen k
                LEFT JOIN tb_relasi_unit ru
                    ON ru.id_komitmen = k.id
                LEFT JOIN tb_relasi_sumberdana rs
                    ON rs.id_komitmen = k.id
                left join tb_relasi_coa c
                    on c.id_komitmen = k.id
                JOIN tb_sumberdana sd
                ON sd.id = rs.id_sumberdana
                WHERE k.is_active = 'true'
                and k.jenis = 'PPK'
            )
            SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            WHERE (
                ppk.maksimal_pengeluaran = 400000000
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                OR
                (
                ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                AND ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
                AND NOT EXISTS (
                            SELECT 1
                            FROM ppk ppk_inner
                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                            AND ppk_inner.idunit = rkt.kd_unit_kerja
                            AND ppk_inner.maksimal_pengeluaran = 400000000
                            AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                    )
        )) ");
        $data = array_merge($data, $fakultasLimit);
        $fakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai > 400000000
            AND (tb_unit_api.nama  like '%Fakultas%'
                or tb_unit_api.nama like '%Sekolah%'
                or tb_unit_api.idunit = '1040301'
                or tb_unit_api.idunit = '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
            SELECT
                k.id,k.is_active, k.nip, k.nama_pejabat,
                ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
            FROM tb_komitmen k
            LEFT JOIN tb_relasi_unit ru
                ON ru.id_komitmen = k.id
            LEFT JOIN tb_relasi_sumberdana rs
                ON rs.id_komitmen = k.id
            left join tb_relasi_coa c
                on c.id_komitmen = k.id
            JOIN tb_sumberdana sd
            ON sd.id = rs.id_sumberdana
            WHERE k.is_active = 'true'
            and k.jenis = 'PPK'
            )
            SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
            rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
            rkt.kd_komponen, rkt.komponen,
            rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
            rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
            rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
            rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
            rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
            rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
            ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            WHERE (
            ppk.minimal_pengeluaran = 400000000
            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            OR
            (
            ppk.maksimal_pengeluaran = 0
                AND ppk.minimal_pengeluaran = 0
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            AND NOT EXISTS (
                    SELECT 1
                    FROM ppk ppk_inner
                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                    AND ppk_inner.idunit = rkt.kd_unit_kerja
                    AND ppk_inner.minimal_pengeluaran = 400000000
                    AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                )
        )) ");
        $data = array_merge($data, $fakultasNonLimit);
        $nonFakultasLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai <= 400000000
            AND (tb_unit_api.nama not like '%Fakultas%'
                and tb_unit_api.nama not like '%Sekolah%'
                and tb_unit_api.idunit != '1040301'
                and tb_unit_api.idunit != '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
                SELECT
                    k.id,k.is_active, k.nip, k.nama_pejabat,
                    ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                    k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
                FROM tb_komitmen k
                LEFT JOIN tb_relasi_unit ru
                    ON ru.id_komitmen = k.id
                LEFT JOIN tb_relasi_sumberdana rs
                    ON rs.id_komitmen = k.id
                left join tb_relasi_coa c
                    on c.id_komitmen = k.id
                JOIN tb_sumberdana sd
                ON sd.id = rs.id_sumberdana
                WHERE k.is_active = 'true'
                and k.jenis = 'PPK'
            )
            SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            WHERE (
            (ppk.maksimal_pengeluaran = 400000000 AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6))
            OR
            (
                ppk.maksimal_pengeluaran = 0
                AND ppk.minimal_pengeluaran = 0
                AND ppk.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                AND NOT EXISTS (
                            SELECT 1
                            FROM ppk ppk_inner
                            WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                                AND ppk_inner.idunit = rkt.kd_unit_kerja
                                AND ppk_inner.maksimal_pengeluaran = 400000000
                                AND ppk_inner.coa_parent = SUBSTRING(rkt.id_jenis_belanja, 1, 6)
                        )
        )) ");
        $data = array_merge($data, $nonFakultasLimit);
        $nonFakultasNonLimit = DB::connection('sirekat')->select("WITH data_rkat AS ( SELECT
            tb_rabgedung.id_jenis_belanja,
            tb_rabgedung.id_mak,
            CONCAT(
                tb_rekat.sd, '.', tb_rekat.kd_kro, MID(tb_rekat.kd_keg, 3), '.',
                RIGHT(tb_rekat.kd_rk, 2),'.', tb_rekat.unit_kerja,
                '.', tb_rekat.id, '.', tb_rabgedung.id_jenis_belanja, '.', tb_rabgedung.id
            ) AS mak,
            tb_rekat.sd AS kd_sumber_dana, tb_sumberdana.sumberdana AS sumber_dana,
            tb_rekat.kd_kro, tb_rekat.sasaran_program as kro,
            MID(tb_rekat.indikator_kinerja_kegiatan,6,3) AS kd_ro,
            MID(tb_rekat.indikator_kinerja_kegiatan,11) AS ro,
            RIGHT(tb_rekat.kd_keg,1)  AS kd_komponen,
            MID(tb_rekat.rincian_kegiatan,12) AS komponen,
            RIGHT(tb_rekat.kd_rk ,2) AS kd_subkomponen,
            tb_rekat.rincian_komponen AS subkomponen,
            tb_rekat.unit_kerja AS kd_unit_kerja,
            tb_unit_api.nama AS unit_kerja,
            tb_rekat.id AS id_rekat,
            tb_rekat.sub_judul AS keg,
            tb_rabgedung.rpd,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.coa
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.coa
                ELSE tb_rabgedung.id_jenis_belanja
            END AS kd_coa,
            CASE
                WHEN tb_rabgedung.id_jenis_belanja like '%5159%'
                    THEN apicoa.nama
                WHEN tb_rabgedung.id_jenis_belanja like '%5259%'
                    THEN apicoa.nama
                ELSE tb_rabgedung.jenis_belanja
            END AS nama_coa,
            tb_rabgedung.id AS id_item_coa,
            CONCAT(
                COALESCE(tb_rabgedung.kebutuhan_kegiatan, ''),
                CASE
                    WHEN (
                        tb_rabgedung.verifikasi_tim != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_unit != 'Setuju' OR
                        tb_rabgedung.verifikasi_pimpinan_univ != 'Setuju' OR
                        tb_rabgedung.verifikasi_keu != 'Setuju' OR
                        tb_rabgedung.verifikasi_aset != 'Setuju'
                    ) THEN '*'
                    ELSE ''
                END
            ) AS item_coa,
            CONCAT(
                '[',
                CASE WHEN tb_rabgedung.DED_AWAL IS NOT NULL THEN tb_rabgedung.DED_AWAL ELSE '' END,
                CASE WHEN tb_rabgedung.DED_REVIEW IS NOT NULL THEN tb_rabgedung.DED_REVIEW ELSE ',' END,
                ']'
            ) AS DED,
            CASE
                WHEN CAST(tb_rabgedung.nilai_perencanaan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_perencanaan AS SIGNED)
            END AS nilai_perencanaan,
            CASE
                WHEN CAST(tb_rabgedung.nilai_struktur AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_struktur AS SIGNED)
            END AS nilai_struktur,
            CASE
                WHEN CAST(tb_rabgedung.nilai_me AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_me AS SIGNED)
            END AS nilai_me,
            CASE
                WHEN CAST(tb_rabgedung.nilai_landscape AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_landscape AS SIGNED)
            END AS nilai_landscape,
            CASE
                WHEN CAST(tb_rabgedung.nilai_pengawasan AS SIGNED) IS NULL THEN 0
                ELSE CAST(tb_rabgedung.nilai_pengawasan AS SIGNED)
            END AS nilai_pengawasan,
            CAST(tb_rabgedung.kuantitas AS SIGNED) AS kuantitas,
            tb_rabgedung.satuan,
            CAST(tb_rabgedung.jumlah_nilai AS SIGNED) AS total_biaya_kegiatan,
            CAST(MID(tb_rekat.tahun, 11) AS SIGNED) AS tahun,
            tb_rabgedung.is_deleted
            FROM tb_rekat
            INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
            INNER JOIN tb_unit_api ON tb_unit_api.idunit = tb_rekat.unit_kerja
            INNER JOIN tb_sumberdana ON tb_rekat.sd = tb_sumberdana.kd_sumberdana
            LEFT JOIN tb_kodefikasi_jenisbelanja
                ON tb_kodefikasi_jenisbelanja.akun = tb_rabgedung.id_jenis_belanja
            LEFT JOIN tb_api_coa apicoa ON tb_kodefikasi_jenisbelanja.ekuivalensi = apicoa.coa
            WHERE
            tb_rekat.tahun = 'Definitif_$tahun'
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai IS NOT NULL
            AND tb_rabgedung.jumlah_nilai != 0
            AND tb_rabgedung.id_mak IS NOT NULL
            AND (
            tb_rabgedung.id_jenis_belanja IS NOT NULL
                and
            tb_rabgedung.jenis_belanja is not null
            )
            AND tb_rabgedung.kebutuhan_kegiatan IS NOT NULL
            AND tb_rabgedung.jumlah_nilai > 400000000
            AND (tb_unit_api.nama not like '%Fakultas%'
                and tb_unit_api.nama not like '%Sekolah%'
                and tb_unit_api.idunit != '1040301'
                and tb_unit_api.idunit != '1040302')
            AND tb_rekat.sd != '4100'
            AND tb_rekat.unit_kerja = '$kd_unit_kerja'
            AND tb_rekat.sd = '$kd_sumber_dana'
            ),
            ppk AS (
            SELECT
                k.id,k.is_active, k.nip, k.nama_pejabat,
                ru.idunit, rs.id_sumberdana, sd.kd_sumberdana,
                k.maksimal_pengeluaran, k.minimal_pengeluaran,c.coa_parent
            FROM tb_komitmen k
            LEFT JOIN tb_relasi_unit ru
                ON ru.id_komitmen = k.id
            LEFT JOIN tb_relasi_sumberdana rs
                ON rs.id_komitmen = k.id
            left join tb_relasi_coa c
                on c.id_komitmen = k.id
            JOIN tb_sumberdana sd
            ON sd.id = rs.id_sumberdana
            WHERE k.is_active = 'true'
            and k.jenis = 'PPK'
            )
            SELECT DISTINCT rkt.id_jenis_belanja, rkt.id_mak, rkt.mak, rkt.kd_sumber_dana, rkt.sumber_dana,
                rkt.kd_kro, rkt.kro, rkt.kd_ro, rkt.ro,
                rkt.kd_komponen, rkt.komponen,
                rkt.kd_subkomponen, rkt.subkomponen, rkt.kd_unit_kerja,
                rkt.unit_kerja, rkt.id_rekat, rkt.keg, rkt.rpd,
                rkt.kd_coa, rkt.nama_coa, rkt.id_item_coa, rkt.item_coa,
                rkt.DED, rkt.nilai_perencanaan, rkt.nilai_struktur, rkt.nilai_me,
                rkt.nilai_landscape, rkt.nilai_pengawasan, rkt.kuantitas, rkt.satuan,
                rkt.total_biaya_kegiatan, rkt.tahun, rkt.is_deleted,
                ppk.nip as nip_pejabat, ppk.nama_pejabat FROM data_rkat rkt
            left JOIN ppk ON
            ppk.kd_sumberdana = rkt.kd_sumber_dana
            AND ppk.idunit = rkt.kd_unit_kerja
            WHERE (
            ppk.minimal_pengeluaran = 400000000
            and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            OR
            (
            ppk.maksimal_pengeluaran = 0 AND ppk.minimal_pengeluaran = 0
                and ppk.coa_parent = substring(rkt.id_jenis_belanja, 1, 6)
            AND NOT EXISTS (
                    SELECT 1
                    FROM ppk ppk_inner
                    WHERE ppk_inner.kd_sumberdana = rkt.kd_sumber_dana
                        AND ppk_inner.idunit = rkt.kd_unit_kerja
                        AND ppk_inner.minimal_pengeluaran = 400000000
                        AND ppk_inner.coa_parent = SUBSTRING(rkt.kd_coa, 1, 6)
                )
        )) ");
        $data = array_merge($data, $nonFakultasNonLimit);
        $withBpp = [];
        foreach ( $data as $item ) {
            $bpp = Komitmen::whereHas('unitKerja', function ($query) use ($item) {
                $query->where("idunit", "=", $item->kd_unit_kerja);
            })->whereHas('sumberDana.sumberdana', function ($query) use ($item) {
                $query->where("kd_sumberdana", "=", $item->kd_sumber_dana);
            })->where("jenis", "BPP")->select("nip", "nama_pejabat")->get()->toArray();
            !empty($bpp) ?
                $withBpp[] = array_merge((array)$item, [
                    "nip_bpp" => $bpp['0']['nip'],
                    "nama_bpp" => $bpp['0']['nama_pejabat']]) :
                $withBpp[] = array_merge((array)$item, [
                    "nip_bpp" => '',
                    "nama_bpp" => '']);
        }
        return $withBpp;
    }


