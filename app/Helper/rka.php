<?php

use App\Events\UserPerformedAction;
use App\Models\Datapaket\Paket;
use App\Models\IKK;
use App\Models\Realisasi;
use App\Models\Rekat;
use Illuminate\Support\Facades\Http;

function importRealisasi( $cronjob = null ){
    try {
        $tahun = [ "2026" ];
        foreach( $tahun as $thn ) {
            $res = Http::withoutVerifying()->get("https://simkeu-ptnbh.usk.ac.id:7000/common/realisasi-tahunan/".$thn)->json()["data"];
            foreach( $res as $item ){
                $kd_rk = null;
                $rekat = null;
                if ($item["sumber_api"] == "SAPRAS_V3") {
                    $rekat = Paket::where(["id_mak" => $item["id_rekat"]])->first();
                    if ($rekat) {
                        $kd_rk = $rekat->kode_keg;
                    }
                }
                if ($item["sumber_api"] == "OPERASIONAL_V2") {
                    $rekat = Rekat::where("id", $item["id_rekat"])->first();
                    if ($rekat) {
                        $kd_rk = $rekat->kd_rk;
                    }
                }
                if ( $kd_rk ) {
                    if ( $item["is_deleted"] === false ) {
                        Realisasi::updateOrCreate([ "id_mak" => $item["id_mak"] ],[
                            "id_rekat"      => $item["id_rekat"],
                            "id_mak"        => $item["id_mak"],
                            "idunit"        => $item["kd_unit_kerja"],
                            "kd_sumberdana" => $item["kd_sumber_dana"],
                            "kd_rk"         => $kd_rk,
                            "coa"           => $item["kd_coa"],
                            "rpd"           => $item["rpd"],
                            "nip_ppk"      => $item["nip_ppk"],
                            "nip_bpp"      => $item["nip_bpp"],
                            "jumlah_biaya" => $item["total_biaya_kegiatan"],
                            "jumlah_realisasi" => $item["jumlah_realisasi"],
                            "jumlah_amprahan" => $item["jumlah_amprahan"],
                            "is_deleted" => "false",
                            "is_posting" => $item["is_posting"],
                            "tahun" => $thn,
                            "nama_pumk"     => $item["nama_pumk"],
                            "tanggal_bayar" => $item["tanggal_bayar"]
                        ]);
                    }
                }
            }
            // ✅ collect all id_mak from the API response & current database
            $id_mak_simkeu = array_map(function($item) {
                return (int)$item["id_mak"];
            }, $res);
            $id_mak_sirekat = Realisasi::where([ "is_deleted" => "false", "tahun" => $thn ])->pluck('id_mak')->toArray();
            $id_mak_to_delete = array_diff($id_mak_sirekat, $id_mak_simkeu);
            // ✅ Delete those records from the database
            Realisasi::whereIn('id_mak', $id_mak_to_delete)->where(["tahun" => $thn])->update(["is_deleted" => "true"]);
        }

        if ( $cronjob != true ) {
            // 💬 Log the action & Return the response
            event(new UserPerformedAction("28", session()->get("id_role"), "Import Data Realisasi",
                "Melakukan import data realisasi simkeu",null, null, null,
                null, null, null, null, null, null,
                null, "UPDATE"));
        }
        return response()->json([
            "success" => true,
            "message" => "Berhasil mengimport data"
        ], 201);
    } catch ( \Exception $e ) {
        // 💬 Log the action & Return the response
        event(new UserPerformedAction("28", session()->get("id_role"), "Import Data Realisasi",
            "GAGAL melakukan import data realisasi simkeu",null, null, null,
            null, null, null, null, null, null,
            null, "ERROR"));
        return response()->json(["success" => false, "message" => "Gagal mengimpor data realisasi", "error" => $e->getMessage() ], 500);
    }
}

