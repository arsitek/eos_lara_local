<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\MasterUnitApi;
use App\Models\RABKEG;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RpdUnitController extends Controller {
    private $baseDataSQL;
    public function __construct() {
    }
    public function index(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->orderByRaw("kd_sumberdana, jenis DESC")->get();
        $rab        = getBaseData("SELECT sd.kd_sumberdana, sd.sumberdana, unit.nama AS nama_unit, rkat.id_rekat, rkat.sub_judul, rkat.itemCoa
                    FROM BaseData rkat
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    WHERE ( rkat.is_deleted = 'false' OR rkat.is_deleted = 0 )  AND rkat.is_deleted_rkt = 'false' AND ( rkat.rpd IS NULL )", $tahun, $tahunAngka);
        $unitkerja  = Rekat::with('unitApi')->select("unit_kerja")->where(["tahun" => $tahun, "is_deleted" => "false"])->distinct()->orderBy('unit_kerja', 'DESC')->get();
        return view('content.laporan.RPD-UNIT.index', compact('unitkerja', 'sumberdana', 'rab'));
    }
    public function getDataRpdNotNull( Request $req ){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $sumberdana = $req->sd;
        $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
        $selectDataMaster = $tahunAngka >= 2026 ? 
                "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan" 
                : "dm.*";
        $params = [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahun, $sumberdana ];
        if ( $tahunAngka < 2026 )
            array_splice( $params, 0, 4 );
        $data = getBaseData("SELECT rkat.rpd, unit.nama AS nama_unit, unit.idunit AS unit_kerja,
                        SUM( CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0)
                        ELSE rkat.jumlah_biaya + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) END ) AS jumlah_biaya,
                        rkat.jumlah_biaya AS jumlah_biaya_usulan
                    FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                    LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                    LEFT JOIN (
                        SELECT id_rab, jenis, SUM(jumlah_tagihan) AS jumlah_tagihan
                        FROM tb_mutasi_percetakan
                        WHERE is_deleted = 'false'
                        GROUP BY id_rab, jenis
                    ) AS relo_sum ON relo_sum.id_rab = rkat.id AND relo_sum.jenis = rkat.rab_type
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rkat.id AND sm.jenis_rab = rkat.rab_type AND sm.is_deleted = 'false' AND sm.status = '' AND sm.jenis_validasi = 'Penambahan'
                    WHERE ( rkat.is_deleted = 'false' OR rkat.is_deleted = 0 )  AND rkat.is_deleted_rkt = 'false'
                GROUP BY rkat.rpd, rkat.unit_kerja_rkt", $tahun, $tahunAngka);
        return $data;
    }
}
