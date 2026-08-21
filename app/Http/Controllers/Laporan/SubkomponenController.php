<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\Kro;
use App\Models\MasterUnitApi;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubkomponenController extends Controller {
    private $baseDataSQL;
    public function __construct(){
        // max 3 minutes
        ini_set('max_execution_time', 180);
        $this->baseDataSQL = "WITH BaseData AS ( SELECT
                rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kodeSd, rkt.tahun, rab.is_deleted,
                rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
                rkt.kd_rk, rab.jumlah_biaya AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                'OPERASIONAL' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
            UNION ALL
            SELECT
                rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kodeSd, rkt.tahun, rab.is_deleted,
                rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
                rkt.kd_rk, rab.jumlah_biaya AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                'SARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
            GROUP BY rab.id
            UNION ALL
            SELECT
                rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kodeSd, rkt.tahun, rab.is_deleted,
                rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
                rkt.kd_rk, rab.jumlah_nilai AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                'PRASARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
            FROM tb_rekat rkt
            JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            GROUP BY rab.id
            ),
            dataMaster AS (
                SELECT DISTINCT keg.kode_keg, keg.rincian_kegiatan, ikv.kode_ikv, ikv.ikv, iku.indikator_kinerja_kegiatan, iku.kode_ikk, kro.sasaran_program, kro.kode_ss
                FROM tb_keg keg
                INNER JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = ?
                INNER JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = ?
                INNER JOIN tb_sasaran kro ON kro.kode_ss = iku.kode_ss AND kro.tahun = ?
            WHERE keg.tahun = ?
            ), pejabat AS (
            SELECT DISTINCT pejabat.nip, pejabat.nama_pejabat, pejabat.jenis
            FROM tb_komitmen pejabat
            WHERE pejabat.is_active = 'true'
        )";
    }
    public function index(){
        $tahun      = session("tahun", "tahun_2025");
        $id_unit    = session('unitkerja');
        $tahunAngka = explode("_", $tahun)[1];
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_deleted" => "false", "is_show" => "true"])->orderByRaw("kd_sumberdana, jenis DESC")->get();
        $nip  = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        return view('content.laporan.SUBKOMPONEN.index', compact('unitkerja', 'id_unit', 'sumberdana'));
    }
    public function indexPdf( Request $req ) {
        $idunit    = $req->idunit;
        $tahun     = explode("_", session()->get('tahun', 'tahun_2025'))[1];
        $unitkerja = MasterUnitApi::where([ "idunit" => $idunit ])->first();
        return view('content.laporan.SUBKOMPONEN.pdf', compact("idunit", "tahun", "unitkerja"));
    }
    public function getRka( $idunit, $kodeSd, Request $req){
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $filter     = $req->filterdata;
            $dataMaster = Kro::with(['ro' => function($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka);
            }, 'ro.ikv' => function($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka);
            }, 'ro.ikv.subkomponen' => function($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka); // Filter subkomponen, on the component
            }])->where('tahun', $tahunAngka)->get();

            // apply filter
            $filterAmprahCondition = '';
            $filterUnitCondition   = '';
            if ( $kodeSd === "!apbn" ) {
                $listKodeSd = SumberDana::where([ "is_deleted" =>"false", "tahun" => $tahunAngka, "jenis" => "ptnbh", "is_show" => "true" ])->pluck("kd_sumberdana")->toArray();
                $kodeSd = array_map(function($item) { return $item; }, $listKodeSd);
                $kodeSd = implode(",", $kodeSd);
            } elseif ( $kodeSd === "apbn" ) {
                $listKodeSd = SumberDana::where([ "is_deleted" =>"false", "tahun" => $tahunAngka, "jenis" => "bptnbh", "is_show" => "true" ])->pluck("kd_sumberdana")->toArray();
                $kodeSd = array_map(function($item) { return $item; }, $listKodeSd);
                $kodeSd = implode(",", $kodeSd);
            }
            $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = rkt.kd_rk";
            $selectDataMaster = $tahunAngka >= 2026 ? 
                "ss.kode_ss, ss.sasaran_program, iku.kode_ikk, iku.indikator_kinerja_kegiatan, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan" 
                : "dm.*";
            if ($filter == '!realisasi') {
                $filterAmprahCondition = " AND amprah.jumlah_realisasi IS NULL ";
            } elseif ($filter == 'realisasi') {
                $filterAmprahCondition = " AND ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) ";
            }
            if ( $idunit !== 'semua' ) {
                $filterUnitCondition = " AND rkat.unit_kerja_rkt = '$idunit'";
            }
            $baseDataKeg = getBaseData("SELECT rkat.rpd, unit.nama AS nama_unit, unit.idunit AS unit_kerja,  $selectDataMaster, sd.kd_sumberdana AS kode_sd, sd.sumberdana,
                        SUM(CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0)
                        ELSE rkat.jumlah_biaya + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) END ) AS jumlah_biaya,
                        SUM( COALESCE(amprah.jumlah_amprahan, 0) ) AS TOTAL_AMPRAH,
                        SUM( COALESCE(amprah.jumlah_realisasi, 0) ) AS TOTAL_REALISASI,
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
                    WHERE ( rkat.is_deleted = 'false' OR rkat.is_deleted = 0 )  AND rkat.is_deleted_rkt = 'false' AND rkat.kd_sumberdana IN ($kodeSd) AND rkat.verifikasi_pimpinan_unit = 'Setuju'
                    $filterUnitCondition $filterAmprahCondition
                GROUP BY rkat.kd_rk", $tahun, $tahunAngka);
            $sumberdana = SumberDana::where(["kd_sumberdana" => $kodeSd, "is_deleted" => "false"])->first();
            $sumSisaSaldo = sumSisaSaldo($idunit, $kodeSd, $tahun);
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data RKA",
                "data"    => [
                    "baseDataKeg" => $baseDataKeg,
                    "sumberdana"  => $sumberdana,
                    "dataMaster"  => $dataMaster,
                    "sumSisaSaldo"=> $sumSisaSaldo,
                ]
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data RKA"
            ], 500);
        }
    }
}
