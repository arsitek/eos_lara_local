<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\SumberDana;
use App\Models\Rekat;
use Illuminate\Http\Request;

class DataErrorController extends Controller {
    public function index(){
        $sumberdana = SumberDana::all();
        $unitkerja  = Rekat::with("unitApi")->select("unit_kerja")->distinct()->get();
        return view('content.laporan.DATAERROR.index', compact("sumberdana", "unitkerja"));
    }
    public function getDataPPKNull( Request $req ) {
        $kd_unit        = $req->kd_unit_kerja;
        $kd_sumber_dana = $req->kd_sumberdana;
        $tahun          = explode("_", session('tahun', 'tahun_2025'))[1];
        $operasional    = getDataOperasionalPPKNull( $kd_unit, $kd_sumber_dana, $tahun, true );
        $prasarana      = getDataPrasaranaPPKNull( $kd_unit, $kd_sumber_dana, $tahun, true );
        $sarana         = getDataSaranaPPKNull( $kd_unit, $kd_sumber_dana, $tahun, true );
        $data           = array_merge($operasional, $prasarana, $sarana);
        return response()->json([
            "data" => $data
        ]);
    }
}
