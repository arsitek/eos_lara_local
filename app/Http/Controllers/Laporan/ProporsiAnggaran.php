<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\CoaApi;
use Illuminate\Http\Request;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProporsiAnggaran extends Controller {
    public function index(){
        // 📦 Init variable
        $idunit    = session()->get('unitkerja', '');
        $tahun     = session("tahun", "tahun_2025");
        $tahunAngka = explode("_", $tahun)[1];
        $nip = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitKerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        $sumberdana = SumberDana::where([ "is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ])->get();
        return view('content.laporan.PROPORSI.index', compact("unitKerja", "sumberdana", "idunit"));
    }
    public function getProporsiAlokasi($idunit, Request $req){
        try {
            // 📦 Init variable
            $tahun                     = session("tahun", "tahun_2025");
            $tahunAngka                = explode("_", $tahun)[1];
            $sumberdana                = $req->sumberdana;
            $alokasi                   = getAlokasi($idunit, $sumberdana, $tahun);
            $alokasi_terpetakan        = getPaguTerpakai($idunit, $sumberdana, $tahun, false, null)['total'];
            $unitFilterCondition       = $idunit == "semua" ? "" : "AND rab.unit_kerja = '$idunit'";
            $sumberdanaFilterCondition = $sumberdana == "semua" ? "" : "AND rab.sd = '$sumberdana'";
            $semuaAlokasi              = DB::connection('sirekat')->select("SELECT * FROM tb_alokasi rab WHERE rab.tahun = '$tahun' AND rab.is_deleted = 'false' $unitFilterCondition");
            $semuaSumberdana           = SumberDana::where([ "is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ])->get();
            $coa                       = DB::connection('sirekat')->select("WITH CombinedRAB AS (
                SELECT rab.is_deleted AS isDeletedRab, r.is_deleted AS isDeletedRkt, r.tahun,
                    CASE
                        WHEN rab.id_jenis_belanja LIKE '%5159%' THEN LEFT(apicoa.coa, 6)
                        WHEN rab.id_jenis_belanja LIKE '%5259%' THEN LEFT(apicoa.coa, 6)
                        ELSE LEFT(rab.id_jenis_belanja, 6)
                    END AS kd_coa_parent, rab.unit_kerja,r.sd,
                    rab.jumlah_biaya AS jumlah
                FROM tb_rekat r
                INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = r.id
                LEFT JOIN tb_kodefikasi_jenisbelanja jb ON jb.akun = rab.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON apicoa.coa = jb.ekuivalensi
                UNION ALL
                SELECT rab.is_deleted AS isDeletedRab, r.is_deleted AS isDeletedRkt, r.tahun,
                    CASE
                        WHEN rab.id_jenis_belanja LIKE '%5159%' THEN LEFT(apicoa.coa, 6)
                        WHEN rab.id_jenis_belanja LIKE '%5259%' THEN LEFT(apicoa.coa, 6)
                        ELSE LEFT(rab.id_jenis_belanja, 6)
                    END AS kd_coa_parent, rab.unit_kerja, r.sd,
                    rab.jumlah_biaya AS jumlah
                FROM tb_rekat r
                INNER JOIN tb_rabperalatan rab ON rab.id_rekat = r.id
                LEFT JOIN tb_kodefikasi_jenisbelanja jb ON jb.akun = rab.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON apicoa.coa = jb.ekuivalensi
                UNION ALL
                SELECT rab.is_deleted AS isDeletedRab, r.is_deleted AS isDeletedRkt, r.tahun,
                    CASE
                        WHEN rab.id_jenis_belanja LIKE '%5159%' THEN LEFT(apicoa.coa, 6)
                        WHEN rab.id_jenis_belanja LIKE '%5259%' THEN LEFT(apicoa.coa, 6)
                        ELSE LEFT(rab.id_jenis_belanja, 6)
                    END AS kd_coa_parent, rab.unit_kerja, r.sd,
                    rab.jumlah_nilai AS jumlah
                FROM tb_rekat r
                INNER JOIN tb_rabgedung rab ON rab.id_rekat = r.id
                LEFT JOIN tb_kodefikasi_jenisbelanja jb ON jb.akun = rab.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON apicoa.coa = jb.ekuivalensi
                UNION ALL
                SELECT rab.is_deleted AS isDeletedRab, r.is_deleted AS isDeletedRkt, r.tahun,
                    CASE
                        WHEN rab.id_jenis_belanja LIKE '%5159%' THEN LEFT(apicoa.coa, 6)
                        WHEN rab.id_jenis_belanja LIKE '%5259%' THEN LEFT(apicoa.coa, 6)
                        ELSE LEFT(rab.id_jenis_belanja, 6)
                    END AS kd_coa_parent, r.unit_kerja, r.sd,
                    rab.jumlah_biaya AS jumlah
                FROM tb_rekat r
                INNER JOIN tb_rab rab ON rab.id_rekat = r.id
                LEFT JOIN tb_kodefikasi_jenisbelanja jb ON jb.akun = rab.id_jenis_belanja
                LEFT JOIN tb_api_coa apicoa ON apicoa.coa = jb.ekuivalensi
            )
            SELECT sd, sumberdana.sumberdana, unit_kerja, unit.nama AS nama_unit, kd_coa_parent, SUM(jumlah) AS TOTAL_COA
            FROM CombinedRAB rab
            INNER JOIN tb_sumberdana sumberdana ON sumberdana.kd_sumberdana = rab.sd AND sumberdana.is_deleted = 'false'
                AND sumberdana.is_show = 'true' AND sumberdana.tahun = '$tahunAngka'
            INNER JOIN tb_unit_api unit ON unit.idunit = rab.unit_kerja
            WHERE ( (rab.isDeletedRab = 'false' OR rab.isDeletedRab = '0') AND rab.isDeletedRkt = 'false' )
                AND rab.tahun = '$tahun' $unitFilterCondition $sumberdanaFilterCondition
            GROUP BY kd_coa_parent, unit_kerja, sd
            ORDER BY unit_kerja, sd DESC");
            $coa_api = CoaApi::all();
            // ✅ return response
            return response()->json(["success" => true, "data" => [
                "alokasi"            => $alokasi,
                "alokasi_terpetakan" => $alokasi_terpetakan,
                "coa"                => $coa,
                "coa_api"            => $coa_api,
                "semuaAlokasi"       => $semuaAlokasi,
                "semuaSumberdana"    => $semuaSumberdana
            ]], 201);
        } catch ( \Exception $e ) {
            // ⛔ return response
            return response()->json([ "success" => false, "error" => $e->getMessage(), "message" => "Gagal mendapatkan data"], 500);
        }
    }
    public function pdf( Request $req ){
        return view('content.laporan.PROPORSI.pdf');
    }
}
