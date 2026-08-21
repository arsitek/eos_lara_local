<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\SumberDana;
use Illuminate\View\View;

class RevisiController extends Controller
{
    public function index(): View
    {
        ["tahunAngka" => $tahunAngka] = getTahunData();
        $id_unit = session("unitkerja");
        $sumberdana = SumberDana::where([
            "is_deleted" => "false",
            "tahun" => $tahunAngka,
            "is_show" => "true",
        ])->get();

        return view('content.laporan.REKAP_REVISI.index', compact(
            "tahunAngka",
            "id_unit",
            "sumberdana"
        ));
    }
}