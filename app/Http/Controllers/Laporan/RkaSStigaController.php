<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\IKK;
use App\Models\Rekat;

class RkaSStigaController extends Controller {
    public function index(){
        return view('content.laporan.KLASIFIKASI.SASARAN.TIGA.tiga');
    }
    public function pdfNonApbn(){
        $idunit = session()->get("unitkerja", "");
        $tahun  = str_replace("_", " ", session()->get("tahun", "tahun_2025"));
        return view('content.laporan.KLASIFIKASI.SASARAN.TIGA.pdf', compact('idunit', 'tahun'));
    }
    public function getNonApbn() {
        $tahun = session()->get("tahun", "tahun_2025");
        $kro = DB::connection('sirekat')->select("SELECT DISTINCT kd_ss, sasaran_program FROM iku_baru WHERE kd_ss = 'SKK' ");
        $ro = DB::connection('sirekat')->select("SELECT DISTINCT SUBSTRING(kode_ikk,5) AS kode_ikk, indikator_kinerja_kegiatan FROM iku_baru
                            WHERE kode_ikk LIKE '%IKU.3%' ORDER BY kode_ikk DESC");
        $kp = DB::connection('sirekat')->select("SELECT DISTINCT kd_ikv, ikv FROM iku_baru ORDER BY RIGHT(kd_ikv, 5) DESC");
        $sk = IKK::SELECT(\DB::raw("SUBSTRING(kd_keg, 4, 5) AS kd_keg_compare"),
                        \DB::raw("SUBSTRING(kd_keg, 10) AS kd_keg"), "rincian_kegiatan")
                        ->distinct()->orderBy("kd_keg", "DESC")->get();
        // ✅ Select data unit kerja dari tabel rekat
        $unit_kerja = DB::connection('sirekat')->select("select DISTINCT tb_rekat.id,tb_rekat.kd_rk, tb_rekat.id_sub_judul,tb_rekat.unit_pelaksana,
        tb_rekat.rincian_komponen,tb_unit.idunit, tb_unit.unitkerja,tb_rekat.kd_rk from tb_rekat
        inner join tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE tb_rekat.tahun = '$tahun'
        AND tb_rekat.sd = '41' AND tb_rekat.kd_kro = 'SKK'
        GROUP BY tb_rekat.unit_pelaksana, tb_rekat.rincian_komponen");
        // ✅ Select data detail kegiatan dari tabel rekat
        $detail_kegiatan =  Rekat::SELECT("id","id_sub_judul", "kd_rk", "sub_judul", "unit_pelaksana", "rincian_komponen", "unit_kerja", "rpd")
                            ->where(['tahun' => $tahun, "sd" => '41'])->where("kd_kro", "=", "SKK")->get();
        // ✅ Select data coa -> join 3 tabel rab (keg, per, gdg)
        $rekat_coa = DB::connection('sirekat')->select("SELECT DISTINCT tb_rekat.id, tb_rekat.unit_kerja,tb_rekat.sub_judul,tb_rekat.id_sub_judul,tb_rekat.rincian_komponen   as rk_rekat,tb_rekat.kd_rk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.jenis_belanja AS belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.jenis_belanja AS belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.jenis_belanja AS belanja_gdg
        from tb_rekat
        left join tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
        left join tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
        left join tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.kd_kro = 'SKK' AND 
        tb_rekat.tahun = '$tahun'
        AND tb_rekat.sd = '41' 
        ORDER BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,
        tb_rabperalatan.id_jenis_belanja DESC");

        $rekat_kk = DB::connection('sirekat')->select("select tb_unit.idunit,tb_unit.unitkerja,tb_rekat.id_sub_judul, tb_rekat.kd_rk,
        tb_rekat.sub_judul, tb_rekat.rincian_komponen AS rk_rekat, tb_rabkegiatan.id_kegiatan as id_keg,
        tb_rabperalatan.id_kegiatan as id_per, tb_rabgedung.id_kegiatan as id_gdg,
        tb_rekat.unit_kerja AS uk, FORMAT(tb_rabkegiatan.jumlah_biaya,0) as total_keg,
        FORMAT(tb_rabperalatan.jumlah_biaya,0) as total_per,
        FORMAT(tb_rabgedung.jumlah_nilai,0) as total_gdg,
        tb_rabkegiatan.id_kegiatan,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabkegiatan.kebutuhan_kegiatan AS kebutuhan_keg,
        tb_rabkegiatan.satuan_kegiatan AS satuan_keg,
        tb_rabperalatan.kuantitas AS qt_per,
        tb_rabperalatan.satuan AS satuan_qt_per,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabperalatan.kebutuhan_kegiatan AS kebutuhan_per,
        tb_rabperalatan.harga_satuan AS biaya_satuan_per,
        FORMAT((tb_rabgedung.DED_AWAL + tb_rabgedung.DED_REVIEW + tb_rabgedung.nilai_perencanaan
        + tb_rabgedung.nilai_struktur + tb_rabgedung.nilai_me + tb_rabgedung.nilai_landscape + tb_rabgedung.nilai_pengawasan),0) AS biaya_satuan_gdg,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.kebutuhan_kegiatan AS kebutuhan_gdg,
        tb_rabkegiatan.kuantitas, tb_rabkegiatan.satuan_kuantitas,tb_rabkegiatan.durasi, tb_rabkegiatan.satuan_durasi,
        tb_rabkegiatan.verifikasi_tim AS verifikasi_tim_keg,
        tb_rabkegiatan.verifikasi_pimpinan AS verifikasi_pimpinan_keg,
        tb_rabkegiatan.verifikasi_pimpinan_univ AS verifikasi_univ_keg,
        tb_rabperalatan.verifikasi_tim AS verifikasi_tim_per,
        tb_rabperalatan.verifikasi_pimpinan AS verifikasi_pimpinan_per,
        tb_rabperalatan.verifikasi_pimpinan_univ AS verifikasi_univ_per,
        tb_rabgedung.verifikasi_tim AS verifikasi_tim_gdg,
        tb_rabgedung.verifikasi_pimpinan AS verifikasi_pimpinan_gdg,
        tb_rabgedung.verifikasi_pimpinan_univ AS verifikasi_univ_gdg,
        tb_rabkegiatan.kegiatan, tb_rabkegiatan.satuan_kegiatan, FORMAT(tb_rabkegiatan.biaya_satuan,0) as biaya_satuan from tb_rekat
        LEFT join tb_rabkegiatan  ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.kd_kro = 'SKK'
        AND tb_rekat.sd = '41' ORDER BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,
        tb_rabperalatan.id_jenis_belanja desc");

        $sum_coa = DB::connection('sirekat')->select("SELECT tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
            tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
            tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
           tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
            FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_coa_keg,
            FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_coa_per,
            FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_coa_gdg
           from tb_rekat
           LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
           LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
            LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
           WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
           OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
           OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.kd_kro = 'SKK' AND tb_rekat.tahun = '$tahun'
           AND tb_rekat.sd = '41' 
           GROUP BY tb_rabkegiatan.id_jenis_belanja, tb_rabkegiatan.id_jenis_belanja,tb_rabgedung.id_jenis_belanja,
           tb_rekat.id_sub_judul ");

        $sum_detail_kegiatan = DB::connection('sirekat')->select("select tb_rekat.id,
        tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rekat.rpd,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_dk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_dk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_dk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.kd_kro = 'SKK' AND tb_rekat.tahun = '$tahun'
        AND tb_rekat.sd = '41' GROUP BY tb_rekat.id, tb_rekat.sub_judul");

        $sum_unit_kerja = DB::connection('sirekat')->select("SELECT 
            tb_rekat.rincian_komponen,tb_rekat.unit_kerja,tb_rekat.kd_rk, tb_rekat.id_sub_judul,
            tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
            tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
            tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
            FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_uk_keg,
            FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_uk_per,
            FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_uk_gdg
        FROM tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (
            tb_rabgedung.id_jenis_belanja LIKE '%51%'
            OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
            OR tb_rabperalatan.id_jenis_belanja LIKE '%51%'
        ) 
            AND tb_rekat.kd_kro = 'SKK'
            AND tb_rekat.tahun = '$tahun'
            AND tb_rekat.sd = '41' 
        GROUP BY  tb_rekat.kd_rk, tb_rekat.unit_kerja");

                $sum_rincian_komponen = DB::connection('sirekat')->select("SELECT 
                    tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
                    tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
                    tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
                    tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
                    tb_rekat.rincian_komponen,
                    FORMAT(
                        SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + 
                        SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
                        SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) 
                        AS TOTAL_RK
                FROM tb_rekat
                LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
                LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
                LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
                WHERE 
                    tb_rekat.tahun = '$tahun' 
                    AND tb_rekat.kd_kro = 'SKK'
                    AND tb_rekat.sd = '41' 
                GROUP BY tb_rekat.kd_rk");

               $sum_ikv = DB::connection('sirekat')->select("SELECT tb_rekat.rincian_kegiatan,tb_rekat.unit_kerja,
               tb_rekat.rincian_komponen,
               FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_ikv_keg,
               FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_ikv_per,
               FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_ikv_gdg,
               FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKV
               from tb_rekat
               LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
               LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
               LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
               WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
               OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
               OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
               AND tb_rekat.kd_kro = 'SKK' AND tb_rekat.tahun = '$tahun'
               AND tb_rekat.sd = '41' GROUP BY tb_rekat.kd_keg");

               $sum_ikk = DB::connection('sirekat')->select("select tb_rabkegiatan.unit_kerja, tb_rekat.indikator_kinerja_kegiatan,
               FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_ikk_keg,
               FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_ikk_per,
               FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_ikk_gdg,
               FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKK
               from tb_rekat
               LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
               LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
               LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
               WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
               OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
               OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
               AND tb_rekat.kd_kro = 'SKK' AND tb_rekat.tahun = '$tahun'
               AND tb_rekat.sd = '41' GROUP BY tb_rekat.indikator_kinerja_kegiatan");

               $sum_ss = DB::connection('sirekat')->select("select tb_rabkegiatan.unit_kerja, tb_rekat.sasaran_program,
               FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_ss_keg,
               FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_ss_per,
               FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_ss_gdg,
               FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
               from tb_rekat
               LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
               LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
               LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
               WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
               OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
               OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
               AND tb_rekat.kd_kro = 'SKK' AND tb_rekat.tahun = '$tahun'
               AND tb_rekat.sd = '41' GROUP BY tb_rekat.sasaran_program");

               $sum_sd = DB::connection('sirekat')->select("select tb_rekat.sd, tb_rekat.unit_kerja, tb_rekat.sasaran_program,
               FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SD
               from tb_rekat
               LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
               LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
               LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
               WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
               OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
               OR tb_rabperalatan.id_jenis_belanja LIKE '%51%')
               AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '41' group BY tb_rekat.sd");

        return response()->json([ "success" => true, "data" => [
            "kro" => $kro, "ro" => $ro, "kp" => $kp, "sk" => $sk, "uk" => $unit_kerja, "dk" => $detail_kegiatan, "rekat_coa" => $rekat_coa,
            "rekat_kk" => $rekat_kk, "sum_coa" => $sum_coa, "sum_dk" => $sum_detail_kegiatan, "sum_uk" => $sum_unit_kerja, "sum_rk" => $sum_rincian_komponen, "sum_ikv" => $sum_ikv, "sum_ikk" => $sum_ikk, "sum_ss" => $sum_ss, "sum_sd" => $sum_sd
        ]]);
    }
}
