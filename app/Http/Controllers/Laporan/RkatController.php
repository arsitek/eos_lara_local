<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\Rekat;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RkatController extends Controller {
    public function index(){
        $unitkerja = session()->get('unitkerja', '');
        // $query = 'select * from tb_rabkegiatan keg left join tb_unit tu on keg.unit_kerja=tu.idunit left join rangka rangka on keg.rincian_komponen= rangka.nama_sk where keg.unit_kerja is not null and rangka.nama_ak = keg.jenis_belanja and unit_kerja = "' . $unitkerja . '"';
        // $query2 = 'select * from tb_rabgedung ged left join tb_unit tu on ged.unit_kerja=tu.idunit left join rangka rangka on ged.rincian_komponen= rangka.nama_sk where ged.unit_kerja is not null and rangka.nama_ak = ged.jenis_belanja and unit_kerja = "' . $unitkerja . '"';
        // $query3 = 'select * from tb_rabperalatan alat left join tb_unit tu on alat.unit_kerja=tu.idunit left join rangka rangka on alat.rincian_komponen= rangka.nama_sk where alat.unit_kerja is not null and rangka.nama_ak = alat.jenis_belanja and unit_kerja = "' . $unitkerja . '"';
        // $reportKegiatan = DB::connection('sirekat')->select($query));
        // $reportGedung = DB::connection('sirekat')->select($query2));
        // $reportPeralatan = DB::connection('sirekat')->select($query3));
        // $rab = $reportGedung + $reportKegiatan + $reportPeralatan;
        $sk          = DB::raw("SELECT DISTINCT kd_sk, nama_sk FROM rangka ORDER BY CAST(mid(kd_sk,11,3) AS unsigned) DESC");
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.index');
    }
    public function pdf(){
        return view('content.laporan.RKAT.LAMPIRAN.pdf');
    }
    public function indexDenganPembatasan() {
        return view('content.laporan.RKAT.PEMBATASAN.index');
    }
    public function indexTanpaPembatasan() {
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.index');
    }
    public function indexTanpaPembatasanSatu() {
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.SATU.main');
    }
    public function indexTanpaPembatasanDua() {
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.DUA.index');
    }
    public function indexTanpaPembatasanTiga() {
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.TIGA.index');
    }
    public function indexDenganPembatasanFilter($kd_kro, $kd_ro){
        return view('content.laporan.RKAT.TANPA_PEMBATASAN.main');
    }
    public function sync($kd_kro, $kd_ro){
        /** ✅ Baris 28 - 32: variabel yg mengambil data dari tabel master(rangka)
         * kp = komponen, sk = sub komponen */
        $kd_kro      = str_replace("0","", $kd_kro);
        $tahun       = session()->get('tahun', 'tahun_2025');
        $sumber_dana = DB::raw("SELECT DISTINCT kd_beban AS sd, nama_beban FROM rangka");
        $kro         = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, nama_kro FROM rangka WHERE kd_kro = '$kd_kro' ");
        $ro          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, nama_ro FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro' ORDER BY kd_ro DESC");
        $kp          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, kd_kp, nama_kp FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro'  ORDER BY CAST(kd_kp as unsigned) DESC");
        $sk          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, kd_kp, kd_sk, nama_sk, ekuivalensi FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro'
                        AND kd_beban = '41'  ORDER BY kd_sk DESC");
        // ✅ Baris 35 - ? seleksi data dari tabel rekat
        $rekat_unitkerja = DB::connection('sirekat')->select("SELECT DISTINCT tb_rekat.sub_judul,tb_rekat.kd_rk from tb_rekat WHERE tb_rekat.tahun = '$tahun'
        AND MID(kd_rk, 4, 1) = '1' OR MID(kd_rk, 4, 1) = '2' OR MID(kd_rk, 4, 1) = '3' 
        AND MID(kd_rk, 8, 1) <> 'V' AND MID(kd_rk, 8, 1) <> 'W'");
        $rekat_dk        = DB::connection('sirekat')->select("SELECT DISTINCT tb_rekat.id, tb_rekat.sub_judul,tb_rekat.kd_rk from tb_rekat 
                            LEFT JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk 
                            WHERE tahun = '$tahun' AND tb_rekat.sd = '41'");
        $rekat_coa      = DB::connection('sirekat')->select("select DISTINCT tb_rekat.kd_rk, tb_rekat.unit_kerja,tb_rekat.sub_judul,tb_rekat.id_sub_judul,tb_rekat.rincian_komponen as rk_rekat, tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.jenis_belanja AS belanja_keg, tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.jenis_belanja AS belanja_per, tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.jenis_belanja AS belanja_gdg
        from tb_rekat left join tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
        left join tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
        left join tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') 
        AND MID(kd_rk, 4, 1) = '1' OR MID(kd_rk, 4, 1) = '2' OR MID(kd_rk, 4, 1) = '3'
        AND MID(kd_rk, 8, 1) <> 'V' AND MID(kd_rk, 8, 1) <> 'W'
        AND tb_rekat.tahun = '$tahun' 
        GROUP BY tb_rabkegiatan.id_jenis_belanja, tb_rabgedung.id_jenis_belanja, tb_rabperalatan.id_jenis_belanja
        ORDER BY tb_rabkegiatan.id_jenis_belanja desc, tb_rabgedung.id_jenis_belanja desc, 
        tb_rabperalatan.id_jenis_belanja desc");
        $rekat_kk        = DB::connection('sirekat')->select("SELECT tb_unit.idunit,tb_unit.unitkerja,tb_rekat.id_sub_judul,tb_rekat.kd_rk,
        tb_rekat.sub_judul, tb_rekat.rincian_komponen AS rk_rekat, tb_rabkegiatan.id_kegiatan as id_keg, 
        tb_rabperalatan.id_kegiatan as id_per, tb_rabgedung.id_kegiatan as id_gdg,
        tb_rekat.unit_kerja AS uk, FORMAT(tb_rabkegiatan.jumlah_biaya,0) as total_keg,
        FORMAT(tb_rabperalatan.jumlah_biaya,0) as total_per,
        FORMAT(tb_rabgedung.jumlah_nilai,0) as total_gdg,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabkegiatan.kebutuhan_kegiatan AS kebutuhan_keg,
        tb_rabperalatan.kuantitas AS qt_per,
        tb_rabperalatan.satuan AS satuan_qt_per,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabperalatan.kebutuhan_kegiatan AS kebutuhan_per,
        tb_rabperalatan.harga_satuan AS biaya_satuan_per,
        FORMAT((tb_rabgedung.DED_AWAL + tb_rabgedung.DED_REVIEW + tb_rabgedung.nilai_perencanaan + tb_rabgedung.nilai_struktur + tb_rabgedung.nilai_me + tb_rabgedung.nilai_landscape + tb_rabgedung.nilai_pengawasan),0) AS biaya_satuan_gdg,
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
               tb_rabkegiatan.kegiatan, tb_rabkegiatan.satuan_kegiatan, FORMAT(tb_rabkegiatan.biaya_satuan,0) as biaya_satuan 
               from tb_rekat 
               LEFT join tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat 
               LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat 
               LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat 
               INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit 
               WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%' OR 
               tb_rabkegiatan.id_jenis_belanja LIKE '%51%' OR 
               tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun' 
                AND MID(kd_rk, 4, 1) = '1' OR MID(kd_rk, 4, 1) = '2' OR MID(kd_rk, 4, 1) = '3'
                AND MID(kd_rk, 8, 1) <> 'V' AND MID(kd_rk, 8, 1) <> 'W'
                order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");
       $sum_coa          = DB::connection('sirekat')->select("SELECT tb_rekat.id, tb_rekat.id_sub_judul,tb_rekat.unit_kerja, tb_rekat.kd_rk,
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
       AND MID(kd_rk, 4, 1) = '1' OR MID(kd_rk, 4, 1) = '2' OR MID(kd_rk, 4, 1) = '3'
       AND MID(kd_rk, 8, 1) <> 'V' AND MID(kd_rk, 8, 1) <> 'W'
       group by tb_rabkegiatan.unit_kerja,tb_rabkegiatan.id_jenis_belanja,tb_rekat.id_sub_judul");
    //    return response()->json($sum_coa, 200);
        $sum_dk         = DB::connection('sirekat')->select("select tb_rekat.sub_judul, tb_rekat.kd_rk,
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
        INNER JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group by tb_rekat.sub_judul");
        $sum_uk = DB::connection('sirekat')->select("select tb_rekat.rincian_komponen,tb_rekat.unit_kerja,
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
        group BY tb_rekat.rincian_komponen, tb_rekat.unit_kerja");
        $sum_rk = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.kd_rk,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RK,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.kd_rk");
        // $sum_rk = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        // tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        // tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        // tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        // tb_rekat.rincian_komponen,rangka.nama_sk,
        // FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RK,
        // FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        // FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        // FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        // from tb_rekat
        // LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        // LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        // LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        // INNER JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        // WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        // OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        // OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        // group BY tb_rekat.rincian_komponen, tb_rekat.unit_pelaksana");
        $sum_kp          = DB::connection('sirekat')->select("select rangka.kd_kp,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KP,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON tb_rekat.kd_rk = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun' AND rangka.kd_ro = '$kd_ro' AND rangka.kd_kro = '$kd_kro'
        group BY rangka.kd_kp");
        $sum_ro          = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_kegiatan, tb_rekat.kd_rk, rangka.kd_kro, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RO,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON tb_rekat.kd_rk = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun' AND rangka.kd_ro = '$kd_ro' AND rangka.kd_kro = '$kd_kro'
        group BY rangka.kd_ro");
        $sum_kro        = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,rangka.nama_kro,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_kegiatan, tb_rekat.kd_rk, rangka.kd_kro, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KRO,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        LEFT JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_kro");
        $sum_sd = DB::connection('sirekat')->select("select tb_rekat.sd, tb_rekat.unit_kerja, tb_rekat.sasaran_program,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SD
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%51%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%51%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%51%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_kro, tb_rekat.sd");
        return response()->json(["success" => true, "data" => [
            "sumber_dana" => $sumber_dana, "kro" => $kro, "ro" => $ro, "kp" => $kp, "sk" => $sk,
            "rekat_unitkerja" => $rekat_unitkerja, "rekat_detail_kegiatan" => $rekat_dk,
            "rekat_jenis_belanja" => $rekat_coa, "rekat_kebutuhan_keg" => $rekat_kk, "sum_coa" => $sum_coa,
            "sum_detail_keg" => $sum_dk, "sum_unitkerja" => $sum_uk, "sum_rincian_kom" => $sum_rk, "sum_komponen" => $sum_kp,"sum_rincian_out" => $sum_ro, "sum_kro" => $sum_kro, "sum_sd" => $sum_sd
        ]]);
    }
    public function syncApbn(){
        /* ✅ Baris 188 -? adalah variabel yang di mengambil data dari tabel master(rangka)
        kp = komponen, sk = sub komponen */
        $tahun       = session()->get('tahun', 'tahun_2025');
        $kro         = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, nama_kro FROM rangka");
        $ro          = DB::raw("SELECT DISTINCT LEFT(kd_sk, 5) AS kd_sk, kd_ro, nama_ro FROM rangka ORDER BY kd_ro DESC");
        $kp          = DB::raw("SELECT DISTINCT LEFT(kd_sk, 7) AS kd_sk, kd_kp, nama_kp FROM rangka ORDER BY CAST(kd_kp as unsigned) DESC");
        $sk          = DB::raw("SELECT DISTINCT kd_sk, nama_sk FROM rangka ORDER BY CAST(mid(kd_sk,11,3) AS unsigned) DESC");
        // ✅ Baris 35 - ? seleksi data dari tabel rekat
        $rekat_unitkerja = DB::connection('sirekat')->select("SELECT tb_rekat.rincian_komponen, rangka.nama_sk, tb_rekat.id_sub_judul,
        tb_unit.unitkerja, tb_rekat.unit_kerja, tb_rekat.unit_pelaksana from tb_rekat
        INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit
        INNER JOIN rangka ON rangka.ekuivalensi = CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') WHERE tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'");
        $rekat_dk       = Rekat::SELECT("id_sub_judul", "sub_judul", "rincian_komponen", "unit_kerja", "rpd")
                            ->where(['tahun' => $tahun, 'sd' => '42'])->get();
        $rekat_coa      = DB::connection('sirekat')->select("select DISTINCT tb_rekat.unit_kerja,tb_rekat.sub_judul,tb_rekat.id_sub_judul,tb_rekat.rincian_komponen as rk_rekat, tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.jenis_belanja AS belanja_keg, tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.jenis_belanja AS belanja_per, tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.jenis_belanja AS belanja_gdg
        from tb_rekat left join tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                      left join tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
                      left join tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' AND tb_rekat.sd = '42'
        order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");
        $rekat_kk        = DB::connection('sirekat')->select("select tb_unit.idunit,tb_unit.unitkerja,tb_rekat.id_sub_judul,tb_rekat.sub_judul, tb_rekat.rincian_komponen AS rk_rekat, tb_rekat.unit_kerja AS uk, tb_rabkegiatan.id_kegiatan as id_keg, tb_rabperalatan.id_kegiatan as id_per, tb_rabgedung.id_kegiatan as id_gdg,FORMAT(tb_rabkegiatan.jumlah_biaya,0) as total_keg,FORMAT(tb_rabperalatan.jumlah_biaya,0) as total_per,FORMAT(tb_rabgedung.jumlah_nilai,0) as total_gdg, tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,tb_rabkegiatan.kebutuhan_kegiatan AS kebutuhan_keg,tb_rabperalatan.kuantitas AS qt_per, tb_rabperalatan.satuan AS satuan_qt_per,tb_rabperalatan.id_jenis_belanja AS id_belanja_per,tb_rabperalatan.kebutuhan_kegiatan AS kebutuhan_per,tb_rabperalatan.harga_satuan AS biaya_satuan_per,FORMAT((tb_rabgedung.DED_AWAL + tb_rabgedung.DED_REVIEW + tb_rabgedung.nilai_perencanaan + tb_rabgedung.nilai_struktur + tb_rabgedung.nilai_me + tb_rabgedung.nilai_landscape + tb_rabgedung.nilai_pengawasan),0) AS biaya_satuan_gdg,tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rabgedung.kebutuhan_kegiatan AS kebutuhan_gdg,tb_rabkegiatan.kuantitas, tb_rabkegiatan.satuan_kuantitas,tb_rabkegiatan.durasi, tb_rabkegiatan.satuan_durasi,
		 tb_rabkegiatan.verifikasi_tim AS verifikasi_tim_keg,
		 tb_rabkegiatan.verifikasi_pimpinan AS verifikasi_pimpinan_keg,
		 tb_rabkegiatan.verifikasi_pimpinan_univ AS verifikasi_univ_keg,
		 tb_rabperalatan.verifikasi_tim AS verifikasi_tim_per,
		 tb_rabperalatan.verifikasi_pimpinan AS verifikasi_pimpinan_per,
		 tb_rabperalatan.verifikasi_pimpinan_univ AS verifikasi_univ_per,
		 tb_rabgedung.verifikasi_tim AS verifikasi_tim_gdg,
		 tb_rabgedung.verifikasi_pimpinan AS verifikasi_pimpinan_gdg,
		 tb_rabgedung.verifikasi_pimpinan_univ AS verifikasi_univ_gdg,
	   tb_rabkegiatan.kegiatan, tb_rabkegiatan.satuan_kegiatan, tb_rabkegiatan.biaya_satuan from tb_rekat LEFT join tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat INNER JOIN tb_unit ON tb_rekat.unit_kerja = tb_unit.idunit WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
       OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
       OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun' order BY tb_rabkegiatan.id_jenis_belanja desc,tb_rabgedung.id_jenis_belanja desc,tb_rabperalatan.id_jenis_belanja desc");
       $sum_coa          = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
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
       OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
       group by tb_rabkegiatan.unit_kerja,tb_rabkegiatan.id_jenis_belanja,tb_rekat.id_sub_judul");
        $sum_dk         = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
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
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group by tb_rekat.unit_kerja,tb_rekat.id_sub_judul");
        $sum_uk = DB::connection('sirekat')->select("select tb_rekat.rincian_komponen,tb_rekat.unit_kerja,
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
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.rincian_komponen, tb_rekat.unit_kerja");
        $sum_rk = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,rangka.nama_sk,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_komponen,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RK,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON rangka.ekuivalensi = CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.')
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY tb_rekat.rincian_komponen");
        $sum_kp          = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,rangka.nama_kp,rangka.kd_ro,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_kegiatan, tb_rekat.kd_rk, rangka.kd_kp, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KP,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        LEFT JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_kp,rangka.kd_ro, rangka.kd_kro");
        $sum_ro          = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_kegiatan, tb_rekat.kd_rk, rangka.kd_kro, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RO,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        LEFT JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_ro, rangka.kd_kro");
        $sum_kro        = DB::connection('sirekat')->select("select tb_rekat.id_sub_judul,tb_rekat.unit_kerja,
        tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
        tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,
        tb_rekat.rincian_kegiatan, tb_rekat.kd_rk, rangka.kd_kro, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KRO,
        FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_rk_keg,
        FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_rk_per,
        FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_rk_gdg,rangka.nama_kro
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_kro");
        $sum_sd = DB::connection('sirekat')->select("select tb_rekat.sd, tb_rekat.unit_kerja, tb_rekat.sasaran_program,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_SD
		from tb_rekat
		LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        JOIN rangka ON CONCAT(SUBSTR(tb_rekat.kd_rk,2),'.') = rangka.ekuivalensi
        WHERE (tb_rabgedung.id_jenis_belanja LIKE '%52%'
        OR tb_rabkegiatan.id_jenis_belanja LIKE '%52%'
        OR tb_rabperalatan.id_jenis_belanja LIKE '%52%') AND tb_rekat.tahun = '$tahun'
        group BY rangka.kd_kro, tb_rekat.sd");
        return response()->json(["kro" => $kro, "ro" => $ro, "kp" => $kp, "sk" => $sk, "rekat_unitkerja" => $rekat_unitkerja, "rekat_detail_kegiatan" => $rekat_dk,
        "rekat_jenis_belanja" => $rekat_coa, "rekat_kebutuhan_keg" => $rekat_kk, "sum_coa" => $sum_coa,
        "sum_detail_keg" => $sum_dk, "sum_unitkerja" => $sum_uk, "sum_rincian_kom" => $sum_rk, "sum_komponen" => $sum_kp, "sum_rincian_out" => $sum_ro, "sum_kro" => $sum_kro, "sum_sd" => $sum_sd]);
    }
    public function getTanpaPembatasan( $kd_kro, $kd_ro ) {
        $tahun       = session()->get('tahun', 'tahun_2025');
        $sumber_dana = DB::raw("SELECT DISTINCT kd_beban AS sd, nama_beban FROM rangka");
        $ro          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, nama_ro FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro' ORDER BY kd_ro DESC");
        $kp          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, kd_kp, nama_kp FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro'  ORDER BY CAST(kd_kp as unsigned) DESC");
        $sk          = DB::connection('sirekat')->select("SELECT DISTINCT kd_kro, kd_ro, kd_kp, kd_sk, nama_sk, ekuivalensi FROM rangka WHERE kd_kro = '$kd_kro' AND kd_ro = '$kd_ro'
                        AND kd_beban = '41'  ORDER BY kd_sk DESC");
        $rekat_dk    = DB::connection('sirekat')->select("SELECT DISTINCT tb_rekat.id, tb_rekat.unit_kerja, tb_rekat.rpd, tb_rekat.sub_judul,tb_rekat.kd_rk from tb_rekat 
                    LEFT JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk 
                    WHERE tahun = '$tahun' AND tb_rekat.sd = '41' AND rangka.kd_kro = '$kd_kro' AND rangka.kd_ro = '$kd_ro' ");
        $sum_dk         = DB::connection('sirekat')->select("SELECT 
            tb_rekat.sub_judul, tb_rekat.id,
            tb_rabkegiatan.id_jenis_belanja AS id_belanja_keg,
		    tb_rabperalatan.id_jenis_belanja AS id_belanja_per,
	        tb_rabgedung.id_jenis_belanja AS id_belanja_gdg,tb_rekat.rpd,
            FORMAT(sum(tb_rabkegiatan.jumlah_biaya),0) as total_dk_keg,
            FORMAT(sum(tb_rabperalatan.jumlah_biaya),0) as total_dk_per,
            FORMAT(sum(tb_rabgedung.jumlah_nilai),0) as total_dk_gdg
        FROM tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk
        WHERE tb_rekat.tahun = '$tahun' AND rangka.kd_kro = '$kd_kro' AND rangka.kd_ro = '$kd_ro' AND rangka.kd_beban = '41'
        group by tb_rekat.id,tb_rekat.sub_judul");

        $sum_ro = DB::connection('sirekat')->select("SELECT rangka.kd_ro, rangka.kd_kro, rangka.kd_kro, rangka.kd_ro,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + 
        SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
        SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_RO
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON tb_rekat.kd_rk = rangka.ekuivalensi
        WHERE tb_rekat.tahun = '$tahun' AND rangka.kd_beban = '41' and rangka.kd_kro = '$kd_kro' AND rangka.kd_ro = '$kd_ro'
        group BY rangka.kd_ro, rangka.kd_kro");
        
        $sum_rk = DB::connection('sirekat')->select("SELECT 
            tb_rekat.kd_rk,
            FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + 
                SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
                SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) AS TOTAL_RK
        FROM tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON rangka.ekuivalensi = tb_rekat.kd_rk
        WHERE tb_rekat.tahun = '$tahun' 
            AND rangka.kd_beban = '41' 
            AND rangka.kd_kro = '$kd_kro' 
            AND rangka.kd_ro = '$kd_ro'
        GROUP BY tb_rekat.kd_rk");
        $sum_kp      = DB::connection('sirekat')->select("SELECT rangka.kd_kp, rangka.kd_ro, rangka.kd_kro, rangka.nama_kp,
        FORMAT(SUM(coalesce(tb_rabkegiatan.jumlah_biaya,0)) + SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
            SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KP
        from tb_rekat
        LEFT JOIN tb_rabkegiatan ON tb_rekat.id = tb_rabkegiatan.id_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON tb_rekat.kd_rk = rangka.ekuivalensi
        WHERE tb_rekat.tahun = '$tahun' AND rangka.kd_beban = '41' and rangka.kd_kro = '$kd_kro' AND rangka.kd_ro = '$kd_ro'
        group BY rangka.kd_kp, rangka.kd_ro, rangka.kd_kro");

        $sum_skt = DB::connection('sirekat')->select("SELECT  rangka.kd_kp, rangka.kd_ro, rangka.kd_kro, rangka.nama_kp,
        FORMAT(SUM(coalesce(tb_rabgedung.jumlah_nilai,0)) + 
            SUM(coalesce(tb_rabperalatan.jumlah_biaya,0)),0) as TOTAL_KP,
            MID(tb_rekat.kd_rk, 4, 1) AS kode_kro
        from tb_rekat
        LEFT JOIN tb_rabgedung ON tb_rekat.id = tb_rabgedung.id_rekat
        LEFT JOIN tb_rabperalatan ON tb_rekat.id = tb_rabperalatan.id_rekat
        INNER JOIN rangka ON tb_rekat.kd_rk = rangka.ekuivalensi
        WHERE tb_rekat.tahun = '$tahun' AND rangka.kd_beban = '41' and rangka.kd_kro = '$kd_kro' AND rangka.kd_ro = '$kd_ro'
        AND MID(tb_rekat.kd_rk, 4, 1) = '4' AND tb_rekat.kd_rk LIKE '%V%'
        group BY rangka.kd_kp, rangka.kd_ro, rangka.kd_kro"); 
        //  return array($sum_skt, $sum_kp);
        return response()->json(["success" => true, "data" => [
            "ro" => $ro, "kp" => $kp, "sk" => $sk, "rekat_dk" => $rekat_dk, "sum_ro" => $sum_ro, 
            "sum_dk" => $sum_dk, "sum_rk" => $sum_rk, "sum_kp" => $sum_kp, "sum_skt" => $sum_skt
        ]], 200);
    }
}
