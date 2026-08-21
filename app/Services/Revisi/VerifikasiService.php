<?php

namespace App\Services\Revisi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VerifikasiService
{
    protected $baseSQL;
    public function __construct(){
        $this->baseSQL = "WITH rekat AS (
                select rkt.id AS id_rekat, rkt.sub_judul, rkt.kd_rk, rkt.sd from tb_rekat rkt
                where rkt.is_deleted = 'false' AND rkt.tahun = ?
            ), sumberdana AS (
                SELECT sd.sumberdana, sd.kd_sumberdana FROM tb_sumberdana sd WHERE sd.tahun = ? AND sd.is_deleted = 'false' AND sd.is_show = 'true'
            ), baseData AS (
                SELECT rab.id AS id_rab,rab.kebutuhan_kegiatan, rab.jumlah_biaya, 'OPERASIONAL' AS jenis_rab,
                    rab.unit_kerja, rab.id_rekat, rab.id_jenis_belanja, rab.jenis_belanja, rab.is_draft
                FROM tb_rabkegiatan rab WHERE rab.is_deleted = 'false'
                UNION ALL
                SELECT rab.id AS id_rab,rab.kebutuhan_kegiatan, rab.jumlah_biaya, 'SARANA' AS jenis_rab,
                    rab.unit_kerja, rab.id_rekat, rab.id_jenis_belanja, rab.jenis_belanja, rab.is_draft
                FROM tb_rabperalatan rab WHERE rab.is_deleted = 'false'
                UNION ALL
                SELECT rab.id AS id_rab, rab.kebutuhan_kegiatan, rab.jumlah_nilai, 'PRASARANA' AS jenis_rab,
                    rab.unit_kerja, rab.id_rekat, rab.id_jenis_belanja, rab.jenis_belanja, rab.is_draft
                FROM tb_rabgedung rab WHERE rab.is_deleted = 'false'
            ), coa AS (
                select coa.coa_parent, coa.nama_parent from tb_api_coa coa
                group by coa.coa_parent
            ), settingValidasi AS (
            SELECT * FROM tb_setting_validasi tsv WHERE tsv.is_deleted = 'false'
        ),";
    }
    public function getDataBelumVerifikasi($jenis) {
        if ( $jenis == "SS" )
            return $this->getDataSemulaMenjadiRevisiSS();
        if ( $jenis == "RO" )
            return $this->getDataSemulaMenjadiRevisiRO();
        if ( $jenis == "KK" )
            return $this->getDataSemulaMenjadiRevisiKK();
        if ( $jenis == "BREAKDOWN" )
            return $this->getDataSemulaMenjadiRevisiBreakdown();
    }
    private function getDataSemulaMenjadiRevisiSS(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $sql = $this->baseSQL . " semulaMenjadi AS (
                SELECT sm.id AS id_sm, rkt.id_rekat, rkt.sd, sd.sumberdana, rkt.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja, # id_sm = id semula menjadi
                    sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status, bd.is_draft, bd.kebutuhan_kegiatan, sm.should_verify_by,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekMenjadi,
                    unit.nama AS namaUnit
                FROM tb_semula_menjadi sm
                INNER JOIN baseData bd ON bd.id_rab = sm.id_rab AND sm.jenis_rab = bd.jenis_rab
                INNER JOIN tb_unit_api unit on unit.idunit = bd.unit_kerja
                INNER JOIN rekat rkt ON rkt.id_rekat = bd.id_rekat
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = rkt.sd
                WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'SS' AND sm.is_deleted = 'false' AND ( sm.status = '' OR sm.status IS NULL ) 
            ORDER BY rkt.sd, bd.unit_kerja, sm.jenis_validasi, sm.status
        ) SELECT * FROM semulaMenjadi";
        $data = DB::connection('sirekat')->select($sql, [ $tahun, $tahunAngka ]);
        return $data;
    }
    private function getDataSemulaMenjadiRevisiRO(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $sql = $this->baseSQL . " semulaMenjadi AS (
                SELECT sm.id AS id_sm, rkt.id_rekat, rkt.sd, sd.sumberdana, rkt.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja, # id_sm = id semula menjadi
                    sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status, bd.is_draft, bd.kebutuhan_kegiatan, sm.should_verify_by,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekMenjadi,
                    unit.nama AS namaUnit
                FROM tb_semula_menjadi sm
                INNER JOIN baseData bd ON bd.id_rab = sm.id_rab AND sm.jenis_rab = bd.jenis_rab
                INNER JOIN tb_unit_api unit on unit.idunit = bd.unit_kerja
                INNER JOIN rekat rkt ON rkt.id_rekat = bd.id_rekat
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = rkt.sd
                WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'RO' AND sm.is_deleted = 'false' AND ( sm.status = '' OR sm.status IS NULL ) 
            ORDER BY rkt.sd, bd.unit_kerja, sm.jenis_validasi, sm.status
        ) SELECT * FROM semulaMenjadi";
        $data = DB::connection('sirekat')->select($sql, [ $tahun, $tahunAngka ]);
        return $data;
    }
    private function getDataSemulaMenjadiRevisiKK(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $sql = $this->baseSQL . " semulaMenjadi AS (
                SELECT sm.id AS id_sm, rkt.id_rekat, rkt.sd, sd.sumberdana, rkt.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja, # id_sm = id semula menjadi
                    sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status, bd.is_draft, bd.kebutuhan_kegiatan, sm.should_verify_by,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 10), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 8), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6), '~~~', -1)
                        ELSE NULL END AS rpdMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 7), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 5), '~~~', -2)
                        ELSE NULL END AS coaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 13), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 12), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -1)
                        ELSE NULL END AS itemCoaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_semula, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekMenjadi,
                    unit.nama AS namaUnit
                FROM tb_semula_menjadi sm
                INNER JOIN baseData bd ON bd.id_rab = sm.id_rab AND sm.jenis_rab = bd.jenis_rab
                INNER JOIN tb_unit_api unit on unit.idunit = bd.unit_kerja
                INNER JOIN rekat rkt ON rkt.id_rekat = bd.id_rekat
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = rkt.sd
                WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'KK' AND sm.is_deleted = 'false' AND ( sm.status = '' OR sm.status IS NULL ) 
            ORDER BY rkt.sd, bd.unit_kerja, sm.jenis_validasi, sm.status
        ) SELECT * FROM semulaMenjadi";
        $data = DB::connection('sirekat')->select($sql, [ $tahun, $tahunAngka ]);
        return $data;
    }
    private function getDataSemulaMenjadiRevisiBreakdown(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $sql = $this->baseSQL . " semulaMenjadi AS (
                SELECT sm.id AS id_sm, rkt.id_rekat, rkt.sd, sd.sumberdana, rkt.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja, # id_sm = id semula menjadi
                    sm.jenis_validasi, sm.jenis_rab, sm.jenis_revisi, sm.jumlah_semula, sm.jumlah_menjadi, sm.status, bd.is_draft, bd.kebutuhan_kegiatan, sm.should_verify_by,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 11), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 9), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 7), '~~~', -1)
                        ELSE NULL END AS rpdSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 11), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 9), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 7), '~~~', -1)
                        ELSE NULL END AS rpdMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 10), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 8), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 6), '~~~', -2)
                        ELSE NULL END AS coaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 10), '~~~', -2)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 8), '~~~', -2)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 6), '~~~', -2)
                        ELSE NULL END AS coaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 14), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 16), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_semula, '~~~', 10), '~~~', -1)
                        ELSE NULL END AS itemCoaSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 14), '~~~', -1)
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 16), '~~~', -1)
                        WHEN bd.jenis_rab = 'PRASARANA' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 10), '~~~', -1)
                        ELSE NULL END AS itemCoaMenjadi,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING( SUBSTRING_INDEX(sm.spek_semula, '~~~', 7), LENGTH(SUBSTRING_INDEX(sm.spek_semula, '~~~', 1)) + 4 )
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING( SUBSTRING_INDEX(sm.spek_semula, '~~~', 3), LENGTH(SUBSTRING_INDEX(sm.spek_semula, '~~~', 1)) + 4 )
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekSemula,
                    CASE
                        WHEN bd.jenis_rab = 'OPERASIONAL' THEN SUBSTRING( SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 7), LENGTH(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 1)) + 4 )
                        WHEN bd.jenis_rab = 'SARANA' THEN SUBSTRING( SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 3), LENGTH(SUBSTRING_INDEX(sm.spek_menjadi, '~~~', 1)) + 4 )
                        WHEN bd.jenis_rab = 'PRASARANA' THEN '1 Paket'
                        ELSE NULL END AS spekMenjadi,
                    unit.nama AS namaUnit
                FROM tb_semula_menjadi sm
                INNER JOIN baseData bd ON bd.id_rab = sm.id_rab AND sm.jenis_rab = bd.jenis_rab
                INNER JOIN tb_unit_api unit on unit.idunit = bd.unit_kerja
                INNER JOIN rekat rkt ON rkt.id_rekat = bd.id_rekat
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = rkt.sd
                WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'BREAKDOWN' AND sm.is_deleted = 'false' AND ( sm.status = '' OR sm.status IS NULL ) 
            ORDER BY rkt.sd, bd.unit_kerja, sm.jenis_validasi, sm.status
        ) SELECT * FROM semulaMenjadi";
        $data = DB::connection('sirekat')->select($sql, [ $tahun, $tahunAngka ]);
        return $data;
    }

    /**
     * Get unverified semulaMenjadi data
     * Uses query builder, caching, and selective column retrieval
     * 
     * @return array Raw unverified semulaMenjadi records
     */
    public function getUnverifiedSemulaMenjadiNotifications(): array {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        
        // Cache key based on tahun to invalidate when year changes
        $cacheKey = "unverified_semula_menjadi_notifications_{$tahun}";
        
        // Cache for 5 minutes (300 seconds)
        // Use Laravel's cache facade for better performance
        return Cache::remember($cacheKey, 300, function () use ($tahun, $tahunAngka) {
            // OPTIMIZED: Use Query Builder with correct JOIN order
            // Join order: sm → baseData (rab) → rekat → sumberdana → unit_api
            // This matches the original CTE structure but optimized
            
            $results = DB::connection('sirekat')->select('tb_semula_menjadi as sm')
                ->select([
                    'rab.unit_kerja',
                    'unit.nama as unit_nama',
                    'rkt.sd',
                    'sd.sumberdana',
                    'sm.jenis_revisi',
                    DB::connection('sirekat')->select('COUNT(*) as total_verifikasi')
                ])
                // STEP 1: Join baseData (UNION of kegiatan, peralatan, gedung)
                // This provides id_rab, jenis_rab, unit_kerja, and id_rekat
                ->join(DB::connection('sirekat')->select("(
                    SELECT rab.id AS id_rab, 'OPERASIONAL' AS jenis_rab, rab.unit_kerja, rab.id_rekat
                    FROM tb_rabkegiatan rab 
                    WHERE rab.is_deleted = 'false'
                    UNION ALL
                    SELECT rab.id AS id_rab, 'SARANA' AS jenis_rab, rab.unit_kerja, rab.id_rekat
                    FROM tb_rabperalatan rab 
                    WHERE rab.is_deleted = 'false'
                    UNION ALL
                    SELECT rab.id AS id_rab, 'PRASARANA' AS jenis_rab, rab.unit_kerja, rab.id_rekat
                    FROM tb_rabgedung rab 
                    WHERE rab.is_deleted = 'false'
                ) as rab"), function($join) {
                    // Join on both id_rab and jenis_rab
                    $join->on('rab.id_rab', '=', 'sm.id_rab')
                         ->on('rab.jenis_rab', '=', 'sm.jenis_rab');
                })
                // STEP 2: Join rekat using id_rekat from baseData
                ->join(DB::connection('sirekat')->select("(
                    SELECT rkt.id AS id_rekat, rkt.sd
                    FROM tb_rekat rkt
                    WHERE rkt.is_deleted = 'false' AND rkt.tahun = '{$tahun}'
                ) as rkt"), 'rkt.id_rekat', '=', 'rab.id_rekat')
                // STEP 3: Join sumberdana using sd from rekat
                ->join(DB::connection('sirekat')->select("(
                    SELECT sd.sumberdana, sd.kd_sumberdana 
                    FROM tb_sumberdana sd 
                    WHERE sd.tahun = {$tahunAngka} AND sd.is_deleted = 'false' AND sd.is_show = 'true'
                ) as sd"), 'sd.kd_sumberdana', '=', 'rkt.sd')
                // STEP 4: Join unit_api using unit_kerja from baseData
                ->join(DB::connection('sirekat')->select("(
                    SELECT idunit, nama 
                    FROM tb_unit_api 
                    WHERE status = 1
                ) as unit"), 'unit.idunit', '=', 'rab.unit_kerja')
                // WHERE conditions - filter unverified records
                ->where('sm.is_deleted', 'false')
                ->where(function($query) {
                    $query->where('sm.status', '')
                          ->orWhereNull('sm.status');
                })
                // GROUP BY for aggregation
                ->groupBy([
                    'rab.unit_kerja',
                    'unit.nama',
                    'rkt.sd',
                    'sd.sumberdana',
                    'sm.jenis_revisi'
                ])
                ->get()
                ->toArray();
            
            return $results;
        });
    }

    /**
     * Mengambil ringkasan data yang masih menunggu verifikasi SPI.
     */
    public function getUnverifiedSpiNotifications(): array {
        ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();

        $queries = [
            $this->buildUnverifiedSpiQuery('tb_rabkegiatan', 'kegiatan', 'rab.unit_kerja', $tahun, $tahunAngka),
            $this->buildUnverifiedSpiQuery('tb_rabperalatan', 'peralatan', 'rab.unit_kerja', $tahun, $tahunAngka),
            $this->buildUnverifiedSpiQuery('tb_rabgedung', 'gedung', 'rab.unit_kerja', $tahun, $tahunAngka),
            $this->buildUnverifiedSpiQuery('tb_rab', 'langganan', 'rkt.unit_kerja', $tahun, $tahunAngka, 'langganan'),
            $this->buildUnverifiedSpiQuery('tb_rab', 'bhp', 'rkt.unit_kerja', $tahun, $tahunAngka, 'bhp'),
        ];

        $query = array_shift($queries);
        foreach ($queries as $unionQuery) {
            $query->unionAll($unionQuery);
        }

        return DB::connection('sirekat')->select()
            ->fromSub($query, 'spi_notifications')
            ->orderBy('jenis')
            ->orderByDesc('total_verifikasi')
            ->get()
            ->toArray();
    }

    /**
     * Menyamakan filter notifikasi SPI untuk seluruh jenis RAB.
     */
    private function buildUnverifiedSpiQuery(
        string $table,
        string $jenis,
        string $unitColumn,
        string $tahun,
        string $tahunAngka,
        ?string $jenisRab = null
    ) {
        $query = DB::connection('sirekat')->select("{$table} as rab")
            ->select([
                DB::connection('sirekat')->select("'{$jenis}' as jenis"),
                "{$unitColumn} as unit_kerja",
                'unit.nama as unit_nama',
                'rkt.sd',
                'sd.sumberdana',
                DB::connection('sirekat')->select('COUNT(*) as total_verifikasi'),
            ])
            ->join('tb_rekat as rkt', 'rkt.id', '=', 'rab.id_rekat')
            ->join('tb_sumberdana as sd', 'sd.kd_sumberdana', '=', 'rkt.sd')
            ->join('tb_unit_api as unit', 'unit.idunit', '=', $unitColumn)
            ->where('rkt.tahun', $tahun)
            ->where('rkt.is_deleted', 'false')
            ->where('rab.is_deleted', 'false')
            ->where('rab.is_draft', 'false')
            ->where('sd.tahun', $tahunAngka)
            ->where('sd.is_deleted', 'false')
            ->where('sd.is_show', 'true')
            ->where('unit.status', 1)
            ->where('rkt.sd', '!=', '41100101')
            ->whereNotNull('rab.id_jenis_belanja')
            ->whereNotNull('rab.nip_ppk')
            ->whereNotNull('rab.kebutuhan_kegiatan')
            ->where('rab.verifikasi_pimpinan_unit', 'Setuju')
            ->where(function ($verificationQuery) {
                $verificationQuery->whereNull('rab.verifikasi_spi')
                    ->orWhere('rab.verifikasi_spi', '')
                    ->orWhere('rab.verifikasi_spi', 'Tolak');
            })
            ->groupBy([
                $unitColumn,
                'unit.nama',
                'rkt.sd',
                'sd.sumberdana',
            ]);

        if ($jenisRab !== null) {
            $query->where('rab.jenis_rab', $jenisRab);
        }

        return $query;
    }
    
    /**
     * Clear cache for unverified notifications
     * Call this method when semulaMenjadi data is updated/verified
     */
    public function clearUnverifiedNotificationsCache(): void {
        try {
            [ "tahun" => $tahun ] = getTahunData();
            $cacheKey = "unverified_semula_menjadi_notifications_{$tahun}";
            Cache::forget($cacheKey);
        } catch (\Exception $e) {
            // Silently fail - cache clearing is not critical
        }
    }
}
