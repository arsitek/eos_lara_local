<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AksesRkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\IKK;
use App\Models\MasterUnit;
use App\Models\Komitmen;
use App\Http\Controllers\Api\ApiRkaPejabatController;
use App\Models\MasterUnitApi;
use App\Models\Rekat;
use App\Models\SumberDana;
use App\Events\UserPerformedAction;
use App\Http\Controllers\Api\ApiRkaPejabatController as RKA;
use App\Models\Datamaster\DuplikasiRkat;
use App\Models\Datamaster\Ikv;
use App\Models\Datamaster\Kro;
use App\Models\Datamaster\Ro;
use App\Models\Datamaster\Subkomponen;
use App\Models\Datamaster\SumberDana2;
use App\Models\Datamaster\SumberDana8;
use App\Models\Datapaket\RelasiPaket;
use App\Models\Penandatanganan;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Realisasi;
use App\Models\TanggapanRabGedung;
use App\Models\TanggapanRabKegiatan;
use App\Models\TanggapanRabPeralatan;
use App\Services\Master\UnitkerjaService;
use Illuminate\Validation\Rule;
use App\Models\Datarevisi\SemulaMenjadi;
use App\Models\TanggapanRab;
use App\Models\Datacreator\Rab as RabDatacreator;

class RekatByUnitController extends Controller {
    private $baseData, $mappingRab, $adminRoles;
    protected $unitkerjaService;
    public function __construct(UnitkerjaService $unitkerjaService) {
        $this->unitkerjaService = $unitkerjaService;
        $this->mappingRab = [
            "OPERASIONAL" => [
                "super" => RABKEG::class,
                "tanggapan" => TanggapanRabKegiatan::class,
                "idKolom" => "id_rabkegiatan"
            ],
            "SARANA"      => [
                "super" => RABPER::class,
                "tanggapan" => TanggapanRabPeralatan::class,
                "idKolom" => "id_rabperalatan"
            ],
            "PRASARANA"   => [
                "super" => RABGDG::class,
                "tanggapan" => TanggapanRabGedung::class,
                "idKolom" => "id_rabgedung"
            ],
            "GEDUNG"      => [
                "super" => RABGDG::class,
                "tanggapan" => TanggapanRabGedung::class,
                "idKolom" => "id_rabgedung",
                "jenisRelasi" => "PRASARANA"
            ],
            "LANGGANAN"   => [
                "super" => RabDatacreator::class,
                "tanggapan" => TanggapanRab::class,
                "idKolom" => "id_rab",
                "jenisTanggapan" => "langganan",
                "superWhere" => ["jenis_rab" => "langganan"],
                "jenisRelasi" => "langganan"
            ],
            "BHP"         => [
                "super" => RabDatacreator::class,
                "tanggapan" => TanggapanRab::class,
                "idKolom" => "id_rab",
                "jenisTanggapan" => "bhp",
                "superWhere" => ["jenis_rab" => "bhp"],
                "jenisRelasi" => "bhp"
            ]
        ];
        $this->adminRoles = ["superadmin", "admin", "Majelis Wali Amanat", "Pengawasan Internal", "Verifikator Aset", "Verifikator Keuangan", "Direktur Keuangan", "Analis Resiko"];
    }
    private function normalizeFilterList($value, array $ignoredValues = []): array {
        if (is_null($value)) return [];

        $values = is_array($value) ? $value : explode(',', (string) $value);
        $ignored = array_map('strval', array_merge(['', 'null', 'undefined'], $ignoredValues));

        return array_values(array_unique(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, $values), function ($item) use ($ignored) {
            return !in_array($item, $ignored, true);
        })));
    }

    private function buildSqlInList(array $values): string {
        if (empty($values)) return '';

        return implode(',', array_map(function ($value) {
            return DB::connection('sirekat')->select()->quote($value);
        }, $values));
    }

    public function index() {
        ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $id_unit = session('unitkerja');
        $sumberdana = Rekat::with(["sumberdana" => function ($q) use ($tahunAngka) {
            $q->where(["is_deleted" => "false", "is_show" => "true", "tahun" => $tahunAngka]);
        }])->select("sd")->where(["tahun" => $tahun]);
        if (!in_array(session("role"), ["superadmin", "admin", "Pimpinan USK", "Majelis Wali Amanat", "Pengawasan Internal"]))
            $sumberdana = $sumberdana->where(["unit_kerja" => $id_unit]);
        $sumberdana = $sumberdana->distinct()->get();
        $akses_rkat = AksesRkat::with("sumberdana")->where(["idunit" => $id_unit, "status" => 1])->get();
        $unitkerja  = Rekat::select("unit_kerja")->with("unitApi")->orderBy("unit_kerja")->distinct()->get();
        $ppk        = Komitmen::select("nama_pejabat", "nip", "jenis")->where(["is_active" => "true"])->distinct()->get();
        $dataBackup = DuplikasiRkat::select("id", "keterangan")->where(["tahun" => $tahun])->where("keterangan", "LIKE", '%' . $tahunAngka . '%')->get();
        return view('content.laporan.REKAT_UK.index', compact('unitkerja', 'id_unit', 'akses_rkat', 'sumberdana', 'ppk', 'tahunAngka', 'dataBackup'));
    }
    public function pdf($idunit, $kd_sumberdana) {
        $tahun      = session('tahun', 'tahun_2025');
        $tahunAngka = explode("_", $tahun)[1];
        $unitkerja  = MasterUnitApi::whereIn("idunit", explode(",", $idunit))->first();
        $sumberdana = SumberDana::whereIn("kd_sumberdana", explode(",", $kd_sumberdana))
            ->where(["is_deleted" => "false", "tahun" => $tahunAngka, "is_show" => "true"])
            ->get();
        $bulan      = date("m");
        $tanggal    = date("d");
        $pttd       = Penandatanganan::where(["unit_kerja" => $idunit, "tahun" => session('tahun', 'tahun_2025')])->first();
        $currentDate = date("Y-m-d");
        return view('content.laporan.REKAT_UK.pdf', compact("idunit", "tahunAngka", "unitkerja", "sumberdana", "pttd", "bulan", "tanggal", "currentDate"));
    }
    public function getRka($idunit, $kd_sumberdana) {
        try {
            ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $req        = request();
            $backup     = $req->backup;
            $filter     = $req->filter;
            $idRekats   = $req->idRekats;
            $rpd        = $req->rpd;
            $verif      = $req->verifikasi;
            $unitKerjaList = $this->normalizeFilterList($req->input('unitkerja', $idunit), ['X', 'semua']);
            $sumberDanaList = $this->normalizeFilterList($req->input('kodeSd', $kd_sumberdana), ['semua']);
            $unitKerjaSqlList = $this->buildSqlInList($unitKerjaList);
            $sumberDanaSqlList = $this->buildSqlInList($sumberDanaList);
            $listSd = $sumberDanaList;

            // join untuk data master baru (>= 2026)
            $joinDataMasterBaru = "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master kegMaster ON kegMaster.kode_keg = rik.kode_keg AND kegMaster.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = '$tahunAngka'";
            $joinDataMasterBaruBackup = "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = backupRkat.id_rekat
                LEFT JOIN tb_keg_master kegMaster ON kegMaster.kode_keg = rik.kode_keg AND kegMaster.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = '$tahunAngka'";
            $isNewDataMaster = (int) $tahunAngka >= 2026;
            $filterRekats    = " AND rkat.id_rekat IN ($idRekats) ";
            $filterCondition = ($verif && $verif != "semua") ? " AND rkat.verifikasi_pimpinan_unit = 'Setuju'" : "";
            $filterBackup    = ($verif && $verif != "semua") ? " AND backupRkatDet.verifikasi_pimpinan_unit = 'Setuju'" : "";
            $filterUnits     = $unitKerjaSqlList ? " AND rkat.unit_kerja IN ($unitKerjaSqlList) AND rkat.unit_kerja_rkt IN ($unitKerjaSqlList) " : "";
            $filterBackupUnits = $unitKerjaSqlList ? " AND backupRkat.idunit IN ($unitKerjaSqlList) " : "";
            $filterSumberDana = $sumberDanaSqlList ? " AND rkat.kd_sumberdana IN ($sumberDanaSqlList) " : "";
            $filterBackupSumberDana = $sumberDanaSqlList ? " AND backupRkat.sd IN ($sumberDanaSqlList) " : "";
            $filterPPK       = $req->ppk ? " AND ( rkat.nip_ppk = '$req->ppk') " : "";
            $filterPaket     = "";
            $selectDraft     = "CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                ELSE rkat.jumlah_biaya END AS jumlah_biaya";
            if ($filter == '!realisasi') {
                if ($backup) {
                    $filterBackup = " AND ( backupRkatDet.jumlah_amprahan IS NULL AND backupRkatDet.jumlah_realisasi IS NULL ) ";
                }
                $filterCondition = " AND ( amprah.jumlah_amprahan IS NULL AND amprah.jumlah_realisasi IS NULL ) ";
            } elseif ($filter == 'realisasi') {
                if ($backup) {
                    $filterBackup = " AND ( backupRkatDet.jumlah_amprahan IS NOT NULL AND backupRkatDet.jumlah_realisasi IS NOT NULL ) ";
                }
                $filterCondition = " AND ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) ";
            } else if ($filter == "!verifikasi") {
                $filterCondition = "";
                $filterBackup = "";
            } else if ($filter == "terpaketkan") {
                $filterPaket = " AND pkt.id_mak_paket IS NOT NULL";
                if ($tahunAngka >= 2026)
                    $filterPaket .= " AND ( (rkat.rab_type = 'OPERASIONAL' AND (
                        (rkat.kd_rk IN ('01.02.04', '01.04.05') AND rkat.jumlah_biaya > 50000000)
                        OR
                        (rkat.kd_rk = '01.04.06')
                    ) OR rkat.rab_type IN ('SARANA', 'PRASARANA') ) ) ";
            } else if ($filter == "!terpaketkan") {
                $filterPaket = " AND pkt.id_mak_paket IS NULL";
                if ($tahunAngka >= 2026)
                    $filterPaket .= " AND ( (rkat.rab_type = 'OPERASIONAL' AND (
                        (rkat.kd_rk IN ('01.02.04', '01.04.05') AND rkat.jumlah_biaya > 50000000)
                        OR
                        (rkat.kd_rk = '01.04.06')
                    ) OR rkat.rab_type IN ('SARANA', 'PRASARANA') ) )";
            } else if ($filter == "draft") {
                $selectDraft = "CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                    THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) )
                ELSE rkat.jumlah_biaya + ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) ) END AS jumlah_biaya";
            }
            if (!$req->idRekats)
                $filterRekats = "";
            if ($rpd)
                $filterCondition .= " AND rkat.rpd = '$rpd' ";
            // if backup is true, then get the backup data
            if ($backup) {
                // convert array $backup to string
                if (is_array($backup))
                    $backup = implode(",", $backup);
                // get the backup data
                $baseData = $isNewDataMaster
                    ? getBaseData(" SELECT pkt.id_mak AS id_mak_paket, pkt.id AS id_paket, pkt.sub_judul AS judul_paket, pkt.jumlah_biaya AS total_paket, pkt_rpd.rpd AS rpd_paket,
                            unit.nama AS nama_unit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt,
                            kro.kode_ss, kro.sasaran_program AS ss, backupRkat.kode_keg AS kd_rk, kegMaster.keg AS rincian_kegiatan,
                            ikv.kode_ikv, ikv.ikv, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk,
                            sd.kd_sumberdana, sd.sumberdana, backupRkatDet.*,
                            ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                            ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                            ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                            ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya_revisi,
                            backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                            relo.jumlah_tagihan AS jumlah_tagihan,
                            COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                            COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI,
                            ( SELECT nama_pejabat FROM pejabat WHERE nip = backupRkatDet.nip_ppk ) AS nama_ppk,
                            ( SELECT nama_pejabat FROM pejabat WHERE nip = backupRkatDet.nip_bpp ) AS nama_bpp
                        FROM tb_backup_rkat backupRkat
                        INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                        $joinDataMasterBaruBackup
                        INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                        LEFT JOIN tb_mutasi_percetakan relo ON relo.id_rab = backupRkatDet.id AND relo.jenis = backupRkatDet.rab_type AND relo.is_deleted = 'false'
                        LEFT JOIN tb_relasi_paket rp ON rp.id_rab = backupRkatDet.id AND rp.jenis_rab = backupRkatDet.rab_type AND rp.is_deleted = 'false'
                        LEFT JOIN tb_paket pkt ON pkt.id = rp.id_paket AND pkt.is_posting = 'true' AND pkt.tahun = '$tahun' AND pkt.is_deleted = 'false'
                        LEFT JOIN tb_relasi_paket_rpd pkt_rpd ON pkt_rpd.id_paket = pkt.id
                        WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? ) $filterBackupUnits
                        $filterBackupSumberDana $filterBackup
                    AND backupRkat.tahun = ? GROUP BY backupRkatDet.id_mak ORDER BY substr( kegMaster.kode_keg, 4, 8 )" , $tahun, $tahunAngka, null, null, [ $backup, $backup, $tahun ])
                    : getBaseData(" SELECT pkt.id_mak AS id_mak_paket, pkt.id AS id_paket, pkt.sub_judul AS judul_paket, pkt.jumlah_biaya AS total_paket, pkt_rpd.rpd AS rpd_paket,
                        unit.nama AS nama_unit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt, dm.*, dm.kode_keg AS kd_rk,
                        sd.kd_sumberdana, sd.sumberdana, backupRkatDet.*,
                        ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                            THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                        ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                        ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                            THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                        ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya_revisi,
                        backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                        relo.jumlah_tagihan AS jumlah_tagihan,
                        COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                        COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = backupRkatDet.nip_ppk ) AS nama_ppk,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = backupRkatDet.nip_bpp ) AS nama_bpp
                    FROM tb_backup_rkat backupRkat
                    INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                    INNER JOIN dataMaster dm ON dm.kode_keg = backupRkat.kode_keg
                    INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                    INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                    LEFT JOIN (
                        SELECT  id_rab,  jenis,  SUM(jumlah_tagihan) AS total_tagihan
                        FROM tb_mutasi_percetakan WHERE is_deleted = 'false'
                        GROUP BY id_rab, jenis
                    ) relo ON relo.id_rab = backupRkatDet.id AND relo.jenis = backupRkatDet.rab_type
                    LEFT JOIN tb_relasi_paket rp ON rp.id_rab = rkat.id AND rp.jenis_rab = rkat.rab_type AND rp.is_deleted = 'false'
                    LEFT JOIN tb_paket pkt ON pkt.id = rp.id_paket AND pkt.is_posting = 'true' AND pkt.tahun = '$tahun' AND pkt.is_deleted = 'false'
                    LEFT JOIN tb_relasi_paket_rpd pkt_rpd ON pkt_rpd.id_paket = pkt.id
                    WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? ) $filterBackupUnits
                    $filterBackupSumberDana $filterBackup
                AND backupRkat.tahun = ? GROUP BY backupRkatDet.id_mak ORDER BY substr( dm.kode_keg, 4, 8 )", $tahun, $tahunAngka, null, null, [$backup, $backup, $tahun]);
            } else {
                $joinDataMaster = $isNewDataMaster ? $joinDataMasterBaru : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
                $selectDataMaster = $isNewDataMaster ? "kro.kode_ss, kro.sasaran_program AS ss, kegMaster.kode_keg, kegMaster.keg AS rincian_kegiatan,
                        ikv.kode_ikv, ikv.ikv, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk"
                    : "dm.*, dm.kode_keg, dm.rincian_kegiatan, dm.kode_ikv, dm.ikv, dm.kode_ikk, dm.ikk, dm.kode_ss, dm.ss";

                $baseData = getBaseData("SELECT pkt.id_mak_paket, pkt.id_paket, pkt.sub_judul AS judul_paket, pkt.rpd AS rpd_paket,
                        rkat.*, sd.sumberdana, $selectDataMaster, unit.nama as nama_unit,
                        $selectDraft,
                        CASE WHEN ( amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL ) AND rt.dipakai IS NOT NULL
                            THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0)
                        ELSE rkat.jumlah_biaya + COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) END AS jumlah_biaya_revisi,
                        rkat.jumlah_biaya AS jumlah_biaya_usulan,
                        COALESCE(amprah.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                        COALESCE(amprah.jumlah_realisasi, 0) AS TOTAL_REALISASI,
                        relo_sum.jumlah_tagihan, amprah.is_posting, rt.dipakai as jumlah_pengalihan, rt.sisa as sisa_pengalihan,
                        sm.jenis_validasi, sm.jenis_revisi, sm.status,
                        ( COALESCE(sm.jumlah_menjadi,0) - coalesce(sm.jumlah_semula, 0) ) AS selisih_semula_menjadi,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = rkat.nip_ppk ) AS nama_ppk,
                        ( SELECT nama_pejabat FROM pejabat WHERE nip = rkat.nip_bpp ) AS nama_bpp
                    FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                    LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                    LEFT JOIN (
                        SELECT id_rab, jenis, SUM(jumlah_tagihan) AS jumlah_tagihan
                        FROM tb_mutasi_percetakan
                        WHERE is_deleted = 'false'
                        GROUP BY id_rab, jenis
                    ) AS relo_sum ON relo_sum.id_rab = rkat.id AND relo_sum.jenis = rkat.rab_type
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rkat.id AND sm.jenis_rab = rkat.rab_type AND sm.is_deleted = 'false' AND sm.status = '' AND sm.jenis_validasi = 'Penambahan'
                    LEFT JOIN paket pkt ON pkt.id_rab = rkat.id AND pkt.jenis_rab = rkat.rab_type
                    WHERE ( rkat.is_deleted = 'false' OR rkat.is_deleted = '0') AND rkat.is_deleted_rkt = 'false'
                        $filterSumberDana $filterUnits $filterCondition $filterRekats $filterPPK $filterPaket
                ORDER BY SUBSTR(rkat.kd_rk, 4, 8), rkat.id_rekat", $tahun, $tahunAngka, null, null);
            }
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data RKA",
                "data"    => [
                    "count"     => count($baseData),
                    "baseData" => $baseData,
                    "listSd"   => $listSd,
                    "tahun"    => $tahunAngka
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data."
            ], 500);
        }
    }
    public function getSumberdanaParent(Request $req) {
        try {
            $tahun         = explode("_", session()->get('tahun', 'tahun_2025'))[1];
            $kd_sumberdana = $req->sumberdana;
            $data          = DB::connection('sirekat')->select("SELECT sd.kd_sumberdana as kd_parent, sd.sumberdana as nama_parent, child.*
                FROM tb_sumberdana sd
                INNER JOIN tb_sumberdana child on sd.id = child.id_parent and child.tahun = sd.tahun
                WHERE sd.tahun = '$tahun' AND sd.kd_sumberdana = '$kd_sumberdana' and sd.is_deleted = 'false' and child.is_deleted = 'false'
            ");
            return response()->json([
                "success" => true,
                "message" => "Berhasil mengambil data Sumberdana Parent",
                "data"    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data Sumberdana Parent"
            ], 500);
        }
    }
    public function updatePpk(Request $req) {
        try {
            // Init variable
            $role           = session("role");
            $tahun          = session("tahun", "tahun_2025");
            $tahunAngka     = explode("_", $tahun)[1];
            $jenis          = $req->jenis;
            $ppk            = $req->ppk;
            $bpp            = $req->bpp;
            $idRab          = $req->idRab;
            $jumlahBiaya    = $req->jumlahBiaya;
            $idunit         = $req->idunit;
            $kodeSd         = $req->kodeSd;
            $idJenisBelanja = $req->idJenisBelanja;

            // Check data
            $idRab      = $req->idRab;
            if (!$idRab || !$jenis) {
                return response()->json([
                    "success" => false,
                    "message" => "Data tidak ditemukan.",
                ], 400);
            }
            if (!$ppk && !$bpp) {
                return response()->json([
                    "success" => false,
                    "message" => "Data PPK dan BPP tidak boleh kosong."
                ], 400);
            }
            // Update PPK
            $mappingRab = [
                "OPERASIONAL" => RABKEG::class,
                "SARANA"      => RABPER::class,
                "PRASARANA"   => RABGDG::class
            ];

            // ✂️ Prepare `PPK` & `BPP` data
            $dataPPK = [
                "jumlah_biaya"  => $jumlahBiaya,
                "unitkerja"     => $idunit,
                "kd_sumberdana" => $kodeSd,
                "coa"           => $idJenisBelanja
            ];

            // 🎯 Get ppk & bpp data
            $komitmen = Komitmen::select("id", "nip", "nama_pejabat")->where("jenis", "ppk");
            $getPpk   = getPPK($komitmen, $dataPPK);
            $getBpp   = getBPP($dataPPK);

            if (!$getPpk) {
                return response()->json(["success" => false, "message" => "Maaf, Data Pejabat Pembuat Komitmen tidak ditemukan"], 400);
            }
            if (!$getBpp) {
                return response()->json(["success" => false, "message" => "Maaf, Data Bendahara Pengeluaran Pembantu tidak ditemukan"], 400);
            }
            if (!in_array($role, ["superadmin"])) {
                if ($ppk != $getPpk["0"]->nip || $bpp != $getBpp->nip) {
                    return response()->json([
                        "success" => false,
                        "message" => "Maaf, Data Pejabat Pembuat Komitmen atau Bendahara Pengeluaran Pembantu tidak sesuai."
                    ], 400);
                }
            }

            $mappingRab[$jenis]::where(["id" => $idRab])->update([
                "nip_ppk" => $ppk,
                "nip_bpp" => $bpp
            ]);
            $foundPpk = Komitmen::where(["nip" => $ppk, "is_active" => "true"])->first();
            $foundBpp = Komitmen::where(["nip" => $bpp, "is_active" => "true"])->first();

            // 💬 Log the action
            event(new UserPerformedAction(
                "97",
                session()->get("id_role"),
                "Perubahan data PPK & BPP",
                "Perubahan Data PPK & BPP $jenis dengan id $idRab menjadi $foundPpk->nama_pejabat dan $foundBpp->nama_pejabat",
                $req->ip(),
                $req->userAgent,
                $req->platform,
                $req->screenSize,
                $req->lang,
                "UPDATE"
            ));
            return response()->json([
                "success" => true,
                "data"    => [
                    "ppk" => $foundPpk,
                    "bpp" => $foundBpp,
                ],
                "message" => "Berhasil mengubah data PPK"
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengubah data PPK"
            ], 500);
        }
    }
    public function getStatusVerifikasi(Request $req) {
        try {
            // Init variable
            $tahun      = session()->get("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1];
            $jenis      = $req->jenis;
            $idRab      = $req->id;
            $kodeKeg    = $req->kodeKeg;
            if (!isset($this->mappingRab[$jenis])) {
                return response()->json(["success" => false, "message" => "Jenis RAB tidak valid"], 422);
            }

            $mapping     = $this->mappingRab[$jenis];
            $jenisRelasi = $mapping["jenisRelasi"] ?? $jenis;
            $rab         = $this->buildRabQueryByJenis($jenis, ["id" => $idRab])->first();
            if (!$rab) {
                return response()->json(["success" => false, "message" => "Data RAB tidak ditemukan"], 404);
            }

            $biayaCol   = in_array($jenis, ["PRASARANA", "GEDUNG"]) ? "jumlah_nilai" : "jumlah_biaya";
            $isPosting  = $this->buildRabQueryByJenis($jenis)->with(["rekat" => function ($query) {
                $query->where("tahun", 'LIKE', '%Definitif%')
                    ->where("sd", '<>', '4100');
            }])->where(function ($query) use ($biayaCol) {
                $query->where(function ($query) use ($biayaCol) {
                    $query->whereNotNull("id_jenis_belanja")
                        ->whereNotNull("jenis_belanja")
                        ->whereNotNull("kebutuhan_kegiatan")
                        ->whereNotNull($biayaCol);
                });
            })->where([
                "verifikasi_pimpinan_unit" => "Setuju",
                "verifikasi_pimpinan_univ" => "Setuju",
                "verifikasi_keu"           => "Setuju",
                "verifikasi_aset"          => "Setuju",
                "verifikasi_tim"           => "Setuju"
            ])->where(["id" => $idRab])->whereNotNull("id_mak")->first();
            $isPaket    = RelasiPaket::with("paket")->where(["id_rab" => $idRab, "is_deleted" => "false", "jenis_rab" => $jenisRelasi])->first();
            $isTorAvail = Rekat::select("tor")->where(["id" => $rab->id_rekat, "is_deleted" => "false"])->first();
            $isRup      = "false";
            if ($isPaket) {
                $isRup = $isPaket->paket->is_rup;
            }
            $tanggapan     = $this->getTanggapanRabByJenis($jenis, $idRab);
            $isProses      = Realisasi::where(["is_deleted" => "false", "id_mak" => $rab->id_mak, "is_posting" => "true"])->first();
            $tanggapanKAI  = $this->resolveTanggapanKai($rab->tanggapan ?? null, $tanggapan);
            $statusKAI     = $rab->verifikasi_spi;
            $pesanKlarifikasiKAI = $statusKAI === "Tolak"
                ? "*Untuk klarifikasi lebih lanjut, harap mengunjungi satuan pengawasan internal (SPI) di kantor Rektorat USK"
                : "";
            $semulaMenjadi = SemulaMenjadi::where(["is_deleted" => "false", "id_rab" => $idRab, "jenis_rab" => $jenisRelasi])->where("status", "=", "")->get();
            $tanggalBayar  = $isProses ? \Carbon\Carbon::parse($isProses->tanggal_bayar)->format("Y-m-d") : null;
            $isWillPaket   = false;

            if (in_array($kodeKeg, ['01.04.06', '01.04.05']) && $rab->jumlah_biaya > 50000000) $isWillPaket = true;
            if (in_array($kodeKeg, ['01.04.06'])) $isWillPaket = true;
            return response()->json([
                "success" => true,
                "data"    => [
                    "verifikasi" => $rab,
                    "isPosting"  => $isPosting,
                    "isPaket"    => $isPaket,
                    "isRUP"      => $isRup,
                    "isProses"   => $isProses,
                    "isTorAvail" => $isTorAvail->tor,
                    "tanggapan"  => $tanggapan,
                    "tanggapanKAI" => $tanggapanKAI,
                    "statusKAI" => $statusKAI,
                    "pesanKlarifikasiKAI" => $pesanKlarifikasiKAI,
                    "semulaMenjadi" => $semulaMenjadi,
                    "tanggalBayar"  => $tanggalBayar,
                    "isWillPaket"   => $isWillPaket
                ],
                "message" => "Berhasil mendapatkan data verifikasi"
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mendapatkan data verifikasi"
            ], 500);
        }
    }
    private function buildRabQueryByJenis(string $jenis, array $where = []) {
        $mapping = $this->mappingRab[$jenis];
        $query = $mapping["super"]::query();

        if (!empty($mapping["superWhere"])) {
            // Model RAB umum dipakai Langganan/BHP, sehingga jenis_rab wajib dikunci.
            $query->where($mapping["superWhere"]);
        }
        if (!empty($where)) {
            $query->where($where);
        }

        return $query;
    }

    private function getTanggapanRabByJenis(string $jenis, $idRab) {
        $mapping = $this->mappingRab[$jenis];
        $query = $mapping["tanggapan"]::where([$mapping["idKolom"] => $idRab]);

        if (!empty($mapping["jenisTanggapan"])) {
            // Tabel tanggapan umum dipisahkan dengan kolom jenis_rab untuk Langganan dan BHP.
            $query->where("jenis_rab", $mapping["jenisTanggapan"]);
        }

        return $query->get();
    }

    private function resolveTanggapanKai($tanggapanRab, $tanggapanFallback): string {
        if (!is_null($tanggapanRab) && trim((string) $tanggapanRab) !== '') {
            return (string) $tanggapanRab;
        }

        if ($tanggapanFallback->isEmpty()) {
            return '';
        }

        $roleKai = ['Pengawasan Internal', 'Auditor'];

        // Fallback KAI hanya mengambil tanggapan dari Pengawasan Internal atau Auditor.
        return $tanggapanFallback->filter(function ($item) use ($roleKai) {
            return in_array($item->role, $roleKai) && !empty($item->tanggapan);
        })->map(function ($item) {
            return '<strong>'.e($item->role).'</strong>: '.e($item->tanggapan);
        })->implode('<br>');
    }
    public function getPaket(Request $req) {
        try {
            $filter      = $req->filterdata;
            $tahun       = session()->get("tahun", "tahun_2025");
            $tahunAngka  = explode("_", $tahun)[1];
            $idunit      = $req->idunit;
            $kodeSd     = $req->sumberdana;
            $sumberdana  = SumberDana::where("kd_sumberdana", $kodeSd)->first();
            $dataMaster = Kro::with(['ro' => function ($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka); // Filter ro, set the flow
            }, 'ro.ikv' => function ($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka); // Filter ikv, keep it alive
            }, 'ro.ikv.subkomponen' => function ($query) use ($tahunAngka) {
                $query->where('tahun', $tahunAngka); // Filter subkomponen, on the component
            }])->where('tahun', $tahunAngka) // Main model filter, keep it right
                ->get();
            $unitKerja  = Rekat::with(["unitApi"])
                ->where(["tahun" => $tahun, "is_deleted" => "false", "unit_kerja" => $idunit, "sd" => $kodeSd])
                ->distinct()
                ->groupBy("kd_rk")
                ->get();
            $subJudul   = Rekat::with(["unitApi"])->where(["tahun" => $tahun, "is_deleted" => "false", "unit_kerja" => $idunit, "sd" => $kodeSd])->get();
            // ✅ Select data coa -> join 3 tabel rab (keg, per, gdg)
            $coa_operasional = DB::connection('sirekat')->select("SELECT DISTINCT
                    tb_rekat.id as id_rekat, kro.kode_ss, tb_rekat.unit_kerja,
                    tb_rekat.kd_rk, tb_rabkegiatan.id_jenis_belanja,
                    tb_rabkegiatan.jenis_belanja
                FROM tb_rekat
                    INNER JOIN tb_rabkegiatan ON tb_rabkegiatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_keg keg ON keg.kode_keg = tb_rekat.kd_rk AND keg.tahun = '$tahunAngka'
                    INNER JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = '$tahunAngka'
                    INNER JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = '$tahunAngka'
                    INNER JOIN tb_sasaran kro ON kro.kode_ss = iku.kode_ss AND kro.tahun = '$tahunAngka'
                WHERE
                    tb_rabkegiatan.verifikasi_pimpinan_unit = 'Setuju'
                    AND tb_rekat.unit_kerja = '$idunit'
                    AND tb_rabkegiatan.unit_kerja = '$idunit'
                    AND tb_rekat.tahun = '$tahun'
                    AND tb_rekat.sd = '$kodeSd'
                    AND tb_rekat.is_deleted = 'false'
                    AND tb_rabkegiatan.is_deleted = 'false'
                GROUP BY
                    tb_rabkegiatan.id_jenis_belanja,
                    tb_rekat.kd_rk,
            tb_rekat.id");
            $coa_sarana = DB::connection('sirekat')->select("SELECT DISTINCT
                    tb_rekat.id as id_rekat, kro.kode_ss,  tb_rekat.unit_kerja,
                    tb_rekat.kd_rk, tb_rabperalatan.id_jenis_belanja,
                    tb_rabperalatan.jenis_belanja
                FROM tb_rekat
                    INNER JOIN tb_rabperalatan ON tb_rabperalatan.id_rekat = tb_rekat.id
                    INNER JOIN tb_keg keg ON keg.kode_keg = tb_rekat.kd_rk AND keg.tahun = '$tahunAngka'
                    INNER JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = '$tahunAngka'
                    INNER JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = '$tahunAngka'
                    INNER JOIN tb_sasaran kro ON kro.kode_ss = iku.kode_ss AND kro.tahun = '$tahunAngka'
                WHERE
                    tb_rabperalatan.verifikasi_pimpinan_unit = 'Setuju'
                    AND tb_rekat.unit_kerja = '$idunit'
                    AND tb_rabperalatan.unit_kerja = '$idunit'
                    AND tb_rekat.tahun = '$tahun'
                    AND tb_rekat.sd = '$kodeSd'
                    AND tb_rekat.is_deleted = 'false'
                    AND tb_rabperalatan.is_deleted = 'false'
                GROUP BY
                    tb_rabperalatan.id_jenis_belanja,
                    tb_rekat.kd_rk,
            tb_rekat.id");
            $coa_prasarana = DB::connection('sirekat')->select("SELECT DISTINCT
                    tb_rekat.id as id_rekat, kro.kode_ss, tb_rekat.unit_kerja,
                    tb_rekat.kd_rk, tb_rabgedung.id_jenis_belanja,
                    tb_rabgedung.jenis_belanja
                FROM tb_rekat
                    INNER JOIN tb_rabgedung ON tb_rabgedung.id_rekat = tb_rekat.id
                    INNER JOIN tb_keg keg ON keg.kode_keg = tb_rekat.kd_rk AND keg.tahun = '$tahunAngka'
                    INNER JOIN tb_ikv ikv ON ikv.kode_ikv = keg.kode_ikv AND ikv.tahun = '$tahunAngka'
                    INNER JOIN tb_iku iku ON iku.kode_ikk = ikv.kode_ikk AND iku.tahun = '$tahunAngka'
                    INNER JOIN tb_sasaran kro ON kro.kode_ss = iku.kode_ss AND kro.tahun = '$tahunAngka'
                WHERE
                    tb_rabgedung.verifikasi_pimpinan_unit = 'Setuju'
                    AND tb_rabgedung.is_deleted = 'false'
                    AND tb_rekat.unit_kerja = '$idunit'
                    AND tb_rabgedung.unit_kerja = '$idunit'
                    AND tb_rekat.tahun = '$tahun'
                    AND tb_rekat.sd = '$kodeSd'
                    AND tb_rekat.is_deleted = 'false'
                GROUP BY
                    tb_rabgedung.id_jenis_belanja,
                    tb_rekat.kd_rk,
            tb_rekat.id");
            $kk_operasional = DB::connection('sirekat')->select("SELECT
                    CONCAT('OPERASIONAL') AS jenis, rab.id as id_item_coa, rkt.prioritas,
                    rab.id_mak, rab.kebutuhan_kegiatan, rab.id_jenis_belanja, rab.jenis_belanja,
                    rkt.unit_kerja, rab.kuantitas, rab.satuan_kuantitas,
                    rab.durasi, rab.satuan_durasi, rab.kegiatan, rab.satuan_kegiatan,
                    rab.biaya_satuan, rab.jumlah_biaya, rab.rpd, rkt.kd_rk, rkt.id as id_rekat,
                    rab.verifikasi_pimpinan_unit as verifikasi_pimpinan_keg, rab.verifikasi_pimpinan_univ as verifikasi_univ_keg,
                    rab.verifikasi_tim as verifikasi_tim_keg, rab.verifikasi_keu as verifikasi_keu_keg,
                    r.jumlah_amprahan, r.jumlah_realisasi, relo.jumlah_tagihan,
                    rt.dipakai as jumlah_pengalihan, rt.sisa as sisa_pengalihan,
                    ppk.nip as nip_ppk, ppk.nama_pejabat as nama_ppk, bpp.nip as nip_bpp, bpp.nama_pejabat as nama_bpp
                FROM tb_rekat rkt
                INNER JOIN tb_rabkegiatan rab
                ON rkt.id = rab.id_rekat
                LEFT JOIN tb_komitmen ppk ON ppk.nip = rab.nip_ppk AND ppk.is_active = 'true'
                LEFT JOIN tb_komitmen bpp ON bpp.nip = rab.nip_bpp AND bpp.is_active = 'true'
                LEFT JOIN tb_realisasi r ON r.id_mak = rab.id_mak AND r.is_deleted = 'false'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'operasional'
                LEFT JOIN tb_mutasi_percetakan relo ON relo.id_rab = rab.id AND relo.jenis = 'OPERASIONAL' AND relo.is_deleted = 'false'
                WHERE rkt.unit_kerja = '$idunit'
                AND rab.unit_kerja = '$idunit'
                AND rkt.tahun = '$tahun'
                AND rab.is_deleted = 'false' AND rkt.is_deleted = 'false'
                AND rkt.sd = '$kodeSd'
            AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rab.id");
            $kk_sarana = DB::connection('sirekat')->select("SELECT
                    CONCAT('SARANA') AS jenis, rab.id as id_item_coa, rkt.prioritas,
                    rab.id_mak, rab.kebutuhan_kegiatan, rab.id_jenis_belanja, rab.jenis_belanja,
                    rkt.unit_kerja, rab.kuantitas, rab.satuan, rkt.kd_rk,
                    rab.harga_satuan, rab.jumlah_biaya, rab.rpd, rkt.id as id_rekat,
                    rab.verifikasi_pimpinan_unit as verifikasi_pimpinan_per,
                    rab.verifikasi_pimpinan_univ as verifikasi_univ_per,
                    rab.verifikasi_tim as verifikasi_tim_per, rab.verifikasi_keu as verifikasi_keu_per, rab.verifikasi_aset as verifikasi_aset_per,
                    r.jumlah_amprahan, r.jumlah_realisasi, relo.jumlah_tagihan,
                    rt.dipakai as jumlah_pengalihan, rt.sisa as sisa_pengalihan,
                    ppk.nip as nip_ppk, ppk.nama_pejabat as nama_ppk, bpp.nip as nip_bpp, bpp.nama_pejabat as nama_bpp
                FROM tb_rekat rkt
                INNER JOIN tb_rabperalatan rab
                on rkt.id = rab.id_rekat
                LEFT JOIN tb_komitmen ppk ON ppk.nip = rab.nip_ppk AND ppk.is_active = 'true'
                LEFT JOIN tb_komitmen bpp ON bpp.nip = rab.nip_bpp AND bpp.is_active = 'true'
                LEFT JOIN tb_realisasi r ON r.id_mak = rab.id_mak AND r.is_deleted = 'false'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'sarana'
                LEFT JOIN tb_mutasi_percetakan relo ON relo.id_rab = rab.id AND relo.jenis = 'SARANA' AND relo.is_deleted = 'false'
                WHERE rkt.unit_kerja = '$idunit'
                AND rab.unit_kerja = '$idunit'
                AND rkt.tahun = '$tahun'
                AND rkt.sd = '$kodeSd'
                AND rab.is_deleted = 'false' AND rkt.is_deleted = 'false'
            AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rab.id");
            $kk_prasarana = DB::connection('sirekat')->select("SELECT
                CONCAT('PRASARANA') AS jenis, rab.id as id_item_coa, rkt.prioritas,
                rab.id_mak, rab.kebutuhan_kegiatan, rab.id_jenis_belanja, rab.jenis_belanja,
                rkt.unit_kerja, rab.kuantitas, rab.satuan,
                rab.jumlah_nilai as jumlah_biaya, rab.rpd, rkt.id as id_rekat,
                rab.verifikasi_pimpinan_unit as verifikasi_pimpinan_gdg, rab.verifikasi_pimpinan_univ as verifikasi_univ_gdg,
                rab.verifikasi_tim as verifikasi_tim_gdg, rab.verifikasi_keu as verifikasi_keu_gdg, rab.verifikasi_aset as verifikasi_aset_gdg,
                r.jumlah_amprahan, r.jumlah_realisasi,
                rt.dipakai as jumlah_pengalihan, rt.sisa as sisa_pengalihan,
                ppk.nip as nip_ppk, ppk.nama_pejabat as nama_ppk, bpp.nip as nip_bpp, bpp.nama_pejabat as nama_bpp
                FROM tb_rekat rkt
                INNER JOIN tb_rabgedung rab ON rkt.id = rab.id_rekat
                LEFT JOIN tb_komitmen ppk ON ppk.nip = rab.nip_ppk AND ppk.is_active = 'true'
                LEFT JOIN tb_komitmen bpp ON bpp.nip = rab.nip_bpp AND bpp.is_active = 'true'
                LEFT JOIN tb_realisasi r ON r.id_mak = rab.id_mak AND r.is_deleted = 'false'
                LEFT JOIN tb_realisasi_terpakai rt ON rt.id_rab = rab.id AND rt.jenis_rab = 'prasarana'
                WHERE rkt.unit_kerja = '$idunit' AND rab.unit_kerja = '$idunit'
                    AND rab.is_deleted = 'false' AND rkt.is_deleted = 'false'
                    AND rkt.sd = '$kodeSd'
                    AND rkt.tahun = '$tahun'
            AND rab.verifikasi_pimpinan_unit = 'Setuju'
            GROUP BY rab.id");
            $itemCoa    = array_merge($kk_operasional, $kk_sarana, $kk_prasarana);
            $coa        = array_merge($coa_operasional, $coa_sarana, $coa_prasarana);


            return response()->json([
                "success" => true,
                "message" => "Berhasil Mengambil Data RKA Paket",
                "data"    => [
                    "dataMaster" => $dataMaster,
                    "unitKerja"  => $unitKerja,
                    "subJudul"   => $subJudul,
                    "coa"        => $coa,
                    "itemCoa"    => $itemCoa,
                    "sumberdana" => $sumberdana
                ],
                $req->all()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal Mengambil Data RKA Paket",
                "error"   => $e->getMessage(),
            ], 400);
        }
    }
    public function getPpkNull(Request $req) {
        try {
            $idRekat = $req->idRekat;
            $masterData = "WITH BaseData AS (
                SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja, rab.rpd,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt, rab.kebutuhan_kegiatan,
                    rkt.kd_rk, rab.jumlah_biaya AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    'OPERASIONAL' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
                FROM tb_rekat rkt
                JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                UNION ALL
                SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja, rab.rpd,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt, rab.kebutuhan_kegiatan,
                    rkt.kd_rk, rab.jumlah_biaya AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    'SARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
                FROM tb_rekat rkt
                JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                UNION ALL
                SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja, rab.rpd,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt, rab.kebutuhan_kegiatan,
                    rkt.kd_rk, rab.jumlah_nilai AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    'PRASARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft
                FROM tb_rekat rkt
                JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
            )";
            $data = DB::connection('sirekat')->select("$masterData SELECT
                    rkat.id_rekat, rkat.id, rkat.kd_sumberdana, rkat.id_jenis_belanja, rkat.sub_judul,
                    rkat.unit_kerja, rkat.kd_rk, rkat.jenis_belanja, rkat.kebutuhan_kegiatan, rkat.rpd
                FROM BaseData rkat
                WHERE
                    rkat.id_rekat = ? AND rkat.is_deleted = 'false' AND rkat.is_draft = 'false'
                    AND rkat.verifikasi_pimpinan_unit = 'Setuju' AND ( rkat.nip_ppk IS NULL OR rkat.nip_bpp IS NULL )
            ORDER BY rkat.id_rekat", [$idRekat]);


            return response()->json([
                "success" => true,
                "message" => "Berhasil Mengambil Data PPK dan BPP",
                "data"    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal Mengambil Data PPK dan BPP",
                "error"   => $e->getMessage(),
            ], 400);
        }
    }
    public function getBaseData(Request $req) {
        try {
            ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $idunit     = $req->idunit;
            $kodeSd     = $req->kodeSd;
            $filter     = $req->filterdata; // keep for parity with existing signature
            $backupRkat = $req->backup;
            $idRekats   = $req->idRekats;

            $formatIn = function ($value) {
                if (is_array($value))
                    $value = implode(',', $value);

                $value = trim((string) $value);
                if ($value === '')
                    return "''";

                $parts = array_filter(array_map('trim', explode(',', $value)), fn($part) => $part !== '');
                return "'" . implode("','", $parts) . "'";
            };

            $kodeSdIn     = $formatIn($kodeSd);
            $idunitIn     = $formatIn($idunit);
            $idRekatsIn   = $formatIn($idRekats ?? '');
            $filterRekats = $req->idRekats ? " AND rkat.id_rekat IN ($idRekatsIn)" : "";
            $filterRekatsBackup = $req->idRekats ? " AND backupRkat.id_rekat IN ($idRekatsIn)" : "";

            $joinDataMasterBaru = "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master kegMaster ON kegMaster.kode_keg = rik.kode_keg AND kegMaster.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = '$tahunAngka'";
            $joinDataMasterBaruBackup = "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = backupRkat.id_rekat
                LEFT JOIN tb_keg_master kegMaster ON kegMaster.kode_keg = rik.kode_keg AND kegMaster.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran kro ON kro.kode_ss = rik.kode_ss AND kro.tahun = '$tahunAngka'";
            $isNewDataMaster = (int) $tahunAngka >= 2026;

            if (is_array($backupRkat))
                $backupRkat = implode(',', $backupRkat);

            if ($req->backup !== null) {
                $data = $isNewDataMaster
                    ? getBaseData(" SELECT pkt.id_mak_paket, pkt.id_paket, pkt.sub_judul AS judul_paket, pkt.jumlah_biaya AS total_paket, pkt.rpd AS rpd_paket,
                            unit.nama AS nama_unit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt,
                            kro.kode_ss, kro.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk,
                            ikv.kode_ikv, ikv.ikv, kegMaster.kode_keg, kegMaster.keg AS rincian_kegiatan,
                            sd.kd_sumberdana, sd.sumberdana, backupRkatDet.*,
                            ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                            ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                            backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                            COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                            COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI
                        FROM tb_backup_rkat backupRkat
                        INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                        $joinDataMasterBaruBackup
                        INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                        LEFT JOIN paket pkt ON pkt.id_rab = backupRkatDet.id AND pkt.jenis_rab = backupRkatDet.rab_type
                        WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? )
                            AND backupRkat.idunit IN ($idunitIn) AND backupRkat.sd IN ($kodeSdIn) $filterRekatsBackup
                    AND backupRkat.tahun = ?", $tahun, $tahunAngka, null, null, [$backupRkat, $backupRkat, $tahun])
                    : getBaseData(" SELECT pkt.id_mak_paket, pkt.id_paket, pkt.sub_judul AS judul_paket, pkt.jumlah_biaya AS total_paket,
                        unit.nama AS nama_unit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt,
                        dm.kode_ss, dm.ss, dm.kode_ikk, dm.ikk, dm.kode_ikv, dm.ikv, dm.kode_keg, dm.rincian_kegiatan,
                        sd.kd_sumberdana, sd.sumberdana, backupRkatDet.*,
                        ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                            THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                        ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                        backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                        COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                        COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI
                    FROM tb_backup_rkat backupRkat
                    INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                    INNER JOIN dataMaster dm ON dm.kode_keg = backupRkat.kode_keg
                    INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                    INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                    LEFT JOIN paket pkt ON pkt.id_rab = backupRkatDet.id AND pkt.jenis_rab = backupRkatDet.rab_type
                    WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? ) AND backupRkat.idunit IN ($idunitIn) AND backupRkat.sd IN ($kodeSdIn)
                        $filterRekatsBackup
                AND backupRkat.tahun = ?", $tahun, $tahunAngka, null, null, [$backupRkat, $backupRkat, $tahun]);
            } else {
                $selectDataMaster = $isNewDataMaster
                    ? "kro.kode_ss, kro.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, kegMaster.kode_keg, kegMaster.keg AS rincian_kegiatan"
                    : "dm.kode_ss, dm.ss, dm.kode_ikk, dm.ikk, dm.kode_ikv, dm.ikv, dm.kode_keg, dm.rincian_kegiatan";
                $joinDataMaster = $isNewDataMaster ? $joinDataMasterBaru : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
                $orderDataMaster = $isNewDataMaster ? "kro.kode_ss" : "dm.kode_ss";

                $data = getBaseData("SELECT pkt.id_mak_paket, pkt.id_paket, pkt.sub_judul AS judul_paket, pkt.jumlah_biaya AS total_paket, rkat.kd_sumberdana, sd.sumberdana, rkat.id_jenis_belanja, pkt.rpd AS rpd_paket,
                        $selectDataMaster, rkat.unit_kerja_rkt, rkat.jenis_belanja, unit.nama as nama_unit,
                        rkat.id_rekat, rkat.sub_judul, rkat.is_draft, rkat.kd_rk,
                        CASE
                            WHEN COALESCE(amprah.jumlah_amprahan, amprah.jumlah_realisasi) IS NOT NULL AND rt.dipakai IS NOT NULL
                                THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                            ELSE rkat.jumlah_biaya END AS jumlah_biaya,
                        rkat.jumlah_biaya AS jumlah_biaya_usulan,
                        COALESCE(amprah.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                        COALESCE(amprah.jumlah_realisasi, 0) AS TOTAL_REALISASI
                    FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                    LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                    LEFT JOIN paket pkt ON pkt.id_rab = rkat.id AND pkt.jenis_rab = rkat.rab_type
                    WHERE  rkat.kd_sumberdana IN ($kodeSdIn) AND rkat.unit_kerja IN ($idunitIn) $filterRekats
                ORDER BY rkat.kd_sumberdana, $orderDataMaster DESC", $tahun, $tahunAngka);
            }
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data base RKA",
                "data"    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mendapatkan data",
                "error"   => $e->getMessage(),
                "trace"   => $e->getTrace()
            ], 500);
        }
    }
    public function getSumberdana(Request $req) {
        try {
            $role       = session("role");
            ["tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $validated  = $req->validate(['idunit' => ['required', Rule::exists('tb_unit_api', 'idunit')]]);
            $idunit     = (int) $validated['idunit'];
            $filterUnit = " AND rkt.unit_kerja = ?";
            $params = array_fill(0, 1, $tahunAngka); // repeat $tahunAngka 5 times
            if (!in_array($role, $this->adminRoles, true) && !in_array($role, ["Wakil Rektor", "Direktur"])) {
                $filterUnit = ' AND rkt.unit_kerja = ?';
                $params[]   = $idunit;
            } else {
                $filterUnit = '';
            }
            $data = DB::connection('sirekat')->select("SELECT DISTINCT sd2.kd_sumberdana AS kodeSd2, sd2.sumberdana as sd2,
                        sd4.kd_sumberdana AS kodeSd4, sd4.sumberdana as sd4,
                        sd6.kd_sumberdana AS kodeSd6, sd6.sumberdana as sd6,
                        sd8.kd_sumberdana AS kodeSd8, sd8.sumberdana as sd8,
                        sd.sumberdana AS sd, sd.kd_sumberdana
                FROM tb_sumberdana_2 sd2
                INNER JOIN tb_sumberdana_4 sd4 ON sd2.kd_sumberdana = sd4.kd_parent AND sd4.tahun = '2025'
                INNER JOIN tb_sumberdana_6 sd6 ON sd4.kd_sumberdana = sd6.kd_parent AND sd6.tahun = '2025'
                INNER JOIN tb_sumberdana_8 sd8 ON sd6.kd_sumberdana = sd8.kd_parent AND sd8.tahun = '2025'
                INNER JOIN tb_rekat rkt ON rkt.sd = sd8.kd_sumberdana AND rkt.is_deleted = 'false'
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = rkt.sd AND sd.tahun = ? AND sd.is_show = 'true' AND sd.is_deleted = 'false'
            WHERE sd2.tahun = '2025' $filterUnit", $params);

            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data Sumberdana",
                "data"    => $data,
                "tahun"   => $tahunAngka
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mendapatkan data Sumberdana",
                "error"   => $e->getMessage(),
            ], 500);
        }
    }
    public function getUnitkerja(Request $req) {
        try {
            $validated = $req->validate(['idunit' => ['required', Rule::exists('tb_unit_api', 'idunit')]]);
            $idunit    = (int) $validated['idunit'];
            $data = $this->unitkerjaService->getUnitkerjaWithParents($idunit, $this->adminRoles);
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data Unit Kerja",
                "data"    => [
                    "data" => $data,
                    "role" => session("role")
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mendapatkan data Unit Kerja",
                "error"   => $e->getMessage(),
            ], 500);
        }
    }
    public function getIdRekats(Request $req) {
        try {
            $tahun      = session("tahun", "tahun_2025");
            $tahunAngka = explode("_", $tahun)[1];
            $idunit     = $req->idunit;
            $kodeSd     = $req->kodeSd;
            $data       = Rekat::select("id", "sub_judul")->where(["tahun" => $tahun, "is_deleted" => "false"])->whereIn("sd", $kodeSd)->whereIn("unit_kerja", $idunit)->get();
            return response()->json([
                "success" => true,
                "message" => "Berhasil mendapatkan data Id Rekat",
                "data"    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mendapatkan data Id Rekat",
                "error"   => $e->getMessage(),
            ], 500);
        }
    }
}
