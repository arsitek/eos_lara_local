<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;

use App\Models\Datamaster\SumberDana2;
use Illuminate\Http\Request;
use App\Models\Rekat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PendapatanController extends Controller {
    public function index(Request $req): \Illuminate\View\View {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $tahunSebelumnya = (int) $tahunAngka - 1;
        $nip  = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) {
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        $index     =  $tahunAngka >= 2026 ? "content.laporan.PENDAPATAN.remake.index" : "content.laporan.PENDAPATAN.index";
        return view( $index, compact("unitkerja", "tahunAngka", "tahun", "tahunSebelumnya") );
    }
    public function index2(): \Illuminate\View\View {
        $tahunAngka = explode("_", session("tahun", "tahun_2025"))[1]; // Definitif_2024 => 2024
        $nip  = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) {
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();
        return view("content.laporan.PENDAPATAN.v2.index", compact("unitkerja", "tahunAngka"));
    }
    public function getRealtime(){
        try {
            $tahunAngka = explode("_", session("tahun", "tahun_2025"))[1]; // Definitif_2024 => 2024
            $currentYear = (int) $tahunAngka;
            $pastYear = $currentYear - 1;
            $tahun = [ (string) $pastYear, (string) $currentYear ]; // e.g., ["2023", "2024"]
            $data = [];
            foreach( $tahun as $thn ) {
                $response = Http::timeout(10)->get( config("app.simkeu_url") . "/penerimaan/rekap/" . $thn );
                if ( $response->successful() ) {
                    // append all data into $data
                    $data[$thn] = $response->json();
                } else {
                    return response()->json([ "success" => false, "message" => "Gagal mendapatkan data." ], 500);
                }
            }
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data realtime",
                "data"    => $data
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([ "success" => false, "message" => "Gagal mendapatkan data", "error" => $e->getMessage() ], 500);
        }
    }
    public function get( Request $req ) {
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1]; // Definitif_2024 => 2024
            $idunit     = $req->unitkerja;
            $dataMaster = SumberDana2::with(["child4" => function( $x ) use ( $tahunAngka ) {
                $x->where("tahun", $tahunAngka);
            }, "child4.child6" => function( $x ) use ( $tahunAngka ) {
                $x->where("tahun", $tahunAngka);
            }, "child4.child6.child8" => function( $x ) use ( $tahunAngka ) {
                $x->where("tahun", $tahunAngka);
            }, "child4.child6.child8.child10" => function( $x ) use ( $tahunAngka ) {
                $x->where("tahun", $tahunAngka);
            } ])->orderBy("kd_sumberdana", "ASC")->get();
            $baseSqlPenerimaan = "WITH sumberdana AS ( SELECT
                    sd2.kd_sumberdana AS kd_parent_sd2,  sd2.sumberdana AS parent_sd2,
                    sd4.kd_sumberdana AS kd_parent_sd4,  sd4.sumberdana AS parent_sd4,
                    sd6.kd_sumberdana AS kd_parent_sd6,  sd6.sumberdana AS parent_sd6,
                    sd8.kd_sumberdana AS kd_parent_sd8,  sd8.sumberdana AS parent_sd8,
                    sd10.kd_sumberdana AS kd_parent_sd10,  sd10.sumberdana AS parent_sd10
                FROM tb_sumberdana_2 sd2
                INNER JOIN tb_sumberdana_4 sd4 ON sd2.kd_sumberdana = sd4.kd_parent AND sd4.tahun = '$tahunAngka'
                INNER JOIN tb_sumberdana_6 sd6 ON sd4.kd_sumberdana = sd6.kd_parent AND sd6.tahun = '$tahunAngka'
                INNER JOIN tb_sumberdana_8 sd8 ON sd6.kd_sumberdana = sd8.kd_parent AND sd8.tahun = '$tahunAngka'
                INNER JOIN tb_sumberdana_10 sd10 ON sd8.kd_sumberdana = sd10.kd_parent AND sd10.tahun = '$tahunAngka'
                WHERE sd2.tahun = '$tahunAngka'
            ),
            penerimaan AS (
                SELECT pe.kode_unit, pe.coa, pe.nama_coa, pe.nominal, pe.tahun FROM tb_penerimaan pe
            )";
            $condition     = "";
            $joinCondition = "";
            $selectCondition = "";
            $joinConditionAgr2024 = "";
            $joinConditionAgr2025 = "";
            $whereConditionAgr = "";
            if ( $idunit && $idunit != "semua" ) {
                $selectCondition = "alokasi.pagu AS pagu_alokasi, ";
                $condition = "WHERE LEFT(pe.kode_unit, 5) = '$idunit' GROUP BY pe.tahun, ";
                $joinCondition = "INNER JOIN tb_unit_api unit ON unit.idunit = LEFT(pe.kode_unit, 5)";
                $joinConditionAgr2024 = "LEFT JOIN tb_alokasi alokasi ON alokasi.kd_sumberdana = msd.kd_2024 AND alokasi.is_deleted = 'false'";
                $joinConditionAgr2025 = "LEFT JOIN tb_alokasi alokasi ON alokasi.kd_sumberdana = bd.kd_sumberdana AND alokasi.tahun = '$tahun' AND alokasi.is_deleted = 'false'";
                $whereConditionAgr = "AND alokasi.unit_kerja = '$idunit'";
            } else {
                $selectCondition = "bd.pagu_alokasi, ";
                $condition = "GROUP BY pe.tahun,";
            }
            $sd10 = DB::raw(" $baseSqlPenerimaan SELECT pe.nama_coa, sd.kd_parent_sd2, sd.kd_parent_sd4, sd.kd_parent_sd6, sd.kd_parent_sd8, sd.kd_parent_sd10,
                    sum(pe.nominal) AS total, pe.tahun
                FROM penerimaan pe
                INNER JOIN sumberdana sd ON sd.kd_parent_sd10 = pe.coa
                $joinCondition
            $condition sd.kd_parent_sd10");
            $sd8 = DB::raw(" $baseSqlPenerimaan SELECT pe.nama_coa, sd.kd_parent_sd2, sd.kd_parent_sd4, sd.kd_parent_sd6, sd.kd_parent_sd8, sd.kd_parent_sd10,
                    sum(pe.nominal) AS total, pe.tahun
                FROM penerimaan pe
                INNER JOIN sumberdana sd ON sd.kd_parent_sd10 = pe.coa
                $joinCondition
            $condition sd.kd_parent_sd8");
            $sd6 = DB::raw(" $baseSqlPenerimaan SELECT pe.nama_coa, sd.kd_parent_sd2, sd.kd_parent_sd4, sd.kd_parent_sd6, sd.kd_parent_sd8, sd.kd_parent_sd10,
                    sum(pe.nominal) AS total, pe.tahun
                FROM penerimaan pe
                INNER JOIN sumberdana sd ON sd.kd_parent_sd10 = pe.coa
                $joinCondition
            $condition sd.kd_parent_sd6");
            $sd4 = DB::raw(" $baseSqlPenerimaan  SELECT pe.nama_coa, sd.kd_parent_sd2, sd.kd_parent_sd4, sd.kd_parent_sd6, sd.kd_parent_sd8, sd.kd_parent_sd10,
                    sum(pe.nominal) AS total, pe.tahun
                FROM penerimaan pe
                INNER JOIN sumberdana sd ON sd.kd_parent_sd10 = pe.coa
                $joinCondition
            $condition sd.kd_parent_sd4");
            $sd2 = DB::raw(" $baseSqlPenerimaan SELECT pe.nama_coa, sd.kd_parent_sd2, sd.kd_parent_sd4, sd.kd_parent_sd6, sd.kd_parent_sd8, sd.kd_parent_sd10,
                    sum(pe.nominal) AS total, pe.tahun
                FROM penerimaan pe
                INNER JOIN sumberdana sd ON sd.kd_parent_sd10 = pe.coa
                $joinCondition
            $condition sd.kd_parent_sd2");

            // hardcode untuk sementara, dikarenakan belum ada data lengkap pada tahun 2024
            $anggaran2024 = DB::connection('sirekat')->select("$baseSqlPenerimaan SELECT
                COALESCE(sd10.kd_parent_sd10, '-') as sd10kd_parent10, COALESCE( sd10.kd_parent_sd8, '-') as sd10kd_parent8,
                COALESCE( sd10.kd_parent_sd6, '-') as sd10kd_parent6, COALESCE( sd10.kd_parent_sd4, '-') as sd10kd_parent4,
                COALESCE( sd10.kd_parent_sd2, '-') as sd10kd_parent2,
                COALESCE(sd8.kd_parent_sd8, '-') as sd8kd_parent8, COALESCE( sd8.kd_parent_sd6, '-') as sd8kd_parent6,
                COALESCE( sd8.kd_parent_sd4, '-' ) as sd8kd_parent4, COALESCE( sd8.kd_parent_sd2, '-' ) as sd8kd_parent2,
                COALESCE(sd6.kd_parent_sd4, '-') as sd6kd_parent4, COALESCE( sd6.kd_parent_sd2, '-') as sd6kd_parent2,
                COALESCE(sd4.kd_parent_sd2, '-') as sd4kd_parent2, $selectCondition
                msd.kd_2025, msd.kd_2024
                FROM tb_mapping_sumberdana msd
                LEFT JOIN sumberdana sd10 ON sd10.kd_parent_sd10 = msd.kd_2025
                LEFT JOIN sumberdana sd8 ON sd8.kd_parent_sd8 = msd.kd_2025
                LEFT JOIN sumberdana sd6 ON sd6.kd_parent_sd6 = msd.kd_2025
                LEFT JOIN sumberdana sd4 ON sd4.kd_parent_sd4 = msd.kd_2025
                LEFT JOIN tb_sumberdana bd ON bd.kd_sumberdana = msd.kd_2024 # bd as BaseData
                $joinConditionAgr2024
                WHERE bd.pagu_alokasi <> '0' AND bd.is_show = 'true' AND bd.is_deleted = 'false' $whereConditionAgr
            GROUP BY msd.kd_2024");

            // anggaran 2025
            $anggaran2025 = DB::connection('sirekat')->select("$baseSqlPenerimaan SELECT $selectCondition bd.sumberdana, sd.*
                FROM tb_sumberdana bd # bd as BaseData
                INNER JOIN sumberdana sd ON sd.kd_parent_sd8 = bd.kd_sumberdana
                $joinConditionAgr2025
            WHERE bd.tahun = '$tahunAngka' AND bd.kd_sumberdana NOT IN ('41050104') AND bd.is_show = 'true' AND bd.is_deleted = 'false' $whereConditionAgr
            OR ( bd.kd_sumberdana = '41010301' AND bd.is_show = 'false' AND bd.is_deleted = 'false' )
            GROUP BY sd.kd_parent_sd8");
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data pendapatan",
                "data"    => [
                    "dataMaster" => $dataMaster,
                    "sd10" => $sd10,
                    "sd8"  => $sd8,
                    "sd6"  => $sd6,
                    "sd4"  => $sd4,
                    "sd2"  => $sd2,
                    "anggaran2024" => $anggaran2024,
                    "anggaran2025" => $anggaran2025,
                ]
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([ "success" => false, "message" => "Gagal mendapatkan data", "error" => $e->getMessage() ], 500);
        }
    }
    // Fungsi yang sama seperti `get` pada baris 50 dengan optimasi lebih lanjut untuk tahun >= 2026
    public function getRemake(Request $req) {
        try {
            ["tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $idunit      = $req->idunit;
            $prevYear    = (int) $tahunAngka - 1; // contoh: 2026 -> 2025
            $tahunPrev   = "Definitif_{$prevYear}";
            $tahunCurr   = "Definitif_{$tahunAngka}";

            // Optional filter per unit kerja
            $unitFilterSql = "";
            $unitPendapatanFilterSql = "";
            $unitFilterParams = [];
            if ($idunit && $idunit !== 'semua') {
                $unitFilterSql = " AND alk.unit_kerja = ?";
                $unitPendapatanFilterSql = " AND LEFT(p.kode_unit,5) = ?";
                $unitFilterParams[] = $idunit;
            }

            /*
             * - Jika tahunAngka = 2025 -> prevYear=2024 (tetap mengikuti formula, walau rules dimulai >=2026)
             * - Jika tahunAngka = 2026 -> ambil 2025 & 2026
             * - Jika tahunAngka = 2027 -> ambil 2026 & 2027
             * - dan seterusnya
             */
            $dataMasterSumberDana = "SELECT
                    sd2.sumberdana AS sd2_sumberdana, sd2.kd_sumberdana AS sd2_kd,
                    sd4.sumberdana AS sd4_sumberdana, sd4.kd_sumberdana AS sd4_kd,
                    sd6.sumberdana AS sd6_sumberdana, sd6.kd_sumberdana AS sd6_kd,
                    sd8.sumberdana AS sd8_sumberdana, sd8.kd_sumberdana AS sd8_kd,
                    sd10.kd_sumberdana AS sd10_kd, sd10.sumberdana AS sd10_sumberdana
                FROM tb_sumberdana_2 sd2
                JOIN tb_sumberdana_4 sd4 ON sd4.kd_parent = sd2.kd_sumberdana AND sd4.tahun = sd2.tahun
                JOIN tb_sumberdana_6 sd6 ON sd6.kd_parent = sd4.kd_sumberdana AND sd6.tahun = sd2.tahun
                JOIN tb_sumberdana_8 sd8 ON sd8.kd_parent = sd6.kd_sumberdana AND sd8.tahun = sd2.tahun
                JOIN tb_sumberdana_10 sd10 ON sd10.kd_parent = sd8.kd_sumberdana AND sd10.tahun = sd2.tahun
            WHERE sd2.tahun = '2025'";
            $sqlAlokasi = "WITH sumberdana AS ( $dataMasterSumberDana ),
                alokasi AS (
                    SELECT
                        alk.pagu_tambahan,
                        sd.kd_sumberdana, sd.sumberdana, alk.unit_kerja,
                        alk.pagu, ? AS tahun_data
                    FROM tb_sumberdana sd
                    JOIN tb_alokasi alk ON alk.kd_sumberdana = sd.kd_sumberdana
                    WHERE sd.tahun = ? AND sd.is_show = 'true' AND sd.is_deleted = 'false' AND alk.tahun = ? AND alk.is_deleted = 'false' $unitFilterSql
                    UNION ALL
                    SELECT
                        alk.pagu_tambahan,
                        sd.kd_sumberdana, sd.sumberdana, alk.unit_kerja,
                        alk.pagu, ? AS tahun_data
                    FROM tb_sumberdana sd
                    JOIN tb_alokasi alk ON alk.kd_sumberdana = sd.kd_sumberdana
                    WHERE sd.tahun = ? AND sd.is_show = 'true' AND sd.is_deleted = 'false' AND alk.tahun = ? AND alk.is_deleted = 'false' $unitFilterSql
                )
                SELECT
                    MAX(s.sd2_kd)           AS sd2_kd,
                    MAX(s.sd2_sumberdana)   AS sd2_sumberdana,
                    MAX(s.sd4_kd)           AS sd4_kd,
                    MAX(s.sd4_sumberdana)   AS sd4_sumberdana,
                    MAX(s.sd6_kd)           AS sd6_kd,
                    MAX(s.sd6_sumberdana)   AS sd6_sumberdana,
                    MAX(s.sd8_kd)           AS sd8_kd,
                    MAX(s.sd8_sumberdana)   AS sd8_sumberdana,
                    MAX(s.sd10_kd)          AS sd10_kd,
                    MAX(s.sd10_sumberdana)  AS sd10_sumberdana,
                    a.unit_kerja, a.unit_kerja, a.kd_sumberdana,
                    coalesce(a.pagu, 0) + coalesce(a.pagu_tambahan,0) AS total_pagu,
                    a.tahun_data
                FROM alokasi a
                JOIN sumberdana s ON s.sd8_kd = a.kd_sumberdana
                GROUP BY
                    a.kd_sumberdana, a.unit_kerja,
                    a.tahun_data, a.pagu, a.pagu_tambahan; ";

            $bindingsAlokasi = [
                $prevYear,           // tahun_data untuk baris prev
                $prevYear,           // sd.tahun prev
                $tahunPrev,          // alk.tahun prev
                ...$unitFilterParams,
                $tahunAngka,         // tahun_data untuk baris current
                $tahunAngka,         // sd.tahun current
                $tahunCurr,          // alk.tahun current
                ...$unitFilterParams,
            ];
            $dataAlokasi    = DB::connection('sirekat')->select($sqlAlokasi, $bindingsAlokasi);

            $bindingsPendapatan = [ $prevYear, $tahunAngka, ...$unitFilterParams ];
            $dataPendapatan = DB::connection('sirekat')->select("WITH sumberdana AS ( $dataMasterSumberDana )
                SELECT * FROM tb_penerimaan p
                JOIN sumberdana sd ON p.coa = sd.sd10_kd
            WHERE (p.tahun = ? or p.tahun = ?) $unitPendapatanFilterSql", $bindingsPendapatan);
            $dataMasterSumberDana = DB::connection('sirekat')->select($dataMasterSumberDana);
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data alokasi",
                "data"    => [
                    "dataMaster" => $dataMasterSumberDana,
                    "dataAlokasi" => $dataAlokasi,
                    "dataPendapatan" => $dataPendapatan
                ],
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json(["success" => false, "message" => "Gagal mendapatkan data", "error" => $e->getMessage()], 500);
        }
    }
}
