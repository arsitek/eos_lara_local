<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\IKK;
use App\Models\MasterUnit;

class RkaRoController extends Controller {
    public function index() {
        $idunit = session()->get('unitkerja', '');
        $unitkerja = MasterUnit::all();
        return view('content.laporan.RKA.RINCIAN_OUTPUT.index', compact('unitkerja', 'idunit'));
    }
    public function show($idunit) {
        $unitkerja = MasterUnit::all();
        return view('content.laporan.RKA.RINCIAN_OUTPUT.index', compact('unitkerja','idunit'));
    }
    public function get($idunit){
        $tahun = session()->get('tahun', 'tahun_2025');
        // ✅ Select data master (kro, ro, kp, sk)
        $kro = IKK::select("kd_ss", "sasaran_program")->distinct()->get();
        $ro = IKK::SELECT(\DB::raw("SUBSTRING(kode_ikk,5) AS kode_ikk"), "indikator_kinerja_kegiatan")
            ->distinct()->orderBy("kode_ikk", "DESC")->get();
        $sum_ikk = DB::connection('sirekat')->select("select tb_rekat.indikator_kinerja_kegiatan,
                FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) 
                + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
                SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKK
                from tb_rekat
                LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
                LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
                LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
                WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
                OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
                OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
                AND tb_rekat.unit_kerja = '$idunit' 
                AND tb_rekat.tahun = '$tahun' 
                group BY tb_rekat.indikator_kinerja_kegiatan");
        $sum_ss = DB::connection('sirekat')->select("SELECT tb_rekat.sasaran_program,
            FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + 
            SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
            from tb_rekat
            LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
            LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
            LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
            WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
            OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
            OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
            AND tb_rekat.unit_kerja = '$idunit' 
            AND tb_rekat.tahun = '$tahun'
            group BY tb_rekat.sasaran_program");
            $sum_sd = DB::connection('sirekat')->select("SELECT tb_rekat.sd, tb_rekat.sasaran_program,
            FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SD
            from tb_rekat
            LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
            LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
            LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
            WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
            OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
            OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
            AND tb_rekat.tahun = '$tahun'
            AND tb_rekat.unit_kerja = '$idunit' 
            group BY tb_rekat.sd"); 
            return response()->json([
                "kro" => $kro, "ro" => $ro, "sum_ikk" => $sum_ikk, "sum_ss" => $sum_ss, "sum_sd" => $sum_sd]);
    }
}
