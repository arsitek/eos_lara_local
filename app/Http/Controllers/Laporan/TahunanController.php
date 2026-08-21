<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\IKK;
use Illuminate\Http\Request;
use App\Models\SumberDana;
use App\Models\MasterUnitApi;
use App\Models\Perkin;
use App\Models\Datamaster\Kro;
use App\Models\Datamaster\ProyeksiPenerimaan;
use App\Models\Rekat;
use App\Models\Datamaster\Ro;
use App\Models\Datamaster\Subkomponen;
use App\Models\Penandatanganan;
use Illuminate\Support\Facades\DB;

class TahunanController extends Controller {
    public function __construct() {
        ini_set('memory_limit', '256M'); // Set memory limit to 256MB
    }
    public function index(){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData( session("tahun", "tahun_2025") );
        $id_unit    = session('unitkerja');
        $role       = session('role');
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->get();
        $unitkerja  = Rekat::select("unit_kerja")->with("unitApi")->orderBy("unit_kerja")->distinct()->get();
        return view('content.laporan.TAHUNAN.index', compact("sumberdana", "unitkerja", "role", "id_unit", "tahun", "tahunAngka"));
    }
    public function storeRO( Request $req ) {
        try {
            $text    = $req->text;
            $idunit  = $req->idunit;
            $kodeIkk = $req->key;
            $tahun   = session("tahun", "tahun_2025");

            $perkin  = Perkin::where([ "unit_kerja" => $idunit, "tahun" => $tahun, "kode_ikk" => $kodeIkk, "is_deleted" => "false" ]);
            // Check if data already exists
            if ( $perkin->exists() ) {
                $perkin->update([ "capaian" => $text ]);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Data perjanjian kinerja tidak ditemukan"
                ], 404);
            }
            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data laporan RO",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data laporan RO"
            ], 500);
        }
    }
    public function getDataProyeksi( Request $req ) {
        try {
            $tahun = session("tahun", "tahun_2025");
            $tahunAngka = explode( "_", $tahun )[1];
            $data  = ProyeksiPenerimaan::where([ "tahun" => $tahunAngka, "unit_kerja" => $req->idunit ])->get();
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data proyeksi penerimaan",
                "data"    => $data,
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data proyeksi penerimaan"
            ], 500);
        }
    }
    public function storeProyeksiPenerimaan( Request $req ) {
        try {
            $text       = $req->text;
            $kodeSd     = $req->key;
            $idunit     = $req->idunit;
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode( "_", $tahun )[1];

            $sd  = ProyeksiPenerimaan::updateOrCreate([ "tahun" => $tahunAngka, "kd_sumberdana" => $kodeSd, "unit_kerja" => $idunit ], [
                "tahun" => $tahunAngka, "kd_sumberdana" => $kodeSd, "proyeksi_penerimaan" => $text, "unit_kerja" => $idunit
            ]);

            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data proyeksi penerimaan sumberdana",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data proyeksi penerimaan sumberdana"
            ], 500);
        }
    }
    public function getBaseData( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $idunit     = $req->idunit;
            $kodeSd     = $req->kodesd;
            $isSemuaSd  = $req->semuaSumberdana;

            if ( !$idunit || !$kodeSd ) {
                return response()->json([
                    "success" => false,
                    "message" => "Idunit atau sumber dana tidak ditemukan."
                ], 400);
            }
            $filterKodeSd = " AND rkat.kd_sumberdana IN ($kodeSd) ";
            $filterUnitKerja = " AND rkat.unit_kerja IN ($idunit) ";
            // if $idunit contains "X" or "semua", then ignore filter unit kerja
            if ( $idunit && ( str_contains($idunit, "X" ) || str_contains($idunit, "semua") ) ) {
                $filterUnitKerja = "";
            }
            if ( $kodeSd == "apbn" ) {
                $filterKodeSd = " AND rkat.kd_sumberdana IN ( SELECT kd_sumberdana FROM sumberdana WHERE kd_sumberdana LIKE '42%' )";
            } if ( $kodeSd == "!apbn" ) {
                $filterKodeSd = " AND rkat.kd_sumberdana IN ( SELECT kd_sumberdana FROM sumberdana WHERE kd_sumberdana LIKE '41%' )";
            } if ( $kodeSd == "semua" || str_contains($kodeSd, "semua") ) {
                $filterKodeSd = "";
            }
            $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                    LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                    LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                    LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                    LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'"
                : "JOIN tb_keg keg ON keg.kode_keg = rkat.kd_rk AND keg.tahun = '$tahunAngka'
                    JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = '$tahunAngka'
                    JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = '$tahunAngka'
                    JOIN tb_sasaran ss ON ss.kode_ss = iku.kode_ss AND ss.tahun = '$tahunAngka'";
            $selectDataMaster = $tahunAngka >= 2026 ?
                    "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan"
                    : "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku. indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.rincian_kegiatan";
            $orderBy = $tahunAngka >= 2026 ? "unit.idunit, ss.kode_ss, iku.kode_ikk, ikv.kode_ikv, keg.kode_keg" : "RIGHT(rkat.kd_rk,8)";
            $query = "SELECT rkat.*, sd.sumberdana, $selectDataMaster, unit.nama as nama_unit,
                        CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                        ELSE rkat.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) ) END AS jumlah_biaya,
                        rkat.jumlah_biaya AS jumlah_biaya_usulan,
                        COALESCE(amprah.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                        COALESCE(amprah.jumlah_realisasi, 0) AS TOTAL_REALISASI,
                        relo.jumlah_tagihan, amprah.is_posting, rt.dipakai as jumlah_pengalihan, rt.sisa as sisa_pengalihan,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = rkat.nip_ppk ) AS nama_ppk,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = rkat.nip_bpp ) AS nama_bpp,
                        total_pagu
                    FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN (
                        SELECT kd_sumberdana, unit_kerja, SUM(coalesce(pagu,0) + coalesce(pagu_tambahan,0) ) AS total_pagu
                        FROM tb_alokasi
                        WHERE is_deleted = 'false' AND tahun = '$tahun'
                        GROUP BY kd_sumberdana, unit_kerja
                    ) a ON a.kd_sumberdana = rkat.kd_sumberdana AND a.unit_kerja = rkat.unit_kerja_rkt
                    LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                    LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rkat.id AND sm.jenis_rab = rkat.rab_type AND sm.jenis_validasi = 'Penambahan' AND sm.is_deleted = 'false' AND sm.status = ''
                    LEFT JOIN ( SELECT id_rab, jenis, SUM(jumlah_tagihan) AS jumlah_tagihan FROM tb_mutasi_percetakan WHERE is_deleted = 'false' GROUP BY id_rab, jenis ) relo
                        ON relo.id_rab = rkat.id AND relo.jenis = rkat.rab_type
                    WHERE ( rkat.is_deleted = 'false' or rkat.is_deleted = 0 ) AND rkat.is_deleted_rkt = 'false' $filterKodeSd $filterUnitKerja AND ( rkat.is_draft = 'false' or rkat.is_draft = 0 ) 
                ORDER BY rkat.kd_sumberdana, $orderBy";
            $result = getBaseData( $query, $tahun, $tahunAngka );
            $pagu   = DB::connection('sirekat')->select("SELECT a.kd_sumberdana, a.unit_kerja, 
                SUM(COALESCE(a.pagu, 0) + COALESCE(a.pagu_tambahan, 0)) AS total_pagu,
                COALESCE(a.pagu_tambahan, 0) AS total_pagu_tambahan
                FROM tb_alokasi a
                WHERE a.is_deleted = 'false' AND a.tahun = ?
                AND EXISTS (
                    SELECT 1
                    FROM tb_rekat rkt
                    WHERE rkt.sd = a.kd_sumberdana AND rkt.is_deleted = 'false' AND rkt.tahun = ? AND rkt.unit_kerja = a.unit_kerja )
                GROUP BY a.kd_sumberdana, a.unit_kerja", [$tahun, $tahun]);
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data",
                "data"    => [
                    "baseData" => $result,
                    "pagu" => $pagu,
                ],
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data"
            ], 500);
        }
    }
    public function getBaseDataBackup( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $idunit     = $req->idunit;
            $kodeSd     = $req->kodesd;

            if ( !$idunit || !$kodeSd ) {
                return response()->json([
                    "success" => false,
                    "message" => "Idunit atau sumber dana tidak ditemukan."
                ], 400);
            }
            $filterKodeSd = " AND backupRkat.sd IN ($kodeSd)";
            $filterUnitKerja = " AND backupRkat.idunit = '$idunit'";
            if ( $idunit == "X" || $idunit == "semua" || str_contains($idunit, "X") ) {
                $filterUnitKerja = "";
            }
            if ( $kodeSd == "semua" )
                $filterKodeSd = "";
            if ( $kodeSd == "apbn" ) {
                $filterKodeSd = " AND backupRkat.kd_sumberdana IN ( SELECT kd_sumberdana FROM sumberdana WHERE kd_sumberdana LIKE '42%' )";
            } if ( $kodeSd == "!apbn" ) {
                $filterKodeSd = " AND backupRkat.kd_sumberdana IN ( SELECT kd_sumberdana FROM sumberdana WHERE kd_sumberdana LIKE '41%' )";
            }
            $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = backupRkat.id_rekat
                    LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                    LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                    LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                    LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'"
                : "JOIN tb_keg keg ON keg.kode_keg = backupRkat.kode_keg AND keg.tahun = '$tahunAngka'
                    JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = '$tahunAngka'
                    JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = '$tahunAngka'
                    JOIN tb_sasaran ss ON ss.kode_ss = iku.kode_ss AND ss.tahun = '$tahunAngka'";
            $selectDataMaster = $tahunAngka >= 2026 ?
                    "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan"
                    : "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku. indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.rincian_kegiatan";

            $queryBackup = "SELECT dr.id AS id_revisi, dr.nama_revisi, unit.nama AS nama_unit, unit.idunit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt,
                    $selectDataMaster, sd.kd_sumberdana, sd.sumberdana, backupRkatDet.*,
                    ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                        THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                    ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                    backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                    COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                    COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI
                FROM tb_backup_rkat backupRkat
                INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                $joinDataMaster
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                INNER JOIN tb_duplikasi_rkat dr ON dr.id = backupRkatDet.id_duplikasi AND dr.id = backupRkat.id_duplikasi
                WHERE 1=1 $filterKodeSd $filterUnitKerja
                AND backupRkat.tahun = '$tahun' AND dr.nama_revisi IS NOT NULL
                GROUP BY dr.nama_revisi, backupRkatDet.id
            ";
            $resultBackup = getBaseData( $queryBackup, $tahun, $tahunAngka );
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data",
                "data"    => [
                    "baseDataBackup" => $resultBackup,
                    $filterKodeSd, $filterUnitKerja
                ],
                $req->all()
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data"
            ], 500);
        }
    }
    public function pdf( Request $req ) {
        $idunit     = $req->idunit;
        $kodeSd     = $req->kodeSd;
        $tahun      = session("tahun", "tahun_2025");
        $tahunAngka = explode( "_", $tahun )[1];
        $unitkerja  = MasterUnitApi::where("idunit", $idunit)->first();
        $sumberdana = SumberDana::where([ "kd_sumberdana" => $kodeSd, "tahun" => $tahunAngka, "is_deleted" => "false", "is_show" => "true"])->get();
        $pttd       = Penandatanganan::where(["unit_kerja" => $idunit, "tahun" => $tahun])->first();
        $bulan      = date("m");
        $tanggal    = date("d");
        return view('content.laporan.TAHUNAN.pdf', compact("idunit", "kodeSd", "tahun", "tahunAngka", "unitkerja", "sumberdana", "pttd", "bulan", "tanggal"));
    }
}
