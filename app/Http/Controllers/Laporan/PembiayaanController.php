<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\SumberDana2;
use Illuminate\Http\Request;
use App\Models\Rekat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PembiayaanController extends Controller {
    public function index(){
        $unitkerja  = Rekat::select("unit_kerja")->with("unitApi")->orderBy("unit_kerja")->distinct()->get();
        $tahun      = session("tahun", "tahun_2025");
        $tahunAngka = explode("_", $tahun)[1]; // Definitif_2024 => 2024
        $masterData = [
            [ "key" => "biayaOperasional", "desc" => "Biaya Operasional", "total" => "Rp 222,826,793,000", "totalDB" => [] ],
            [ "key" => "biayaDosenPNS", "desc" => "Biaya Dosen PNS ( Gaji dan tunjangan yang melekat pada gaji )", "total" => "Rp 215,434,470,000" ],
            [ "key" => "biayaTendikPNS", "desc" => "Biaya Tenaga Kependidikan PNS ( gaji dan tunjangan yang melekat pada gaji )", "total" => "Rp 46,475,608,000" ],
            [ "key" => "GAJI DOSEN NON PNS", "desc" => "Biaya Dosen Non PNS ( gaji dan tunjangan yang melekat pada gaji )", "total" => "Rp 13,634,608,800",
                "totalDB2025" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'GAJI DOSEN NON PNS' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.E'
                    AND rkt.tahun = 'Definitif_2025' AND rab.is_deleted = 'false' AND rkt.sd != '4100'"),
                "totalDB2024" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'GAJI DOSEN NON PNS' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.D'
                    AND rkt.tahun = 'Definitif_2024' AND rab.is_deleted = 'false' AND rkt.sd != '4100'")
            ],
            [ "key" => "GAJI TENDIK NON PNS", "desc" => "Biaya Tenaga Kependidikan Non PNS ( gaji dan tunjangan yang melekat pada gaji )", "total" => "Rp 41,193,343,000",
                "totalDB2025" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'GAJI TENDIK NON PNS' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.F'
                    AND rkt.tahun = 'Definitif_2025' AND rab.is_deleted = 'false' AND rkt.sd != '4100'"),
                "totalDB2024" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'GAJI TENDIK NON PNS' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.E'
                    AND rkt.tahun = 'Definitif_2024' AND rab.is_deleted = 'false' AND rkt.sd != '4100'")
            ],
            [ "key" => "REMUNERASI", "desc" => "Remunerasi/Imbal Jasa", "total" => "Rp 147,000,000,000",
                "totalDB2025" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'REMUNERASI' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.J'
                    AND rkt.tahun = 'Definitif_2025' AND rab.is_deleted = 'false' AND rkt.sd != '4100'"),
                "totalDB2024" => DB::connection('sirekat')->select("SELECT SUM(rab.jumlah_biaya) AS total, 'REMUNERASI' as kategori FROM tb_rekat rkt
                    INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                    WHERE rkt.is_deleted = 'false' AND left( rkt.kd_rk, 8 ) = 'KT.4.2.I'
                    AND rkt.tahun = 'Definitif_2024' AND rab.is_deleted = 'false' AND rkt.sd != '4100'")
            ],
            [ "key" => "SARANA & PRASARANA", "desc" => "Biaya Investasi ( Prasarana dan Sarana )", "total" => "Rp 146,914,438,000",
                "totalDB2025" => DB::connection('sirekat')->select("WITH baseData AS (
                    SELECT rab.id, rab.jumlah_biaya FROM tb_rabperalatan rab
                    INNER JOIN tb_rekat rkt ON rkt.id = rab.id_rekat AND rab.is_deleted = 'false'
                    WHERE rkt.is_deleted = 'false' AND rkt.tahun = 'Definitif_2025' AND rkt.kd_rk LIKE '%W%' AND rkt.sd != '4100'
                    UNION ALL
                    SELECT rab.id, rab.jumlah_nilai as jumlah_biaya FROM tb_rabgedung rab
                    INNER JOIN tb_rekat rkt ON rkt.id = rab.id_rekat AND rab.is_deleted = 'false'
                    WHERE rkt.is_deleted = 'false' AND rkt.tahun = 'Definitif_2025' AND rkt.kd_rk LIKE '%V%' AND rkt.sd != '4100'
                    )
                    SELECT sum(bd.jumlah_biaya) AS total, 'SARANA & PRASARANA' as kategori FROM baseData bd"),
                "totalDB2024" => DB::raw("WITH baseData AS (
                    SELECT rab.id, rab.jumlah_biaya FROM tb_rabperalatan rab
                    INNER JOIN tb_rekat rkt ON rkt.id = rab.id_rekat AND rab.is_deleted = 'false'
                    WHERE rkt.is_deleted = 'false' AND rkt.tahun = 'Definitif_2024' AND rkt.kd_rk LIKE '%W%' AND rkt.sd != '4100'
                    UNION ALL
                    SELECT rab.id, rab.jumlah_nilai as jumlah_biaya FROM tb_rabgedung rab
                    INNER JOIN tb_rekat rkt ON rkt.id = rab.id_rekat AND rab.is_deleted = 'false'
                    WHERE rkt.is_deleted = 'false' AND rkt.tahun = 'Definitif_2024' AND rkt.kd_rk LIKE '%V%' AND rkt.sd != '4100'
                )
                SELECT sum(bd.jumlah_biaya) AS total, 'SARANA & PRASARANA' as kategori FROM baseData bd"),
            ],
            [ "key" => "biayaPengembangan", "desc" => "Biaya Pengembangan", "total" => "Rp 36,985,901,000" ],
        ];
        $res2023   = Http::withOptions([ 'verify' => false ])->get( config("app.simkeu_url_old") . "/anggaran_simkeu/realisasi2023" )->json();
        $res       = Http::get( config("app.simkeu_url") . "/penerimaan/pembiayaan/rekap/2024" )->json();
        $realisasi = [];
        if (!$res) {
            $realisasi["2024"] = [];
        }
        if (!$res2023) {
            $realisasi["2023"] = [];
        }

        $realisasi["2024"] = $res["data"] ?? [];
        $realisasi["2023"] = $res2023 ?? [];
        foreach( $realisasi["2024"] as $key => $item ) {
            foreach( $masterData as $masterKey => $masterItem ) {
                if ( $item["kategori"] == $masterItem["key"] ) {
                    $masterData[$masterKey]["total_realisasi"]["2024"] = $item["total_rincian"];
                }
            }
        }
        foreach( $realisasi["2023"] as $key => $item ) {
            foreach( $masterData as $masterKey => $masterItem ) {
                if ( $item["kategori"] == $masterItem["key"] || ( $item["kategori"] == "Data Remunerasi" && $masterItem["key"] == "REMUNERASI" ) ) {
                    $masterData[$masterKey]["total_realisasi"]["2023"] = $item["nominal"];
                }
            }
        }
        return view('content.laporan.PEMBIAYAAN.index', compact("unitkerja", "masterData", "realisasi"));
    }
    public function get( Request $req ) {
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1]; // Definitif_2024 => 2024
            $idunit     = $req->unitkerja;

            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data",
                "data"    => [
                ]
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([ "success" => false, "message" => "Gagal mendapatkan data", "error" => $e->getMessage() ], 500);
        }
    }
}
