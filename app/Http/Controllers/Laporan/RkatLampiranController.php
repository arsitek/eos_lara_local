<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\KroRkat;
use App\Models\SumberDana;
use App\Models\Rekat;
use App\Models\Penandatanganan;
use App\Models\Datamaster\SubkomponenRkat;
use App\Models\Datarevisi\RkatModifikasi;
use App\Models\MasterUnitApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 use App\Events\UserPerformedAction;

class RkatLampiranController extends Controller {
    protected $allowedYears = [];
    protected $baseSql = "";

    public function __construct(){
        $this->baseSql = "WITH BaseData AS (SELECT
                    rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rab.unit_kerja, rkt.is_deleted as is_deleted_rkt,
                    rkt.kd_rk,
                    rab.id_mak,
                    rab.id AS rab_id, rab.verifikasi_pimpinan_unit,
                    rab.jumlah_biaya AS jumlah_biaya,
                    'OPERASIONAL' AS rab_type
                FROM tb_rekat rkt
                JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                UNION ALL -- gabung engan rabperalatan
                SELECT
                    rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rab.unit_kerja, rkt.is_deleted as is_deleted_rkt,
                    rkt.kd_rk,
                    rab.id_mak,
                    rab.id AS rab_id, rab.verifikasi_pimpinan_unit,
                    rab.jumlah_biaya AS jumlah_biaya,
                    'SARANA' AS rab_type
                FROM tb_rekat rkt
                JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                UNION ALL -- gabung dengan rab gedung
                SELECT
                    rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rab.unit_kerja, rkt.is_deleted as is_deleted_rkt,
                    rkt.kd_rk,
                    rab.id_mak,
                    rab.id AS rab_id, rab.verifikasi_pimpinan_unit,
                    rab.jumlah_nilai AS jumlah_biaya,
                    'PRASARANA' AS rab_type
                FROM tb_rekat rkt
                JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
                ), dataMaster AS (SELECT DISTINCT
                    kegRkat.kode_keg,
                    kegRkat.kode_keg_rkat, kegRkat.keg_rkat,
                    ikvRkat.kode_ikv_rkat, ikvRkat.ikv_rkat,
                    ikuRkat.kode_ro_rkat, ikuRkat.ro_rkat,
                    kroRkat.kode_ss_rkat, kroRkat.sasaran_rkat
                FROM tb_keg_rkat kegRkat
                JOIN tb_ikv_rkat ikvRkat ON ikvRkat.kode_ikv_rkat = kegRkat.kode_ikv_rkat AND ikvRkat.tahun = ?
                JOIN tb_iku_rkat ikuRkat ON ikuRkat.kode_ro_rkat = ikvRkat.kode_ro_rkat AND ikuRkat.tahun = ?
                JOIN tb_sasaran_rkat kroRkat ON kroRkat.kode_ss_rkat = ikuRkat.kode_ss_rkat AND kroRkat.tahun = ?
                WHERE kegRkat.tahun = ?
        )";
        $this->allowedYears = ["Indikatif_2025", "Definitif_2025", "Definitif_2026"];
    }
    public function index( Request $req ){
        $tahun      = session("tahun", "tahun_2025");
        $tahunAngka = explode("_", $tahun)[1];
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->orderBy("kd_sumberdana")->get();
        $nip = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        return view('content.laporan.RKAT.LAMPIRAN.index', compact("sumberdana", "unitkerja"));
    }
    public function pdf( Request $req ){
        $tahun      = session("tahun", "tahun_2025");
        $tahunAngka = explode("_", $tahun)[1];
        $idunit     = $req->idunit;
        $kodeSd     = $req->sumberdana;
        $currentSd  = SumberDana::where(["is_deleted" => "false", "kd_sumberdana" => $kodeSd, "tahun" => $tahunAngka ])->first();
        $pttd       = Penandatanganan::where(["unit_kerja" => $idunit, "tahun" => $tahun ])->first();
        $unitkerja  = MasterUnitApi::where(["idunit" => $idunit])->first();
        $listKodeSd = null;
        if ( in_array( $kodeSd, [ "ptnbh", "bptnbh" ] ) ) {
            $listKodeSd = DB::connection('sirekat')->select("SELECT *
                FROM tb_sumberdana sd
            WHERE sd.tahun = '$tahunAngka' AND sd.jenis = ? AND LENGTH(sd.kd_sumberdana) = 8 and sd.is_deleted = 'false'", [ $kodeSd ]);
        } else {
            $listKodeSd = DB::connection('sirekat')->select("SELECT sd.kd_sumberdana as kd_parent, sd.sumberdana as nama_parent, child.*
                FROM tb_sumberdana sd
                INNER JOIN tb_sumberdana child on sd.id = child.id_parent and child.tahun = sd.tahun
            WHERE sd.tahun = '$tahunAngka' AND sd.kd_sumberdana = '$kodeSd' and sd.is_deleted = 'false' and child.is_deleted = 'false'");
        }
        if ( $idunit === "semua_unit" ) {
            $unitkerja = (object) ["nama" => "Semua Unit"];
        }
        return view('content.laporan.RKAT.LAMPIRAN.pdf', compact("currentSd", "idunit", "tahunAngka", "unitkerja", "pttd", "listKodeSd"));
    }
    public function getRkatLampiran( Request $req ){
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1];
            $idunit     = $req->idunit;
            $kodeSd     = $req->sumberdana;
            $filter     = $req->filter;

            if ( !in_array($tahun, $this->allowedYears) )
                return response()->json(["success" => false, "message" => "Data tidak ditemukan."], 400);


            $baseSQL     = $this->baseSql;
            $filterUnit  = " AND rkat.unit_kerja = '$idunit' ";
            $filterVerif = " AND rkat.verifikasi_pimpinan_unit = 'Setuju' ";
            $filterSd    = " AND rkat.kd_sumberdana = '$kodeSd' ";

            if ( $idunit == 'semua_unit' )
                $filterUnit = "";
            if ( $kodeSd == "ptnbh" ) {
                $filterSd = " AND rkat.kd_sumberdana LIKE '41%' ";
            } else if ( $kodeSd == "bptnbh" ) {
                $filterSd = " AND rkat.kd_sumberdana LIKE '42%' ";
            }
            if ( $tahunAngka == "2026" )
                $tahunAngka = "2025"; // karena 2026 data master belum ada, jadinya pakai 2025

            $data = DB::connection('sirekat')->select($baseSQL . " SELECT
                rkat.kd_sumberdana, dm.*,
                SUM( CASE
                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rkat.jumlah_biaya END ) AS TOTAL_KEG,
                sd8.kd_sumberdana AS kodeSd8, sd8.sumberdana AS sumberDana8,
                sd6.kd_sumberdana AS kodeSd6, sd6.sumberdana AS sumberDana6,
                sd4.kd_sumberdana AS kodeSd4, sd4.sumberdana AS sumberDana4,
                sd2.kd_sumberdana AS kodeSd2, sd2.sumberdana AS sumberDana2,
                SUM(COALESCE(amprah.jumlah_amprahan, 0) ) AS TOTAL_AMPRAH,
                SUM(COALESCE(amprah.jumlah_realisasi, 0) )AS TOTAL_REALISASI,
                COUNT(*) AS JUMLAH_KEG
                FROM BaseData rkat
                JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rkat.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rkat.rab_id AND rt.jenis_rab = rkat.rab_type
                INNER JOIN tb_sumberdana_8 sd8 ON sd8.kd_sumberdana = rkat.kd_sumberdana AND sd8.tahun = '2025'
                INNER JOIN tb_sumberdana_6 sd6 ON sd6.kd_sumberdana = sd8.kd_parent AND sd6.tahun = '2025'
                INNER JOIN tb_sumberdana_4 sd4 ON sd4.kd_sumberdana = sd6.kd_parent AND sd4.tahun = '2025'
                INNER JOIN tb_sumberdana_2 sd2 ON sd2.kd_sumberdana = sd4.kd_parent AND sd2.tahun = '2025'
                WHERE rkat.tahun = ? AND rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false' $filterUnit $filterSd $filterVerif
                GROUP BY rkat.kd_sumberdana, dm.kode_keg_rkat ORDER BY rkat.kd_sumberdana, dm.kode_keg_rkat ASC", [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahun ]);
            return response()->json([
                "success" => true,
                "data"    => [
                    "data" => $data
                ],
                "message" => "Berhasil mendapatkan data lampiran RKAT"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data lampiran RKAT"
            ], 500);
        }
    }
    public function getRekapSemuaUnit( Request $req ){
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1];
            $kodeSd     = $req->sumberdana;
            $baseSql    = $this->baseSql;
            $listKodeSd = null;

            if ( in_array( $kodeSd, [ "ptnbh", "bptnbh" ] ) ) {
                $listKodeSd = DB::connection('sirekat')->select("SELECT *
                    FROM tb_sumberdana sd
                WHERE sd.tahun = '$tahunAngka' AND sd.jenis = ? AND LENGTH(sd.kd_sumberdana) = 8 and sd.is_deleted = 'false'", [ $kodeSd ]);
            } else {
                $listKodeSd = DB::connection('sirekat')->select("SELECT sd.kd_sumberdana as kd_parent, sd.sumberdana as nama_parent, child.*
                    FROM tb_sumberdana sd
                    INNER JOIN tb_sumberdana child on sd.id = child.id_parent and child.tahun = sd.tahun
                WHERE sd.tahun = '$tahunAngka' AND sd.kd_sumberdana = '$kodeSd' and sd.is_deleted = 'false' and child.is_deleted = 'false'");
            }
            $listSd = [ $kodeSd ];
            if ( count( $listKodeSd ) > 0 ) {
                $listSd = [];
                $listSd = array_map( function( $sd ) {
                    return $sd->kd_sumberdana;
                }, $listKodeSd);
            }
            $data    = DB::connection('sirekat')->select("$baseSql SELECT
                    rkat.kd_sumberdana, rkat.unit_kerja, sd.sumberdana,
                    SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                        ELSE rkat.jumlah_biaya END
                    ) AS TOTAL,
                    SUM(COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0)) AS TOTAL_AMPRAH,
                    COUNT(DISTINCT rkat.rab_id) AS jumlahKeg,
                    unit.nama AS nama_unit
                FROM BaseData rkat
                JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja
                JOIN tb_sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana AND sd.tahun = ? AND sd.is_deleted = 'false' AND sd.is_show = 'true'
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rkat.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rkat.rab_id AND rt.jenis_rab = rkat.rab_type
                WHERE
                    rkat.tahun = ?
                    AND rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false'
                    AND rkat.kd_sumberdana IN ('". implode("','", $listSd ) ."') AND rkat.verifikasi_pimpinan_unit = 'Setuju'
                GROUP BY rkat.unit_kerja, rkat.kd_sumberdana ORDER BY rkat.kd_sumberdana", [ $tahunAngka, $tahun ]);
            return response()->json([
                "success" => true,
                "data"    => $data,
                "message" => "Berhasil mendapatkan data rekap lampiran RKAT"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data rekap lampiran RKAT"
            ], 500);
        }
    }
    public function getParentSumberdana( Request $req ) {
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1];
            $kodeSd     = $req->sumberdana;
            $idunit     = $req->idunit;
            $modifikasi = $req->modifikasi ?: 0;

            $filterUnit = " AND rkat.unit_kerja = '$idunit' ";
            $filterUnitModif = " AND rm.idunit = '$idunit'";
            if ( $idunit == 'semua_unit' ) {
                $filterUnit = "";
                $filterUnitModif = "";
            }

            $SumSd6 = DB::connection('sirekat')->select("$this->baseSql SELECT
                    parent.kd_sumberdana,
                    SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                        ELSE rkat.jumlah_biaya END
                    ) AS TOTAL_SD,
                    COALESCE ( (SELECT COALESCE(SUM(rm.total), 0) AS total FROM rkatModif rm
                        WHERE LEFT(rm.kd_sumberdana,6) = LEFT(rkat.kd_sumberdana,6)
                        GROUP BY LEFT(rm.kd_sumberdana,6)
                    ), 'not-found' ) AS TOTAL_MODIF,
                    SUM(COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0)) AS TOTAL_AMPRAH,
                    COUNT(DISTINCT rkat.rab_id) AS jumlahKeg
                FROM BaseData rkat
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rkat.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rkat.rab_id AND rt.jenis_rab = rkat.rab_type
                INNER JOIN tb_sumberdana sd on sd.kd_sumberdana = rkat.kd_sumberdana and sd.tahun = ? AND sd.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent on sd.id_parent = parent.id and parent.tahun = sd.tahun AND parent.is_deleted = 'false'
                WHERE
                    rkat.tahun = ?
                    AND rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false'
                    $filterUnit
                    AND rkat.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY parent.kd_sumberdana", [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $modifikasi, $tahun, $tahunAngka, $tahun]);
            $SumSd4 = DB::connection('sirekat')->select("$this->baseSql SELECT
                    parent4.kd_sumberdana,
                    SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                        ELSE rkat.jumlah_biaya END
                    ) AS TOTAL_SD,
                    COALESCE ( (SELECT COALESCE(SUM(rm.total), 0) AS total FROM rkatModif rm
                        WHERE LEFT(rm.kd_sumberdana,4) = LEFT(rkat.kd_sumberdana,4)
                        GROUP BY LEFT(rm.kd_sumberdana,4)
                    ), 'not-found' ) AS TOTAL_MODIF,
                    SUM(COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0)) AS TOTAL_AMPRAH,
                    COUNT(DISTINCT rkat.rab_id) AS jumlahKeg
                FROM BaseData rkat
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rkat.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rkat.rab_id AND rt.jenis_rab = rkat.rab_type
                INNER JOIN tb_sumberdana sd on sd.kd_sumberdana = rkat.kd_sumberdana and sd.tahun = ? AND sd.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent on sd.id_parent = parent.id and parent.tahun = sd.tahun AND parent.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent4 on parent.id_parent = parent4.id and parent4.tahun = sd.tahun AND parent.is_deleted = 'false'
                WHERE
                    rkat.tahun = ?
                    AND rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false'
                    $filterUnit
                    AND rkat.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY parent4.kd_sumberdana", [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $modifikasi, $tahun, $tahunAngka, $tahun ]);
            $SumSd2 = DB::connection('sirekat')->select("$this->baseSql SELECT
                    parent2.kd_sumberdana,
                    SUM( CASE
                        WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                        ELSE rkat.jumlah_biaya END
                    ) AS TOTAL_SD,
                    COALESCE ( (SELECT COALESCE(SUM(rm.total), 0) AS total FROM rkatModif rm
                        WHERE LEFT(rm.kd_sumberdana,2) = LEFT(rkat.kd_sumberdana,2)
                        GROUP BY LEFT(rm.kd_sumberdana,2)
                    ), 'not-found' ) AS TOTAL_MODIF,
                    SUM(COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0)) AS TOTAL_AMPRAH,
                    COUNT(DISTINCT rkat.rab_id) AS jumlahKeg
                FROM BaseData rkat
                LEFT JOIN tb_realisasi amprah ON amprah.id_mak = rkat.id_mak AND amprah.is_deleted = 'false' AND amprah.is_posting = 'true'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rkat.rab_id AND rt.jenis_rab = rkat.rab_type
                INNER JOIN tb_sumberdana sd on sd.kd_sumberdana = rkat.kd_sumberdana and sd.tahun = ? AND sd.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent on sd.id_parent = parent.id and parent.tahun = sd.tahun AND parent.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent4 on parent.id_parent = parent4.id and parent4.tahun = sd.tahun AND parent.is_deleted = 'false'
                INNER JOIN tb_sumberdana parent2 on parent4.id_parent = parent2.id and parent2.tahun = sd.tahun AND parent.is_deleted = 'false'
                WHERE
                    rkat.tahun = ?
                    AND rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false'
                    $filterUnit
                    AND rkat.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY parent2.kd_sumberdana", [ $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $modifikasi, $tahun, $tahunAngka, $tahun ]);
            return response()->json([
                "success" => true,
                "data"    => [
                    "sumSd6"      => $SumSd6,
                    "sumSd4"      => $SumSd4,
                    "sumSd2"      => $SumSd2,
                ],
                "message" => "Berhasil mendapatkan data rekap lampiran RKAT"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data rekap lampiran RKAT"
            ], 500);
        }
    }
    public function storeModif( Request $req ){
        try {
            $tahun      = session("tahun", "tahun_2025");
            $data       = json_decode($req->data, true);
            $modifikasi = $data["0"]["modifikasi"] ?? 0;

            if ( !isset($data) || !is_array($data) || empty($data) ) { // Is there any data ???
                return response()->json([ "success" => false, "message" => "Data tidak ditemukan" ], 400);
            }
            DB::connection('sirekat')->select( function() use ( $data, $tahun ) {
                foreach ( $data as $item ) {
                    $kodeKeg   = $item["kodeKeg"];
                    $total     = $item["total"];
                    $idunit    = $item["idunit"];
                    $kodeSd    = $item["kodeSd"];
                    $rev       = $item["modifikasi"];
                    $jumlahKeg = $item["jumlahKeg"];

                    RkatModifikasi::updateOrCreate([
                        "idunit"        => $idunit,
                        "kd_sumberdana" => $kodeSd,
                        "kode_keg_rkat" => $kodeKeg,
                        "jumlah_keg"    => $jumlahKeg,
                        "rev"           => $rev,
                        "tahun"         => $tahun
                    ], [ "total" => $total ]);
                }
            });

            event(new UserPerformedAction("84", session("id_role"), "Memodifikasi Data RKAT",
                "Memodifikasi data pada lampiran RKAT, modifikasi ke-$modifikasi", $req->ip(), $req->userAgent(), "-", "-", "-", "VIEW"
            ));
            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data rekap lampiran RKAT"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data rekap lampiran RKAT",
                json_decode($req->data, true),
                "trace" => $e->getTrace()
            ], 500);
        }
    }
    public function checkModif( Request $req ) {
        try {
            $modifikasi = $req->data;
            $tahun      = session("tahun", "tahun_2025");

            $isExists = RkatModifikasi::where([
                "idunit"        => $req->idunit,
                "tahun"         => $tahun,
                "rev"           => $modifikasi,
                "is_deleted"    => "false"
            ])->exists();

            if ( $isExists )
                return response()->json([ "success" => true, "message" => "Data rekap lampiran RKAT ditemukan", "data" => $isExists ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Pemeriksaan data lampiran RKAT tidak berhasil"
            ], 500);
        }
    }
    public function getModifiedData( Request $req ) {
        try {
            $modifikasi = $req->data;
            $tahun      = session("tahun", "tahun_2025");
            $idunit     = $req->idunit;
            $kodeSd     = $req->kodeSd;

            $queryWhere = [ "tahun" => $tahun, "is_deleted" => "false", "idunit" => $idunit ]; // default where
            if ( !in_array( $kodeSd, [ "ptnbh", "bptnbh" ] ) )
                $queryWhere["kd_sumberdana"] = $kodeSd;

            $data = RkatModifikasi::where($queryWhere)->select([ 'rev',
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as createdAt')
            ])->groupBy("rev")->orderBy("rev", "asc")->get();

            return response()->json([ "success" => true, "data" => [
                "data" => $data, "count" => count($data), $req->all()
            ], "message" => "Berhasil mendapatkan data lampiran RKAT modifikasi" ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data lampiran RKAT modifikasi"
            ], 500);
        }
    }
}
