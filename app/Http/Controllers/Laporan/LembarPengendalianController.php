<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LembarPengendalianController extends Controller {
    public function index(): View {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $deletedRealisasi = $this->getRealisasiDeleted();
        $data = getBaseData("SELECT rkat.rab_type AS jenisRab, sd.kd_sumberdana AS kodeSd, sd.sumberdana, unit.idunit, unit.nama, rkat.id_rekat AS idRekat, rkat.id AS idItemCoa, rkat.itemCoa,
            CASE
                WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rkat.jumlah_biaya END AS jumlah_biaya_akhir,
            amprah.jumlah_amprahan, amprah.jumlah_realisasi,
            rkat.jumlah_biaya
            FROM BaseData rkat
            JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk
            JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
            JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
            LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
            LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
            LEFT JOIN tb_mutasi_percetakan relo ON relo.id_rab = rkat.id AND relo.jenis = rkat.rab_type AND relo.is_deleted = 'false'
            WHERE rkat.is_deleted = 'false'
            AND rkat.is_deleted_rkt = 'false'
            AND ( rkat.jumlah_biaya < (amprah.jumlah_amprahan + amprah.jumlah_realisasi) )
            GROUP BY rkat.id_mak
            ORDER BY sd.kd_sumberdana, unit.idunit, SUBSTR(rkat.kd_rk, 4, 8), rkat.id_rekat, rkat.id_mak;", $tahun, $tahunAngka);
        return view('content.laporan.PENGENDALIAN.index', compact('data', 'deletedRealisasi'));
    }
    public function getHistories( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $idItemCoa  = $req->id;
            $jenisRab   = $req->jenis_rab;
            $data       = getBaseData("SELECT
                (CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND
                    backupRkatDet.sisa_pengalihan IS NOT NULL
                    THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) +
                        backupRkatDet.sisa_pengalihan
                    ELSE backupRkatDet.jumlah_biaya END)     AS TOTAL,
                COALESCE(backupRkatDet.jumlah_amprahan, 0)   AS TOTAL_AMPRAH,
                COALESCE(backupRkatDet.jumlah_realisasi, 0)  AS TOTAL_REALISASI,
                backupRkatDet.id_duplikasi, duplikasi.keterangan, backupRkatDet.rab_type
            FROM baseDataBackup backupRkatDet
            INNER JOIN tb_duplikasi_rkat duplikasi ON duplikasi.id = backupRkatDet.id_duplikasi
            WHERE duplikasi.keterangan LIKE '%$tahunAngka%' AND backupRkatDet.id = ? AND backupRkatDet.rab_type = ?
            GROUP BY duplikasi.id
            ORDER BY duplikasi.id", $tahun, $tahunAngka, null, null, [ $idItemCoa, $jenisRab ]);
            return response()->json([
                "success" => true,
                "data"    => $data,
                "message" => "Berhasil mendapatkan data histori lembar kendali."
            ]);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"  => $e->getMessage(),
                "message" => "Gagal mendapatkan data histori pengendalian."
            ]);
        }
    }
    private function getRealisasiDeleted(): array {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        return DB::connection('sirekat')->select("SELECT * FROM (SELECT rkt.sd, sd.sumberdana,
                                unit.idunit, unit.nama, rkt.id AS id_rekat,
                                rab.id,
                                rab.kebutuhan_kegiatan,
                                'OPERASIONAL'                        AS jenis_rab,
                                CASE
                                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND
                                        rt.dipakai IS NOT NULL
                                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                                    ELSE rab.jumlah_biaya
                                    END                              AS jumlah_biaya,
                                COALESCE(amprah.jumlah_amprahan, 0)  AS jumlah_amprahan,
                                COALESCE(amprah.jumlah_realisasi, 0) AS jumlah_realisasi
                        FROM tb_rekat rkt
                                JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                                JOIN tb_unit_api unit ON unit.idunit = rab.unit_kerja
                                JOIN tb_sumberdana sd ON sd.is_deleted = 'false' AND sd.is_show = 'true' AND sd.tahun = '$tahunAngka' AND sd.kd_sumberdana = rkt.sd
                                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'operasional'
                        WHERE rkt.id = rab.id_rekat
                            AND rkt.tahun = '$tahun'
                            AND rab.is_deleted = 'true'
                        UNION ALL
                        SELECT rkt.sd, sd.sumberdana,
                                unit.idunit, unit.nama, rkt.id AS id_rekat,
                                rab.id,
                                rab.kebutuhan_kegiatan,
                                'SARANA'                             AS jenis_rab,
                                CASE
                                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                                    ELSE rab.jumlah_biaya
                                    END                              AS jumlah_biaya,
                                COALESCE(amprah.jumlah_amprahan, 0)  AS jumlah_amprahan,
                                COALESCE(amprah.jumlah_realisasi, 0) AS jumlah_realisasi
                        FROM tb_rekat rkt
                                JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                                LEFT JOIN tb_realisasi amprah
                                            ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                                JOIN tb_unit_api unit ON unit.idunit = rab.unit_kerja
                                JOIN tb_sumberdana sd ON sd.is_deleted = 'false' AND sd.is_show = 'true' AND sd.tahun = '$tahunAngka' AND sd.kd_sumberdana = rkt.sd
                                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'sarana'
                        WHERE rkt.id = rab.id_rekat
                            AND rkt.tahun = '$tahun'
                            AND rab.is_deleted = 'true'
                        UNION ALL
                        SELECT rkt.sd, sd.sumberdana,
                                unit.idunit, unit.nama, rkt.id AS id_rekat,
                                rab.id,
                                rab.kebutuhan_kegiatan,
                                'PRASARANA'                          AS jenis_rab,
                                CASE
                                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND
                                        rt.dipakai IS NOT NULL
                                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                                    ELSE rab.jumlah_nilai
                                    END                              AS jumlah_biaya,
                                COALESCE(amprah.jumlah_amprahan, 0)  AS jumlah_amprahan,
                                COALESCE(amprah.jumlah_realisasi, 0) AS jumlah_realisasi
                        FROM tb_rekat rkt
                                JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
                                LEFT JOIN tb_realisasi amprah
                                            ON amprah.id_mak = rab.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                                JOIN tb_unit_api unit ON unit.idunit = rab.unit_kerja
                                JOIN tb_sumberdana sd ON sd.is_deleted = 'false' AND sd.is_show = 'true' AND sd.tahun = '$tahunAngka' AND sd.kd_sumberdana = rkt.sd
                                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'prasarana'
                        WHERE rkt.id = rab.id_rekat
                            AND rkt.tahun = '$tahun'
                        AND rab.is_deleted = 'true') AS TOTAL_SD
        WHERE (jumlah_amprahan <> 0 OR jumlah_realisasi <> 0)");
    }
}
