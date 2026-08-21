<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\Kro;
use App\Models\Datamaster\Subkomponen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterUnitApi;
use App\Models\IKK;
use App\Models\Rekat;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\RABGDG;
use App\Models\SumberDana;

class RpdController extends Controller {
    private $bulan, $tahun;
    public function __construct() {
        $this->bulan = [];
        for ($i=1; $i <= 12; $i++) {
            $strLen = strlen($i);
            if ( $strLen == 1 ) {
                $i = "0".$i;
            }
            $this->bulan[] = $i;
        }
    }
    public function index() {
        // 📦 Init variable
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $id_unit    = session()->get('unitkerja', '');
        $nip = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        $bulan      = $this->bulan;
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->get();
        return view('content.laporan.RPD.index', compact("unitkerja", "id_unit", "bulan", "sumberdana", "tahunAngka"));
    }
    public function indexPdf( Request $req ) {
        // 📦 Init variable
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $idunit     = MasterUnitApi::where(["idunit" => $req->idunit])->first();
        $unitkerja  = Rekat::select("unit_kerja")->with("unitApi")->distinct()->get();
        $bulan      = $this->bulan;
        $sumberdana = SumberDana::all();
        return view('content.laporan.RPD.pdf', compact("unitkerja", "idunit", "bulan", "sumberdana", "tahun", "tahunAngka"));
    }
    public function getRpd( Request $r ) {
        $tahun      = session()->get('tahun', 'tahun_2025');
        $tahunAngka = explode("_", $tahun)[1];
        $idunit     = $r->idunit;
        $rpd        = $r->rpd;
        $sd         = $r->sd;
        $sumberdana = SumberDana::where(["kd_sumberdana" => $sd])->first();
        return response()->json([
            "success" => true,
            "data" => [
                "sumberdana" => $sumberdana,
            ]]
        ,200);
    }
    public function updateRpd( Request $r ){
        try {
            if ( $r->jenis_rab == "rab_kegiatan" ) {
                RABKEG::WHERE(["id" => $r->id_rab])->UPDATE(["rpd" => $r->bulan]);
                return response()->json(["success" => true, "data" => "Berhasil memperbarui rpd rab kegiatan"], 201);
            } else if ( $r->jenis_rab == "rab_peralatan" ) {
                RABPER::WHERE(["id" => $r->id_rab])
                        ->UPDATE(["rpd" => $r->bulan]);
                return response()->json(["success" => true, "data" => "Berhasil memperbarui rpd rab peralatan"], 201);
            } else {
                RABGDG::WHERE(["id" => $r->id_rab])
                        ->UPDATE(["rpd" => $r->bulan]);
                return response()->json(["success" => true, "data" =>
                        "Berhasil memperbarui rpd rab gedung"], 201);
            }
        } catch( \Exception $e ) {
            return response()->json(["success" => true, "error" => $e->getMessage() ], 400);
        }
    }
}
