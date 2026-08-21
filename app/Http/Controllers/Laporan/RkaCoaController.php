<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\Kro;
use Illuminate\Http\Request;
use App\Models\IKK;
use App\Models\Realisasi;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Support\Facades\DB;

class RkaCoaController extends Controller {
    public function index() {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $idunit     = session('unit_kerja');
        $sumberdana = SumberDana::where(["is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ])->get();
        $nip = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        $alokasi    = getPaguTerpakai($idunit, null, $tahun, false, false)['total'];
        return view('content.laporan.RKA.COA.index', compact('unitkerja', 'alokasi','sumberdana', 'tahunAngka'));
    }
    public function getRkaJenisBelanja( Request $req ){
        try {
            $idunit         = $req->idunit;
            $kodeSd         = $req->kd_sumberdana;
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
            $selectDataMaster = $tahunAngka >= 2026 ? 
                "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan" 
                : "dm.*";
            # GET UNIT KERJA AND SUMBER DANA BASED ON USER SELECTION
            $filterUnit = "";
            if ( $idunit != "semua" && $idunit != "" ) {
                $filterUnit = " AND rkat.unit_kerja IN ($idunit) ";
            }
            if ( $kodeSd == "semua" ) {
                $kodeSd = SumberDana::where([ "is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ])->get()->pluck('kd_sumberdana')->implode(',');
            } else if ( in_array($kodeSd, ["ptnbh", "bptnbh"]) ) {
                $kodeSd = SumberDana::where([ "jenis" => $kodeSd, "is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ])->get()->pluck('kd_sumberdana')->implode(',');
            }

            $query = "SELECT rkat.kd_sumberdana, sd.sumberdana, rkat.id_jenis_belanja,
                $selectDataMaster, rkat.unit_kerja_rkt, rkat.jenis_belanja, unit.nama as nama_unit,
                rkat.id_rekat, rkat.sub_judul,
                CASE
                    WHEN COALESCE(amprah.jumlah_amprahan, amprah.jumlah_realisasi) IS NOT NULL AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                    ELSE rkat.jumlah_biaya END AS jumlah_biaya,
                    rkat.jumlah_biaya AS jumlah_biaya_usulan,
                    COALESCE(amprah.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                    COALESCE(amprah.jumlah_realisasi, 0) AS TOTAL_REALISASI
                FROM BaseData rkat
                $joinDataMaster
                JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                LEFT JOIN paket pkt ON pkt.id_rab = rkat.id AND pkt.jenis_rab = rkat.rab_type
                WHERE rkat.kd_sumberdana IN ($kodeSd) AND ( rkat.is_draft = 'false' OR rkat.is_draft = 0) $filterUnit
            ORDER BY rkat.kd_sumberdana";
            $data = getBaseData( $query, $tahun, $tahunAngka, $idunit, $kodeSd );
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data RKA jenis belanja",
                "data"    => [ "data" => $data, ]
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json(["success" => false, "message" => "Gagal mendapatkan data", "error" => $e->getMessage()], 500);
        }
    }
    public function getDanaAlokasi($unitkerja, $tahun){
        $alokasi       = DB::connection('sirekat')->select("SELECT * FROM tb_alokasi WHERE unit_kerja = '$unitkerja'");
        $alokasi_ptnbh = getPaguTerpakai($unitkerja, '4101', $tahun, false, false)['total'];
        return array($alokasi,$alokasi_ptnbh);
    }
}
