<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perkin;
use App\Models\MasterUnitApi;
use Excel;
use App\Exports\PerkinExport;
use App\Models\Rekat;
use App\Models\SumberDana;
use Illuminate\Contracts\View\View;

class PerkinController extends Controller {
    public function index() {
        ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $role       = session('role');
        $unitKerja  = Rekat::with(["unitApi"])->select("unit_kerja")->where(["tahun" => $tahun, "is_deleted" => "false"])->distinct()->get();
        $sumberdana = SumberDana::where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])->get();
        return view('content.laporan.PERKIN.index', compact("unitKerja", 'sumberdana', 'role', 'tahunAngka'));
    }
    public function getData(Request $req) {
        ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $idunit = $req->input('idunit', []);
        if (empty($idunit)) return response()->json(["success" => false, "message" => "Unit kerja tidak boleh kosong"], 400);

        // Convert the array of unit IDs into a comma-separated string for SQL IN clause
        $idunitIn = implode(',', array_map('intval', $idunit));
        try {
            $query = "WITH BaseData AS (SELECT rab.id,
                         rkt.id AS id_rekat,
                         rkt.kd_rk,
                         rab.id_mak,
                         rkt.unit_kerja AS unit_kerja_rkt,
                         rkt.sd         AS kd_sumberdana,
                         rab.jumlah_biaya,
                         rkt.sub_judul,
                         'OPERASIONAL'  AS rab_type
                  FROM tb_rekat rkt INNER JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id AND rab.is_deleted = 'false' AND rab.unit_kerja IN ($idunitIn)
                  WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                  UNION ALL
                  SELECT rab.id,
                         rkt.id AS id_rekat,
                         rkt.kd_rk,
                         rab.id_mak,
                         rkt.unit_kerja AS unit_kerja_rkt,
                         rkt.sd         AS kd_sumberdana,
                         rab.jumlah_biaya,
                         rkt.sub_judul,
                         'SARANA'       AS rab_type
                  FROM tb_rekat rkt INNER JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id AND rab.is_deleted = 'false' AND rab.unit_kerja IN ($idunitIn)
                  WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                  UNION ALL
                  SELECT rab.id,
                         rkt.id AS id_rekat,
                         rkt.kd_rk,
                         rab.id_mak,
                         rkt.unit_kerja   AS unit_kerja_rkt,
                         rkt.sd           AS kd_sumberdana,
                         rab.jumlah_nilai AS jumlah_biaya,
                         rkt.sub_judul,
                         'PRASARANA'      AS rab_type
                  FROM tb_rekat rkt INNER JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id AND rab.is_deleted = 'false' AND rab.unit_kerja IN ($idunitIn)
                  WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                  UNION ALL
                  SELECT rab.id,
                         rkt.id AS id_rekat,
                         rkt.kd_rk,
                         rab.id_mak,
                         rkt.unit_kerja       AS unit_kerja_rkt,
                         rkt.sd               AS kd_sumberdana,
                         rab.jumlah_biaya,
                         rkt.sub_judul,
                         UPPER(rab.jenis_rab) AS rab_type
                  FROM tb_rekat rkt INNER JOIN tb_rab rab ON rab.id_rekat = rkt.id AND rab.is_deleted = '0'
                  WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false' AND rkt.unit_kerja IN ($idunitIn) ),
                Realisasi AS (SELECT id_mak,
                          SUM(COALESCE(jumlah_amprahan, 0))  AS jumlah_amprahan,
                          SUM(COALESCE(jumlah_realisasi, 0)) AS jumlah_realisasi
                   FROM tb_realisasi
                   WHERE is_deleted = 'false' AND is_posting = 'true'
                   GROUP BY id_mak),
                   RealisasiTerpakai AS (SELECT id_rab,
                                  jenis_rab,
                                  SUM(COALESCE(dipakai, 0)) AS dipakai,
                                  SUM(COALESCE(sisa, 0))    AS sisa
                           FROM tb_realisasi_terpakai
                           WHERE is_deleted = 'false'
                           GROUP BY id_rab, jenis_rab),
                    SemulaMenjadi AS (SELECT id_rab,
                              jenis_rab,
                              SUM(COALESCE(jumlah_menjadi, 0)) AS jumlah_menjadi,
                              SUM(COALESCE(jumlah_semula, 0))  AS jumlah_semula
                       FROM tb_semula_menjadi
                       WHERE is_deleted = 'false' AND status = '' AND jenis_validasi = 'Penambahan'
                       GROUP BY id_rab, jenis_rab),
                    paguAlokasi AS (
                        SELECT a.unit_kerja, SUM(coalesce(a.pagu,0) + coalesce(a.pagu_tambahan,0)) AS pagu FROM tb_alokasi a
                        WHERE a.is_deleted = 'false' AND tahun = '$tahun'
                        GROUP BY a.unit_kerja
                    )
            SELECT sd.sumberdana,
                    unit.nama                                                                                                AS nama_unit,
                    kro.kode_ss, kro.sasaran_program                                                                         AS ss, 
                    kro.kode_ss, kro.sasaran_program                                                                         AS ss, 
                    iku.kode_ikk, iku.indikator_kinerja_kegiatan                                                             AS ikk,
                    ikv.kode_ikk_sekjen, ikv.kode_ikk_dirjen, ikv.kode_ikv_usk, ikv_usk, ikv.kode_ikv, ikv.ikv, ikv.baseline_awal, ikv.target_akhir, 
                    pagu.pagu,
                SUM(CASE
                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN COALESCE(amprah.jumlah_amprahan, 0) + COALESCE(amprah.jumlah_realisasi, 0) + COALESCE(rt.sisa, 0) +
                                COALESCE(sm.jumlah_menjadi, 0) - COALESCE(sm.jumlah_semula, 0)
                    ELSE COALESCE(rkat.jumlah_biaya, 0) + COALESCE(sm.jumlah_menjadi, 0) - COALESCE(sm.jumlah_semula, 0) END ) AS jumlah_biaya_revisi,
                    rkat.jumlah_biaya                                                                                          AS jumlah_biaya_usulan,
                rkat.id_rekat, rkat.sub_judul
            FROM BaseData rkat
                LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = '$tahunAngka'
                INNER JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                INNER JOIN paguAlokasi pagu ON pagu.unit_kerja = rkat.unit_kerja_rkt
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana AND sd.tahun = '$tahunAngka' AND sd.is_deleted = 'false' AND sd.is_show = 'true'
                LEFT JOIN Realisasi amprah ON amprah.id_mak = rkat.id_mak
                LEFT JOIN RealisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                LEFT JOIN SemulaMenjadi sm ON sm.id_rab = rkat.id AND sm.jenis_rab = rkat.rab_type
                GROUP BY rkat.sub_judul
                ORDER BY kro.kode_ss, ikv.kode_ikk_sekjen, ikv.kode_ikk_sekjen, ikv.kode_ikv_usk";
            $data = DB::connection('sirekat')->select($query);
            return response()->json(["success" => true, "data" => $data, "request" => $req->all()], 200);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => "Gagal mendapatkan data", "error" => $th->getMessage(), "trace" => $th->getTrace()], 500);
        }
    }

    public function pdf(Request $request): View {
        // ID unit divalidasi dari URL sebelum dipakai oleh halaman PDF untuk mengambil data laporan.
        $validated = $request->validate([
            'idunit' => ['required', 'integer', 'exists:tb_unit_api,idunit'],
        ]);
        ["tahunAngka" => $tahunAngka] = getTahunData();
        $unitKerja = MasterUnitApi::select('idunit', 'nama')
            ->where('idunit', $validated['idunit'])
            ->firstOrFail();

        return view('content.laporan.PERKIN.pdf', [
            'idunit' => (int) $unitKerja->idunit,
            'namaUnit' => $unitKerja->nama,
            'tahunAngka' => $tahunAngka,
        ]);
    }
}
