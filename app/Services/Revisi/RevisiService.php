<?php
namespace App\Services\Revisi;

use App\Events\UserPerformedAction;
use App\Models\Datarevisi\SemulaMenjadi;
use App\Models\Datarevisi\SisaSaldoValidasi;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Komitmen;
use App\Models\Rekat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Revisi\RekatService;

class RevisiService {
    protected $rabConfig;
    public function __construct(
        protected RekatService $rekatService
    ) {
        $this->rabConfig = [
            "RAB_GEDUNG" => [
                "model"      => RABGDG::class,
                "field"      => "jumlah_nilai",
                "paketLabel" => "PRASARANA",
            ],
            "RAB_PERALATAN" => [
                "model"      => RABPER::class,
                "field"      => "jumlah_biaya",
                "paketLabel" => "SARANA",
            ],
            null => [
                "model"      => RABKEG::class,
                "field"      => "jumlah_biaya",
                "paketLabel" => "OPERASIONAL",
            ],
            "RAB_KEGIATAN" => [
                "model"      => RABKEG::class,
                "field"      => "jumlah_biaya",
                "paketLabel" => "OPERASIONAL",
            ],
        ];
    }
    /**
     * Generate spek semula in JSON format for database storage
     * Converts RAB data object into a structured JSON array based on RAB type
     *
     * @param object $data The RAB data object (RABKEG, RABPER, or RABGDG model instance)
     * @param string $jenisRab The type of RAB (RAB_KEGIATAN, RAB_PERALATAN, RAB_GEDUNG)
     * @return array Associative array ready for JSON encoding and database storage
     * @throws \Exception When RAB type is invalid or data processing fails
     */
    public function generateSpekSemulaJson($data, $jenisRab) {
        try {
            if ($jenisRab == "RAB_KEGIATAN") {
                return [
                    'kode_sbm' => $data->kode_sbm ?? '-',
                    'kuantitas' => $data->kuantitas,
                    'satuan_kuantitas' => $data->satuan_kuantitas,
                    'durasi' => $data->durasi,
                    'satuan_durasi' => $data->satuan_durasi,
                    'kegiatan' => $data->kegiatan,
                    'satuan_kegiatan' => $data->satuan_kegiatan,
                    'biaya_satuan' => $data->biaya_satuan ?? '0',
                    'id_jenis_belanja' => $data->id_jenis_belanja,
                    'jenis_belanja' => $data->jenis_belanja,
                    'rpd' => $data->rpd,
                    'kebutuhan_kegiatan' => $data->kebutuhan_kegiatan,
                ];
            }
            if ($jenisRab == "RAB_PERALATAN") {
                return [
                    'kode_sbm' => $data->kode_sbm ?? '-',
                    'kuantitas' => $data->kuantitas,
                    'satuan' => $data->satuan,
                    'harga_satuan' => $data->harga_satuan,
                    'biaya_pajak' => $data->biaya_pajak,
                    'biaya_lainnya' => $data->biaya_lainnya,
                    'id_jenis_belanja' => $data->id_jenis_belanja,
                    'jenis_belanja' => $data->jenis_belanja,
                    'rpd' => $data->rpd,
                    'kode_aset' => $data->kode_aset ?? '-',
                    'aset' => $data->aset ?? '-',
                    'merk' => $data->merk ?? '-',
                    'type' => $data->type ?? '-',
                    'url' => $data->url ?? '-',
                    'kebutuhan_kegiatan' => $data->kebutuhan_kegiatan,
                ];
            }
            if ($jenisRab == "RAB_GEDUNG") {
                return [
                    'kode_sbm' => $data->kode_sbm ?? '-',
                    'kuantitas' => 1,
                    'satuan' => 'Paket',
                    'id_jenis_belanja' => $data->id_jenis_belanja,
                    'jenis_belanja' => $data->jenis_belanja,
                    'rpd' => $data->rpd,
                    'kode_aset' => $data->kode_aset ?? '-',
                    'aset' => $data->aset ?? '-',
                    'merk' => $data->merk ?? '-',
                    'type' => $data->type ?? '-',
                    'url' => $data->url ?? '-',
                    'jenis_pekerjaan' => $data->jenis_pekerjaan ?? '-',
                    'kebutuhan_kegiatan' => $data->kebutuhan_kegiatan,
                ];
            }

            throw new \Exception("Invalid jenis RAB: {$jenisRab}. Expected RAB_KEGIATAN, RAB_PERALATAN, or RAB_GEDUNG.");
        } catch (\Exception $e) {
            throw new \Exception("Error generating spek semula JSON: " . $e->getMessage());
        }
    }
    /**
     * Build spek menjadi payload from breakdown request array using JSON helper
     *
     * @param array $item Breakdown row data
     * @param string $idCoa COA identifier
     * @param string $coa COA description
     * @param string $jenisRab RAB type (RAB_KEGIATAN, RAB_PERALATAN, RAB_GEDUNG)
     * @param object|null $selectedRab Existing RAB record (if available)
     * @return array [ 'spek_menjadi_json' => array ]
     */
    public function buildSpekMenjadiFromBreakdown(array $item, string $idCoa, string $coa, string $jenisRab, ?object $selectedRab = null): array {
        $jenisRab = $this->normalizeJenisRab($jenisRab);
        $kodeSbm = $item["2"] ?? null;
        if ($kodeSbm === "Kode SBM tidak ditemukan.") {
            $kodeSbm = "-";
        }

        if ($jenisRab === "RAB_PERALATAN") {
            $asetParts = isset($item["4"]) ? explode(" | ", $item["4"]) : [null, null];
            $kodeAset  = $asetParts[0] ?? '-';
            $asetName  = $asetParts[1] ?? '-';

            $requestPayload = (object) [
                "kode_sbm"     => $kodeSbm,
                "rpd"          => $item["3"] ?? null,
                "itemCoa"      => $item["5"] ?? null,
                "kuantitas"    => $item["9"] ?? null,
                "sKuantitas"   => $item["10"] ?? null,
                "hargaSatuan"  => $item["11"] ?? null,
                "biayaPajak"   => $item["12"] ?? null,
                "biayaLainnya" => $item["13"] ?? null,
                "jumlahBiaya"  => $item["14"] ?? null,
                "idCoa"        => $idCoa,
                "coa"          => $coa,
                "kode_aset"    => $kodeAset,
                "aset"         => $asetName,
                "merk"         => $item["6"] ?? null,
                "type"         => $item["7"] ?? null,
                "url"          => $item["8"] ?? null,
            ];

            $referenceRab = $selectedRab ?? (object) [
                "kode_sbm"           => $kodeSbm,
                "rpd"                => $item["3"] ?? null,
                "kebutuhan_kegiatan" => $item["5"] ?? null,
                "kode_aset"          => $kodeAset,
                "aset"               => $asetName,
                "merk"               => $item["6"] ?? null,
                "type"               => $item["7"] ?? null,
                "url"                => $item["8"] ?? null,
            ];
        } elseif ($jenisRab === "RAB_GEDUNG") {
            $asetParts = isset($item["4"]) ? explode(" | ", $item["4"]) : [null, null];
            $kodeAset  = $asetParts[0] ?? '-';
            $asetName  = $asetParts[1] ?? '-';
            $jenisPekerjaan = $item["5"] ?? null;
            if ($jenisPekerjaan === "Perencanaan (DED)") {
                $jenisPekerjaan = "Perencanaan";
            }

            $requestPayload = (object) [
                "kode_sbm"       => $kodeSbm,
                "rpd"            => $item["3"] ?? null,
                "itemCoa"        => $item["6"] ?? null,
                "jumlahBiaya"    => $item["7"] ?? null,
                "idCoa"          => $idCoa,
                "coa"            => $coa,
                "kode_aset"      => $kodeAset,
                "aset"           => $asetName,
                "jenisPekerjaan" => $jenisPekerjaan,
            ];

            $referenceRab = $selectedRab ?? (object) [
                "kode_sbm"           => $kodeSbm,
                "rpd"                => $item["3"] ?? null,
                "kebutuhan_kegiatan" => $item["6"] ?? null,
                "kode_aset"          => $kodeAset,
                "aset"               => $asetName,
                "jenis_pekerjaan"    => $jenisPekerjaan,
            ];
        } else { // RAB_KEGIATAN default
            $requestPayload = (object) [
                "kode_sbm"     => $kodeSbm,
                "rpd"          => $item["3"] ?? null,
                "itemCoa"      => $item["4"] ?? null,
                "kuantitas"    => $item["5"] ?? null,
                "sKuantitas"   => $item["6"] ?? null,
                "durasi"       => $item["7"] ?? null,
                "sDurasi"      => $item["8"] ?? null,
                "kegiatan"     => $item["9"] ?? null,
                "sKegiatan"    => $item["10"] ?? null,
                "hargaSatuan"  => $item["11"] ?? null,
                "jumlahBiaya"  => $item["12"] ?? null,
                "idCoa"        => $idCoa,
                "coa"          => $coa,
            ];

            $referenceRab = $selectedRab ?? (object) [
                "kode_sbm"           => $kodeSbm,
                "rpd"                => $item["3"] ?? null,
                "kebutuhan_kegiatan" => $item["4"] ?? null,
            ];
        }

        $spekMenjadiJson = $this->buildSpekMenjadiJson($requestPayload, $referenceRab, $jenisRab);

        return [
            "spek_menjadi_json" => $spekMenjadiJson,
        ];
    }

    private function normalizeJenisRab(string $jenisRab): string {
        $map = [
            'OPERASIONAL' => 'RAB_KEGIATAN',
            'SARANA'      => 'RAB_PERALATAN',
            'PRASARANA'   => 'RAB_GEDUNG',
        ];

        return $map[$jenisRab] ?? $jenisRab;
    }

    /**
     * Resolve spek data array using JSON payload or legacy string fallback
     */
    private function resolveSpekData(?string $spekJson, ?string $legacyString, string $jenisRab): array {
        if ($spekJson) {
            $decoded = json_decode($spekJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        if ($legacyString) {
            return $this->convertLegacySpekStringToArray($legacyString, $jenisRab);
        }

        return [];
    }

    /**
     * Convert the historical spek delimiter format into an associative array
     */
    private function convertLegacySpekStringToArray(string $legacyString, string $jenisRab): array {
        $parts = explode('~~~', $legacyString);
        $jenisRab = $this->normalizeJenisRab($jenisRab);

        if ($jenisRab === 'RAB_KEGIATAN') {
            return [
                'kode_sbm'           => $parts[0]  ?? null,
                'kuantitas'          => $parts[1]  ?? null,
                'satuan_kuantitas'   => $parts[2]  ?? null,
                'durasi'             => $parts[3]  ?? null,
                'satuan_durasi'      => $parts[4]  ?? null,
                'kegiatan'           => $parts[5]  ?? null,
                'satuan_kegiatan'    => $parts[6]  ?? null,
                'biaya_satuan'       => $parts[7]  ?? null,
                'id_jenis_belanja'   => $parts[8]  ?? null,
                'jenis_belanja'      => $parts[9]  ?? null,
                'rpd'                => $parts[10] ?? null,
                'kebutuhan_kegiatan' => $parts[11] ?? null,
            ];
        }

        if ($jenisRab === 'RAB_PERALATAN') {
            return [
                'kode_sbm'           => $parts[0]  ?? null,
                'kuantitas'          => $parts[1]  ?? null,
                'satuan'             => $parts[2]  ?? null,
                'harga_satuan'       => $parts[3]  ?? null,
                'biaya_pajak'        => $parts[4]  ?? null,
                'biaya_lainnya'      => $parts[5]  ?? null,
                'id_jenis_belanja'   => $parts[6]  ?? null,
                'jenis_belanja'      => $parts[7]  ?? null,
                'rpd'                => $parts[8]  ?? null,
                'kode_aset'          => $parts[9]  ?? null,
                'aset'               => $parts[10] ?? null,
                'merk'               => $parts[11] ?? null,
                'type'               => $parts[12] ?? null,
                'url'                => $parts[13] ?? null,
                'kebutuhan_kegiatan' => $parts[14] ?? null,
            ];
        }

        if ($jenisRab === 'RAB_GEDUNG') {
            return [
                'kode_sbm'           => $parts[0]  ?? null,
                'kuantitas'          => $parts[1]  ?? 1,
                'satuan'             => $parts[2]  ?? 'Paket',
                'id_jenis_belanja'   => $parts[3]  ?? null,
                'jenis_belanja'      => $parts[4]  ?? null,
                'rpd'                => $parts[5]  ?? null,
                'kode_aset'          => $parts[6]  ?? null,
                'aset'               => $parts[7]  ?? null,
                'jenis_pekerjaan'    => $parts[8]  ?? null,
                'kebutuhan_kegiatan' => $parts[9]  ?? null,
            ];
        }

        return [];
    }
    /**
     * Create SemulaMenjadi record for breakdown addition
     */
    public function createSemulaMenjadiForAddition($idRab, $jumlahMenjadi, $jenisRab, array $spekMenjadiJson = null ) {
        try {
            return SemulaMenjadi::create([
                "id_rab"           => $idRab,
                "jenis_revisi"     => "BREAKDOWN",
                "tor"              => null,
                "jumlah_semula"    => 0,
                "jumlah_menjadi"   => $jumlahMenjadi,
                "status"           => "",
                "spek_semula"      => "-",
                "spek_menjadi_json" => $spekMenjadiJson ? json_encode($spekMenjadiJson) : null,
                "should_verify_by" => "SUPERADMIN",
                "jenis_rab"        => $jenisRab,
                "jenis_validasi"   => "Penambahan Item Coa",
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error creating SemulaMenjadi for addition: " . $e->getMessage());
        }
    }

    /**
     * Create SemulaMenjadi record for breakdown modification
     */
    public function createSemulaMenjadiForModification($idRab, $jumlahSemula, $jumlahMenjadi, $jenisRab, $jenisValidasi, array $spekSemulaJson = null, array $spekMenjadiJson = null ) {
        try {
            return SemulaMenjadi::create([
                "id_rab"           => $idRab,
                "jenis_revisi"     => "BREAKDOWN",
                "tor"              => null,
                "jumlah_semula"    => $jumlahSemula,
                "jumlah_menjadi"   => $jumlahMenjadi,
                "status"           => "",
                "spek_semula_json" => $spekSemulaJson ? json_encode($spekSemulaJson) : null,
                "spek_menjadi_json" => $spekMenjadiJson ? json_encode($spekMenjadiJson) : null,
                "should_verify_by" => "SUPERADMIN",
                "jenis_rab"        => $jenisRab,
                "jenis_validasi"   => $jenisValidasi
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error creating SemulaMenjadi for modification: " . $e->getMessage());
        }
    }

    /**
     * Validate breakdown data input
     */
    public function validateBreakdownData($data, $dataDeleted, $jenisRab) {
        $data        = $data ?? [];
        $dataDeleted = $dataDeleted ?? [];
        $mappingRab = [
            "OPERASIONAL" => RABKEG::class,
            "SARANA"      => RABPER::class,
            "PRASARANA"   => RABGDG::class
        ];
        // Check if data is empty
        if (count($data) === 0 && count($dataDeleted) === 0)
            throw new \Exception("Data tidak ditemukan.");
        $selectedRabClass = $mappingRab[$jenisRab] ?? null;
        if ( !$selectedRabClass )
            throw new \Exception("Jenis RAB tidak valid.");
        foreach ($data as $item) {
            $isExists = SemulaMenjadi::where([
                "is_deleted"   => "false",
                "status"       => "",
                "id_rab"       => $item["0"],
                "jenis_revisi" => "BREAKDOWN",
                "jenis_rab"    => $jenisRab
            ])->exists();
            if ( $isExists )
                throw new \Exception("Data sudah diajukan untuk proses breakdown.");
        }
        return true;
    }
    public function isExistsOnSemulaMenjadi( $data, $jenisRab ) {
        $data = $data ?? [];
        if (count($data) === 0)
            throw new \Exception("Data tidak ditemukan.");

        foreach ($data as $item) {
            $isExists = SemulaMenjadi::where([
                "is_deleted"   => "false", "status" => "", "id_rab" => $item->id,
                "jenis_revisi" => "BREAKDOWN", "jenis_rab" => $jenisRab
            ])->exists();
            if ( $isExists )
                throw new \Exception("Data sudah diajukan untuk proses breakdown.");
        }
        return false;
    }
    /**
     * Validate breakdown total amount
     */
    public function validateBreakdownTotal($data, $idRekat, $idCoa, $rabClass, $sumColumn, $kodeSd, $idunit, $finalTotal ) {
        $totalBreakdown = 0;
        if ( $rabClass === RABPER::class )
            $totalBreakdown  = (int) array_sum( array_column($data, "14") );
        if ( $rabClass === RABKEG::class )
            $totalBreakdown  = (int) array_sum( array_column($data, "12") );
        if ( $rabClass === RABGDG::class )
            $totalBreakdown  = (int) array_sum( array_column($data, "7") );

        $totalDataSemula = $rabClass::where([ "id_rekat" => $idRekat, "id_jenis_belanja" => $idCoa, "is_deleted" => "false", "is_draft" => "false" ])
                            ->sum($sumColumn);
        $totalSaldo = SisaSaldoValidasi::where([
            "id_rekat" => $idRekat, "kode_coa" => $idCoa, "jenis_saldo" => "BREAKDOWN", "tahun" => session("tahun")
        ])->sum("sisa_saldo") ?? 0;

        $totalSemula    = $totalDataSemula + $totalSaldo;
        $totalPerubahan = $totalSemula - $finalTotal;

        if ($totalBreakdown <= 0)
            throw new \Exception("Total biaya tidak boleh kosong atau bernilai nol. Silakan masukkan nilai yang valid untuk melanjutkan proses.");
        if ( $finalTotal < $totalSemula )
            throw new \Exception("Total biaya perubahan anggaran harus sama dengan alokasi yang tersedia.");
        if ($totalBreakdown > $totalDataSemula + $totalSaldo )
            throw new \Exception("Total biaya perubahan anggaran melampaui alokasi yang tersedia.");
        if ( $finalTotal > $totalSemula )
            throw new \Exception("Total biaya perubahan anggaran melampaui alokasi yang tersedia.");
        if ( cekPagu( $idunit, $kodeSd, session("tahun"), $totalPerubahan ) == "error" )
            throw new \Exception("Total biaya perubahan anggaran melampaui pagu yang tersedia.");

        return [
            'totalBreakdown' => $totalBreakdown,
            'totalDataSemula' => $totalDataSemula
        ];
    }

    /**
     * Validate if item is packaged
     */
    public function validatePackageStatus($itemId, $jenisValidasi) {
        if (function_exists('cekPaket') && cekPaket($itemId, $jenisValidasi) === true) {
            // if data is already packaged, skip the data
            return true;
        }
        return false;
    }

    /**
     * Validate if RAB record exists
     */
    public function validateRabExists($idItem, $rabClass) {
        $foundRab = $rabClass::where("id", $idItem);
        if (!$foundRab->exists())
            throw new \Exception("Data tidak ditemukan.");
        return $foundRab;
    }

    /**
     * Validate equal amount comparison for RAB Peralatan and Gedung
     */
    public function validateEqualAmount($sumPerubahan, $sumOriginal, $fieldName = "biaya") {
        if ($sumPerubahan != $sumOriginal)
            throw new \Exception("Jumlah {$fieldName} perubahan harus sama dengan jumlah {$fieldName} awal");
        return true;
    }

    /**
     * Get rekapitulasi data with secure parameterized query
     */
    public function getRekapitulasiData($idunit, $kodeSd, $tahun, $tahunAngka) {
        try {
            // Validate inputs
            $this->validateRekapitulasiInputs($idunit, $kodeSd, $tahun, $tahunAngka);

            // Use Laravel's query builder with parameter binding for security
            // $query = ", semulaMenjadi AS (
            //         SELECT
            //             bd.id_rekat, sm.id AS id_sm, bd.kd_sumberdana, bd.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja,
            //             sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status,
            //             bd.is_draft, bd.itemCoa,
            //             unit.nama AS namaUnit, sm.jenis_revisi, sm.spek_semula_json, sm.spek_menjadi_json,
            //             sm.is_deleted
            //         FROM tb_semula_menjadi sm
            //         INNER JOIN BaseData bd ON bd.id = sm.id_rab AND sm.jenis_rab = bd.rab_type
            //         INNER JOIN tb_unit_api unit ON unit.idunit = bd.unit_kerja
            //         WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'BREAKDOWN'
            //         AND (
            //             sm.created_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND sm.created_at < DATE_FORMAT(CURRENT_DATE() + INTERVAL 2 MONTH, '%Y-%m-01')
            //             OR
            //             sm.updated_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND sm.updated_at < DATE_FORMAT(CURRENT_DATE() + INTERVAL 2 MONTH, '%Y-%m-01')
            //         )
            //         ORDER BY sm.jenis_validasi, sm.status
            //     ) SELECT * FROM semulaMenjadi sm WHERE sm.unit_kerja = '$idunit' AND sm.kd_sumberdana = '$kodeSd' AND sm.jenis_revisi = 'BREAKDOWN'
            //     ORDER BY CASE WHEN sm.status IS NULL OR sm.status = '' THEN 0 ELSE 1 END, sm.status";
            // Execute the query
            // $query = getBaseData($query, $tahun, $tahunAngka );
            // Return the query result
            $query = DB::connection('sirekat')->select("WITH BaseData AS ( SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted AS is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja AS unit_kerja_rkt,
                    rkt.kd_rk, rab.biaya_satuan, rab.jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                    rab.kuantitas, rab.satuan_kuantitas AS sKuantitas, rab.durasi, rab.satuan_durasi AS sDurasi, rab.kegiatan, rab.satuan_kegiatan AS sKegiatan,
                    'OPERASIONAL' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan AS itemCoa, rab.tanggapan
                FROM tb_rekat rkt
                JOIN tb_rabkegiatan rab ON rab.id_rekat = rkt.id
                WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                UNION ALL
                SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted AS is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja AS unit_kerja_rkt,
                    rkt.kd_rk, rab.harga_satuan AS biaya_satuan, rab.jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                    rab.kuantitas, rab.satuan AS sKuantitas, '1' AS durasi , 'Pkt' AS sDurasi, '1' AS kegiatan, 'Keg' AS sKegiatan,
                    'SARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan AS itemCoa, rab.tanggapan
                FROM tb_rekat rkt
                JOIN tb_rabperalatan rab ON rab.id_rekat = rkt.id
                WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                UNION ALL
                SELECT
                    rkt.id AS id_rekat, rkt.sub_judul, rkt.sd AS kd_sumberdana, rkt.tahun, rab.is_deleted,
                    rkt.is_deleted as is_deleted_rkt, rab.id_jenis_belanja, rab.jenis_belanja,
                    rab.unit_kerja, rab.id_mak, rab.id, rkt.unit_kerja as unit_kerja_rkt,
                    rkt.kd_rk, rab.jumlah_nilai AS biaya_satuan, rab.jumlah_nilai AS jumlah_biaya, rab.verifikasi_pimpinan_unit,
                    rab.verifikasi_tim, rab.verifikasi_keu, rab.verifikasi_aset, rab.verifikasi_pimpinan_univ,
                    rab.kuantitas, rab.satuan AS sKuantitas, '1' AS durasi , 'Pkt' AS sDurasi, '1' AS kegiatan, 'Keg' AS sKegiatan,
                    'PRASARANA' AS rab_type, rab.nip_bpp, rab.nip_ppk, rab.is_draft, rab.rpd, rab.kebutuhan_kegiatan as itemCoa, rab.tanggapan
                FROM tb_rekat rkt
                JOIN tb_rabgedung rab ON rab.id_rekat = rkt.id
                WHERE rkt.tahun = '$tahun' AND rkt.is_deleted = 'false'
                ),
                semulaMenjadi AS (
                    SELECT
                        bd.is_deleted AS is_deleted_rab, bd.id_rekat, sm.id AS id_sm, bd.kd_sumberdana, bd.sub_judul, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja,
                        sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status,
                        bd.is_draft, bd.itemCoa,
                        unit.nama AS namaUnit, sm.jenis_revisi, sm.spek_semula_json, sm.spek_menjadi_json,
                        sm.is_deleted
                    FROM tb_semula_menjadi sm
                    INNER JOIN BaseData bd ON bd.id = sm.id_rab AND sm.jenis_rab = bd.rab_type
                    INNER JOIN tb_unit_api unit ON unit.idunit = bd.unit_kerja
                    WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'BREAKDOWN'
                    AND (
                        sm.created_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND sm.created_at < DATE_FORMAT(CURRENT_DATE() + INTERVAL 2 MONTH, '%Y-%m-01')
                        OR
                        sm.updated_at >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND sm.updated_at < DATE_FORMAT(CURRENT_DATE() + INTERVAL 2 MONTH, '%Y-%m-01')
                    )
                    ORDER BY sm.jenis_validasi, sm.status
                ) SELECT * FROM semulaMenjadi sm WHERE sm.unit_kerja = '$idunit' AND sm.kd_sumberdana = '$kodeSd' AND sm.jenis_revisi = 'BREAKDOWN'
                ORDER BY CASE WHEN sm.status IS NULL OR sm.status = '' THEN 0 ELSE 1 END, sm.status");
            return $query;
        } catch (\Exception $e) {
            throw new \Exception("Error getting rekapitulasi data: " . $e->getMessage());
        }
    }

    /**
     * Validate is item is already in revision/validation process
    */
    public function isItemInProcess($idRab, $jenisRab) {
        $isExistsOnSemulaMenjadi = SemulaMenjadi::where([
            "id_rab"         => $idRab,
            "jenis_rab"      => $jenisRab == "RAB_GEDUNG" ? "PRASARANA" : ( $jenisRab == "RAB_PERALATAN" ? "SARANA" : "OPERASIONAL" ),
            "status"         => "",
            "is_deleted"     => "false"
        ])->exists();
        return $isExistsOnSemulaMenjadi;
    }

    /**
     * Check if rekat is already canceled
     */
    public function isRekatCanceled($idRekat) {
        $isCanceled = DB::connection('sirekat')->select("tb_kegiatan_batal")->where([ "id_rekat" => $idRekat ])->exists();
        return $isCanceled;
    }

    /**
     * Validate canceled item
     */
    public function validateCanceledItem($jenisRab, $idRekat, $rekat) {
        // pick the right config
        $config = $this->rabConfig[$jenisRab] ?? $this->rabConfig[null];
        // Build query
        $rabQuery = $config["model"]::where([ "id_rekat" => $idRekat, "is_deleted" => "false" ]);
        $jumlahBiaya = $rabQuery->sum($config["field"]);

        // Loop through items and run validations
        foreach ($rabQuery->get() as $mak) {
            if ( function_exists('cekAmprah') && cekAmprah($mak->id_mak))
                return [ "message" => "Terdapat item coa dari kegiatan '{$rekat->first()->sub_judul}' yang telah diproses", "success" => false, "jumlahBiaya" => $jumlahBiaya, "rab" => $rabQuery ];
            if ( function_exists('cekAmprahRealtime') && cekAmprahRealtime($mak->id_mak))
                return [ "message" => "Terdapat item coa dari kegiatan '{$rekat->first()->sub_judul}' yang telah diproses", "success" => false, "jumlahBiaya" => $jumlahBiaya, "rab" => $rabQuery ];
            if ( function_exists('cekPaket') && cekPaket($mak->id, $config["paketLabel"]))
               return [ "message" => "Terdapat item coa dari kegiatan '{$rekat->first()->sub_judul}' yang telah dipaketkan.", "success" => false, "jumlahBiaya" => $jumlahBiaya, "rab" => $rabQuery ];
            $checkPending = $this->checkPendingValidation($mak->id, $config["paketLabel"]);
            if ( $checkPending['exists'] )
                return [ "message" => "Terdapat item coa dari kegiatan '{$rekat->first()->sub_judul}' yang sedang dalam proses revisi/validasi. {$mak->id}", "success" => false, "jumlahBiaya" => $jumlahBiaya, "rab" => $rabQuery ];
        }
        return [ "message" => "Validasi berhasil", "success" => true, "jumlahBiaya" => $jumlahBiaya, "rab" => $rabQuery ];
    }

    /**
     * Validate rekapitulasi inputs
     */
    private function validateRekapitulasiInputs($idunit, $kodeSd, $tahun, $tahunAngka) {
        if (empty($idunit) || !is_string($idunit)) {
            throw new \Exception("Unit kerja tidak valid");
        }

        if (empty($kodeSd) || !is_string($kodeSd)) {
            throw new \Exception("Kode sumber dana tidak valid");
        }

        if (empty($tahun) || !is_string($tahun)) {
            throw new \Exception("Tahun tidak valid");
        }

        if (empty($tahunAngka) || !is_numeric($tahunAngka)) {
            throw new \Exception("Tahun angka tidak valid");
        }

        // Additional validation for expected formats
        if (!preg_match('/^[A-Z0-9_]+$/', $idunit)) {
            throw new \Exception("Format unit kerja tidak valid");
        }

        if (!preg_match('/^[A-Z0-9_]+$/', $kodeSd)) {
            throw new \Exception("Format kode sumber dana tidak valid");
        }

        if (!preg_match('/^\d{4}$/', $tahunAngka)) {
            throw new \Exception("Format tahun tidak valid");
        }

        return true;
    }

    public function updateSemulaMenjadiStatus($idSm, $status, $isDraft, $jenisRab, $idunit, $kodeSd, $tahun ) {
        try {
            $sm = SemulaMenjadi::where(["is_deleted" => "false", "id" => $idSm, "jenis_revisi" => "BREAKDOWN" ]);
            $this->validateSemulaMenjadi($sm, $status, $jenisRab, $idunit, $kodeSd, $tahun);
            // Update the status of SemulaMenjadi
            $sm->update([ "status" => $status ]);
            $idRab = $sm->first()->id_rab;

            if ( $jenisRab == "OPERASIONAL" ) {
                $this->updateSemulaMenjadiOperasional( $idRab, $isDraft, $sm->first(), $status, $tahun );
            } if ( $jenisRab == "SARANA" ) {
                $this->updateSemulaMenjadiSarana( $idRab, $isDraft, $sm->first(), $status, $tahun );
            } if ( $jenisRab == "PRASARANA" ) {
                $this->updateSemulaMenjadiPrasarana( $idRab, $isDraft, $sm->first(), $status, $tahun );
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getSisaSaldo( $jenis, $tahun, $request ) {
        if ( !$request->idunit || !$request->kodeSd || !$jenis || !$tahun ) {
            return response()->json([
                "success" => false,
                "message" => "Parameter tidak lengkap."
            ]);
        }
        $whereParams = [];
        if ( $jenis == "BREAKDOWN" )
            $whereParams = ["idunit" => $request->idunit, "sd" => $request->kodeSd, "tahun" => $tahun, "jenis_saldo" => "BREAKDOWN" ];

        $saldo = SisaSaldoValidasi::where($whereParams)->get();
        return $saldo;
    }
    private function updateSaldo( $jenis, $tahun, $kodeCoa, $semulaMenjadi, $idRekat, $status, $aksi ) {
        if ( !$jenis || !$tahun || !$kodeCoa || !$semulaMenjadi || !$idRekat || !$status ) {
            return response()->json([
                "success" => false,
                "message" => "Parameter tidak lengkap.",
            ]);
        }
        if ( !in_array( $status, ["Setuju", "Tolak"]) ) {
            return response()->json([
                "success" => false,
                "message" => "Status tidak valid.",
            ]);
        }

        $whereParams = [];
        $updateData = [];

        if ( $jenis == "BREAKDOWN" ) {
            // Define WHERE conditions for finding existing record
            $whereParams = [
                "idunit" => "-",
                "sd" => "-",
                "kode_ss" => "-",
                "kode_ikk" => "-",
                "jenis" => "-",
                "id_rekat" => $idRekat,
                "kode_coa" => $kodeCoa,
                "tahun" => $tahun,
                "jenis_saldo" => "BREAKDOWN"
            ];
            $saldo = $semulaMenjadi->jumlah_menjadi;
            if ( $aksi == "!addItemCoa" ) {
                $saldo = $semulaMenjadi->jumlah_menjadi - $semulaMenjadi->jumlah_semula;
            }
            if ( $saldo < 0 ) {
                $saldo = 0;
            }
            // Define UPDATE data (only the fields that should be updated)
            if ( $status == "Setuju" ) {
                $updateData = [ "sisa_saldo" => 0 ];
            } else { // Tolak
                $currentSaldo = SisaSaldoValidasi::where($whereParams)->lockForUpdate()->value("sisa_saldo") ?? 0;
                $updateData = [ "sisa_saldo" => $currentSaldo + $saldo ];
            }
        }

        // Use updateOrCreate with separate WHERE and UPDATE parameters
        return SisaSaldoValidasi::updateOrCreate($whereParams, $updateData);
    }
    private function validateSemulaMenjadi( $sm, $status, $jenisRab, $idunit, $kodeSd, $tahun ) {
        if ( $sm->exists() ) {
            $data = $sm->first();
            if ( $data->jenis_validasi == "Penambahan Item Coa" ) {
                if ( cekPagu($idunit, $kodeSd, $tahun, $data->jumlah_menjadi, false, true) === "error" ) {
                    throw new \Exception("Pagu tidak mencukupi untuk penambahan item COA.");
                }
            } if ( $data->jenis_validasi == "Pengurangan" && $status == "Tolak" ) {
                if ( cekPagu($idunit, $kodeSd, $tahun, ( $data->jumlah_semula - $data->jumlah_menjadi ) ) === "error" ) {
                    throw new \Exception("Pagu tidak mencukupi untuk persetujuan ini.");
                }
            }
        }
        if (!$sm->exists()) {
            throw new \Exception("Data tidak ditemukan.");
        }
        if ($sm->first()->status == $status) {
            throw new \Exception("Data sudah dalam status {$status}.");
        }
        if ( $sm->first()->jenis_rab != $jenisRab ) {
            throw new \Exception("Jenis RAB tidak sesuai.");
        }
        if ( !in_array($status, ["Setuju", "Tolak"]) ) {
            throw new \Exception("Status tidak valid.");
        }
    }
    private function updateSemulaMenjadiOperasional( $idRab, $isDraft, $semulaMenjadi, $status, $tahun ) {
        try {
            $rab = RABKEG::where([ "id" => $idRab, "is_deleted" => "false" ]);
            if (!$rab->exists()) {
                throw new \Exception("Data tidak ditemukan.");
            }
            $dataRab          = $rab->first();
            $spekMenjadiData  = $this->resolveSpekData($semulaMenjadi->spek_menjadi_json, $semulaMenjadi->spek_menjadi, "RAB_KEGIATAN");
            $spekSemulaData   = $this->resolveSpekData($semulaMenjadi->spek_semula_json, $semulaMenjadi->spek_semula, "RAB_KEGIATAN");
            if ( $isDraft == "true" || $semulaMenjadi->jenis_validasi == "Penambahan Item Coa" ) {
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "is_draft" => "false", "verifikasi_pimpinan_univ" => "Setuju", "verifikasi_pimpinan_unit" => "Setuju",
                        "verifikasi_aset" => "Setuju", "verifikasi_keu" => "Setuju", "verifikasi_tim" => "Setuju"
                    ]);
                }
                if ( $status == "Tolak" ) {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([ "is_draft" => "true", "is_deleted" => "true", "file_pendukung_rpd" => null, "catatan" => "null" ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "addItemCoa" );
                }
            } else if ( $isDraft == "false" ) {
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "kuantitas"         => $spekMenjadiData['kuantitas'] ?? $dataRab->kuantitas,
                        "satuan_kuantitas"  => $spekMenjadiData['satuan_kuantitas'] ?? $dataRab->satuan_kuantitas,
                        "durasi"            => $spekMenjadiData['durasi'] ?? $dataRab->durasi,
                        "satuan_durasi"     => $spekMenjadiData['satuan_durasi'] ?? $dataRab->satuan_durasi,
                        "kegiatan"          => $spekMenjadiData['kegiatan'] ?? $dataRab->kegiatan,
                        "satuan_kegiatan"   => $spekMenjadiData['satuan_kegiatan'] ?? $dataRab->satuan_kegiatan,
                        "biaya_satuan"      => $spekMenjadiData['biaya_satuan'] ?? $dataRab->biaya_satuan,
                        "jumlah_biaya"      => $semulaMenjadi->jumlah_menjadi,
                        "id_jenis_belanja"  => $spekMenjadiData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"     => $spekMenjadiData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"               => $spekMenjadiData['rpd'] ?? $dataRab->rpd,
                        "kebutuhan_kegiatan"=> $spekMenjadiData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                        "kode_sbm"          => $spekMenjadiData['kode_sbm'] ?? $dataRab->kode_sbm,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Setuju", "!addItemCoa" );
                } else {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([
                        "kuantitas"         => $spekSemulaData['kuantitas'] ?? $dataRab->kuantitas,
                        "satuan_kuantitas"  => $spekSemulaData['satuan_kuantitas'] ?? $dataRab->satuan_kuantitas,
                        "durasi"            => $spekSemulaData['durasi'] ?? $dataRab->durasi,
                        "satuan_durasi"     => $spekSemulaData['satuan_durasi'] ?? $dataRab->satuan_durasi,
                        "kegiatan"          => $spekSemulaData['kegiatan'] ?? $dataRab->kegiatan,
                        "satuan_kegiatan"   => $spekSemulaData['satuan_kegiatan'] ?? $dataRab->satuan_kegiatan,
                        "biaya_satuan"      => $spekSemulaData['biaya_satuan'] ?? $dataRab->biaya_satuan,
                        "jumlah_biaya"      => $semulaMenjadi->jumlah_semula,
                        "id_jenis_belanja"  => $spekSemulaData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"     => $spekSemulaData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"               => $spekSemulaData['rpd'] ?? $dataRab->rpd,
                        "kebutuhan_kegiatan"=> $spekSemulaData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                        "kode_sbm"          => $spekSemulaData['kode_sbm'] ?? $dataRab->kode_sbm,
                        "file_pendukung_rpd"=> null,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "!addItemCoa" );
                }
            }
            event(new UserPerformedAction("98", session("id_role"), "Verifikasi Data Breakdown",
                "Verifikasi data breakdown operasional dengan id rab: $idRab dan status: $status", null, null,
                null, null, null, "UPDATE"
            ));
        } catch (\Exception $e) {
            throw new \Exception("Error updating SemulaMenjadi status by RAB: " . $e->getMessage());
        }
    }
    private function updateSemulaMenjadiSarana( $idRab, $isDraft, $semulaMenjadi, $status, $tahun ) {
        try {
            $rab = RABPER::where([ "id" => $idRab, "is_deleted" => "false" ]);
            if (!$rab->exists()) {
                throw new \Exception("Data tidak ditemukan.");
            }
            $dataRab           = $rab->first();
            $spekMenjadiData   = $this->resolveSpekData($semulaMenjadi->spek_menjadi_json, $semulaMenjadi->spek_menjadi, "RAB_PERALATAN");
            $spekSemulaData    = $this->resolveSpekData($semulaMenjadi->spek_semula_json, $semulaMenjadi->spek_semula, "RAB_PERALATAN");
            if ( $isDraft == "true" || $semulaMenjadi->jenis_validasi == "Penambahan Item Coa" ) {
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "is_draft" => "false", "verifikasi_pimpinan_univ" => "Setuju", "verifikasi_pimpinan_unit" => "Setuju",
                        "verifikasi_aset" => "Setuju", "verifikasi_keu" => "Setuju", "verifikasi_tim" => "Setuju"
                    ]);
                }
                if ( $status == "Tolak" ) {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([ "is_draft" => "true", "is_deleted" => "true", "file_pendukung_rpd" => null, "catatan" => "null" ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "addItemCoa" );
                }
            } else if ( $isDraft == "false" ) {
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "kuantitas"          => $spekMenjadiData['kuantitas'] ?? $dataRab->kuantitas,
                        "satuan"             => $spekMenjadiData['satuan'] ?? $dataRab->satuan,
                        "harga_satuan"       => $spekMenjadiData['harga_satuan'] ?? $dataRab->harga_satuan,
                        "biaya_pajak"        => $spekMenjadiData['biaya_pajak'] ?? $dataRab->biaya_pajak,
                        "biaya_lainnya"      => $spekMenjadiData['biaya_lainnya'] ?? $dataRab->biaya_lainnya,
                        "jumlah_biaya"       => $semulaMenjadi->jumlah_menjadi,
                        "id_jenis_belanja"   => $spekMenjadiData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"      => $spekMenjadiData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"                => $spekMenjadiData['rpd'] ?? $dataRab->rpd,
                        "kode_aset"          => $spekMenjadiData['kode_aset'] ?? $dataRab->kode_aset,
                        "aset"               => $spekMenjadiData['aset'] ?? $dataRab->aset,
                        "kebutuhan_kegiatan" => $spekMenjadiData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                        "merk"               => $spekMenjadiData['merk'] ?? $dataRab->merk,
                        "type"               => $spekMenjadiData['type'] ?? $dataRab->type,
                        "url"                => $spekMenjadiData['url'] ?? $dataRab->url,
                        "kode_sbm"           => $spekMenjadiData['kode_sbm'] ?? $dataRab->kode_sbm,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Setuju", "!addItemCoa" );
                } else {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([
                        "kuantitas"          => $spekSemulaData['kuantitas'] ?? $dataRab->kuantitas,
                        "satuan"             => $spekSemulaData['satuan'] ?? $dataRab->satuan,
                        "harga_satuan"       => $spekSemulaData['harga_satuan'] ?? $dataRab->harga_satuan,
                        "biaya_pajak"        => $spekSemulaData['biaya_pajak'] ?? $dataRab->biaya_pajak,
                        "biaya_lainnya"      => $spekSemulaData['biaya_lainnya'] ?? $dataRab->biaya_lainnya,
                        "jumlah_biaya"       => $semulaMenjadi->jumlah_semula,
                        "id_jenis_belanja"   => $spekSemulaData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"      => $spekSemulaData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"                => $spekSemulaData['rpd'] ?? $dataRab->rpd,
                        "kode_aset"          => $spekSemulaData['kode_aset'] ?? $dataRab->kode_aset,
                        "aset"               => $spekSemulaData['aset'] ?? $dataRab->aset,
                        "kebutuhan_kegiatan" => $spekSemulaData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                        "merk"               => $spekSemulaData['merk'] ?? $dataRab->merk,
                        "type"               => $spekSemulaData['type'] ?? $dataRab->type,
                        "url"                => $spekSemulaData['url'] ?? $dataRab->url,
                        "kode_sbm"           => $spekSemulaData['kode_sbm'] ?? $dataRab->kode_sbm,
                        "file_pendukung_rpd" => null,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "!addItemCoa" );
                }
            }
            event(new UserPerformedAction("98", session("id_role"), "Verifikasi Data Breakdown",
                "Verifikasi data breakdown sarana dengan id rab: $idRab dan status: $status", null, null,
                null, null, null, "UPDATE"
            ));
        } catch (\Exception $e) {
            throw new \Exception("Error updating SemulaMenjadi status by RAB: " . $e->getMessage());
        }
    }
    private function updateSemulaMenjadiPrasarana( $idRab, $isDraft, $semulaMenjadi, $status, $tahun ) {
        try {
            $rab = RABGDG::where([ "id" => $idRab, "is_deleted" => "false" ]);
            if (!$rab->exists()) {
                throw new \Exception("Data tidak ditemukan.");
            }
            $dataRab           = $rab->first();
            $spekMenjadiData   = $this->resolveSpekData($semulaMenjadi->spek_menjadi_json, $semulaMenjadi->spek_menjadi, "RAB_GEDUNG");
            $spekSemulaData    = $this->resolveSpekData($semulaMenjadi->spek_semula_json, $semulaMenjadi->spek_semula, "RAB_GEDUNG");
            if ( $isDraft == "true" || $semulaMenjadi->jenis_validasi == "Penambahan Item Coa" ) {
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "is_draft" => "false", "verifikasi_pimpinan_univ" => "Setuju", "verifikasi_pimpinan_unit" => "Setuju",
                        "verifikasi_aset" => "Setuju", "verifikasi_keu" => "Setuju", "verifikasi_tim" => "Setuju"
                    ]);
                }
                if ( $status == "Tolak" ) {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([ "is_draft" => "true", "is_deleted" => "true", "file_pendukung_rpd" => null, "catatan" => "null" ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "addItemCoa" );
                }
            } else if ( $isDraft == "false" ) {
                $jenisMenjadi = $spekMenjadiData['jenis_pekerjaan'] ?? $dataRab->jenis_pekerjaan;
                if ( $jenisMenjadi == "Perencanaan (DED)" )
                    $jenisMenjadi = "Perencanaan";
                $jenisSemula = $spekSemulaData['jenis_pekerjaan'] ?? $dataRab->jenis_pekerjaan;
                if ( $jenisSemula == "Perencanaan (DED)" )
                    $jenisSemula = "Perencanaan";
                if ( $status == "Setuju" ) {
                    $rab->update([
                        "jumlah_nilai"       => $semulaMenjadi->jumlah_menjadi,
                        "kode_sbm"           => $spekMenjadiData['kode_sbm'] ?? $dataRab->kode_sbm,
                        "id_jenis_belanja"   => $spekMenjadiData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"      => $spekMenjadiData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"                => $spekMenjadiData['rpd'] ?? $dataRab->rpd,
                        "kode_aset"          => $spekMenjadiData['kode_aset'] ?? $dataRab->kode_aset,
                        "aset"               => $spekMenjadiData['aset'] ?? $dataRab->aset,
                        "jenis_pekerjaan"    => $jenisMenjadi,
                        "kebutuhan_kegiatan" => $spekMenjadiData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Setuju", "!addItemCoa" );
                } else {
                    $this->deleteFilePendukungRpd($dataRab);
                    $rab->update([
                        "jumlah_nilai"       => $semulaMenjadi->jumlah_semula,
                        "kode_sbm"           => $spekSemulaData['kode_sbm'] ?? $dataRab->kode_sbm,
                        "id_jenis_belanja"   => $spekSemulaData['id_jenis_belanja'] ?? $dataRab->id_jenis_belanja,
                        "jenis_belanja"      => $spekSemulaData['jenis_belanja'] ?? $dataRab->jenis_belanja,
                        "rpd"                => $spekSemulaData['rpd'] ?? $dataRab->rpd,
                        "kode_aset"          => $spekSemulaData['kode_aset'] ?? $dataRab->kode_aset,
                        "aset"               => $spekSemulaData['aset'] ?? $dataRab->aset,
                        "jenis_pekerjaan"    => $jenisSemula,
                        "kebutuhan_kegiatan" => $spekSemulaData['kebutuhan_kegiatan'] ?? $dataRab->kebutuhan_kegiatan,
                        "file_pendukung_rpd" => null,
                    ]);
                    $this->updateSaldo( "BREAKDOWN", $tahun, $dataRab->id_jenis_belanja, $semulaMenjadi, $dataRab->id_rekat, "Tolak", "!addItemCoa");
                }
            }
            event(new UserPerformedAction("98", session("id_role"), "Verifikasi Data Breakdown",
                "Verifikasi data breakdown parasarana dengan id rab: $idRab dan status: $status", null, null,
                null, null, null, "UPDATE"
            ));
        } catch (\Exception $e) {
            throw new \Exception("Error updating SemulaMenjadi status by RAB: " . $e->getMessage());
        }
    }

    private function deleteFilePendukungRpd($rabModel): void {
        if (!$rabModel) return;

        $filePath = $rabModel->file_pendukung_rpd ?? null;
        if (!empty($filePath) && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
    }
    public function getDetailRevisi( $idSm ) {
        $sm = SemulaMenjadi::where([ "is_deleted" => "false", "id" => $idSm ]);
        if ( !$sm->exists() ) {
            throw new \Exception("Data tidak ditemukan.");
        }
        return $sm->first();
    }
    public function getDataExistingRevisi( $tahun, $tahunAngka, $idunit, $kodeSd, $idBackup ){
        $jenisRevisi = "SS";
        $filterUnit = " AND backupRkat.idunit = ? ";
        $params     = [ $idBackup, $idBackup, $tahun, $idunit ];
        if ( $idunit == "X" ) {
            $filterUnit = "";
            $params     = [ $idBackup, $idBackup, $tahun ];
        }
        $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = backupRkat.id_rekat
                LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = backupRkat.kd_rk";
        $selectDataMaster = $tahunAngka >= 2026 ?
                "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan"
                : "dm.*";
        $orderBy = $tahunAngka >= 2026 ? "ikv.kode_ikv" : "dm.kode_keg";
        $sql = "SELECT unit.nama AS nama_unit, backupRkat.sub_judul, unit.idunit AS unit_kerja_rkt,
                backupRkatDet.id, backupRkatDet.id_jenis_belanja, backupRkatDet.jenis_belanja,
                $selectDataMaster,
                sd.kd_sumberdana, sd.sumberdana,
                ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL) AND backupRkatDet.sisa_pengalihan IS NOT NULL
                    THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) + backupRkatDet.sisa_pengalihan
                ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya,
                backupRkatDet.jumlah_biaya AS jumlah_biaya_usulan,
                COALESCE(backupRkatDet.jumlah_amprahan, 0) AS TOTAL_AMPRAH,
                COALESCE(backupRkatDet.jumlah_realisasi, 0) AS TOTAL_REALISASI
            FROM tb_backup_rkat backupRkat
            INNER JOIN baseDataBackup backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
            $joinDataMaster
            INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
            INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
            WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? ) AND backupRkat.sd IN ($kodeSd)
        AND backupRkat.tahun = ? $filterUnit
        ORDER BY $orderBy";
        $data = getBaseData($sql, $tahun, $tahunAngka, null, null, $params );
        return $data;
    }
    public function getDataUsulanRevisi($tahun, $tahunAngka, $idunit, $kodeSd){
        $filterUnit = " AND ( rkat.unit_kerja = ? AND rkat.unit_kerja_rkt = ? )";
        $params     = [ $idunit, $idunit, $tahun ];
        if ( $idunit == "X" ) {
            $filterUnit = "";
            $params     = [ $tahun ];
        }
        $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
            LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
            LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
            LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
            LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
        $selectDataMaster = $tahunAngka >= 2026 ?
                "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan"
                : "dm.*";
        $sql = "SELECT sd.kd_sumberdana, sd.sumberdana, rkat.id, unit.nama AS nama_unit, $selectDataMaster,
                rkat.id_jenis_belanja, rkat.jenis_belanja,
                CASE
                    WHEN (amprah.jumlah_amprahan IS NOT NULL OR amprah.jumlah_realisasi IS NOT NULL) AND rt.dipakai IS NOT NULL
                        THEN amprah.jumlah_amprahan + amprah.jumlah_realisasi + rt.sisa
                    ELSE rkat.jumlah_biaya END                    AS jumlah_biaya,
                rkat.jumlah_biaya                                 AS jumlah_biaya_usulan,
                COALESCE(amprah.jumlah_amprahan, 0)               AS TOTAL_AMPRAH,
                COALESCE(amprah.jumlah_realisasi, 0)              AS TOTAL_REALISASI,
                COALESCE(sm.jumlah_menjadi, 0) AS TOTAL_REVISI
            FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN realisasi amprah ON amprah.id_mak = rkat.id_mak
                    LEFT JOIN realisasiTerpakai rt ON rt.id_rab = rkat.id AND rt.jenis_rab = rkat.rab_type
                    LEFT JOIN tb_semula_menjadi sm ON sm.id_rab = rkat.id AND sm.jenis_rab = rkat.rab_type AND sm.status = '' AND sm.is_deleted = 'false' AND sm.jenis_revisi = 'SS'
                AND ( sm.jenis_validasi = 'Penambahan' AND sm.jumlah_semula <> sm.jumlah_menjadi )
            WHERE ( rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false' )
            AND rkat.kd_sumberdana IN ($kodeSd)
            $filterUnit
            AND rkat.tahun = ?
            ";
        $data = getBaseData($sql, $tahun, $tahunAngka, null, null, $params);
        return $data;
    }
    public function getPenambahanItemCoa($tahun, $tahunAngka, $idunit, $kodeSd) {
        $sql = ", semulaMenjadi AS (
                SELECT sm.id AS id_sm, bd.id_rekat, bd.unit_kerja, bd.id_jenis_belanja, bd.jenis_belanja, # id_sm = id semula menjadi
                    sm.jenis_validasi, sm.jenis_rab, sm.jumlah_semula, sm.jumlah_menjadi, sm.status, bd.is_draft,
                    unit.nama AS namaUnit, dm.kode_ss, dm.ss, dm.kode_ikk, dm.ikk, dm.kode_ikv, dm.ikv, sd.kd_sumberdana, sd.sumberdana
                FROM tb_semula_menjadi sm
                INNER JOIN BaseData bd ON bd.id = sm.id_rab AND sm.jenis_rab = bd.rab_type
                INNER JOIN dataMaster dm ON dm.kode_keg = bd.kd_rk
                INNER JOIN sumberdana sd ON sd.kd_sumberdana = bd.kd_sumberdana
                INNER JOIN tb_unit_api unit on unit.idunit = bd.unit_kerja
                WHERE sm.is_deleted = 'false' AND sm.jenis_revisi = 'SS' AND sm.is_deleted = 'false' AND (sm.status = '' or sm.status is null )
                AND sm.jenis_validasi = 'Penambahan Item Coa' AND bd.kd_sumberdana IN ($kodeSd) AND bd.unit_kerja = ? AND bd.unit_kerja_rkt = ? AND bd.tahun = ?
        ) SELECT * FROM semulaMenjadi sm";
        // Execute the query
        $data = getBaseData($sql, $tahun, $tahunAngka, null, null, [ $idunit, $idunit, $tahun ]);
        return $data;
    }

    /**
     * Validate numeric fields for budget reduction
     *
     * @param array $fields Associative array of field names and values to validate
     * @return array Returns validation result ['success' => bool, 'message' => string]
     */
    public function validateNumericFields(array $fields): array {
        foreach ($fields as $fieldName => $value) {
            if (!is_numeric($value)) {
                return [
                    'success' => false,
                    'message' => ucfirst($fieldName) . " harus berupa angka"
                ];
            }
        }
        return ['success' => true];
    }

    /**
     * Check if item has pending validation
     *
     * @param int $idRab RAB ID
     * @param string $jenisRab RAB type (OPERASIONAL, SARANA, PRASARANA)
     * @param string $jenisValidasi Validation type (Pengurangan, Penambahan)
     * @return array Returns check result ['exists' => bool, 'message' => string]
     */
    public function checkPendingValidation(int $idRab, string $jenisRab, string $jenisValidasi = 'Pengurangan'): array {
        $exists = SemulaMenjadi::where([
            "id_rab"         => $idRab,
            "jenis_rab"      => $jenisRab,
            "status"         => "",
            "is_deleted"     => "false"
        ])->exists();
        return [
            'exists' => $exists,
            'message' => $exists ? "Data sudah diajukan untuk validasi, harap tunggu proses selanjutnya" : ""
        ];
    }

    /**
     * Get 10% minimum threshold for Rekat budget
     * Reusable method to calculate the 10% minimum threshold from backup data
     *
     * @param int $idRekat Rekat ID
     * @return array Returns ['tenPercent' => float, 'totalBackup' => float]
     */
    public function getTenPercentMinimum(int $idRekat): array {
        try {
            $totalRekat = DB::connection('sirekat')->select("SELECT br.id_rekat, SUM(brd.jumlah_biaya) AS TOTAL
                FROM tb_backup_rkat br
                INNER JOIN tb_backup_rkat_detail brd ON br.id_rekat = brd.id_rekat AND br.id_duplikasi = '69'
                WHERE brd.is_deleted = 'false' AND br.is_deleted = 'false'
                AND brd.id_duplikasi = '69' AND br.id_rekat = ?
                GROUP BY br.id_rekat", [$idRekat]);

            $totalBackup = $totalRekat && count($totalRekat) > 0 ? $totalRekat[0]->TOTAL : 0;
            $tenPercent = $totalBackup * 0.1;

            return [
                'tenPercent' => $tenPercent,
                'totalBackup' => $totalBackup
            ];
        } catch (\Exception $e) {
            throw new \Exception("Error calculating 10% minimum: " . $e->getMessage());
        }
    }

    /**
     * Validate budget reduction constraints with 10% minimum check
     *
     * @param float $currentAmount Current budget amount
     * @param float $newAmount New budget amount
     * @param float $minAmount Minimum allowed amount (e.g., 10% of total)
     * @param float $totalAllData Total budget for the activity
     * @return array Returns validation result ['success' => bool, 'message' => string]
     */
    public function validateBudgetReduction(float $currentAmount, float $newAmount, float $minAmount = null, float $totalAllData = null): array {
        if ($currentAmount < $newAmount) {
            return [
                'success' => false,
                'message' => "Jumlah biaya tidak boleh lebih besar dari sebelumnya"
            ];
        }

        if ($minAmount !== null && $totalAllData !== null) {
            $totalPengurangan = $currentAmount - $newAmount;
            if ($totalAllData - $totalPengurangan < $minAmount) {
                return [
                    'success' => false,
                    'message' => "Jumlah biaya tidak boleh kurang dari batas minimum yang ditentukan (10% dari total anggaran kegiatan)"
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Validate packet status and amprah status
     *
     * @param object $rab RAB object
     * @param string $paketLabel Packet label (OPERASIONAL, SARANA, PRASARANA)
     * @param bool $checkAmprah Whether to check amprah status
     * @return array Returns validation result ['success' => bool, 'message' => string]
     */
    public function validateItemStatus(object $rab, string $paketLabel, bool $checkAmprah = true): array {
        // Check if item is in a packet
        if (function_exists('cekPaket') && cekPaket($rab->id, $paketLabel) == "200") {
            return [
                'success' => false,
                'message' => "Data tidak dapat di revisi karena sudah dipaketkan"
            ];
        }
        // Check if item has been processed by SIMKEU
        if ($checkAmprah && function_exists('cekAmprah') && cekAmprah($rab->id_mak) === true) {
            return [
                'success' => false,
                'message' => "Data tidak dapat di revisi karena telah diproses di SIMKEU",
                'canProcess' => false
            ];
        }
        if ($checkAmprah && function_exists('cekAmprahRealtime') && cekAmprahRealtime($rab->id_mak) === true) {
            return [
                'success' => false,
                'message' => "Data tidak dapat di revisi karena telah diproses di SIMKEU",
                'canProcess' => false
            ];
        }
        return ['success' => true];
    }

    /**
     * Create SemulaMenjadi record for budget reduction (KK revision)
     *
     * @param int $idRab RAB ID
     * @param string $jenisRab RAB type (OPERASIONAL, SARANA, PRASARANA)
     * @param float $jumlahSemula Original amount
     * @param float $jumlahMenjadi New amount
     * @param array $spekSemulaJson Original spec in JSON format
     * @param array $spekMenjadiJson New spec in JSON format
     * @param string $jenisValidasi Validation type (default: 'Pengurangan')
     * @return SemulaMenjadi Created SemulaMenjadi instance
     */
    public function createBudgetReductionRecord(
        int $idRab,
        string $jenisRab,
        float $jumlahSemula,
        float $jumlahMenjadi,
        array $spekSemulaJson,
        array $spekMenjadiJson,
        string $jenisValidasi = 'Pengurangan',
        string $jenisRevisi
    ): SemulaMenjadi {
        return SemulaMenjadi::create([
            "id_rab"            => $idRab,
            "jenis_rab"         => $jenisRab,
            "jenis_validasi"    => $jenisValidasi,
            "jenis_revisi"      => $jenisRevisi,
            "jumlah_semula"     => $jumlahSemula,
            "jumlah_menjadi"    => $jumlahMenjadi,
            "spek_semula_json"  => json_encode($spekSemulaJson),
            "spek_menjadi_json" => json_encode($spekMenjadiJson),
            "status"            => ""
        ]);
    }

    /**
     * Parse spek data from JSON or delimiter format
     * Automatically detects format and returns associative array
     *
     * @param string|null $jsonData JSON encoded string
     * @param string|null $delimiterData Delimiter-based string (~~~)
     * @param string $jenisRab RAB type for determining field mapping
     * @return array|null Parsed spec data as associative array
     */
    public function parseSpekData(?string $jsonData, ?string $delimiterData, string $jenisRab): ?array {
        // Try JSON first
        if (!empty($jsonData)) {
            $decoded = json_decode($jsonData, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback to delimiter format
        if (!empty($delimiterData)) {
            $parts = explode("~~~", $delimiterData);
            return $this->mapDelimiterToArray($parts, $jenisRab);
        }

        return null;
    }

    /**
     * Map delimiter-based array to associative array based on RAB type
     *
     * @param array $parts Exploded delimiter array
     * @param string $jenisRab RAB type
     * @return array Associative array with named keys
     */
    private function mapDelimiterToArray(array $parts, string $jenisRab): array {
        if ($jenisRab === 'OPERASIONAL') {
            return [
                'kode_sbm' => $parts[0] ?? null,
                'kuantitas' => $parts[1] ?? null,
                'satuan_kuantitas' => $parts[2] ?? null,
                'durasi' => $parts[3] ?? null,
                'satuan_durasi' => $parts[4] ?? null,
                'kegiatan' => $parts[5] ?? null,
                'satuan_kegiatan' => $parts[6] ?? null,
                'biaya_satuan' => $parts[7] ?? null,
                'id_jenis_belanja' => $parts[8] ?? null,
                'jenis_belanja' => $parts[9] ?? null,
                'rpd' => $parts[10] ?? null,
                'kebutuhan_kegiatan' => $parts[11] ?? null,
            ];
        } elseif ($jenisRab === 'SARANA') {
            return [
                'kode_sbm' => $parts[0] ?? null,
                'kuantitas' => $parts[1] ?? null,
                'satuan' => $parts[2] ?? null,
                'harga_satuan' => $parts[3] ?? null,
                'biaya_pajak' => $parts[4] ?? null,
                'biaya_lainnya' => $parts[5] ?? null,
                'id_jenis_belanja' => $parts[6] ?? null,
                'jenis_belanja' => $parts[7] ?? null,
                'rpd' => $parts[8] ?? null,
                'kode_aset' => $parts[9] ?? null,
                'aset' => $parts[10] ?? null,
                'merk' => $parts[11] ?? null,
                'type' => $parts[12] ?? null,
                'url' => $parts[13] ?? null,
                'kebutuhan_kegiatan' => $parts[14] ?? null,
            ];
        } elseif ($jenisRab === 'PRASARANA') {
            return [
                'kode_sbm' => $parts[0] ?? null,
                'kuantitas' => $parts[1] ?? null,
                'satuan' => $parts[2] ?? null,
                'id_jenis_belanja' => $parts[3] ?? null,
                'jenis_belanja' => $parts[4] ?? null,
                'rpd' => $parts[5] ?? null,
                'kode_aset' => $parts[6] ?? null,
                'aset' => $parts[7] ?? null,
                'jenis_pekerjaan' => $parts[8] ?? null,
                'kebutuhan_kegiatan' => $parts[9] ?? null,
            ];
        }

        return [];
    }

    /**
     * Update RAB model with spec data (supports both JSON and delimiter formats)
     *
     * @param object $rab RAB model instance (RABKEG, RABPER, or RABGDG)
     * @param array $spekData Parsed spec data as associative array
     * @param float $jumlahBiaya Budget amount
     * @param string $jenisRab RAB type
     * @return bool Update result
     */
    public function updateRabWithSpec($rab, array $spekData, float $jumlahBiaya, string $jenisRab): bool {
        $updateData = [];

        if ($jenisRab === 'OPERASIONAL') {
            $updateData = [
                "kuantitas"        => $spekData['kuantitas'] ?? null,
                "satuan_kuantitas" => $spekData['satuan_kuantitas'] ?? null,
                "durasi"           => $spekData['durasi'] ?? null,
                "satuan_durasi"    => $spekData['satuan_durasi'] ?? null,
                "kegiatan"         => $spekData['kegiatan'] ?? null,
                "satuan_kegiatan"  => $spekData['satuan_kegiatan'] ?? null,
                "biaya_satuan"     => $spekData['biaya_satuan'] ?? null,
                "jumlah_biaya"     => $jumlahBiaya,
                "id_jenis_belanja" => $spekData['id_jenis_belanja'] ?? null,
                "jenis_belanja"    => $spekData['jenis_belanja'] ?? null,
            ];
        } elseif ($jenisRab === 'SARANA') {
            $updateData = [
                "kode_aset"        => $spekData['kode_aset'] ?? null,
                "aset"             => $spekData['aset'] ?? null,
                "kuantitas"        => $spekData['kuantitas'] ?? null,
                "satuan"           => $spekData['satuan'] ?? null,
                "harga_satuan"     => $spekData['harga_satuan'] ?? null,
                "biaya_pajak"      => $spekData['biaya_pajak'] ?? null,
                "biaya_lainnya"    => $spekData['biaya_lainnya'] ?? null,
                "jumlah_biaya"     => $jumlahBiaya,
                "id_jenis_belanja" => $spekData['id_jenis_belanja'] ?? null,
                "jenis_belanja"    => $spekData['jenis_belanja'] ?? null,
            ];
        } elseif ($jenisRab === 'PRASARANA') {
            $updateData = [
                "kode_aset"        => $spekData['kode_aset'] ?? null,
                "aset"             => $spekData['aset'] ?? null,
                "jumlah_nilai"     => $jumlahBiaya,
                "id_jenis_belanja" => $spekData['id_jenis_belanja'] ?? null,
                "jenis_belanja"    => $spekData['jenis_belanja'] ?? null,
            ];
        }

        return $rab->update($updateData);
    }

    /**
     * Process budget reduction verification (approve/reject)
     * Handles all RAB types with JSON/delimiter format support
     *
     * @param SemulaMenjadi $semulaMenjadi SemulaMenjadi record
     * @param object $rab RAB model instance
     * @param string $status Status ('Setuju' or 'Tolak')
     * @param string $jenisRab RAB type class
     * @param float $totalPengurangan Total reduction amount
     * @param SisaSaldoValidasi $sisaSaldo Sisa saldo instance
     * @param float $currentSisaSaldo Current remaining balance
     * @return bool Transaction result
     */
    public function processBudgetVerification(
        SemulaMenjadi $semulaMenjadi,
        $rab,
        string $status,
        string $jenisRab,
        float $totalPengurangan,
        SisaSaldoValidasi $sisaSaldo,
        float $currentSisaSaldo
    ): bool {
        // Map class to string type
        $jenisRabStr = $jenisRab == RABKEG::class ? 'OPERASIONAL' :
                      ($jenisRab == RABPER::class ? 'SARANA' : 'PRASARANA');

        // Parse spec data
        $spekAwal = $this->parseSpekData(
            $semulaMenjadi->spek_semula_json,
            $semulaMenjadi->spek_semula,
            $jenisRabStr
        );

        $spekMenjadi = $this->parseSpekData(
            $semulaMenjadi->spek_menjadi_json,
            $semulaMenjadi->spek_menjadi,
            $jenisRabStr
        );

        return DB::connection('sirekat')->select(function () use (
            $sisaSaldo, $semulaMenjadi, $status, $rab, $totalPengurangan,
            $currentSisaSaldo, $spekAwal, $spekMenjadi, $jenisRabStr
        ) {
            if ($status == "Setuju") {
                // Update RAB with new spec
                $this->updateRabWithSpec(
                    $rab,
                    $spekMenjadi,
                    $semulaMenjadi->jumlah_menjadi,
                    $jenisRabStr
                );
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo + $totalPengurangan]);
            } else {
                // Revert to original spec
                $this->updateRabWithSpec(
                    $rab,
                    $spekAwal,
                    $semulaMenjadi->jumlah_semula,
                    $jenisRabStr
                );
                $semulaMenjadi->update(["status" => $status]);
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $totalPengurangan]);
            }
            return true;
        });
    }

    /**
     * Process budget reduction for a specific RAB type
     * Centralized method handling all validation and creation logic
     *
     * @param object $request Request object with all input data
     * @param string $jenisRab RAB type (RAB_KEGIATAN, RAB_PERALATAN, RAB_GEDUNG)
     * @param int $idRekat Rekat ID for 10% validation
     * @return array Returns result ['success' => bool, 'data' => array, 'message' => string, 'code' => int]
     */
    public function processBudgetReduction($request, string $jenisRab, int $idRekat, string $jenisRevisi): array {
        try {
            // Get RAB model config
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $config     = $this->rabConfig[$jenisRab] ?? $this->rabConfig[null];
            $rabModel   = $config['model'];
            $paketLabel = $config['paketLabel'];
            $fieldName  = $config['field'];

            // Fetch RAB record
            $rab = $rabModel::where(['id' => $request->idItem]);
            if (!$rab->exists())
                return ['success' => false, 'message' => 'Data tidak ditemukan', 'code' => 404];

            $firstRab           = $rab->first();
            $currentJumlahBiaya = $firstRab->$fieldName;

            // Get 10% minimum threshold (applies to all RAB types)
            $tenPercentData = $this->getTenPercentMinimum($idRekat);
            $tenPercent = $tenPercentData['tenPercent'];
            $totalAllData = $rabModel::where(['id_rekat' => $idRekat, 'is_deleted' => 'false'])->sum($fieldName);

            // Validate numeric fields based on RAB type
            if ($jenisRab == "RAB_KEGIATAN") {
                $validation = $this->validateNumericFields([
                    'kuantitas' => $request->kuantitas,
                    'durasi' => $request->durasi,
                    'kegiatan' => $request->kegiatan,
                    'harga satuan' => $request->hargaSatuan
                ]);
                if (!$validation['success']) {
                    return array_merge($validation, ['code' => 422]);
                }
            } elseif ($jenisRab == "RAB_PERALATAN") {
                $validation = $this->validateNumericFields([
                    'kuantitas' => $request->kuantitas,
                    'harga satuan' => $request->hargaSatuan
                ]);
                if (!$validation['success']) {
                    return array_merge($validation, ['code' => 422]);
                }

                // Validate biaya lainnya (max 20%)
                $maxBiayaLainnya = round($request->hargaSatuan * 0.20);
                if ($request->biayaLainnya > $maxBiayaLainnya) {
                    return [
                        'success' => false,
                        'message' => 'Biaya lainnya tidak boleh lebih 20% dari harga satuan',
                        'code' => 422
                    ];
                }
            } elseif ($jenisRab == "RAB_GEDUNG") {
                $validation = $this->validateNumericFields([
                    'jumlah_nilai' => $request->jumlahBiaya,
                ]);
                if (!$validation['success']) {
                    return array_merge($validation, ['code' => 422]);
                }
            }

            // Validate 10% minimum (applies to all RAB types)
            $budgetValidation = $this->validateBudgetReduction($currentJumlahBiaya,$request->jumlahBiaya,$tenPercent,$totalAllData);
            if (!$budgetValidation['success'])
                return array_merge($budgetValidation, ['code' => 422]);

            // Check pending validation
            $pendingCheck = $this->checkPendingValidation($firstRab->id, $paketLabel, 'Pengurangan');
            if ($pendingCheck['exists'])
                return [ 'success' => false, 'message' => $pendingCheck['message'], 'code' => 400 ];

            // Validate item status (packet & amprah)
            $statusValidation = $this->validateItemStatus($firstRab, $paketLabel, true);
            if (!$statusValidation['success'])
                return array_merge($statusValidation, ['code' => 400]);

            // Generate JSON specs
            $currentSpekJson = $this->generateSpekSemulaJson($firstRab, $jenisRab);
            $spekMenjadiJson = $this->buildSpekMenjadiJson($request, $firstRab, $jenisRab);

            // Create SemulaMenjadi record
            $semulaMenjadi = $this->createBudgetReductionRecord($firstRab->id, $paketLabel,
                $currentJumlahBiaya, $request->jumlahBiaya,
                $currentSpekJson, $spekMenjadiJson,
                'Pengurangan', $jenisRevisi
            );

            return [
                'success' => true,
                'data' => ['idSm' => $semulaMenjadi->id, 'idRab' => $firstRab->id],
                'message' => 'Berhasil mengurangkan anggaran kegiatan',
                'code' => 200
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Validate budget addition constraints
     *
     * @param float $currentAmount Current budget amount
     * @param float $newAmount New budget amount
     * @return array Returns validation result ['success' => bool, 'message' => string]
     */
    public function validateBudgetAddition(float $currentAmount, float $newAmount): array {
        if ($currentAmount > $newAmount) {
            return [
                'success' => false,
                'message' => "Jumlah biaya tidak boleh lebih kurang dari sebelumnya"
            ];
        }

        return ['success' => true];
    }

    /**
     * Validate rounding requirements (must be in thousands)
     *
     * @param float $originalAmount Original amount before rounding
     * @param float $roundedAmount Rounded amount
     * @param array $excludedSd Excluded source of funds from rounding check
     * @param string $currentSd Current source of fund
     * @return array Returns validation result ['success' => bool, 'message' => string]
     */
    public function validateBudgetRounding(float $originalAmount, float $roundedAmount, array $excludedSd, string $currentSd): array {
        if (in_array($currentSd, $excludedSd)) {
            return ['success' => true];
        }

        if ($originalAmount % 1000 !== 0) {
            $roundedDown = number_format(floor($originalAmount / 1000) * 1000, 0, ",", ".");
            $roundedUp = number_format($roundedAmount, 0, ",", ".");
            return [
                'success' => false,
                'message' => "Mohon bulatkan total biaya ke dalam nominal ribuan terdekat, contoh: {$roundedDown} atau {$roundedUp}"
            ];
        }

        return ['success' => true];
    }

    /**
     * Process budget addition for a specific RAB type
     * Centralized method handling all validation and creation logic for addition
     *
     * @param object $request Request object with all input data
     * @param string $jenisRab RAB type (RAB_KEGIATAN, RAB_PERALATAN, RAB_GEDUNG)
     * @param int $idRekat Rekat ID
     * @return array Returns result ['success' => bool, 'data' => array, 'message' => string, 'code' => int]
     */
    public function processBudgetAddition($request, string $jenisRab, int $idRekat): array {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();

            // Resolve RAB config (model, field, label)
            $config     = $this->rabConfig[$jenisRab] ?? $this->rabConfig[null];
            $rabModel   = $config['model'];
            $paketLabel = $config['paketLabel'];
            $fieldName  = $config['field'];

            // Guard: Rekat and RAB must exist
            $rekat = Rekat::find($idRekat);
            if (!$rekat) return ['success' => false, 'message' => 'Rekat tidak ditemukan', 'code' => 404];

            $rab = $rabModel::where(['id' => $request->idItem])->first();
            if (!$rab) return ['success' => false, 'message' => 'Data tidak ditemukan', 'code' => 404];

            $currentJumlahBiaya = $rab->$fieldName;

            // Prepare common variables
            $kodeIkk     = $request->kode_ikk;
            $kodeSs      = $request->kode_ss;
            $jenisSaldo  = in_array($paketLabel, ["SARANA", "PRASARANA"]) ? "sapras" : "operasional";

            // Normalize jumlah biaya + apply rounding rule
            $jumlahBiaya = $request->jumlahBiaya;
            $biayaAwal   = $jumlahBiaya;
            $excludedSd  = ["42010999", "42010801", "42010913", "42010901", "42010204", "42010915", "41050105", "41050201", "41050103", "42010915"];
            if (!in_array($request->kd_sumberdana, $excludedSd)) {
                $jumlahBiaya = ceil($jumlahBiaya / 1000) * 1000;
            }

            // --- Validation phase (fast exit, no DB writes) ---
            $numericValidations = match($jenisRab) {
                'RAB_KEGIATAN'  => ['kuantitas' => $request->kuantitas, 'durasi' => $request->durasi, 'kegiatan' => $request->kegiatan, 'harga satuan' => $request->hargaSatuan],
                'RAB_PERALATAN' => ['kuantitas' => $request->kuantitas, 'harga satuan' => $request->hargaSatuan],
                'RAB_GEDUNG'    => ['jumlah_nilai' => $request->jumlahBiaya],
                default         => []
            };
            if ($numericValidations) {
                $validation = $this->validateNumericFields($numericValidations);
                if (!$validation['success']) return array_merge($validation, ['code' => 422]);
            }

            if ($jenisRab == "RAB_PERALATAN") {
                $maxBiayaLainnya = round($request->hargaSatuan * 0.20);
                if ($request->biayaLainnya > $maxBiayaLainnya) {
                    return ['success' => false, 'message' => 'Biaya lainnya tidak boleh lebih 20% dari jumlah biaya', 'code' => 422];
                }
            }

            $pendingCheck = $this->checkPendingValidation($rab->id, $paketLabel, 'Penambahan');
            if ($pendingCheck['exists']) return ['success' => false, 'message' => $pendingCheck['message'], 'code' => 400];

            $statusValidation = $this->validateItemStatus($rab, $paketLabel, true);
            if (!$statusValidation['success']) return array_merge($statusValidation, ['code' => 400]);

            $additionValidation = $this->validateBudgetAddition($currentJumlahBiaya, $jumlahBiaya);
            if (!$additionValidation['success']) return array_merge($additionValidation, ['code' => 422]);

            $roundingValidation = $this->validateBudgetRounding($biayaAwal, $jumlahBiaya, $excludedSd, $request->kd_sumberdana);
            if (!$roundingValidation['success']) return array_merge($roundingValidation, ['code' => 400]);

            $jenisRevisi  = $request->jenis_revisi;
            $kodeKomponen = null;
            if ( $jenisRevisi == "SS" ) $kodeKomponen = (new RekatService())->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
            if ( $jenisRevisi == "RO" ) $kodeKomponen = (new RekatService())->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
            if ( $jenisRevisi == "KK" ) $kodeKomponen = (new RekatService())->getKodeKomponenMasterByLevel(2, $rekat->kd_rk);

            // --- Transactional phase: balance-safe writes ---
            $result = DB::connection('sirekat')->select(function () use (
                $request, $tahun, $tahunAngka, $rab, $jenisRab, $paketLabel, $fieldName,
                $jumlahBiaya, $currentJumlahBiaya, $kodeIkk, $kodeSs, $jenisSaldo, $rekat, $jenisRevisi, $kodeKomponen
            ) {

                $params = [
                    "idunit"      => $request->idunit,
                    "sd"          => $request->kd_sumberdana,
                    "jenis"       => $jenisSaldo,
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi == 'KK') $params['kode_ikk'] = $kodeIkk;
                if ($jenisRevisi == 'RO') $params['kode_ss']  = $kodeSs;
                if ( in_array($jenisRevisi, ['RO', 'KK', 'SS'] ) ) $params['kode_komponen'] = $kodeKomponen;

                // Lock saldo row (create if missing) to avoid race on sisa saldo
                $sisaSaldo = SisaSaldoValidasi::where($params)->lockForUpdate()->first();
                if (!$sisaSaldo)
                    $sisaSaldo = SisaSaldoValidasi::create(array_merge($params, ["sisa_saldo" => 0]));

                $currentSisaSaldo = $sisaSaldo->sisa_saldo;
                $totalPenambahan  = $jumlahBiaya - $currentJumlahBiaya;

                // Guard: saldo must be enough
                if ($currentSisaSaldo < $totalPenambahan)
                    return ['success' => false, 'message' => "Sisa saldo tidak mencukupi", 'data' => [$totalPenambahan, $kodeKomponen], 'code' => 422];

                // Guard: pagu must not be exceeded
                if (function_exists('cekPagu') && cekPagu($request->idunit, $request->kd_sumberdana, $tahun, $totalPenambahan, true) == "error")
                    return ['success' => false, 'message' => "Sisa pagu tidak mencukupi.", 'code' => 400];

                // Build specs once
                $currentSpekJson = $this->generateSpekSemulaJson($rab, $jenisRab);
                $spekMenjadiJson = $this->buildSpekMenjadiJson($request, $rab, $jenisRab);

                // Create SemulaMenjadi record
                $semulaMenjadi = SemulaMenjadi::create([
                    "id_rab"            => $rab->id,
                    "jenis_rab"         => $paketLabel,
                    "jenis_validasi"    => "Penambahan",
                    "jenis_revisi"      => $jenisRevisi,
                    "jumlah_semula"     => $currentJumlahBiaya,
                    "jumlah_menjadi"    => $jumlahBiaya,
                    "spek_semula_json"  => json_encode($currentSpekJson),
                    "spek_menjadi_json" => json_encode($spekMenjadiJson),
                    "status"            => ""
                ]);

                // Deduct saldo (cannot go minus due to guard above)
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $totalPenambahan]);

                return [
                    'success' => true,
                    'data'    => ['idSm' => $semulaMenjadi->id, 'idRab' => $rab->id],
                    'message' => 'Berhasil menyimpan perubahan',
                    'code'    => 200
                ];
            });

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Process budget addition verification (approve/reject)
     * Handles all RAB types with JSON/delimiter format support for addition
     *
     * @param SemulaMenjadi $semulaMenjadi SemulaMenjadi record
     * @param object $rab RAB model instance
     * @param string $status Status ('Setuju' or 'Tolak')
     * @param string $jenisRab RAB type class
     * @param float $totalPenambahan Total addition amount
     * @param SisaSaldoValidasi $sisaSaldo Sisa saldo instance
     * @param float $currentSisaSaldo Current remaining balance
     * @param string $isDraft Is draft status
     * @return bool Transaction result
     */
    public function processBudgetAdditionVerification(
        SemulaMenjadi $semulaMenjadi,
        $rab,
        string $status,
        string $jenisRab,
        float $totalPenambahan,
        SisaSaldoValidasi $sisaSaldo,
        float $currentSisaSaldo,
        string $isDraft
    ): bool {
        // Map class to string type
        $jenisRabStr = $jenisRab == RABKEG::class ? 'OPERASIONAL' : ($jenisRab == RABPER::class ? 'SARANA' : 'PRASARANA');

        // Parse spec data
        $spekAwal = $this->parseSpekData(
            $semulaMenjadi->spek_semula_json,
            $semulaMenjadi->spek_semula,
            $jenisRabStr
        );

        $spekMenjadi = $this->parseSpekData(
            $semulaMenjadi->spek_menjadi_json,
            $semulaMenjadi->spek_menjadi,
            $jenisRabStr
        );

        $isPenambahanItemCoa = $semulaMenjadi->jenis_validasi == "Penambahan Item Coa";

        return DB::connection('sirekat')->select(function () use (
            $sisaSaldo, $semulaMenjadi, $status, $rab, $totalPenambahan,
            $currentSisaSaldo, $spekAwal, $spekMenjadi, $jenisRabStr, $isDraft, $isPenambahanItemCoa
        ) {
            // Handle draft status
            if ($isDraft == "true") {
                $rab->update(["is_draft" => "false"]);
            }

            if ($status == "Setuju") {
                // Adjust saldo if previously rejected
                if ($semulaMenjadi->status == "Tolak") {
                    $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $totalPenambahan]);
                }

                // Update RAB based on validation type
                if (!$isPenambahanItemCoa) {
                    $this->updateRabWithSpec(
                        $rab,
                        $spekMenjadi,
                        $semulaMenjadi->jumlah_menjadi,
                        $jenisRabStr
                    );
                    $rab->update(["verifikasi_pimpinan_univ" => ""]);
                } else {
                    $rab->update(["is_deleted" => "false"]);
                }
            } else {
                // Handle rejection
                if (!$isPenambahanItemCoa) {
                    $this->updateRabWithSpec(
                        $rab,
                        $spekAwal,
                        $semulaMenjadi->jumlah_semula,
                        $jenisRabStr
                    );
                } else {
                    $rab->update(["is_deleted" => "true", "is_draft" => "true"]);
                }
                $semulaMenjadi->update(["status" => $status]);
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo + $totalPenambahan]);
            }
            return true;
        });
    }

    /**
     * Build spek menjadi JSON based on request data and RAB type
     *
     * @param object $request Request object
     * @param object $rabData Original RAB data
     * @param string $jenisRab RAB type
     * @return array Spec menjadi as associative array
     */
    private function buildSpekMenjadiJson($request, $rabData, string $jenisRab): array {
        if ($jenisRab == "RAB_KEGIATAN") {
            return [
                'kode_sbm'           => $request->kode_sbm ?? $rabData->kode_sbm,
                'rpd'                => $request->rpd ?? $rabData->rpd,
                'id_jenis_belanja'   => $request->idCoa,
                'jenis_belanja'      => $request->coa,
                'kebutuhan_kegiatan' => $request->itemCoa ?? $rabData->kebutuhan_kegiatan,
                'kode_sbm'           => $request->kode_sbm ?? $rabData->kode_sbm,
                'kuantitas'          => $request->kuantitas,
                'satuan_kuantitas'   => $request->sKuantitas,
                'durasi'             => $request->durasi,
                'satuan_durasi'      => $request->sDurasi,
                'kegiatan'           => $request->kegiatan,
                'satuan_kegiatan'    => $request->sKegiatan,
                'biaya_satuan'       => $request->hargaSatuan,
                'jumlah_biaya'       => $request->jumlahBiaya,
            ];
        } elseif ($jenisRab == "RAB_GEDUNG") {
            $kodeAsetReq = explode(" | ", $request->kodeAset ?? "")[0] ?? null;
            $asetReq     = explode(" | ", $request->kodeAset ?? "")[1] ?? null;
            return [
                'kode_sbm'           => $request->kode_sbm ?? $rabData->kode_sbm,
                'kuantitas'          => 1,
                'satuan'             => 'Paket',
                'id_jenis_belanja'   => $request->idCoa,
                'jenis_belanja'      => $request->coa,
                'rpd'                => $request->rpd ?? $rabData->rpd,
                'kode_aset'          => $kodeAsetReq ?? $rabData->kode_aset,
                'aset'               => $asetReq ?? $rabData->aset,
                'jenis_pekerjaan'    => $request->jenisPekerjaan ?? $rabData->jenis_pekerjaan,
                'kebutuhan_kegiatan' => $request->itemCoa ?? $rabData->kebutuhan_kegiatan,
            ];
        } elseif ($jenisRab == "RAB_PERALATAN") {
            $biayaPajak = property_exists($request, 'biayaPajak') && $request->biayaPajak !== null
                ? $request->biayaPajak
                : round(($request->hargaSatuan ?? 0) * 0.12);
            $kodeAsetReq = explode(" | ", $request->kodeAset ?? "")[0] ?? null;
            $asetReq     = explode(" | ", $request->kodeAset ?? "")[1] ?? null;
            return [
                'kode_sbm'           => $request->kode_sbm ?? $rabData->kode_sbm,
                'kuantitas'          => $request->kuantitas,
                'satuan'             => $request->sKuantitas,
                'harga_satuan'       => $request->hargaSatuan,
                'biaya_pajak'        => $biayaPajak,
                'biaya_lainnya'      => $request->biayaLainnya,
                'id_jenis_belanja'   => $request->idCoa,
                'jenis_belanja'      => $request->coa,
                'rpd'                => $request->rpd ?? $rabData->rpd,
                'kode_aset'          => $kodeAsetReq ?? $rabData->kode_aset,
                'aset'               => $asetReq ?? $rabData->aset,
                'merk'               => $request->merk ?? $rabData->merk,
                'type'               => $request->type ?? $rabData->type,
                'url'                => $request->url ?? $rabData->url,
                'kebutuhan_kegiatan' => $request->itemCoa ?? $rabData->kebutuhan_kegiatan,
            ];
        }

        return [];
    }

    /**
     * Store new Item COA for Operasional (RAB_KEGIATAN)
     *
     * @param array $data Request data
     * @param string $kodeKeg Kode IKK/SS depending on jenis revisi
     * @param string $jenisRevisi Jenis revisi sumber saldo (KK/RO)
     * @return array Result ['success' => bool, 'message' => string, 'data' => object|null, 'code' => int]
     */
    public function storeItemCoaOperasional(array $data, string $kodeKeg, string $jenisRevisi): array {
        try {
            [ "tahun" => $tahun ] = getTahunData();
            $idRekat    = $data["idRekat"];
            $dataRevisi = $data["dataRevisi"];
            $idunit     = $dataRevisi["idunit"];

            // Required-field guardrails for clearer user feedback
            $requiredFields = [
                "kebutuhanKegiatan" => "Kebutuhan Kegiatan tidak boleh kosong",
                "coa"               => "Jenis belanja tidak boleh kosong",
                "satuanKuantitas"   => "Satuan kuantitas tidak boleh kosong",
                "satuanDurasi"      => "Satuan durasi tidak boleh kosong",
                "satuanKegiatan"    => "Satuan kegiatan tidak boleh kosong",
                "rpd"               => "RPD tidak boleh kosong",
                "hargaSatuan"       => "Biaya satuan tidak boleh kosong",
            ];
            foreach ($requiredFields as $field => $message) {
                if (empty($dataRevisi[$field])) {
                    return ['success' => false, 'message' => $message, 'code' => 400];
                }
            }

            $coa = (string) ($dataRevisi["coa"] ?? "");
            $role = session()->get("role");
            // if ( $role != 'superadmin' && $idunit == '10607' && !( str_starts_with($coa, '510401') || str_starts_with($coa, '520401') ) ) {
            //     return [ 'success' => false, 'message' => 'COA tidak diizinkan untuk proses ini', 'code' => 422 ];
            // }
            if ( !in_array($role, ["superadmin"]) && $coa !== "" && ( str_starts_with($coa, "510401") || str_starts_with($coa, "520401") ) && !in_array($idunit, ["1040106", "1050505"]) ) {
                return ['success' => false, 'message' => 'COA tidak diizinkan untuk proses ini', 'code' => 422];
            }

            $bulanSekarang = (int) date('n');
            $rpd = (int) ($dataRevisi["rpd"] ?? 0);
            $isAdminRole = in_array((string) $role, ["superadmin", "admin"], true);
            if (!$isAdminRole && $rpd <= $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh kurang dari atau sama dengan bulan berjalan', 'code' => 422];
            }
            if ($rpd == $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh sama dengan bulan berjalan', 'code' => 422];
            }

            // Generate unique id_mak early for deterministic RAB creation
            $idMak = $this->generateUniqueIdMak('11');

            // Amprah guard: block when amprah already submitted
            if (function_exists('cekAmprahRealtime') && cekAmprahRealtime($idMak) === true) {
                return [
                    'success' => false,
                    'message' => 'Penambahan tidak dapat diproses karena data sudah diajukan amprah',
                    'code'    => 422
                ];
            }
            // Calculate and validate biaya (prevent tampering)
            $jumlahBiaya = (int)$dataRevisi["kuantitas"] * (int)$dataRevisi["durasi"] * (int)$dataRevisi["kegiatan"] * (int)$dataRevisi["hargaSatuan"];
            if ($jumlahBiaya <= 0)
                return ['success' => false, 'message' => "Total biaya tidak valid", 'code' => 400];
            if ($jumlahBiaya != (int)$dataRevisi["jumlahBiaya"])
                return ['success' => false, 'message' => "Terjadi kesalahan pada total biaya", 'code' => 400];

            $biayaAwal   = $jumlahBiaya;
            $excludedSd  = ["42010999", "42010801", "42010913", "42010901", "42010204", "42010915", "41050105", "41050201", "42010915"];
            if (!in_array($dataRevisi["kd_sumberdana"], $excludedSd))
                $jumlahBiaya = ceil($jumlahBiaya / 1000) * 1000; // round up to nearest thousand

            // Validate rounding expectation
            if ($biayaAwal % 1000 !== 0) {
                $roundedUp = number_format($jumlahBiaya, 0, ",", ".");
                return [
                    'success' => false,
                    'message' => "Mohon bulatkan total biaya ke dalam nominal ribuan terdekat, contoh: {$roundedUp}",
                    'code' => 400
                ];
            }

            // Prepare PPK & BPP input
            $ppkBppData = [
                "jumlah_biaya"  => $jumlahBiaya,
                "unitkerja"     => $dataRevisi["idunit"],
                "kd_sumberdana" => $dataRevisi["kd_sumberdana"],
                "coa"           => $dataRevisi["coa"]
            ];

            $komitmen = Komitmen::select("id", "nip", "nama_pejabat")->where("jenis", "ppk");
            $ppk = getPPK($komitmen, $ppkBppData);
            $bpp = getBPP($ppkBppData);
            if (!$ppk)
                return ['success' => false, 'message' => "Maaf, Data Pejabat Pembuat Komitmen tidak ditemukan", 'code' => 400];
            if (!$bpp)
                return ['success' => false, 'message' => "Maaf, Data Bendahara Pengeluaran Pembantu tidak ditemukan", 'code' => 400];

            // Fetch Rekat up front (needed for saldo/pagu checks)
            $rekat = Rekat::where("id", $idRekat)->first();
            if (!$rekat)
                return ['success' => false, 'message' => "Data rekat tidak ditemukan", 'code' => 404];

            $level = 1;
            if ($jenisRevisi === 'KK') $level = 2;
            $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel($level, $rekat->kd_rk);

            // Run everything inside a DB transaction to keep saldo/RAB in sync
            $result = DB::connection('sirekat')->select(function () use (
                $dataRevisi, $jenisRevisi, $kodeKeg, $jumlahBiaya, $biayaAwal, $rekat, $tahun, $kodeKomponen, $ppk, $bpp, $idMak
            ) {
                // Build saldo lookup params
                $saldoParams = [
                    "idunit"      => $rekat->unit_kerja,
                    "sd"          => $rekat->sd,
                    "jenis"       => "operasional",
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi === 'KK') {
                    $saldoParams['kode_ikk'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'RO') {
                    $saldoParams['kode_ss'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'SS') {
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                }

                // Lock saldo row to avoid race conditions; create if missing
                $sisaSaldo = SisaSaldoValidasi::where($saldoParams)->lockForUpdate()->first();
                if (!$sisaSaldo)
                    $sisaSaldo = SisaSaldoValidasi::create(array_merge($saldoParams, ["sisa_saldo" => 0]));
                $currentSisaSaldo = $sisaSaldo->sisa_saldo;

                // Balance guard: prevent minus
                if ($currentSisaSaldo < $jumlahBiaya)
                    throw new \Exception("Sisa saldo tidak mencukupi untuk melakukan penambahan anggaran.");

                if (function_exists('cekPagu') && cekPagu($rekat->unit_kerja, $rekat->sd, $tahun, $jumlahBiaya, true) == "error")
                    throw new \Exception("Total biaya melampaui batas pagu anggaran yang telah ditetapkan.");

                // Compose RAB payload
                $rabData = [
                    "nip_ppk"            => $ppk['0']->nip,
                    "nip_bpp"            => $bpp->nip,
                    "id_mak"             => $idMak,
                    "id_rekat"           => $rekat->id,
                    "unit_kerja"         => $rekat->unit_kerja,
                    "kebutuhan_kegiatan" => $dataRevisi["kebutuhanKegiatan"],
                    "id_jenis_belanja"   => $dataRevisi["coa"],
                    "jenis_belanja"      => $dataRevisi["namaCoa"],
                    "kuantitas"          => $dataRevisi["kuantitas"],
                    "satuan_kuantitas"   => $dataRevisi["satuanKuantitas"],
                    "durasi"             => $dataRevisi["durasi"],
                    "satuan_durasi"      => $dataRevisi["satuanDurasi"],
                    "kegiatan"           => $dataRevisi["kegiatan"],
                    "satuan_kegiatan"    => $dataRevisi["satuanKegiatan"],
                    "biaya_satuan"       => $dataRevisi["hargaSatuan"],
                    "jumlah_biaya"       => $jumlahBiaya,
                    "rpd"                => $dataRevisi["rpd"],
                    "is_draft"           => "true"
                ];

                $rabkeg = RABKEG::create($rabData);

                // Deduct saldo once (already locked)
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $jumlahBiaya]);
                // Persist SemulaMenjadi for validation trail
                $spekMenjadiJson = [
                    'kuantitas'          => $dataRevisi["kuantitas"],
                    'satuan_kuantitas'   => $dataRevisi["satuanKuantitas"],
                    'durasi'             => $dataRevisi["durasi"],
                    'satuan_durasi'      => $dataRevisi["satuanDurasi"],
                    'kegiatan'           => $dataRevisi["kegiatan"],
                    'satuan_kegiatan'    => $dataRevisi["satuanKegiatan"],
                    'biaya_satuan'       => $dataRevisi["hargaSatuan"],
                    'id_jenis_belanja'   => $dataRevisi["coa"],
                    'jenis_belanja'      => $dataRevisi["namaCoa"],
                    'rpd'                => $dataRevisi["rpd"],
                    'kebutuhan_kegiatan' => $dataRevisi["kebutuhanKegiatan"],
                ];

                SemulaMenjadi::create([
                    "id_rab"            => $rabkeg->id,
                    "jenis_rab"         => "OPERASIONAL",
                    "jenis_validasi"    => "Penambahan Item Coa",
                    "jenis_revisi"      => $jenisRevisi,
                    "jumlah_semula"     => "0",
                    "jumlah_menjadi"    => $jumlahBiaya,
                    "spek_semula_json"  => json_encode([]),
                    "spek_menjadi_json" => json_encode($spekMenjadiJson),
                ]);

                return [
                    'success' => true,
                    'message' => 'Berhasil menambahkan item coa',
                    'data'    => $rabkeg,
                    'code'    => 200
                ];
            });

            return $result;
        } catch (\Exception $e) {
            $errorMessages = [
                "Sisa saldo tidak mencukupi untuk melakukan penambahan anggaran.",
                "Total biaya melampaui batas pagu anggaran yang telah ditetapkan."
            ];
            if (in_array($e->getMessage(), $errorMessages))
                return [ 'success' => false, 'message' => $e->getMessage(), 'code' => 422 ];

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan item coa',
                'code'    => 500,
                'error'   => $e->getMessage()
            ];
        }
    }

    /**
     * Store new Item COA for Sarana (RAB_PERALATAN)
     *
     * @param array $data Request data
     * @param string $kodeIkk IKK code
     * @param string $ip Client IP address
     * @return array Result ['success' => bool, 'message' => string, 'data' => object|null, 'code' => int]
     */
    public function storeItemCoaSarana(array $data, string $kodeKeg, string $jenisRevisi ): array {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $idRekat    = $data["idRekat"];
            $dataRevisi = $data["dataRevisi"];
            $idunit     = $dataRevisi["idunit"];

            // Required-field guardrails for clear UX
            $requiredFields = [
                'kebutuhanKegiatan'  => 'Kebutuhan Kegiatan tidak boleh kosong',
                'coa'                => 'Jenis belanja tidak boleh kosong',
                'satuanKuantitas'    => 'Satuan tidak boleh kosong',
                'kuantitas'          => 'Kuantitas tidak boleh kosong',
                'hargaSatuan'        => 'Harga satuan tidak boleh kosong',
                'rpd'                => 'RPD tidak boleh kosong',
                'kodeAset'           => 'Kodefikasi aset tidak boleh kosong',
            ];
            foreach ($requiredFields as $field => $message) {
                if (empty($dataRevisi[$field])) {
                    return ['success' => false, 'message' => $message, 'code' => 400];
                }
            }

            $coa = (string) ($dataRevisi["coa"] ?? "");
            $role = session()->get("role");
            // if ( $role != 'superadmin' && $idunit == '10607' && !( str_starts_with($coa, '510401') || str_starts_with($coa, '520401') ) ) {
            //     return [ 'success' => false, 'message' => 'COA tidak diizinkan untuk proses ini', 'code' => 422 ];
            // }
            if ( !in_array($role, ["superadmin"]) && $coa !== "" && ( str_starts_with($coa, "510401") || str_starts_with($coa, "520401") ) && !in_array($idunit, ["1040106", "1050505"]) ) {
                return ['success' => false, 'message' => 'COA tidak diizinkan untuk proses ini', 'code' => 422];
            }
            $bulanSekarang = (int) date('n');
            $rpd = (int) ($dataRevisi["rpd"] ?? 0);
            $isAdminRole = in_array((string) $role, ["superadmin", "admin"], true);
            if (!$isAdminRole && $rpd <= $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh kurang dari atau sama dengan bulan berjalan', 'code' => 422];
            }
            if ($rpd == $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh sama dengan bulan berjalan', 'code' => 422];
            }
            // Generate unique id_mak early
            $idMak = $this->generateUniqueIdMak('22');

            // Amprah guard
            if (function_exists('cekAmprahRealtime') && cekAmprahRealtime($idMak) === true) {
                return [
                    'success' => false,
                    'message' => 'Penambahan tidak dapat diproses karena data sudah diajukan amprah',
                    'code'    => 422
                ];
            }

            // Calculate biaya (base + optional pajak & biaya lainnya)
            $jumlahBiayaDasar = (int)$dataRevisi["kuantitas"] * (int)$dataRevisi["hargaSatuan"];
            if ($jumlahBiayaDasar <= 0)
                return ['success' => false, 'message' => "Total biaya tidak valid", 'code' => 400];

            $biayaPajak       = (int) $tahunAngka >= 2025 ? round($jumlahBiayaDasar * 0.12) : 0;
            $maxBiayaLainnya  = round($jumlahBiayaDasar * 0.20);
            $biayaLainnya     = (int) $tahunAngka >= 2025 ? (int)($dataRevisi["biayaLainnya"] ?? 0) : 0;

            if ($biayaLainnya > $maxBiayaLainnya)
                return ['success' => false, 'message' => "Biaya lainnya tidak boleh lebih besar dari 20% dari total biaya", 'code' => 400];

            $jumlahBiaya             = $jumlahBiayaDasar + $biayaPajak + $biayaLainnya;
            $biayaSebelumPembulatan = $jumlahBiaya; // simpan nilai awal sebelum dibulatkan

            // Round up to nearest thousand when applicable
            $excludedSd = ["42010999", "42010801", "42010913", "42010901", "42010204", "42010915", "41050105", "41050201", "42010915"];
            if (!in_array($dataRevisi["kd_sumberdana"], $excludedSd))
                $jumlahBiaya = ceil($jumlahBiaya / 1000) * 1000;

            // Pastikan $biayaAwal mengikuti nilai akhir setelah pembulatan
            $biayaAwal = $jumlahBiaya;

            // Enforce rounding expectation berdasarkan nilai sebelum pembulatan
            if ($biayaSebelumPembulatan % 1000 !== 0) {
                $roundedDown = number_format(floor($biayaSebelumPembulatan / 1000) * 1000, 0, ",", ".");
                $roundedUp   = number_format($jumlahBiaya, 0, ",", ".");
                return [
                    'success' => false,
                    'message' => "Mohon bulatkan total biaya ke dalam nominal ribuan terdekat, contoh: {$roundedDown} atau {$roundedUp}",
                    'code' => 400
                ];
            }

            // Tamper check: client-provided total must match server calc post-rounding
            if ($jumlahBiaya != (int)$dataRevisi["jumlahBiaya"])
                return ['success' => false, 'message' => "Terjadi kesalahan pada total biaya", 'code' => 400];

            // Parse kode aset string
            $dataAset = $dataRevisi["kodeAset"];
            $kodeAset = explode(" | ", $dataAset)[0] ?? "";
            $aset     = explode(" | ", $dataAset)[1] ?? "";

            // Prepare PPK & BPP lookup payload
            $ppkBppData = [
                "jumlah_biaya"  => $jumlahBiaya,
                "unitkerja"     => $dataRevisi["idunit"],
                "kd_sumberdana" => $dataRevisi["kd_sumberdana"],
                "coa"           => $dataRevisi["coa"]
            ];

            $komitmen = Komitmen::select("id", "nip", "nama_pejabat")->where("jenis", "ppk");
            $ppk = getPPK($komitmen, $ppkBppData);
            $bpp = getBPP($ppkBppData);
            if (!$ppk)
                return ['success' => false, 'message' => "Maaf, Data Pejabat Pembuat Komitmen tidak ditemukan", 'code' => 400];
            if (!$bpp)
                return ['success' => false, 'message' => "Maaf, Data Bendahara Pengeluaran Pembantu tidak ditemukan", 'code' => 400];

            // Fetch Rekat early for saldo/pagu context
            $rekat = Rekat::where("id", $idRekat)->first();
            if (!$rekat)
                return ['success' => false, 'message' => "Data rekat tidak ditemukan", 'code' => 404];

            $level = 1;
            if ($jenisRevisi === 'KK') $level = 2;
            $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel($level, $rekat->kd_rk);

            // Run core logic inside a single transaction for consistency
            $result = DB::connection('sirekat')->select(function () use (
                $dataRevisi, $jenisRevisi, $kodeKeg, $jumlahBiaya, $kodeKomponen, $biayaPajak, $biayaLainnya, $kodeAset, $aset, $rekat, $tahun, $ppk, $bpp, $idMak
            ) {
                // Build saldo params
                $saldoParams = [
                    "idunit"      => $rekat->unit_kerja,
                    "sd"          => $rekat->sd,
                    "jenis"       => "sapras",
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi === 'KK') {
                    $saldoParams['kode_ikk'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'RO') {
                    $saldoParams['kode_ss'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'SS') {
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                }

                // Lock saldo row to prevent race; create when missing
                $sisaSaldo = SisaSaldoValidasi::where($saldoParams)->lockForUpdate()->first();
                if (!$sisaSaldo)
                    $sisaSaldo = SisaSaldoValidasi::create(array_merge($saldoParams, ["sisa_saldo" => 0]));
                $currentSisaSaldo = $sisaSaldo->sisa_saldo;

                // Balance guard
                if ($currentSisaSaldo < $jumlahBiaya)
                    throw new \Exception("Sisa saldo tidak mencukupi");

                // Pagu guard
                if (function_exists('cekPagu') && cekPagu($rekat->unit_kerja, $rekat->sd, $tahun, $jumlahBiaya, true) == "error")
                    throw new \Exception("Total biaya melampaui batas pagu anggaran yang telah ditetapkan.");

                // Create RABPER payload
                $rabData = [
                    "nip_ppk"            => $ppk['0']->nip,
                    "nip_bpp"            => $bpp->nip,
                    "id_mak"             => $idMak,
                    "id_rekat"           => $rekat->id,
                    "unit_kerja"         => $rekat->unit_kerja,
                    "kebutuhan_kegiatan" => $dataRevisi["kebutuhanKegiatan"],
                    "id_jenis_belanja"   => $dataRevisi["coa"],
                    "jenis_belanja"      => $dataRevisi["namaCoa"],
                    "kode_aset"          => $kodeAset,
                    "aset"               => $aset,
                    "merk"               => $dataRevisi["merk"] ?? null,
                    "type"               => $dataRevisi["type"] ?? null,
                    "url"                => $dataRevisi["eCatalog"] ?? null,
                    "kuantitas"          => $dataRevisi["kuantitas"],
                    "satuan"             => $dataRevisi["satuanKuantitas"],
                    "harga_satuan"       => $dataRevisi["hargaSatuan"],
                    "biaya_pajak"        => $biayaPajak,
                    "biaya_lainnya"      => $biayaLainnya,
                    "jumlah_biaya"       => $jumlahBiaya,
                    "rpd"                => $dataRevisi["rpd"],
                    "is_draft"           => "true"
                ];

                $rabper = RABPER::create($rabData);
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $jumlahBiaya]);

                // Create SemulaMenjadi trail
                $spekMenjadiJson = [
                    'kuantitas'          => $dataRevisi["kuantitas"],
                    'satuan'             => $dataRevisi["satuanKuantitas"],
                    'harga_satuan'       => $dataRevisi["hargaSatuan"],
                    'biaya_pajak'        => $biayaPajak,
                    'biaya_lainnya'      => $biayaLainnya,
                    'id_jenis_belanja'   => $dataRevisi["coa"],
                    'jenis_belanja'      => $dataRevisi["namaCoa"],
                    'rpd'                => $dataRevisi["rpd"],
                    'kode_aset'          => $kodeAset,
                    'aset'               => $aset,
                    'kebutuhan_kegiatan' => $dataRevisi["kebutuhanKegiatan"],
                ];

                SemulaMenjadi::create([
                    "id_rab"            => $rabper->id,
                    "jenis_rab"         => "SARANA",
                    "jenis_validasi"    => "Penambahan Item Coa",
                    "jenis_revisi"      => $jenisRevisi,
                    "jumlah_semula"     => "0",
                    "jumlah_menjadi"    => $jumlahBiaya,
                    "spek_semula_json"  => json_encode([]),
                    "spek_menjadi_json" => json_encode($spekMenjadiJson),
                ]);

                return [
                    'success' => true,
                    'message' => 'Berhasil menambahkan sarana',
                    'data'    => $rabper,
                    'code'    => 200
                ];
            });
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan sarana: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Store new Item COA for Prasarana (RAB_GEDUNG)
     *
     * @param array $data Request data
     * @param string $kodeKeg Kode IKK/SS depending on jenis revisi
     * @param string $jenisRevisi Jenis revisi sumber saldo (KK/RO)
     * @return array Result ['success' => bool, 'message' => string, 'data' => object|null, 'code' => int]
     */
    public function storeItemCoaPrasarana(array $data, string $kodeKeg, string $jenisRevisi): array {
        try {
            [ "tahun" => $tahun ] = getTahunData();
            $idRekat    = $data["idRekat"];
            $dataRevisi = $data["dataRevisi"];
            $idunit     = $dataRevisi["idunit"];

            // Required-field guardrails
            $requiredFields = [
                "kebutuhanKegiatan" => "Kebutuhan Kegiatan tidak boleh kosong",
                "coa"               => "Jenis belanja tidak boleh kosong",
                "rpd"               => "RPD tidak boleh kosong",
                "kodeAset"          => "Kodefikasi aset tidak boleh kosong",
            ];
            foreach ($requiredFields as $field => $message) {
                if (empty($dataRevisi[$field])) {
                    return ['success' => false, 'message' => $message, 'code' => 400];
                }
            }

            $coa = (string) ($dataRevisi["coa"] ?? "");
            $role = session()->get("role");
            // if ( $role != 'superadmin' && $idunit == '10607' && !( str_starts_with($coa, '510401') || str_starts_with($coa, '520401') ) ) {
            //     return [ 'success' => false, 'message' => 'Revisi tidak diizinkan untuk jenis belanja ini', 'code' => 422 ];
            // }
            if ( !in_array($role, ["superadmin"]) && $coa !== "" && ( str_starts_with($coa, "510401") || str_starts_with($coa, "520401") ) && !in_array($idunit, ["1040106", "1050505"]) ) {
                return ['success' => false, 'message' => 'Revisi tidak diizinkan untuk jenis belanja ini', 'code' => 422];
            }
            $bulanSekarang = (int) date('n');
            $rpd = (int) ($dataRevisi["rpd"] ?? 0);
            $isAdminRole = in_array((string) $role, ["superadmin", "admin"], true);
            if (!$isAdminRole && $rpd <= $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh kurang dari atau sama dengan bulan berjalan', 'code' => 422];
            }
            if ($rpd == $bulanSekarang) {
                return ['success' => false, 'message' => 'RPD tidak boleh sama dengan bulan berjalan', 'code' => 422];
            }

            // Generate unique id_mak early
            $idMak = $this->generateUniqueIdMak('33');

            // Amprah guard
            if (function_exists('cekAmprahRealtime') && cekAmprahRealtime($idMak) === true) {
                return [
                    'success' => false,
                    'message' => 'Penambahan tidak dapat diproses karena data sudah diajukan amprah',
                    'code'    => 422
                ];
            }
            // Calculate & validate biaya
            $jumlahBiaya = (int) $dataRevisi["jumlahBiaya"];
            if ($jumlahBiaya <= 0)
                return ['success' => false, 'message' => "Total biaya tidak valid", 'code' => 400];
            $biayaAwal = $jumlahBiaya;

            // Apply rounding rule when sumber dana not excluded
            $excludedSd = ["42010999", "42010801", "42010913", "42010901", "42010204", "42010915", "41050105", "41050201", "42010915"];
            if (!in_array($dataRevisi["kd_sumberdana"], $excludedSd))
                $jumlahBiaya = ceil($jumlahBiaya / 1000) * 1000;

            // Rounding expectation guard
            if ($biayaAwal % 1000 !== 0) {
                $roundedDown = number_format(floor($biayaAwal / 1000) * 1000, 0, ",", ".");
                $roundedUp   = number_format($jumlahBiaya, 0, ",", ".");
                return [
                    'success' => false,
                    'message' => "Mohon bulatkan total biaya ke dalam nominal ribuan terdekat, contoh: {$roundedDown} atau {$roundedUp}",
                    'code' => 400
                ];
            }

            // Tamper check: client-provided total must match after rounding
            if ($jumlahBiaya != (int) $dataRevisi["jumlahBiaya"]) {
                return ['success' => false, 'message' => "Terjadi kesalahan pada total biaya", 'code' => 400];
            }

            // Parse kode aset string
            $dataAset = $dataRevisi["kodeAset"];
            $kodeAset = explode(" | ", $dataAset)[0] ?? "";
            $aset     = explode(" | ", $dataAset)[1] ?? "";

            // Prepare PPK & BPP payload
            $ppkBppData = [
                "jumlah_biaya"  => $jumlahBiaya,
                "unitkerja"     => $dataRevisi["idunit"],
                "kd_sumberdana" => $dataRevisi["kd_sumberdana"],
                "coa"           => $dataRevisi["coa"]
            ];

            $komitmen = Komitmen::select("id", "nip", "nama_pejabat")->where("jenis", "ppk");
            $ppk = getPPK($komitmen, $ppkBppData);
            $bpp = getBPP($ppkBppData);
            if (!$ppk)
                return ['success' => false, 'message' => "Maaf, Data Pejabat Pembuat Komitmen tidak ditemukan", 'code' => 400];
            if (!$bpp)
                return ['success' => false, 'message' => "Maaf, Data Bendahara Pengeluaran Pembantu tidak ditemukan", 'code' => 400];

            // Fetch Rekat for saldo/pagu context
            $rekat = Rekat::where("id", $idRekat)->first();
            if (!$rekat)
                return ['success' => false, 'message' => "Data rekat tidak ditemukan", 'code' => 404];

            $level = 1;
            if ($jenisRevisi === 'KK') $level = 2;
            $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel($level, $rekat->kd_rk);
            // Core flow inside transaction for consistency
            $result = DB::connection('sirekat')->select(function () use (
                $dataRevisi, $jenisRevisi, $kodeKeg, $jumlahBiaya, $kodeAset, $aset, $rekat, $tahun, $ppk, $bpp, $idMak, $kodeKomponen
            ) {
                // Build saldo params
                $saldoParams = [
                    "idunit"      => $rekat->unit_kerja,
                    "sd"          => $rekat->sd,
                    "jenis"       => "sapras",
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi === 'KK') {
                    $saldoParams['kode_ikk'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'RO') {
                    $saldoParams['kode_ss'] = $kodeKeg;
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                } elseif ($jenisRevisi === 'SS') {
                    $saldoParams['kode_komponen'] = $kodeKomponen;
                }
                $sisaSaldo = SisaSaldoValidasi::where($saldoParams)->lockForUpdate()->first();
                if (!$sisaSaldo)
                    $sisaSaldo = SisaSaldoValidasi::create(array_merge($saldoParams, ["sisa_saldo" => 0]));
                $currentSisaSaldo = $sisaSaldo->sisa_saldo;

                // Balance guard
                if ($currentSisaSaldo < $jumlahBiaya)
                    throw new \Exception("Sisa saldo tidak mencukupi");

                // Pagu guard
                if (function_exists('cekPagu') && cekPagu($rekat->unit_kerja, $rekat->sd, $tahun, $jumlahBiaya, true) == "error")
                    throw new \Exception("Total biaya melampaui batas pagu anggaran yang telah ditetapkan.");

                // Create RABGDG payload
                $rabData = [
                    "nip_ppk"            => $ppk['0']->nip,
                    "nip_bpp"            => $bpp->nip,
                    "id_mak"             => $idMak,
                    "id_rekat"           => $rekat->id,
                    "unit_kerja"         => $rekat->unit_kerja,
                    "kebutuhan_kegiatan" => $dataRevisi["kebutuhanKegiatan"],
                    "id_jenis_belanja"   => $dataRevisi["coa"],
                    "jenis_belanja"      => $dataRevisi["namaCoa"],
                    "jenis_pekerjaan"    => $dataRevisi["jenisPekerjaan"] ?? null,
                    "kode_aset"          => $kodeAset,
                    "aset"               => $aset,
                    "jumlah_nilai"       => $jumlahBiaya,
                    "rpd"                => $dataRevisi["rpd"],
                    "is_draft"           => "true"
                ];

                $rabgdg = RABGDG::create($rabData);
                $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo - $jumlahBiaya]);

                // Create SemulaMenjadi trail
                $spekMenjadiJson = [
                    'kuantitas'          => 1,
                    'satuan'             => 'Paket',
                    'id_jenis_belanja'   => $dataRevisi["coa"],
                    'jenis_belanja'      => $dataRevisi["namaCoa"],
                    'rpd'                => $dataRevisi["rpd"],
                    'kode_aset'          => $kodeAset,
                    'aset'               => $aset,
                    'kebutuhan_kegiatan' => $dataRevisi["kebutuhanKegiatan"],
                    'jenis_pekerjaan'    => $dataRevisi["jenisPekerjaan"] ?? null,
                ];

                SemulaMenjadi::create([
                    "id_rab"             => $rabgdg->id,
                    "jenis_rab"          => "PRASARANA",
                    "jenis_validasi"     => "Penambahan Item Coa",
                    "jenis_revisi"       => $jenisRevisi,
                    "jumlah_semula"      => "0",
                    "jumlah_menjadi"     => $jumlahBiaya,
                    "spek_semula_json"   => json_encode([]),
                    "spek_menjadi_json"  => json_encode($spekMenjadiJson),
                ]);

                return [
                    'success' => true,
                    'message' => 'Berhasil menambahkan prasarana',
                    'data'    => $rabgdg,
                    'code'    => 200
                ];
            });

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan prasarana: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Generate unique ID MAK with prefix
     *
     * @param string $prefix Prefix for ID (11, 22, or 33)
     * @return int Unique ID MAK
     */
    private function generateUniqueIdMak(string $prefix): int {
        do {
            if ($prefix == '11') {
                $maxId = RABKEG::max('id');
            } elseif ($prefix == '22') {
                $maxId = RABPER::max('id');
            } else {
                $maxId = RABGDG::max('id');
            }

            $randomPart = $prefix == '33' ? rand(1, 99) : rand(1, 99);
            $idMak = $prefix . $randomPart . ($maxId + 1);

            $existsInRabKegiatan = RABKEG::where("id_mak", "=", $idMak)->exists();
            $existsInRabPeralatan = RABPER::where("id_mak", "=", $idMak)->exists();
            $existsInRabGedung = RABGDG::where("id_mak", "=", $idMak)->exists();

        } while ($existsInRabKegiatan || $existsInRabPeralatan || $existsInRabGedung);

        return $idMak;
    }

    /**
     * Process transfer of remaining realization budget (sisa realisasi)
     *
     * @param array $data Request data
     * @param string $tahun Year in format "TA_YYYY"
     * @param int $tahunAngka Year as integer
     * @return array Result with success status, message, and optional data
     * @throws \Exception If database transaction fails
     */
    public function processSisaRealisasiTransfer(array $data, string $tahun, int $tahunAngka, string $jenisRevisi): array {
        try {
            // Get RAB configuration
            $rabConfig = $this->getRabConfigForRealisasiTransfer($data['jenisRab'] ?? null);
            $rab = $rabConfig['model']::where(["id_mak" => $data['idMak']])->first();

            if (!$rab)
                return [ 'success' => false, 'message' => 'Data RAB tidak ditemukan', 'code' => 404 ];

            // Calculate values
            $ambilRealisasi   = $data['ambilRealisasi'];
            $sisaRealisasi    = $data['sisaRealisasi'];
            $sisa             = $sisaRealisasi - $ambilRealisasi;
            $jenisSaldo       = $rabConfig['jenisSaldo'];
            $jenisRabTerpakai = $rabConfig['jenisRabTerpakai'];

            // Get current realisasi terpakai
            $currentRealisasi = DB::connection('sirekat')->select("tb_realisasi_terpakai")->where(["id_rab" => $rab->id, "jenis_rab" => $jenisRabTerpakai])->first();
            $dipakai          = $currentRealisasi ? $currentRealisasi->dipakai : 0;

            // Process in transaction
            DB::connection('sirekat')->select(function () use ($rab, $ambilRealisasi, $jenisRabTerpakai, $sisa, $dipakai, $data, $tahun, $jenisSaldo, $jenisRevisi) {
                DB::connection('sirekat')->select("tb_realisasi_terpakai")->updateOrInsert(
                    ["id_rab" => $rab->id, "jenis_rab" => $jenisRabTerpakai],
                    [
                        "sisa" => $sisa,
                        "dipakai" => $dipakai + $ambilRealisasi
                    ]
                );

                // Update sisa saldo validasi
                $whereConditions = [
                    "idunit"      => $data['idunit'],
                    "sd"          => $data['kd_sumberdana'],
                    "jenis"       => $jenisSaldo,
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi == 'RO') {
                    $whereConditions['kode_ss'] = $data['kodeSs'];
                    $whereConditions['kode_komponen'] = $data['kodeKomponen'];
                } else if ($jenisRevisi == 'KK') {
                    $whereConditions['kode_ikk'] = $data['kodeIkk'];
                    $whereConditions['kode_komponen'] = $data['kodeKomponen'];
                } else if ($jenisRevisi == 'SS') {
                    $whereConditions['kode_komponen'] = $data['kodeKomponen'] ?? null;
                }
                $currentSisaSaldo = SisaSaldoValidasi::where($whereConditions)->value('sisa_saldo') ?? 0;
                SisaSaldoValidasi::updateOrCreate($whereConditions, [ "sisa_saldo" => $currentSisaSaldo + $ambilRealisasi ]);
            });

            return [
                'success' => true,
                'message' => 'Berhasil mengalihkan sisa realisasi',
                'data' => [
                    'id_rab' => $rab->id,
                    'jenis_rab' => $jenisRabTerpakai,
                    'ambil_realisasi' => $ambilRealisasi,
                    'sisa_realisasi' => $sisa,
                    'dipakai_total' => $dipakai + $ambilRealisasi,
                    'kode_komponen' => $data['kodeKomponen'] ?? null
                ],
                'code' => 200
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengalihkan sisa realisasi: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Get RAB configuration for realisasi transfer
     *
     * @param string|null $jenisRab RAB type
     * @return array Configuration array
     */
    private function getRabConfigForRealisasiTransfer(?string $jenisRab): array {
        return match($jenisRab) {
            "RAB_GEDUNG", "prasarana" => [
                'model' => RABGDG::class,
                'jenisRabTerpakai' => 'prasarana',
                'jenisSaldo' => 'sapras'
            ],
            "RAB_PERALATAN", "sarana" => [
                'model' => RABPER::class,
                'jenisRabTerpakai' => 'sarana',
                'jenisSaldo' => 'sapras'
            ],
            default => [ // RAB_KEGIATAN, operasional, or null
                'model' => RABKEG::class,
                'jenisRabTerpakai' => 'operasional',
                'jenisSaldo' => 'operasional'
            ]
        };
    }

    /**
     * Group and sum data by a specific key
     *
     * Aggregates an array of objects by the provided group key,
     * summing the desired value field and optionally allowing a formatter
     * callback to extend the grouped payload for custom use cases.
     *
     * @param array $data           Array of objects/stdClass items to group
     * @param string $groupKey      The property name to group by
     * @param string $valueKey      The property name whose numeric value will be summed
     * @param callable|null $formatter Optional callback receiving ($currentGroup, $item)
     * @param string|null $groupAlias  Override for the alias stored alongside the group key
     * @return array Associative array keyed by the group value containing aggregated data
     */
    public function groupAndSum(
        array $data,
        string $groupKey,
        string $valueKey,
        ?callable $formatter = null,
        ?string $groupAlias = null
    ): array {
        $result = [];

        foreach ($data as $item) {
            if (!isset($item->$groupKey)) {
                continue;
            }

            $groupValue = (string) $item->$groupKey;
            $value      = isset($item->$valueKey) ? (float) $item->$valueKey : 0;

            if (!isset($result[$groupValue])) {
                $alias = $groupAlias ?: $groupKey;
                $result[$groupValue] = [
                    $alias     => $groupValue,
                    'total'    => 0,
                    'minRange' => 0,
                    'maxRange' => 0,
                ];
            }

            $result[$groupValue]['total'] += $value;

            if ($formatter) {
                $result[$groupValue] = $formatter($result[$groupValue], $item);
            }
        }

        return $result;
    }

    /**
     * Apply percentage range to grouped data
     *
     * Calculates and sets minRange and maxRange for each item based on
     * a percentage margin from the total value.
     *
     * @param array $data Reference to grouped data array (modified in place)
     * @param float $percent Percentage to apply as margin (default 0.1 = 10%)
     * @return void
     */
    public function applyRange(array &$data, float $percent = 0.1): void {
        foreach ($data as &$item) {
            if (is_array($item) && array_key_exists("total", $item)) {
                $margin = $item["total"] * $percent;
                $item["minRange"] = floor(abs($item["total"] - $margin));
                $item["maxRange"] = floor(abs($item["total"] + $margin));
            } elseif (is_array($item)) {
                $this->applyRange($item, $percent);
            }
        }
    }
    public function getRecapPaguAlokasi(array $params): array {
        $idunit = $params['idunit'] ?? null;
        $tahun = $params['tahun'] ?? null;
        $kodeSd = $params['kodeSd'] ?? null;

        if ( !$idunit || !$tahun || $kodeSd)
            throw new \InvalidArgumentException("Parameter tidak lengkap. Pastikan idunit, tahun, dan kodeSd disertakan.");
        return [];
    }
}
