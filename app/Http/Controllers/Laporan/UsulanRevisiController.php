<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Support\Facades\DB;
use App\Models\Datamaster\DuplikasiRkat;
use App\Services\Revisi\RevisiService;
use App\Models\MasterUnitApi;

class UsulanRevisiController extends Controller {
    protected RevisiService $revisiService;
    public function __construct( RevisiService $revisiService ) {
        $this->revisiService = $revisiService;
    }
    public function index(): \Illuminate\View\View {
        $id_unit    = session('unitkerja');
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka, ] = getTahunData();
        
        $sumberdana = Rekat::with(["sumberdana" => function( $q ) use ( $tahunAngka ) {
            $q->where(["is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka ]);
        }])->select("sd")->where([ "tahun" => $tahun ]);

        if ( !in_array( session("role"), ["superadmin", "admin", "Pimpinan USK", "Majelis Wali Amanat", "Pengawasan Internal"] ) )
        $sumberdana = $sumberdana->where([ "unit_kerja" => $id_unit ]);
        $sumberdana = $sumberdana->distinct()->get();
        $nip  = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) { 
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        $dataBackup = DuplikasiRkat::select("id", "keterangan")->where(["tahun" => $tahun, "peruntukan" => "Rev SS"])->get();
        $id_unit    = session('unitkerja');
        $idunit     = null;
        $kodeSd     = null;
        $idBackup   = null;
        $filterTampilan = null;
        return view('content.laporan.USULANREVISI.index', compact("sumberdana", "unitkerja", "tahun", "tahunAngka", "dataBackup", "id_unit", "idunit", "kodeSd", "idBackup", "filterTampilan"));
    }
    public function getData( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka, ] = getTahunData();
            $idunit   = $req->idunit;
            $kodeSd   = $req->kodeSd;
            $idBackup = $req->idBackup;

            if ( is_array( $idunit ) )
                $idunit = implode( ",", $idunit );
            if ( is_array( $idBackup ) )
                $idBackup = implode( ",", $idBackup );
            if ( is_array( $kodeSd ) )
                $kodeSd = implode( ",", $kodeSd );
            
            $startTime = microtime(true);
            $data = []; // Replace with actual data fetching logic
            $dataExisting    = $this->revisiService->getDataExistingRevisi($tahun, $tahunAngka, $idunit, $kodeSd, $idBackup);
            $dataUsulan      = $this->revisiService->getDataUsulanRevisi($tahun, $tahunAngka, $idunit, $kodeSd);
            // $dataPenambahan  = $this->revisiService->getPenambahanItemCoa($tahun, $tahunAngka, $idunit, $kodeSd);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            // Log execution time if needed
            return response()->json([
                "success" => true,
                "executionTime" => round($executionTime, 2) . " detik",
                "data" => [
                    "dataExisting" => $dataExisting,
                    "dataUsulan"   => $dataUsulan,
                    // "dataPenambahan" => $dataPenambahan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Terjadi kesalahan saat mencoba mendapatkan data",
                "error"   => $e->getMessage(),
                "trace"   => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * Export data usulan revisi ke PDF
     */
    public function exportPdf($idunit, $kodeSd, Request $req ) {
        $idBackup       = $req->backup;
        $filterTampilan = $req->filter;
        $namaUnit       = MasterUnitApi::where("idunit", $idunit)->first()?->nama ?? "Universitas Syiah Kuala";
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka, ] = getTahunData();
        $sumberdana = SumberDana::whereIn("kd_sumberdana",explode(",", $kodeSd))
            ->where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])
            ->get();
        return view('content.laporan.USULANREVISI.pdf', compact("tahun", "tahunAngka", "idunit", "kodeSd", "sumberdana", "idBackup", "filterTampilan", "namaUnit"));
    }
}
