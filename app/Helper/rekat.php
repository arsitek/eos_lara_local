<?php

use App\Models\Komitmen;
use Illuminate\Support\Facades\DB;
use App\Models\Alokasi;
use App\Models\Datacenter\AksesPrioritas;
use App\Models\Datamaster\Subkomponen;
use App\Models\Datamaster\SubkomponenMaster;
use App\Models\Datapaket\RelasiPaket;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Realisasi;
use App\Models\Rekat;
use Illuminate\Support\Facades\Http;

// ✅ Mengambil data sisa pagu berdasarkan unit, sumberdana & tahun
function getSisaAlokasiUnit( $idunit, $kd_sumberdana, $tahun ){
    $alokasi = Alokasi::select('*',
                    DB::connection('sirekat')->select("CONCAT('Rp. ', FORMAT(pagu, 0)) AS TOTAL"))
                    ->where('unit_kerja', '=', $idunit)
                    ->where('kd_sumberdana', '=', $kd_sumberdana)
                    ->where('tahun', '=', $tahun)
                    ->where('is_deleted', '=', 'false')
                    ->first();
    $pagu_terpakai_operasional = DB::connection('sirekat')->select("SELECT SUM(tb_rabkegiatan.jumlah_biaya) as TOTAL
                    FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = rkt.id
                    WHERE
                        rkt.is_deleted = 'false' AND tb_rabkegiatan.is_deleted = 'false'
                        AND rkt.tahun = '$tahun' AND rkt.unit_kerja = '$idunit'
                        AND rkt.sd = '$kd_sumberdana'
    ")['0']->TOTAL;
    $pagu_terpakai_sarana = DB::connection('sirekat')->select("SELECT SUM(tb_rabperalatan.jumlah_biaya) as TOTAL
                FROM tb_rekat rkt
                INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = rkt.id
                WHERE
                    rkt.is_deleted = 'false' AND tb_rabperalatan.is_deleted = 'false'
                    AND rkt.tahun = '$tahun' AND rkt.unit_kerja = '$idunit' AND rkt.sd = '$kd_sumberdana'
    ")['0']->TOTAL;
    $pagu_terpakai_prasarana = DB::connection('sirekat')->select("SELECT SUM(tb_rabgedung.jumlah_nilai) as TOTAL
                FROM tb_rekat rkt
                INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = rkt.id
                WHERE
                    rkt.is_deleted = 'false' AND tb_rabgedung.is_deleted = 'false'
                    AND rkt.tahun = '$tahun' AND rkt.unit_kerja = '$idunit'
                    AND rkt.sd = '$kd_sumberdana'
    ")['0']->TOTAL;
    $pagu_terpakai = $pagu_terpakai_operasional + $pagu_terpakai_prasarana + $pagu_terpakai_sarana;
    $sisa_pagu     = $alokasi->pagu ?? 0 - $pagu_terpakai;
    return [$sisa_pagu, $pagu_terpakai];
}
// ✅ Mengambil data alokasi berdasarkan unit, sumberdana & tahun
function getAlokasi( $idunit, $kd_sumberdana, $tahun ){
    $alokasi = Alokasi::select('jenis','pagu', 'pagu_tambahan', DB::raw("pagu AS TOTAL") )
                    ->where('is_deleted', '=', 'false')
                    ->where('unit_kerja', '=', $idunit)
                    ->where('kd_sumberdana', '=', $kd_sumberdana)
                    ->where('tahun', '=', $tahun)
                    ->first();
    if ( !empty($alokasi) ) {
        return $alokasi->pagu + $alokasi->pagu_tambahan;
    }
    return 0;
}
// ✅ Mengambil data pagu terpakai berdasarkan unit, sumberdana & tahun.
function getPaguTerpakai( $idunit, $kd_sumberdana, $tahun, $isVerified, $prioritas ){
    $queryOperasional = "SELECT
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                        FROM
                            tb_rekat rkt
                        INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                        LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                        LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'operasional'
                        LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'OPERASIONAL' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                        WHERE rkt.is_deleted = 'false' AND rab.is_deleted = 'false' AND rkt.sd <> '4100'";
    $querySarana = "SELECT
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                    FROM
                        tb_rekat rkt
                    INNER JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                    LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                    LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'sarana'
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'SARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    WHERE rkt.is_deleted = 'false' AND rab.is_deleted = 'false' AND rkt.sd <> '4100'";
    $queryPrasarana = "SELECT
                            SUM( CASE
                                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                                ELSE rab.jumlah_nilai + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            END ) AS TOTAL
                        FROM
                            tb_rekat rkt
                        INNER JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
                        LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                        LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'prasarana'
                        LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'PRASARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                        WHERE rkt.is_deleted = 'false' AND rab.is_deleted = 'false' AND rkt.sd <> '4100'";
    $queryRab = "SELECT
            SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                    ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                END ) AS TOTAL
            FROM tb_rekat rkt
            JOIN tb_rab rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = rab.jenis_rab
            LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = upper(rab.jenis_rab) AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
            WHERE rab.is_draft = 0 AND rab.is_deleted = 0";
    if ( $prioritas != null  ) {
        $queryOperasional .= " AND rkt.prioritas = '$prioritas' ";
        $querySarana      .= " AND rkt.prioritas = '$prioritas' ";
        $queryPrasarana   .= " AND rkt.prioritas = '$prioritas' ";
    }
    if ( $tahun != null  ) {
        $queryOperasional .= " AND rkt.tahun = '$tahun' ";
        $querySarana      .= " AND rkt.tahun = '$tahun' ";
        $queryPrasarana   .= " AND rkt.tahun = '$tahun' ";
        $queryRab          .= " AND rkt.tahun = '$tahun' ";
    }
    if ( $idunit != null ) {
        $queryOperasional .= " AND rkt.unit_kerja = '$idunit' AND rab.unit_kerja = '$idunit' ";
        $querySarana      .= " AND rkt.unit_kerja = '$idunit' AND rab.unit_kerja = '$idunit' ";
        $queryPrasarana   .= " AND rkt.unit_kerja = '$idunit' AND rab.unit_kerja = '$idunit' ";
        $queryRab          .= " AND rkt.unit_kerja = '$idunit' ";
    }
    if ( $kd_sumberdana != null ) {
        $queryOperasional .= " AND rkt.sd = '$kd_sumberdana' ";
        $querySarana      .= " AND rkt.sd = '$kd_sumberdana' ";
        $queryPrasarana   .= " AND rkt.sd = '$kd_sumberdana' ";
        $queryRab         .= " AND rkt.sd = '$kd_sumberdana' ";
    }
    if ( true === $isVerified ) {
        $queryOperasional .= " AND rab.verifikasi_pimpinan_unit = 'Setuju'";
        $querySarana      .= " AND rab.verifikasi_pimpinan_unit = 'Setuju'";
        $queryPrasarana   .= " AND rab.verifikasi_pimpinan_unit = 'Setuju'";
        $queryRab         .= " AND rab.verifikasi_pimpinan_unit = 'Setuju'";
    }
    $terpakaiSarana      = DB::connection('sirekat')->select($querySarana);
    $terpakaiPrasarana   = DB::connection('sirekat')->select($queryPrasarana);
    $terpakaiOperasional = DB::connection('sirekat')->select($queryOperasional);
    $terpakaiRab         = DB::connection('sirekat')->select($queryRab);
    $terpakaiTotal       = $terpakaiOperasional['0']->TOTAL + $terpakaiSarana['0']->TOTAL + $terpakaiPrasarana['0']->TOTAL + $terpakaiRab['0']->TOTAL;
    $paguTambahan        = Alokasi::where([ "unit_kerja" => $idunit, "kd_sumberdana" => $kd_sumberdana, "tahun" => $tahun, "is_deleted" => "false" ])->sum("pagu_tambahan");
    return [
        "operasional"   => $terpakaiOperasional,
        "sarana"        => $terpakaiSarana,
        "prasarana"     => $terpakaiPrasarana,
        "total"         => $terpakaiTotal
    ];
}
// ✅ Mengambil data pagu terpakai dari semua unit berdasarkan data diverifikasi dan tidak diverifikasi
function AlokasiMaster( $tahun ) {
    try {
        $notVerified = DB::connection('sirekat')->select("SELECT sd, unit_kerja, unit.nama AS nama_unit, tahun, sum(TOTAL) AS TOTAL, jenis FROM (
                    SELECT rkt.sd, rkt.unit_kerja, rkt.tahun, 'SARANA' AS jenis,
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                    FROM
                        tb_rekat rkt
                    INNER JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                    LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                    LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'sarana' and rt.is_deleted = 'false'
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'SARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    WHERE
                        rab.is_deleted = 'false'
                        AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                    GROUP BY
                        rkt.unit_kerja, rkt.sd
                    UNION ALL
                    SELECT rkt.sd, rkt.unit_kerja, rkt.tahun, 'OPERASIONAL' AS jenis,
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                    FROM
                        tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                    LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'operasional' and rt.is_deleted = 'false'
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'OPERASIONAL' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    WHERE
                        rab.is_deleted = 'false'
                        AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                    GROUP BY
                            rkt.unit_kerja, rkt.sd
                    UNION ALL
                    SELECT rkt.sd, rkt.unit_kerja, rkt.tahun, 'PRASARANA' AS jenis,
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_nilai + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                    FROM
                        tb_rekat rkt
                    INNER JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
                    LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                    LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'prasarana' and rt.is_deleted = 'false'
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'PRASARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    WHERE
                        rab.is_deleted = 'false'
                        AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                        GROUP BY
                            rkt.unit_kerja, rkt.sd
                    UNION ALL
                    SELECT rkt.sd, rkt.unit_kerja, rkt.tahun, rab.jenis_rab,
                        SUM( CASE
                            WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                            ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        END ) AS TOTAL
                    FROM
                        tb_rekat rkt
                    INNER JOIN tb_rab rab ON rab.id_rekat = rkt.id
                    LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                    LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = lower(rab.jenis_rab) and rt.is_deleted = 'false'
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = rab.jenis_rab AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    WHERE
                        rab.is_deleted = 'false'
                        AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                        GROUP BY rkt.unit_kerja, rkt.sd
                    ) AS RESULT
            INNER JOIN tb_unit_api unit ON unit.idunit = RESULT.unit_kerja
            GROUP BY sd, unit_kerja
        ORDER BY sd");
        $verified = DB::connection('sirekat')->select("SELECT sd, unit_kerja, tahun, sum(TOTAL) AS TOTAL FROM (
            SELECT rkt.sd, rkt.unit_kerja, rkt.tahun,
                SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                END ) AS TOTAL
            FROM
                tb_rekat rkt
            INNER JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'sarana' and rt.is_deleted = 'false'
            LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'SARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
            WHERE
                rab.is_deleted = 'false'
                AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY
                rkt.unit_kerja, rkt.sd
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja, rkt.tahun,
                SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                END ) AS TOTAL
            FROM
                tb_rekat rkt
            INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'operasional' and rt.is_deleted = 'false'
            LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'OPERASIONAL' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
            WHERE
                rab.is_deleted = 'false'
                AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY
                rkt.unit_kerja, rkt.sd
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja, rkt.tahun,
                SUM( CASE
                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                    ELSE rab.jumlah_nilai + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                END ) AS TOTAL
            FROM
                tb_rekat rkt
            INNER JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'prasarana' and rt.is_deleted = 'false'
            LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = 'PRASARANA' AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
            WHERE
                rab.is_deleted = 'false'
                AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY
                rkt.unit_kerja, rkt.sd
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja, rkt.tahun,
                    SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        ELSE rab.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                    END ) AS TOTAL
                FROM
                    tb_rekat rkt
                INNER JOIN tb_rab rab ON rab.id_rekat = rkt.id
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = lower(rab.jenis_rab) and rt.is_deleted = 'false'
                LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rab.id AND sm.jenis_rab = rab.jenis_rab AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                WHERE
                    rab.is_deleted = '0'
                    AND rkt.is_deleted = 'false' AND rkt.tahun = '$tahun' AND rab.verifikasi_pimpinan_unit = 'Setuju'
                GROUP BY rkt.unit_kerja, rkt.sd
            ) AS RESULT
            GROUP BY sd, unit_kerja
        ORDER BY sd");
        return [
            "notVerified" => $notVerified,
            "verified" => $verified
        ];
    } catch ( \Exception $e ) {
        return $e->getMessage();
    }
}
// ✅ Mengambil biaya Rekat per id
function getBiayaRekat( $idunit, $kd_sumberdana, $tahun ){
    $baseData = DB::connection('sirekat')->select("WITH BaseData AS ( SELECT
                rkt.id AS id_rekat, rab.jumlah_biaya, rkt.sd AS kd_sumberdana, rab.unit_kerja, rkt.unit_kerja as unit_kerja_rkt,
                rab.verifikasi_pimpinan_unit, rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                'OPERASIONAL' AS rab_type, rkt.is_deleted as is_deleted_rkt, rab.is_deleted, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
            WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
            UNION ALL
            SELECT
                rkt.id AS id_rekat, rab.jumlah_biaya, rkt.sd AS kd_sumberdana, rab.unit_kerja, rkt.unit_kerja as unit_kerja_rkt,
                rab.verifikasi_pimpinan_unit, rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                'SARANA' AS rab_type,rkt.is_deleted as is_deleted_rkt, rab.is_deleted, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
            WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
            UNION ALL
            SELECT
                rkt.id AS id_rekat, rab.jumlah_nilai AS jumlah_biaya, rkt.sd AS kd_sumberdana, rab.unit_kerja, rkt.unit_kerja as unit_kerja_rkt,
                rab.verifikasi_pimpinan_unit, rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                'PRASARANA' AS rab_type,rkt.is_deleted as is_deleted_rkt, rab.is_deleted, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
            UNION ALL
            SELECT
                rkt.id AS id_rekat, rab.jumlah_biaya, rkt.sd AS kd_sumberdana, rkt.unit_kerja as unit_kerja_rkt, rkt.unit_kerja as unit_kerja,
                rab.verifikasi_pimpinan_unit, rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                rkt.is_deleted as is_deleted_rkt, rab.is_deleted, rab.is_draft,
                upper(rab.jenis_rab) AS rab_type
            FROM tb_rekat rkt
            JOIN tb_rab rab ON rab.id_rekat = rkt.id
            WHERE rkt.tahun = ? AND rab.is_draft = 0 AND rab.is_deleted = 0
        )
        SELECT rkat.id_rekat AS id, SUM( rkat.jumlah_biaya ) AS TOTAL
        FROM BaseData rkat
        WHERE rkat.kd_sumberdana = ? AND rkat.unit_kerja = ? AND rkat.unit_kerja_rkt = ? 
    GROUP BY rkat.id_rekat", [ $tahun, $tahun, $tahun, $tahun, $kd_sumberdana, $idunit, $idunit ]);
   return [
        "byId" => $baseData
    ];
}
function generateIdMakRab( $jenis_rab ) {
    $id_mak = '';
    if ( $jenis_rab == 'RAB_PERALATAN' ) {
        do {
            $max_id = RABPER::max('id');
            $id_mak = '22' . rand(1, 99) . $max_id + 2;
            $existInRabKegiatan  = RABKEG::where("id_mak", "=", $id_mak)->exists();
            $existInRabPeralatan = RABPER::where("id_mak", "=", $id_mak)->exists();
            $existInRabGedung    = RABGDG::where("id_mak", "=", $id_mak)->exists();
        } while ($existInRabKegiatan || $existInRabPeralatan || $existInRabGedung);
    } else if ( $jenis_rab == 'RAB_GEDUNG' ) {
        do {
            $max_id = RABGDG::max('id');
            $id_mak = '33' . rand(1, 99) . $max_id + 2;
            $existInRabKegiatan  = RABKEG::where("id_mak", "=", $id_mak)->exists();
            $existInRabPeralatan = RABPER::where("id_mak", "=", $id_mak)->exists();
            $existInRabGedung    = RABGDG::where("id_mak", "=", $id_mak)->exists();
        } while ($existInRabKegiatan || $existInRabPeralatan || $existInRabGedung);
    } else {
        do {
            $max_id = RABKEG::max('id');
            $id_mak = '11' . rand(1, 99) . $max_id + 2;
            $existInRabKegiatan  = RABKEG::where("id_mak", "=", $id_mak)->exists();
            $existInRabPeralatan = RABKEG::where("id_mak", "=", $id_mak)->exists();
            $existInRabGedung    = RABKEG::where("id_mak", "=", $id_mak)->exists();
        } while ($existInRabKegiatan || $existInRabPeralatan || $existInRabGedung);
    }
    return $id_mak;
}
function getBiayaKro( $idunit, $tahun, $sd ) {
    $totalKro = DB::connection('sirekat')->select("SELECT
                    kd_kro, sasaran_program,
                    SUM(total) AS total_sum
                FROM (
                    -- union (Gedung)
                    SELECT
                        rkt.kd_kro, rkt.sasaran_program,
                        COALESCE(rab.jumlah_nilai, 0) AS total
                    FROM
                        tb_rekat rkt
                    INNER JOIN
                        tb_rabgedung rab ON rab.id_rekat = rkt.id
                    WHERE
                        rkt.is_deleted = 'false'
                        AND rab.is_deleted = 'false'
                        AND rkt.tahun = '$tahun'
                        AND rkt.unit_kerja = '$idunit'
                        AND rkt.sd = '$sd'

                    UNION ALL

                    -- union (Peralatan)
                    SELECT
                        rkt.kd_kro, rkt.sasaran_program,
                        COALESCE(rab.jumlah_biaya, 0) AS total
                    FROM
                        tb_rekat rkt
                    INNER JOIN
                        tb_rabperalatan rab ON rab.id_rekat = rkt.id
                    WHERE
                        rkt.is_deleted = 'false'
                        AND rab.is_deleted = 'false'
                        AND rkt.tahun = '$tahun'
                        AND rkt.unit_kerja = '$idunit'
                        AND rkt.sd = '$sd'

                    UNION ALL

                    -- union (Kegiatan)
                    SELECT
                        rkt.kd_kro, rkt.sasaran_program,
                        COALESCE(rab.jumlah_biaya, 0) AS total
                    FROM
                        tb_rekat rkt
                    INNER JOIN
                        tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE
                        rkt.is_deleted = 'false'
                        AND rab.is_deleted = 'false'
                        AND rkt.tahun = '$tahun'
                        AND rkt.unit_kerja = '$idunit'
                        AND rkt.sd = '$sd'
                ) AS unioned
                GROUP BY
                    sasaran_program");
    return [
        "total" => $totalKro
    ];
}
function getBiayaKroRev( $idunit, $tahun, $sd, $revStatus ) {
    $totalKroSemula = DB::connection('sirekat')->select("SELECT kro.kode_ss as kd_kro, kro.sasaran_program,
            rks.kode_keg,
            COALESCE(SUM(rksd.jumlah_biaya), 0) AS total_sum
            FROM tb_revisi_kro_semula rks
            INNER JOIN tb_revisi_kro_semula_detail rksd
            ON rksd.id_rekat = rks.id_rekat
            INNER JOIN tb_keg keg
            ON keg.kode_keg = rks.kode_keg
            INNER JOIN tb_ikv ikv
            ON ikv.kode_ikv = keg.kode_ikv
            INNER JOIN tb_iku iku
            ON iku.kode_ikk = ikv.kode_ikk
            INNER JOIN tb_sasaran kro
            ON kro.kode_ss = iku.kode_ss
            WHERE
            rks.idunit = '$idunit'
            AND rks.sd = '$sd'
            AND rks.is_deleted = 'false'
            AND rks.is_revisi_kro = 'false'
            AND rks.is_revisi_keg = 'false'
            AND rks.tahun = '$tahun'
            AND rks.status = '$revStatus'
            GROUP BY kro.kode_ss
            ORDER BY kro.kode_ss
    ");
    return [
        "total" => $totalKroSemula
    ];
}
/*** Cek jenis RAB berdasarkan kode kegiatan dan tahun
 * @param string $kodeKeg kode kegiatan
 * @param string $tahun tahun anggaran
 */
function cekJenisRab( $kodeKeg, $tahun ) {
    // Mulai 2026 data jenis_rab diambil dari tabel master
    $jenisRab = $tahun >= 2026
        ? SubkomponenMaster::select('jenis_rab')->where(["kode_keg" => $kodeKeg, "tahun" => $tahun])->first()
        : Subkomponen::select('jenis_rab')->where([ "kode_keg" => $kodeKeg, "tahun" => $tahun ])->first();
    return $jenisRab->jenis_rab ?? null;
}
// ✅ Mengambil data pagu sebelum di revisi/validasi berdasarkan unit
function getPaguSebelumRevisi( $idunit, $kd_sumberdana, $tahun, $jenis, $prioritas ) {
    // jika jenis saldo adalah saldo validasi
    if ($jenis == "kk") {
        $paguTerpakai        = (int)getPaguTerpakai($idunit, $kd_sumberdana, $tahun, false, $prioritas)['total'];
        $validasiKegiatanSQL = DB::connection('sirekat')->select("
            SELECT
                vk.idunit, vk.jenis, vk.tahun, sv.sd, SUM(vk.jumlah_biaya) AS total, vk.id_item_coa
            FROM tb_validasi_kegiatan vk
            INNER JOIN (
                SELECT DISTINCT idunit, sd
                FROM tb_saldo_validasi
                WHERE sd = ? AND tahun = ?
            ) sv ON sv.idunit = vk.idunit
            WHERE
                vk.tahun = ?
                 AND vk.idunit = ?
			     AND vk.jenis LIKE '%Pengalihan%'
            GROUP BY vk.idunit, vk.jenis, vk.tahun, sv.sd", [$kd_sumberdana, $tahun, $tahun, $idunit]);
        $sisaSaldoSQL = DB::connection('sirekat')->select("
            SELECT SUM(sv.sisa_saldo) AS total FROM tb_saldo_validasi sv
                WHERE sv.idunit = ?
                  AND sv.tahun = ?
                AND sv.sd = ?", [$idunit, $tahun, $kd_sumberdana]);
        // ✅ Kalkulasikan saldo digunakan dan anggaran dikurangkan serta kelompokkan berdasarkan unit dan sumber dana
        $pengalihan  = 0;
        foreach ($validasiKegiatanSQL as $result) {
            if (strpos($result->jenis, 'Pengalihan') !== false) {
                $pengalihan += $result->total;
            }
        }
        $sisaSaldo = (int)$sisaSaldoSQL['0']->total;
        $pagu      = ( $paguTerpakai - $pengalihan ) + $sisaSaldo;
        if ( $pengalihan === 0 ) {
            $pagu = 0;
        }
        return $pagu;
    }
}
function getSisaSaldo( $idunit, $kd_sumberdana, $tahun ){
    $sisaSaldoSQL = DB::connection('sirekat')->select("SELECT SUM(sv.sisa_saldo) AS total FROM tb_saldo_validasi sv
        WHERE sv.idunit = ? AND sv.tahun = ? AND sv.sd = ?", [$idunit, $tahun, $kd_sumberdana]);
    $totalSaldo = isset($sisaSaldoSQL[0]->total) ? $sisaSaldoSQL[0]->total : 0;
    return (int)$totalSaldo;
}
function getPengalihan( $idunit, $kd_sumberdana, $tahun, $jenis ){
    $validasiKegiatanSQL = DB::connection('sirekat')->select("
            SELECT
                vk.idunit, vk.jenis, vk.tahun, sv.sd, SUM(vk.jumlah_biaya) AS total, vk.id_item_coa
            FROM tb_validasi_kegiatan vk
            INNER JOIN (
                SELECT DISTINCT idunit, sd
                FROM tb_saldo_validasi
                WHERE sd = ?
            ) sv ON sv.idunit = vk.idunit
            WHERE
                vk.tahun = ?
                 AND vk.idunit = ?
			     AND vk.jenis LIKE '%Pengalihan%'
            GROUP BY vk.idunit, vk.jenis, vk.tahun, sv.sd", [$kd_sumberdana, $tahun, $idunit]);
            $pengalihan  = 0;
            foreach ($validasiKegiatanSQL as $result) {
                if ($result->jenis === 'Pengalihan') {
                    $pengalihan += $result->total;
                }
            }
            return $pengalihan;
}
// ✅ Mengambil data pagu semua unit sebelum di revisi/validasi berdasarkan unit, sumberdana & tahun
function getPaguSebelumRevisiSemuaUnit( $tahun, $idunit = null ){
    // ✅ Base sql
    $sql = "
            SELECT
                vk.idunit, vk.jenis, vk.tahun, sv.sd, SUM(vk.jumlah_biaya) AS total, vk.id_item_coa
            FROM tb_validasi_kegiatan vk
            INNER JOIN (
                SELECT DISTINCT idunit, sd
                FROM tb_saldo_validasi
            ) sv ON sv.idunit = vk.idunit
            WHERE
                vk.tahun = ? " .
                ($idunit != null ? " AND vk.idunit = $idunit" : "") . "
            AND vk.jenis ='Pengalihan'
    GROUP BY vk.idunit, vk.jenis, vk.tahun, sv.sd";

    $pengalihan = DB::connection('sirekat')->select($sql, [ $tahun ]);

    // ✅ Kalkulasikan saldo digunakan dan anggaran dikurangkan serta kelompokkan berdasarkan unit dan sumber dana
    $sisaSaldoSQL = DB::connection('sirekat')->select("
            SELECT sv.idunit, sv.sd, SUM(sv.sisa_saldo) AS total FROM tb_saldo_validasi sv
                WHERE sv.tahun = ?
                 " . ($idunit != null ? " AND sv.idunit = $idunit" : "") . "
                GROUP BY sv.sd, sv.idunit", [$tahun]);
    return [
        "pengalihan" => $pengalihan,
        "sisaSaldo"  => $sisaSaldoSQL
    ];
}
function getPPK($ppk, $data) {
    $tahun       = session('tahun');
    $tahunAngka  = explode( '_', $tahun )[1];
    $jumlahBiaya = $data['jumlah_biaya'];
    $coaChild    = $data['coa'];
    $coaParent   = substr($coaChild, 0, 6);
    $sumberDana  = $data['kd_sumberdana'];
    $idunit      = $data['unitkerja'];
    $fakultasAndDirUnit = [
        "10501", "10601", "10602", "10603", "10604", "10504", "10605", "106060904", "10607",
        "10608", "10609", "10610", "10611", "10612", "10701", "10606",
        "1040301", "1040302","1040301", "1040402", "10404020101", "105010305", "104030101"
    ];
    $condition = "rcc.max_anggaran = 0";
    if ( $jumlahBiaya < 400000000 && in_array($idunit, $fakultasAndDirUnit) )
        $condition = "(rcc.max_anggaran = 400000000 AND level = 'Fakultas')";
    if ( $jumlahBiaya > 100000000 && in_array($idunit, ["10501", "10601", "10602", "10603", "10604", "10504", "10605", "106060904", "10607",
        "10608", "10609", "10610", "10611", "10612", "10701", "10606"]) && in_array($coaChild, ["51030212", "52030212"]) )
        return DB::connection('sirekat')->select("SELECT * FROM tb_komitmen WHERE nama_pejabat LIKE '%rudiansyah%'");
    if ( in_array($idunit, $fakultasAndDirUnit) && $jumlahBiaya >= 400000000 )
        $condition = "(rcc.min_anggaran = 400000000 AND level = 'Fakultas')";
    if ( !in_array($idunit, $fakultasAndDirUnit) && $jumlahBiaya < 400000000 )
        $condition = "(rcc.max_anggaran = 400000000 AND level = 'Universitas')";
    if ( !in_array($idunit, $fakultasAndDirUnit) && $jumlahBiaya >= 400000000 )
        $condition = "(rcc.min_anggaran = 400000000 AND level = 'Universitas')";

    $query = "SELECT * FROM tb_komitmen ppk
        INNER JOIN tb_relasi_coa_child rcc ON rcc.id_komitmen = ppk.id AND rcc.tahun = '$tahunAngka'
        INNER JOIN tb_relasi_unit ru ON ru.id_komitmen = ppk.id AND ru.is_deleted = 'false' AND ru.tahun = '$tahunAngka'
        INNER JOIN tb_unit_api unit ON unit.idunit = ru.idunit
        INNER JOIN tb_relasi_sumberdana rs ON rs.id_komitmen = ppk.id AND rs.tahun = '$tahunAngka'
        INNER JOIN tb_sumberdana sd ON rs.id_sumberdana = sd.id AND sd.is_deleted = 'false' AND sd.is_show = 'true' AND sd.tahun = '$tahunAngka'
        WHERE sd.kd_sumberdana = '$sumberDana' AND unit.idunit = '$idunit'
        AND ppk.is_active = 'true' AND rcc.coa_child = '$coaChild' AND ppk.jenis = 'PPK' AND rcc.is_deleted = 'false'";

    $ppkWithCoaChild = DB::connection('sirekat')->select($query . " AND $condition ORDER BY ppk.id DESC");
    if ( count($ppkWithCoaChild) > 0 )
        return $ppkWithCoaChild;

    if ( in_array($idunit, $fakultasAndDirUnit) && $jumlahBiaya < 400000000 ) {
        $condition = "( rcc.min_anggaran = 0 AND rcc.max_anggaran = 0 )";
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND $condition AND level = 'Fakultas' ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND rcc.max_anggaran = 100000000 AND level = 'Fakultas' ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND ( rcc.min_anggaran = 0 AND rcc.max_anggaran = 0 ) AND level = 'Universitas' ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
    }
    if ( in_array($idunit, $fakultasAndDirUnit) && $jumlahBiaya >= 400000000 ) {
        $condition = "( rcc.min_anggaran = 0 AND rcc.max_anggaran = 0 )";
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND $condition AND level = 'Fakultas' ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND $condition AND level = 'Universitas' ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
    }
    if ( !in_array($idunit, $fakultasAndDirUnit) ) {
        $condition = "( rcc.min_anggaran = 0 AND rcc.max_anggaran = 0 ) AND level = 'Universitas'";
        $ppkWithoutRestrict = DB::connection('sirekat')->select($query . " AND $condition ORDER BY ppk.id DESC");
        if ( count($ppkWithoutRestrict) > 0 )
            return $ppkWithoutRestrict;
    }
    return [];
}
function getBPP( $data ) {
    $bpp = Komitmen::whereHas('unitKerja', function ($query) use ($data) {
        $query->where("idunit", "=", $data['unitkerja'])->where("is_deleted", "false");
    })->whereHas('sumberDana.sumberdana', function ($query) use ($data) {
        $query->where("kd_sumberdana", "=", $data['kd_sumberdana']);
    })->where("jenis", "bpp")->where("is_active", "true")->orderBy("created_at", "DESC")->first();
    return $bpp;
}
function sumSisaSaldo( $idunit, $kd_sumberdana, $tahun ) {
    return DB::connection('sirekat')->select("SELECT sum(sv.sisa_saldo) AS TOTAL from tb_saldo_validasi sv
        WHERE sv.idunit = '$idunit'
        AND sv.tahun = '$tahun'
    AND sv.sd = '$kd_sumberdana'");
}
/**
 * Mendapatkan data total dari sisa realisasi berdasarkan unit, sumberdana, dan tahun
 *
 * Fungsi ini akan mendapatkan data sisa realisasi dari unit yang diinginkan
 * Hasil data yang didapatkan akan di filter berdasarkan ID unit, kode sumberdana,
 * dan tahun.
 *
 * @param string $idunit idunit dari data yang ingin di cari
 * @param string $kd_sumberdana kode sumberdana dari data yang ingin di cari
 * @param string $tahun tahun dari data yang ingin di cari
 *
 * @return \Illuminate\Support\Facades\DB
 */
function sumSisaReal( $idunit, $kd_sumberdana, $tahun ){
    return DB::connection('sirekat')->select("SELECT sum(TOTAL) as TOTAL FROM (
        SELECT SUM(rt.sisa) AS TOTAL FROM tb_rabkegiatan k
        INNER JOIN tb_rekat r ON r.id = k.id_rekat
        INNER JOIN tb_realisasi_terpakai rt ON rt.id_rab = k.id AND rt.jenis_rab = 'operasional' AND rt.is_deleted = 'false'
        where k.is_deleted = 'false' AND r.sd = '$kd_sumberdana'
        AND r.unit_kerja = '$idunit' AND k.unit_kerja = '$idunit' AND r.tahun = '$tahun' AND k.is_draft = 'false'
        UNION ALL
        SELECT SUM(rt.sisa) AS TOTAL FROM tb_rabperalatan k
        INNER JOIN tb_rekat r ON r.id = k.id_rekat
        INNER JOIN tb_realisasi_terpakai rt ON rt.id_rab = k.id AND rt.jenis_rab = 'sarana' AND rt.is_deleted = 'false'
        where k.is_deleted = 'false' AND r.sd = '$kd_sumberdana'
        AND r.unit_kerja = '$idunit' AND k.unit_kerja = '$idunit' AND r.tahun = '$tahun' AND k.is_draft = 'false'
        UNION ALL
        SELECT SUM(rt.sisa) AS TOTAL FROM tb_rabgedung k
        INNER JOIN tb_rekat r ON r.id = k.id_rekat
        INNER JOIN tb_realisasi_terpakai rt ON rt.id_rab = k.id AND rt.jenis_rab = 'prasarana' AND rt.is_deleted = 'false'
        where k.is_deleted = 'false' AND r.sd = '$kd_sumberdana'
        AND r.unit_kerja = '$idunit' AND k.unit_kerja = '$idunit' AND r.tahun = '$tahun' AND k.is_draft = 'false'
        ) AS SISA_REALISASI
    ");
}

/**
 * Mendapatkan data untuk mengakses prioritas pada halaman rekat
 *
 * Fungsi ini akan mendapatkan data dari model AksesPrioritas, serta dengan relasi
 * `sumberdana` data yang sudah di filter. Hasil data yang didapatkan akan
 * di filter berdasarkan ID unit, kode sumberdana, status aktif, dan tahun.
 *
 * @param string $idunit idunit login
 * @param string $kd_sumberdana kode sumberdana
 * @param string $tahun tahun login
 *
 * @return \Illuminate\Database\Eloquent\Collection The collection of access priority records.
 */
function getAksesPrioritas($idunit, $kd_sumberdana, $tahun) {
    return AksesPrioritas::with([ "sumberdana" => function($query) use ($kd_sumberdana) {
        $query->where("kd_sumberdana", $kd_sumberdana);
    }])->where([
        "idunit" => $idunit,
        "status" => 1,
        "tahun" => $tahun
    ])->get();
}

/**
 * Mendapatkan data total realisasi berdasarkan unit dan semua unit
 *
 * Fungsi ini akan mendapatkan data total realisasi untuk semua unit
 *
 * @param string $jenis jenis rekap data realisasi yang ingin di cari
 * @param string $tahun tahun dari data yang ingin di cari
 *
 * @return \Illuminate\Support\Facades\DB
 */
function getRekapRealisasi( $tahun, $jenis ) {
    if ( $jenis === "unit" ) {
        return DB::connection('sirekat')->select("SELECT unit_kerja, SUM(jumlah_biaya) as TOTAL_SD, sum(jumlah_amprah) as TOTAL_AMPRAH, sd as kodeSd FROM (
            SELECT rkt.sd, rkt.unit_kerja,
            SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_biaya
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'operasional'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd, rkt.unit_kerja
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja,
            SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_biaya
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'sarana'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd, rkt.unit_kerja
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja,
            SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_nilai
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'prasarana'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd, rkt.unit_kerja
            ) AS TOTAL_SD
        GROUP BY sd, unit_kerja");
    } if ( $jenis === "semua" ) {
        return DB::connection('sirekat')->select("SELECT SUM(jumlah_biaya) as TOTAL_SD, sum(jumlah_amprah) as TOTAL_AMPRAH, sd as kodeSd FROM (
            SELECT rkt.sd, rkt.unit_kerja,
           SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_biaya
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'operasional'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja,
           SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_biaya
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'sarana'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd
            UNION ALL
            SELECT rkt.sd, rkt.unit_kerja,
            SUM( CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rab.jumlah_nilai
            END ) AS jumlah_biaya,
            SUM( COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) ) AS jumlah_amprah
            FROM tb_rekat rkt
            JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
            LEFT JOIN tb_realisasi_terpakai rt on rt.id_rab = rab.id and rt.jenis_rab = 'prasarana'
            WHERE rkt.id = rab.id_rekat
                AND rkt.tahun      = '$tahun'
                AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
                AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rkt.sd
            ) AS TOTAL_SD
        GROUP BY sd");
    }
}

/**
 * Mengecek data apakah sudah terpaketkan atau belum.
 *
 * Fungsi ini akan mengembalikan nilai true atau false apakah data sudah terpaketkan atau belum.
 *
 * @param string $idRab id item coa dari data yang akan di cek.
 * @param string $jenisRab jenis rab dari data yang akan di cek ( operasional, sarana, dan prasarana ).
 *
 * @return boolean
 */
function cekItemPaket( $idRab, $jenisRab ) {
    $isExists = RelasiPaket::where([ "id_rab" => $idRab, "is_deleted" => "false", "jenis_rab"  => $jenisRab ])->exists();
    if ( $isExists ) {
        return true;
    }
    return false;
}

/**
 * Mengecek data apakah sudah teramprah.
 *
 * Fungsi ini akan mengembalikan nilai true atau false apakah data sudah teramprah atau belum.
 *
 * @param integer $idmak dari data yang akan di cek.
 *
 * @return boolean
 */
function cekAmprah( $idmak ) {
    $isExists = Realisasi::where([ "id_mak" => $idmak, "is_deleted" => "false", "is_posting" => "true" ])->exists();
    if ( $isExists ) {
        return true;
    }
    return false;
}

/**
 * Mengecek data apakah sudah sedang dalam proses bayar.
 *
 * Fungsi ini akan mengembalikan nilai true atau false apakah data sedang dalam proses bayar atau belum.
 *
 * @param integer $idmak dari data yang akan di cek.
 * @return boolean
 */
function cekSedangProsesBayar( $idmak ) {
    $realisasi = Realisasi::where([ "id_mak" => $idmak, "is_deleted" => "false", "is_posting" => "true" ])->first();

    // Jika amprahan sudah terisi (atau amprahan & realisasi sama-sama terisi), bukan lagi proses bayar
    if ( !$realisasi )
        return false;

    $amprahanTerisi  = !is_null($realisasi->jumlah_amprahan) && $realisasi->jumlah_amprahan != '0';
    $realisasiTerisi = !is_null($realisasi->jumlah_realisasi) && $realisasi->jumlah_realisasi != '0';
    if ( $amprahanTerisi || ( $amprahanTerisi && $realisasiTerisi ) )
        return true;
    return false;
}

/**
 * Mengecek data apakah sudah terpaketkan.
 *
 * Fungsi ini akan mengembalikan nilai true atau false apakah data sudah terpaketkan atau belum.
 *
 * @param integer $idrab dari data yang akan di cek.
 * @param string $jenisRab dari data yang akan di cek.
 *
 * @return boolean
 */
function cekPaket( $idRab, $jenisRab ) {
    $isExists = RelasiPaket::where([ "id_rab" => $idRab, "is_deleted" => "false", "jenis_rab"  => $jenisRab ])->exists();
    if ( $isExists ) {
        return true;
    }
    return false;
}

/**
 * Fungsi ini akan mengembalikan nilai true atau false apakah data sudah terpaketkan atau belum.
 *
 * @param integer $idRekat dari data yang akan di cek.
 *
 * @return string
 */
function getKodeIkk( $idRekat, $tahun ) {
    // Mulai 2026, kode IKU/IKK disimpan lewat relasi relasiMasterIku
    if ( $tahun >= 2026 ) {
        $rekat = Rekat::with('relasiMasterIku')->find($idRekat);
        return $rekat->relasiMasterIku->kode_ikk ?? "";
    }

    // Versi tahun < 2026 tetap pakai rantai relasi subkomponen -> ikv -> ro
    $rekat = Rekat::with([
        'subkomponen' => function($query) use ($tahun) {
            $query->where('tahun', $tahun);
        },
        'subkomponen.ikv' => function($query) use ($tahun) {
            $query->where('tahun', $tahun);
        },
        'subkomponen.ikv.ro' => function($query) use ($tahun) {
            $query->where('tahun', $tahun);
        }
    ])->find($idRekat);
    return $rekat->subkomponen->ikv->ro->kode_ikk ?? "";
}

/**
 * Fungsi ini akan mengembalikan nilai true atau false apakah data yang akan diinput melebihi pagu atau tidak.
 *
 * @param string $idunit dari data yang akan di cek.
 * @param string $kodeSd dari data yang akan di cek.
 * @param string $tahun dari data yang akan di cek.
 *
 * @return string "error" atau "success"
 */
function cekPagu( $idunit, $kodeSd, $tahun, $jumlahBiaya, $isRevisi = false, $isTambahItemCoa = false, $statusRevisi = null ) {
    $alokasi         = (int)getAlokasi($idunit, $kodeSd, $tahun);
    $pagu_digunakan  = getPaguTerpakai($idunit, $kodeSd, session()->get('tahun'), false, null)['total'];
    $sisaSaldo       = getSisaSaldo($idunit, $kodeSd, $tahun, null);

    if ( $statusRevisi == "Tolak" ) {
        $totalPaguDigunakan = $pagu_digunakan + $sisaSaldo - $jumlahBiaya;
        if ( $alokasi < $totalPaguDigunakan ) {
            return "error";
        } else {
            return "success";
        }
    }

    if ( $isRevisi === true ) {
        $sisaSaldo = 0;
    }

    $pagu_digunakan = $pagu_digunakan + $sisaSaldo + $jumlahBiaya;
    if ( $isTambahItemCoa === true )
        $pagu_digunakan = $pagu_digunakan - $jumlahBiaya;
    if ( $alokasi < $pagu_digunakan ) {
        return "error";
    } else {
        return "success";
    }
}

/**
 * @param string $query     SQL fragment appended after the CTE, e.g.
 * @param int    $tahun      Fiscal year for data filtering by year
 * @param int    $tahunAngka Numeric year for tb_ikv.tahun, tb_iku.tahun, and tb_sasaran.tahun
 * @param string $idunit     Unit identifier for additional filtering (tb_keg.idunit)
 * @param string $kodeSd     Funding source code for additional filtering (tb_keg.kode_sd)
 *
 * @return array Array of stdClass objects returned by DB::SELECT
*/
function getBaseData( $query, $tahun, $tahunAngka, $idunit = null, $kodeSd = null, $additionalParams = [] ) {
    // default params
    $params = [ $tahun, $tahun, $tahun, $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahun, $tahunAngka ];
    if ( count($additionalParams) > 0 )
        $params = array_merge($params, $additionalParams);

    // Base SQL with CTE
    $baseSQL = "WITH BaseData AS ( SELECT
            rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
            rkt.is_deleted AS is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
            rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja AS unit_kerja_rkt,
            rkt.kd_rk, rab.biaya_satuan, rab.jumlah_biaya, rab.verifikasi_pimpinan_unit,
            rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ, rab.verifikasi_spi,
            rab.kuantitas, rab.satuan_kuantitas AS sKuantitas, rab.durasi, rab.satuan_durasi AS sDurasi, rab.kegiatan, rab.satuan_kegiatan AS sKegiatan,
            'OPERASIONAL' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan AS itemCoa, rab.tanggapan
        FROM tb_rekat rkt
        JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
        WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
        UNION ALL
        SELECT
            rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
            rkt.is_deleted AS is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
            rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja AS unit_kerja_rkt,
            rkt.kd_rk, rab.harga_satuan AS biaya_satuan, rab.jumlah_biaya, rab.verifikasi_pimpinan_unit,
            rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ, rab.verifikasi_spi,
            rab.kuantitas, rab.satuan AS sKuantitas, '1' AS durasi , 'Pkt' AS sDurasi, '1' AS kegiatan, 'Keg' AS sKegiatan,
            'SARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan AS itemCoa, rab.tanggapan
        FROM tb_rekat rkt
        JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
        WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
        UNION ALL
        SELECT
            rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
            rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
            rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
            rkt.kd_rk, rab.jumlah_nilai AS biaya_satuan, rab.jumlah_nilai AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
            rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ, rab.verifikasi_spi,
            rab.kuantitas, rab.satuan AS sKuantitas, '1' AS durasi , 'Pkt' AS sDurasi, '1' AS kegiatan, 'Keg' AS sKegiatan,
            'PRASARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan as itemCoa, rab.tanggapan
        FROM tb_rekat rkt
        JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
        WHERE rkt.tahun = ? AND rkt.is_deleted = 'false' AND rab.is_deleted = 'false'
        UNION ALL
        SELECT
            rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
            rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
            rkt.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
            rkt.kd_rk, rab.biaya_satuan, rab.jumlah_biaya, rab.verifikasi_pimpinan_unit,
            rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ, rab.verifikasi_spi,
            rab.kuantitas, rab.satuan_kuantitas AS sKuantitas, '1' AS durasi , rab.satuan_durasi AS sDurasi, '1' AS kegiatan, rab.satuan_kegiatan AS sKegiatan,
            UPPER(rab.jenis_rab) AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan as itemCoa, rab.tanggapan
        FROM tb_rekat rkt
        JOIN tb_rab rab ON rab.id_rekat = rkt.id
        WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false' AND rab.is_deleted = '0'
        ),  baseDataBackup AS ( SELECT tbrd.id_coa AS id_jenis_belanja, tbrd.coa AS jenis_belanja, tbrd.id_mak, tbrd.jenis AS rab_type, tbrd.id_item_coa AS id, tbrd.id_rekat,
            tbrd.verifikasi_tim, tbrd.verifikasi_keu, tbrd.verifikasi_aset, tbrd.verifikasi_pimpinan_unit, tbrd.verifikasi_pimpinan_univ,
            tbrd.nip_ppk, tbrd.nip_bpp, tbrd.rpd, tbrd.item_coa AS itemCoa, tbrd.is_draft,
            tbrd.kuantitas, COALESCE( tbrd.satuan_kuantitas, 'Pkt') AS sKuantitas, tbrd.durasi, COALESCE(tbrd.satuan_durasi, 'Keg') AS sDurasi,
            tbrd.kegiatan, COALESCE(tbrd.satuan_kegiatan, 'Keg') AS sKegiatan, tbrd.harga_satuan AS biaya_satuan, tbrd.jumlah_biaya,
            tbrd.jumlah_amprahan, tbrd.jumlah_realisasi, tbrd.terpakai_sisa AS sisa_pengalihan, tbrd.terpakai AS jumlah_pengalihan, tbrd.id_duplikasi
            FROM tb_backup_rkat_detail tbrd
            WHERE tbrd.is_deleted = 'false'
        ), dataMaster AS (
            SELECT keg.kode_keg, keg.rincian_kegiatan, ikv.kode_ikv, ikv.ikv, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, kro.kode_ss, kro.sasaran_program AS ss
            FROM tb_keg keg
            INNER JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = ?
            INNER JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = ?
            INNER JOIN tb_sasaran kro ON kro.kode_ss = iku.kode_ss AND kro.tahun = ?
            WHERE keg.tahun = ?
        ), pejabat AS (
            SELECT pejabat.nip, pejabat.nama_pejabat, pejabat.jenis
            FROM tb_komitmen pejabat WHERE pejabat.is_active = 'true'
            GROUP BY pejabat.nip
        ), paket AS (
            SELECT pkt.id_mak AS id_mak_paket, pkt.id AS id_paket, rp.id_rab, rp.jenis_rab, pkt.sub_judul, pkt.jumlah_biaya,
                pkt.coa AS id_jenis_belanja, pkt.nama_coa AS jenis_belanja, rpd.rpd FROM tb_paket pkt
            INNER JOIN tb_relasi_paket rp ON rp.id_paket = pkt.id AND rp.is_deleted = 'false'
            INNER JOIN tb_relasi_paket_rpd rpd ON rpd.id_paket = pkt.id
            WHERE pkt.is_posting = 'true' AND pkt.tahun = ? AND pkt.is_deleted = 'false'
        ), realisasi AS (
            SELECT amprah.id_mak, amprah.jumlah_biaya, amprah.jumlah_amprahan, amprah.jumlah_realisasi, amprah.is_posting FROM tb_realisasi amprah
            WHERE amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
        ), realisasiTerpakai AS (
            SELECT rt.dipakai, rt.jenis_rab, rt.id_rab, rt.sisa FROM tb_realisasi_terpakai rt WHERE rt.is_deleted = 'false'
        ), sumberdana AS (
        SELECT sd.sumberdana, sd.kd_sumberdana FROM tb_sumberdana sd WHERE sd.tahun = ? AND sd.is_deleted = 'false' AND sd.is_show = 'true'
    )";
    $runQuery = DB::connection('sirekat')->select($baseSQL . " " . $query, $params);
    return $runQuery;
}

function getTahunData(): array {
    $tahun = session('tahun', 'tahun_2025');
    if (strpos($tahun, '_') !== false) {
        $parts = explode('_', $tahun);
        $tahunAngka = $parts[1];
    } else {
        $tahunAngka = $tahun;
        $tahun = 'tahun_' . $tahun;
    }

    return compact('tahun', 'tahunAngka');
}
function getPayloadForSyncData($tahunAngka, $idRekat, $unitKerja): array {
    $unitKerja  = DB::connection('sirekat')->select("SELECT idunit, nama FROM tb_unit_api unit WHERE unit.idunit = ?", [ $unitKerja ]);
    $dataMaster = DB::connection('sirekat')->select("SELECT rik.kode_ss, kro.sasaran_program,
            rik.kode_iku, iku.indikator_kinerja_kegiatan,
            rik.kode_ikv, ikv.ikv,
            rik.kode_keg, keg.keg, keg.izin
        FROM tb_rekat rkt
        INNER JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkt.id
        INNER JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = ?
        INNER JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = ?
        INNER JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = ?
        INNER JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = ?
        WHERE rkt.id = ?", [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $idRekat ]);

    if ( count($dataMaster) === 0 )
        return [ "kd_kro" => "", "kro" => "", "kd_ro" => "", "ro" => "", "kd_komponen" => "", "komponen" => "", "kd_subkomponen" => "", "subkomponen" => "" ];
    if ( count($unitKerja) === 0 )
        throw new \Exception('Unit kerja tidak ditemukan di API Unit Kerja');
    return [
        "kd_kro"         => $dataMaster[0]->kode_ss,
        "kro"            => $dataMaster[0]->sasaran_program,
        "kd_ro"          => $dataMaster[0]->kode_iku,
        "ro"             => $dataMaster[0]->indikator_kinerja_kegiatan,
        "kd_komponen"    => $dataMaster[0]->kode_ikv,
        "komponen"       => $dataMaster[0]->ikv,
        "kd_subkomponen" => $dataMaster[0]->kode_keg,
        "subkomponen"    => $dataMaster[0]->keg,
        "kd_unit_kerja"  => $unitKerja[0]->idunit,
        "unit_kerja"     => $unitKerja[0]->nama,
        "izin"           => $dataMaster[0]->izin,
    ];
}
function cekAmprahRealtime($id_mak){
    try {
        $endpoint = config("app.simkeu_url") . "/common/realisasi/" . $id_mak;
        $response = Http::get($endpoint);
        if ( $response->successful() ) {
            $data = $response->json();
            if ( isset($data['data']) && count($data['data']) > 0 )
                return true;
        }
        return false;
    } catch (\Exception $e) {
        return false;
    }
}
function getUnitKerjaRekat(?string $idunit, ?string $tahun): array {
    $sql = "SELECT * FROM tb_unit_api unit
        join tb_rekat rkt ON rkt.unit_kerja = unit.idunit
        WHERE rkt.tahun = ?";
    if ($idunit) $sql .= " AND unit.idunit LIKE '$idunit%'";
    $unitKerja = DB::connection('sirekat')->select($sql, [$tahun]);
    return $unitKerja;
}
function getBulan(): array {
    return ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
}
function isFakultas(string $activeUnitkerja): bool {
    return in_array($activeUnitkerja, ["10601","10602","10603","10604","10605","10607","10608","10609","10610","10611","10612","10606", "10701"], true);
}
function isSumberDanaAllowZeroPagu(string $activeKodeSd): bool {
    return in_array($activeKodeSd, ["4101"], true);
}