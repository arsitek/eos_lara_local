<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IKK;
use App\Models\Rekat;
use Illuminate\Support\Facades\DB;

class RekapAnggaranUnitController extends Controller {
    public function index() {
        return view('content.laporan.ANGGARANUNIT.index');
    }
    public function getAnggaran(){
        $tahun = session()->get('tahun', 'tahun_2025');
        $idunit = session()->get('unitkerja', '');
        // ✅ Select data master (kro, ro, kp, sk)
        $kro = IKK::select("kd_ss", "sasaran_program")->distinct()->get();
        $ro = IKK::SELECT(\DB::raw("SUBSTRING(kode_ikk,5) AS kode_ikk"), "indikator_kinerja_kegiatan")
            ->distinct()->orderBy("kode_ikk", "DESC")->get();
        $kp = DB::connection('sirekat')->select("SELECT DISTINCT kd_ikv, ikv FROM iku_baru ORDER BY RIGHT(kd_ikv, 5) DESC");
        $sk = IKK::SELECT(\DB::raw("SUBSTRING(kd_keg, 4, 5) AS kd_keg_compare"),
                \DB::raw("SUBSTRING(kd_keg, 10) AS kd_keg"), "rincian_kegiatan")
                ->distinct()->orderBy("kd_keg", "DESC")->get();
        $unitkerja = Rekat::with('unit')
                    ->SELECT("unit_kerja")
                    ->orderBy("unit_kerja", "ASC")
                    ->DISTINCT()
                    ->GET();
        $ss        = Rekat::SELECT("iku_baru.kd_ss","iku_baru.sasaran_program", "tb_rekat.unit_kerja")
                    ->join('iku_baru', 'iku_baru.sasaran_program', '=', 'tb_rekat.sasaran_program')->DISTINCT()
                    ->orderBy("tb_rekat.sasaran_program", "DESC")->GET();
        $sum_ss    = DB::connection('sirekat')->select("SELECT 
                        tb_rekat.unit_kerja, tb_rekat.sasaran_program,
                        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) 
                            + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) 
                            + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
                		FROM tb_rekat
                    		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
                            LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
                            LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
                        WHERE (
                            tb_rabgedung.id_jenis_belanja LIKE '%51%' 
                            OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
                            OR tb_rabperalatan.id_jenis_belanja LIKE '%51%'
                        )   AND tb_rekat.tahun = '$tahun'
                            AND tb_rekat.sd = '41' 
                        GROUP BY 
                            tb_rekat.sasaran_program, 
                            tb_rekat.unit_kerja"); 
        $sum_unit    = DB::connection('sirekat')->select("SELECT tb_rekat.unit_kerja,
                            FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) 
                                + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) 
                                + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
                		FROM tb_rekat
                    		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
                            LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
                            LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
                        WHERE (
                            tb_rabgedung.id_jenis_belanja LIKE '%51%' 
                            OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
                            OR tb_rabperalatan.id_jenis_belanja LIKE '%51%'
                        ) AND tb_rekat.tahun = '$tahun'
                        AND tb_rekat.sd = '41' 
                    GROUP BY tb_rekat.unit_kerja"); 
        return response()->json(["success" => true, "data" => [
            "unitkerja" => $unitkerja, "sasaran" => $ss, "sum_sasaran" => $sum_ss, "sum_unit" => $sum_unit
        ]]);
    }
}
