<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Datamaster\Kro;
use App\Models\Datapaket\Paket;
use App\Models\Datapaket\PaketDetail;
use App\Models\Komitmen;
use App\Models\SumberDana;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\ApiRkaPejabatController as RKA;
use App\Models\Datapaket\RelasiPaket;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Rekat;
use PDF;

class DatapaketController extends Controller {
    public function index(){
        $tahun      = session('tahun', 'tahun_2025');
        $tahunAngka = explode("_", $tahun)[1];
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->get();
        $ppk        = Komitmen::select("nip", "nama_pejabat")->where(["is_active" => "true", "jenis" => "PPK"])->distinct()->get();
        $masterData  = Kro::with(["ro" => function($q) use ($tahun) {
            $q->where("tahun", $tahun);
        },"ro.ikv" => function($q) use ($tahun) {
            $q->where("tahun", $tahun);
        },"ro.ikv.subkomponen" => function($q) use ($tahun) {
            $q->where("tahun", $tahun);
        },])->where("tahun", $tahunAngka)->get();
        return view('content.laporan.DATAPAKET.index', compact('ppk', 'masterData', 'sumberdana'));
    }
    public function getSumberDanaPPK(Request $req){
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $ppk  = $req->ppk;
            $data = DB::connection('sirekat')->select("SELECT sd.kd_sumberdana, sd.sumberdana FROM (
                SELECT id_rekat, nip_ppk, 'OPERASIONAL' AS jenis FROM tb_rabkegiatan rab
                WHERE rab.is_deleted = 'false' AND rab.is_draft = 'false'
                UNION ALL
                SELECT id_rekat, nip_ppk, 'SARANA' AS jenis FROM tb_rabperalatan rab
                WHERE rab.is_deleted = 'false' AND rab.is_draft = 'false'
                UNION All
                SELECT id_rekat, nip_ppk, 'PRASARANA' AS jenis FROM tb_rabgedung rab
                WHERE rab.is_deleted = 'false' AND rab.is_draft = 'false'
                ) AS basedata
                INNER JOIN tb_rekat rkt ON rkt.id = basedata.id_rekat
                INNER JOIN tb_sumberdana sd ON sd.tahun = '$tahunAngka' AND sd.is_deleted = 'false' AND sd.is_show = 'true' AND rkt.sd = sd.kd_sumberdana
                WHERE rkt.tahun = '$tahun' AND basedata.nip_ppk = '$ppk'
            GROUP BY sd.kd_sumberdana, sd.sumberdana");
            return response()->json(["success" => true, "message" => "Berhasil mendapatkan data sumber dana", "data" => $data], 200);
        } catch ( \Exception $e ) {
            return response()->json(["success" => false, "message" => "Terjadi kesalahan saat memuat data", "error" => $e->getMessage()], 400);
        }
    }
    public function indexSaprasPdf( Request $req ){
        $nip      = $req->nip;
        $tahun    = explode("_",session("tahun", "tahun_2025"))[1];
        $komitmen = Komitmen::select("nip", "nama_pejabat")
                    ->where(["is_active" => "true", "jenis" => "PPK", "nip" => $nip])->first();
        $nama_ppk   = $komitmen ? $komitmen->nama_pejabat : '-';
        return view('content.laporan.DATAPAKET.RKA_SAPRAS.pdf', compact('tahun', 'nama_ppk'));
    }
    public function getItemTerpaketkan(){
        $data = RelasiPaket::where(["is_deleted" => "false"])->get();
        return response()->json(["success" => true, "message" => "Berhasil mendapatkan data", "data" => $data], 200);
    }
    public function show( $id ) {
        try {
            $data = PaketDetail::with("paket")->where("id_paket", $id)->first();
            return response()->json([
                "success" => true,
                "data"    => $data,
                "message" => "Berhasil memuat data detail paket"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json(["success" => false, "message" => $e->getMessage()], 500);
        }
    }
    public function indexPaketPdf( Request $req ){
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $nip_ppk    = $req->ppk;
        $kodeSd     = $req->kode_sd;
        $komitmen   = Komitmen::select("nama_pejabat")->where("nip", $nip_ppk)->first();
        $nama_ppk   = $komitmen ? $komitmen->nama_pejabat : '-';
        $sumberdana = SumberDana::where(["tahun" => $tahunAngka, "is_deleted" => "false", "is_show" => "true", "kd_sumberdana" => $kodeSd])->first();
        return view('content.laporan.DATAPAKET.RKA_PAKET.pdf', compact('tahun', 'nama_ppk', 'sumberdana'));
    }
    public function getRka( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $ppk    = $req->ppk;
            $kodeSd = $req->kode_sd;
            if ( !$ppk || !$kodeSd ) return response()->json(["success" => false, "message" => "PPK dan Sumberdana tidak ditemukan"], 400);
            
            $data = DB::connection('sirekat')->select("SELECT
                pkt.idunit, unit.nama AS nama_unit, sd.kd_sumberdana, sd.sumberdana, pkt.sub_judul, rpd.rpd,
                pkt.satuan_durasi, pkt.durasi, pkt.satuan_kegiatan, pkt.kegiatan, pkt.satuan_kuantitas, pkt.kuantitas,
                ppk.nama_pejabat AS nama_ppk, bpp.nama_pejabat AS nama_bpp,
                pkt.id_mak, pkt.kode_keg, keg.keg, pkt.kode_ikv, ikv.ikv, pkt.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk,
                pkt.kode_ss, ss.sasaran_program AS ss, pkt.jumlah_biaya, realisasi.total_realisasi, realisasi.total_amprah
                FROM tb_paket pkt
                INNER JOIN tb_relasi_paket rp ON rp.id_paket = pkt.id AND rp.is_deleted = 'false'
                INNER JOIN tb_keg_master keg ON keg.kode_keg = pkt.kode_keg AND keg.tahun = ?
                INNER JOIN tb_ikv ikv ON ikv.kode_ikv = pkt.kode_ikv AND ikv.tahun = ?
                INNER JOIN tb_iku iku ON iku.kode_ikk = pkt.kode_ikk AND iku.tahun = ?
                INNER JOIN tb_sasaran ss ON ss.kode_ss = pkt.kode_ss AND ss.tahun = ?
                INNER JOIN tb_unit_api unit ON unit.idunit = pkt.idunit
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = pkt.kd_sumberdana AND sd.tahun = ? AND sd.is_deleted = 'false' AND sd.is_show = 'true'
                LEFT JOIN tb_komitmen ppk ON ppk.id = pkt.id_pejabat AND ppk.is_active = 'true'
                LEFT JOIN tb_komitmen bpp ON bpp.id = pkt.id_bpp AND bpp.is_active = 'true'
                LEFT JOIN (
                    SELECT id_rekat, SUM(jumlah_realisasi) AS total_realisasi, SUM(jumlah_amprahan) AS total_amprah
                    FROM tb_realisasi
                    WHERE tahun = ? AND is_posting = 'true' AND is_deleted = 'false'
                    GROUP BY id_rekat
                ) realisasi ON realisasi.id_rekat = pkt.id_mak
                INNER JOIN (
                    SELECT MAX(rpd) AS rpd, id_paket FROM tb_relasi_paket_rpd rpr
                    GROUP BY rpd, id_paket
                ) rpd ON rpd.id_paket = pkt.id
                WHERE pkt.is_deleted = 'false' AND pkt.tahun = ? AND pkt.kd_sumberdana = ? AND ppk.nip = ?
            GROUP BY unit.nama, rpd.rpd, pkt.idunit, sd.sumberdana, pkt.satuan_durasi, pkt.durasi, pkt.satuan_kegiatan, pkt.kegiatan,
         pkt.satuan_kuantitas, pkt.kuantitas, ppk.nama_pejabat, bpp.nama_pejabat, pkt.id_mak, pkt.kode_keg, keg.keg, pkt.kode_ikv, ikv.ikv, pkt.kode_ikk,
         iku.indikator_kinerja_kegiatan, pkt.kode_ss, ss.sasaran_program, pkt.jumlah_biaya, realisasi.total_realisasi;", [$tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahunAngka, $tahun, $kodeSd, $ppk]);
            return response()->json([
                "success" => true,
                "data"    => $data,
                "message" => "Berhasil mendapatkan data RKA"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json(["success" => false, "message" => "Terjadi kesalahan saat memuat data", "error" => $e->getMessage()], 400);
        }
    }
    public function indexSapras() {
        $tahun      = session('tahun', 'tahun_2025');
        $tahunAngka = explode("_", $tahun)[1];
        $ppk        = Komitmen::select("nip", "nama_pejabat")
                        ->where(["is_active" => "true", "jenis" => "PPK"])->distinct()->get();
        $masterData = Kro::with("ro.ikv.subkomponen")->get();
        $sumberdana = SumberDana::where(["tahun" => $tahunAngka ])->get();
        return view('content.laporan.DATAPAKET.RKA_SAPRAS.index', compact('ppk', 'masterData', 'sumberdana'));
    }
    public function indexPaket() {
        $tahun      = session('tahun', 'tahun_2025');
        $tahunAngka = explode("_", $tahun)[1];
        $sumberdana = SumberDana::where(["tahun" => $tahunAngka])->get();

        $ppk        = Komitmen::select("nip", "nama_pejabat")
                        ->where(["is_active" => "true", "jenis" => "PPK"])->distinct()->get();
        $masterData  = Kro::with(["ro" => function($q) use ($tahunAngka) {
            $q->where("tahun", $tahunAngka);
        },"ro.ikv" => function($q) use ($tahunAngka) {
            $q->where("tahun", $tahunAngka);
        },"ro.ikv.subkomponen" => function($q) use ($tahunAngka) {
            $q->where("tahun", $tahunAngka);
        },])->where("tahun", $tahunAngka)->get();
        return view('content.laporan.DATAPAKET.RKA_PAKET.index', compact('ppk', 'masterData', 'sumberdana'));
    }

}
