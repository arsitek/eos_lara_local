<?php

namespace App\Http\Controllers;

use App\Models\Rekat;
use PDF;
use App\Models\AksesMenu;
use Illuminate\Support\Facades\DB;
use App\Models\IKK;
use App\Models\Service;
use App\Exports\RekatExport;
use App\Exports\RekatRabExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class REKATREPORTController extends Controller{

    public function syncKro(){
        $unit_kerja = session()->get('unitkerja', '');
        // ✅ Select data master (kro, ro, kp, sk)
        $kro = IKK::select("kd_ss", "sasaran_program")->distinct()->get();
        $ro = IKK::SELECT(\DB::raw("SUBSTRING(kode_ikk,5) AS kode_ikk"), "indikator_kinerja_kegiatan")
            ->distinct()->orderBy("kode_ikk", "DESC")->get();
        $kp = DB::connection('sirekat')->select("SELECT DISTINCT kd_ikv, ikv FROM iku_baru ORDER BY RIGHT(kd_ikv, 5) DESC");
        $sk = IKK::SELECT(\DB::raw("SUBSTRING(kd_keg, 4, 5) AS kd_keg_compare"),
                \DB::raw("SUBSTRING(kd_keg, 10) AS kd_keg"), "rincian_kegiatan")
                ->distinct()->orderBy("kd_keg", "DESC")->get();
        // ✅ Select data unit kerja dari tabel rekat 
        $tahun = session()->get('tahun', 'tahun_2025');
        $unit_kerja = DB::connection('sirekat')->select("select DISTINCT tb_rekat.unit_pelaksana, tb_rekat.id_sub_judul, tb_rekat.rincian_komponen,tb_unit.  idunit, tb_unit.unitkerja, tb_rekat.unit_kerja AS uk_rekat,tb_rekat.kd_rk from tb_rekat 
                inner join tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE tb_rekat.tahun = '$tahun' 
                GROUP BY tb_rekat.unit_pelaksana, tb_rekat.kd_rk");
        // ✅ Select data detail kegiatan dari tabel rekat
        $detail_kegiatan = Rekat::SELECT("id","id_sub_judul", "sub_judul", "unit_pelaksana", "rincian_komponen", "unit_kerja", "rpd")
                            ->where('tahun', $tahun)->get();
        // ✅ Select data coa -> join 3 tabel rab (keg, per, gdg)
        $coa = DB::connection('sirekat')->select("select DISTINCT tb_rekat.unit_kerja,tb_rekat.sub_judul,tb_rekat.id_sub_judul,tb_rekat.rincian_komponen   as rk_rekat, tb_rekat.kd_rk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.jenis_belanja AS belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.jenis_belanja AS belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.jenis_belanja AS belanja_gdg
        from tb_rekat
        left join tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
        left join tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
        left join tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");
        
        $rekat_kk = DB::connection('sirekat')->select("select tb_unit.idunit,tb_unit.unitkerja,tb_rekat.id_sub_judul,
        tb_rekat.sub_judul, tb_rekat.rincian_komponen AS rk_rekat, 
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
                 tb_rabkegiatan.verifikasi_pimpinan_unit AS verifikasi_pimpinan_keg,
                 tb_rabkegiatan.verifikasi_pimpinan_univ AS verifikasi_univ_keg,
                 tb_rabperalatan.verifikasi_tim AS verifikasi_tim_per, 
                 tb_rabperalatan.verifikasi_pimpinan_unit AS verifikasi_pimpinan_per,
                 tb_rabperalatan.verifikasi_pimpinan_univ AS verifikasi_univ_per,
                 tb_rabgedung.verifikasi_tim AS verifikasi_tim_gdg, 
                 tb_rabgedung.verifikasi_pimpinan_unit AS verifikasi_pimpinan_gdg,
                 tb_rabgedung.verifikasi_pimpinan_univ AS verifikasi_univ_gdg,
               tb_rabkegiatan.kegiatan, tb_rabkegiatan.satuan_kegiatan, FORMAT(tb_rabkegiatan.biaya_satuan,0) as biaya_satuan from tb_rekat LEFT join tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%' OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun' order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");

        $sum_coa = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja, tb_rekat.kd_rk,
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
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group by tb_rabkegiatan.unit_kerja,tb_rabkegiatan.id_jenis_belanja,tb_rekat.id_sub_judul ");

        $sum_detail_kegiatan = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
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
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group by tb_rabkegiatan.unit_kerja,tb_rekat.id_sub_judul");

        $sum_unit_kerja = DB::connection('sirekat')->select("select tb_rekat.rincian_komponen,tb_rekat.unit_kerja,tb_rekat.kd_rk, tb_rekat.id_sub_judul,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
		tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
	    tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_uk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_uk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_uk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.rincian_komponen, tb_rekat.unit_kerja, tb_rekat.sub_judul");

        $sum_rincian_komponen = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,  
        tb_rekat.rincian_komponen, 
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RK
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%')  AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.rincian_komponen");

        $sum_ikv = DB::connection('sirekat')->select("select tb_rekat.rincian_kegiatan,tb_rekat.unit_kerja,
        tb_rekat.rincian_komponen,tb_rekat.kd_rk,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKV
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.rincian_kegiatan");

        $sum_ikk = DB::connection('sirekat')->select("select tb_rekat.indikator_kinerja_kegiatan,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) 
        + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKK
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun' 
        group BY tb_rekat.indikator_kinerja_kegiatan");
        $sum_ss = DB::connection('sirekat')->select("select tb_rekat.sasaran_program,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + 
        SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
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
        group BY tb_rekat.sd");
        return response()->json(["kro" => $kro, "ro" => $ro, "kp" => $kp, "sk" => $sk, 
        "unit_kerja" => $unit_kerja, "detail_kegiatan" => $detail_kegiatan
        ,"coa" => $sum_coa, "rekat_coa" => $coa, "rekat_kk" => $rekat_kk, 
        "sum_detail_kegiatan" => $sum_detail_kegiatan, "sum_unit_kerja" => $sum_unit_kerja,
        "sum_rincian_komponen" => $sum_rincian_komponen,"sum_ikv" => $sum_ikv,
        "sum_ikk" => $sum_ikk, "sum_ss" => $sum_ss, "sum_sd" => $sum_sd]);
    }

    public function syncKroApbn(){
        $tahun = session()->get('tahun', 'tahun_2025');
        // ✅ Select data master (kro, ro, kp, sk)
        $kro = IKK::select("kd_ss", "sasaran_program")->distinct()->get();
        $ro = IKK::SELECT(\DB::raw("SUBSTRING(kode_ikk,5) AS kode_ikk"), "indikator_kinerja_kegiatan")
            ->distinct()->orderBy("kode_ikk", "DESC")->get();
        $kp = IKK::SELECT("kd_ikv", "ikv")->distinct()->orderBy("kd_ikv", "DESC")->get();
        $sk = IKK::SELECT(\DB::raw("SUBSTRING(kd_keg, 4, 5) AS kd_keg_compare"),
                \DB::raw("SUBSTRING(kd_keg, 10) AS kd_keg"), "rincian_kegiatan")
                ->distinct()->orderBy("kd_keg", "DESC")->get();
        // ✅ Select data unit kerja kegiatan dari tabel rekat
        $unit_kerja = DB::connection('sirekat')->select("select DISTINCT tb_rekat.unit_pelaksana, tb_rekat.kd_rk, tb_rekat.rincian_komponen,tb_unit.  idunit, tb_unit.unitkerja, tb_rekat.unit_kerja AS uk_rekat from tb_rekat 
                inner join tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
                GROUP BY tb_rekat.kd_rk");
        // ✅ Select data detail kegiatan dari tabel rekat
        $detail_kegiatan = Rekat::SELECT("id_sub_judul", "sub_judul", "rincian_komponen", "unit_kerja", "rpd")
                            ->where(['tahun' => $tahun, 'sd' => '42'])
                            ->get();
        // ✅ Select data coa -> join 3 tabel rab (keg, per, gdg)
        // ✅ Select data coa -> join 3 tabel rab (keg, per, gdg)
        $coa = DB::connection('sirekat')->select("select DISTINCT tb_rekat.unit_kerja,tb_rekat.sub_judul,tb_rekat.id_sub_judul,tb_rekat.rincian_komponen as rk_rekat, tb_rekat.sd,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.jenis_belanja AS belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.jenis_belanja AS belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.jenis_belanja AS belanja_gdg
        from tb_rekat
        left join tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
        left join tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
        left join tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");
        
        $rekat_kk = DB::connection('sirekat')->select("select tb_unit.idunit,tb_unit.unitkerja,tb_rekat.id_sub_judul,tb_rekat.sub_judul, tb_rekat.rincian_komponen AS rk_rekat, tb_rekat.unit_kerja AS uk, tb_rabkegiatan.id_kegiatan as id_keg, tb_rabperalatan.id_kegiatan as id_per, tb_rabgedung.id_kegiatan as id_gdg, FORMAT(tb_rabkegiatan.jumlah_biaya,0) as total_keg,FORMAT(tb_rabperalatan.jumlah_biaya,0) as total_per,FORMAT(tb_rabgedung.jumlah_nilai,0) as total_gdg, tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.kebutuhan_kegiatan AS kebutuhan_keg,tb_rabperalatan.kuantitas AS qt_per, tb_rabperalatan.satuan AS satuan_qt_per,tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.kebutuhan_kegiatan AS kebutuhan_per,	tb_rabperalatan.harga_satuan AS biaya_satuan_per,FORMAT((tb_rabgedung.DED_AWAL + tb_rabgedung.DED_REVIEW + tb_rabgedung.nilai_perencanaan 
		+ tb_rabgedung.nilai_struktur + tb_rabgedung.nilai_me + tb_rabgedung.nilai_landscape + tb_rabgedung.nilai_pengawasan),0) AS biaya_satuan_gdg,tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.kebutuhan_kegiatan AS kebutuhan_gdg,tb_rabkegiatan.kuantitas, tb_rabkegiatan.satuan_kuantitas,tb_rabkegiatan.durasi, tb_rabkegiatan.satuan_durasi,
	    tb_rabkegiatan.kegiatan, tb_rabkegiatan.satuan_kegiatan, tb_rabkegiatan.biaya_satuan ,
        tb_rabkegiatan.verifikasi_tim AS verifikasi_tim_keg, 
        tb_rabkegiatan.verifikasi_pimpinan_unit AS verifikasi_pimpinan_keg,
        tb_rabkegiatan.verifikasi_pimpinan_univ AS verifikasi_univ_keg,
        tb_rabperalatan.verifikasi_tim AS verifikasi_tim_per, 
        tb_rabperalatan.verifikasi_pimpinan_unit AS verifikasi_pimpinan_per,
        tb_rabperalatan.verifikasi_pimpinan_univ AS verifikasi_univ_per,
        tb_rabgedung.verifikasi_tim AS verifikasi_tim_gdg, 
        tb_rabgedung.verifikasi_pimpinan_unit AS verifikasi_pimpinan_gdg,
        tb_rabgedung.verifikasi_pimpinan_univ AS verifikasi_univ_gdg
       from tb_rekat LEFT join tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%' OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42' order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");

        $sum_coa = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
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
         WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
         OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
         OR tb_rabperalatan.id_jenis_belanja LIKE '%52%')
         AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
         group by tb_rabkegiatan.unit_kerja,tb_rabkegiatan.id_jenis_belanja,tb_rekat.id_sub_judul");

        $sum_detail_kegiatan = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
		tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
	    tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rpd,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_dk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_dk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_dk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group by tb_rabkegiatan.unit_kerja,tb_rekat.id_sub_judul");

        $sum_unit_kerja = DB::connection('sirekat')->select("select tb_rekat.rincian_komponen,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
		tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
	    tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,  
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_uk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_uk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_uk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group BY tb_rekat.rincian_komponen, tb_rekat.unit_kerja");

        $sum_rincian_komponen = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,tb_rekat.kd_rk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
         tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
         tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,  
         tb_rekat.rincian_komponen, 
         FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RK
             from tb_rekat
             LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
             LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
             LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
             WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
             OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
             OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42' group BY tb_rekat.rincian_komponen");

        $sum_ikv = DB::connection('sirekat')->select("select tb_rekat.rincian_kegiatan,tb_rekat.unit_kerja,
        tb_rekat.rincian_komponen, tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
		tb_rabperalatan.id_jenis_belanja AS id_belanja_per, 
	    tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKV
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group BY tb_rekat.rincian_kegiatan");

        $sum_ikk = DB::connection('sirekat')->select("select tb_rekat.unit_kerja, tb_rekat.indikator_kinerja_kegiatan,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_ikk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_ikk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_ikk_gdg,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_IKK
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group BY tb_rekat.indikator_kinerja_kegiatan");
        
        $sum_ss = DB::connection('sirekat')->select("select tb_rekat.unit_kerja, tb_rekat.sasaran_program,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_ss_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_ss_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_ss_gdg,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SS
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group BY tb_rekat.sasaran_program");
        $sum_sd = DB::connection('sirekat')->select("select tb_rekat.sd, tb_rekat.unit_kerja, tb_rekat.sasaran_program,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SD
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%' 
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        group BY tb_rekat.sasaran_program, tb_rekat.sd");
        return response()->json(["kro" => $kro, "ro" => $ro, "kp" => $kp, "sk" => $sk, 
        "unit_kerja" => $unit_kerja, "detail_kegiatan" => $detail_kegiatan
        ,"coa" => $sum_coa, "rekat_coa" => $coa, "rekat_kk" => $rekat_kk, 
        "sum_detail_kegiatan" => $sum_detail_kegiatan, "sum_unit_kerja" => $sum_unit_kerja,
        "sum_rincian_komponen" => $sum_rincian_komponen,"sum_ikv" => $sum_ikv,"sum_ikk" => $sum_ikk, "sum_ss" => $sum_ss, "sum_sd" => $sum_sd]);
    }

    public function getUnitKerja(Request $req){
        $id_unit = $req->unitkerja;
        if($id_unit == "semua-unit"){
            $data = Rekat::leftjoin("tb_rabgedung", "tb_rekat.id", "=", "tb_rabgedung.id_rekat")
            ->leftjoin("tb_rabkegiatan", "tb_rekat.id", "=", "tb_rabkegiatan.id_rekat")
            ->leftjoin("tb_rabperalatan", "tb_rekat.id", "=", "tb_rabperalatan.id_rekat")
            ->select("tb_rekat.*", DB::raw('SUM(tb_rabgedung.jumlah_nilai) AS TOTAL_GEDUNG'), 
            DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabgedung.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_GDG"),
            DB::raw('SUM(tb_rabkegiatan.jumlah_biaya) AS TOTAL_KEGIATAN'), 
            DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabkegiatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_KEG"),
            DB::raw('SUM(tb_rabperalatan.jumlah_biaya) AS TOTAL_PERALATAN'), 
            DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabperalatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_PER"))->where( DB::raw('tb_rekat.tahun'), '=', $req->tahun)->groupBy('id')->get();
            return $data;
        }
        // $data = Rekat::where('unit_kerja', '=', $id_unit)->where('verifikasi_tim', '=', 'Setuju')->where('verifikasi_pimpinan_unit', '=', 'Setuju')->whereYear('created_at', '=', $req->tahun)->get();
        $data = Rekat::leftjoin("tb_rabgedung", "tb_rekat.id", "=", "tb_rabgedung.id_rekat")
        ->leftjoin("tb_rabkegiatan", "tb_rekat.id", "=", "tb_rabkegiatan.id_rekat")
        ->leftjoin("tb_rabperalatan", "tb_rekat.id", "=", "tb_rabperalatan.id_rekat")
        ->select("tb_rekat.*", DB::raw('SUM(tb_rabgedung.jumlah_nilai) AS TOTAL_GEDUNG'), 
        DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabgedung.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_GDG"),
        DB::raw('SUM(tb_rabkegiatan.jumlah_biaya) AS TOTAL_KEGIATAN'), 
        DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabkegiatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_KEG"),
        DB::raw('SUM(tb_rabperalatan.jumlah_biaya) AS TOTAL_PERALATAN'), 
        DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabperalatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_PER"))
        ->where('tb_rekat.unit_kerja', '=', $id_unit)->where( DB::raw('tb_rekat.tahun'), '=', $req->tahun)->groupBy('id')->get();
        return $data;
    }
    public function index(){
        $raw_rekat = Rekat::leftjoin("tb_rabgedung", "tb_rekat.id", "=", "tb_rabgedung.id_rekat")
        ->leftjoin("tb_rabkegiatan", "tb_rekat.id", "=", "tb_rabkegiatan.id_rekat")
        ->leftjoin("tb_rabperalatan", "tb_rekat.id", "=", "tb_rabperalatan.id_rekat")
        ->select("tb_rekat.*", DB::raw('SUM(tb_rabgedung.jumlah_nilai) AS TOTAL_GEDUNG'), 
        DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabgedung.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_GDG"),
        DB::raw('SUM(tb_rabkegiatan.jumlah_biaya) AS TOTAL_KEGIATAN'), 
        DB::connection('sirekat')->select("(SELECT GROUP_CONCAT(tb_rabkegiatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_KEG"),
        DB::raw('SUM(tb_rabperalatan.jumlah_biaya) AS TOTAL_PERALATAN'), 
        DB::raw("(SELECT GROUP_CONCAT(tb_rabperalatan.kebutuhan_kegiatan SEPARATOR ', ')) AS KEBUTUHAN_PER"));
       
        $unitkerja = session()->get('unitkerja', '');
        if (!in_array(session()->get('role', 'Admin'), ["superadmin", 'admin'])) {
            $raw_rekat      = $raw_rekat->where('tb_rekat.unit_kerja', '=', $unitkerja)->groupBy('id');
        }else{
            $raw_rekat     = $raw_rekat->groupBy('id');
        }
        // $rekat_report = ;
        $raw_rekat = $raw_rekat->get();
        $rekat = Rekat::all();
        $ikk = IKK::select("kd_ss")->distinct()->get();
        $semua_unit = DB::connection('sirekat')->select("select * from tb_unit");
        $semua_tahun = DB::connection('sirekat')->select("select tahun from tb_tahun");
        return view('content.laporan.REKAT.index', compact('ikk','rekat','raw_rekat', 'semua_unit', 'semua_tahun'));
    }

    public function laprka(){
        return view('content.laporan.REKAT.coba');
    }
    public function pdf(){
        // ambil tahun nya saja
        $tahun = explode("_", session()->get('tahun', 'tahun_2025'))[1]; 
        return view('content.laporan.REKAT.pdf', compact('tahun'));
        // $unitkerja = session()->get('unitkerja', '');
        // $rekat = Rekat::where('verifikasi_tim', '=', 'Setuju')->where('verifikasi_pimpinan_unit', '=', 'Setuju')->where('unit_kerja', '=', $unitkerja)->orderBy('sasaran_program', 'ASC')->get();
        // $ttd = DB::connection('sirekat')->select("SELECT * FROM tb_penandatangan WHERE unit_kerja = '$unitkerja' LIMIT 1"));
        // return view('content.laporan.REKAT.pdf', compact('rekat', 'ttd'));
    }
    public function excel(){
        return Excel::download(new RekatExport, date('Y-m-d H') . '-LaporanRekat.xlsx');
    }
    
    // fungsi untuk menampilkan preview data rekat untuk semua rab berupa preview pdf
    public function rekatpdf(){
        $unitkerja = session()->get('unitkerja', '');
        $rekat = DB::connection('sirekat')->select("SELECT tb_rekat.*, 
        (SELECT GROUP_CONCAT(tb_rabgedung.kebutuhan_kegiatan SEPARATOR ',')) AS KEBUTUHAN_GDG,
        (SELECT SUM(tb_rabgedung.jumlah_nilai)) AS TOTAL_GEDUNG,
        (SELECT GROUP_CONCAT(tb_rabkegiatan.kebutuhan_kegiatan SEPARATOR ',')) AS KEBUTUHAN_KEG,
        (SELECT SUM(tb_rabkegiatan.jumlah_biaya)) AS TOTAL_KEGIATAN,
        (SELECT GROUP_CONCAT(tb_rabperalatan.kebutuhan_kegiatan SEPARATOR ',')) AS KEBUTUHAN_PER,
        (SELECT SUM(tb_rabperalatan.jumlah_biaya)) AS TOTAL_PERALATAN
        FROM tb_rekat
        LEFT JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
        LEFT JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
        LEFT JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
        GROUP BY id ORDER BY tb_rekat.sasaran_program ASC"); 
        $ttd = DB::connection('sirekat')->select("SELECT * FROM tb_penandatangan WHERE unit_kerja = '$unitkerja' LIMIT 1");
        return view('content.laporan.REKAT.rekatrabpdf', compact('rekat', 'ttd'));
    }
    // fungsi untuk menampilkan preview data rekat untuk semua rab 
    public function rekatxls(){
        return Excel::download(new RekatRabExport, date('Y-m-d H') . '-LaporanRekatRab.xlsx');
    }
}
