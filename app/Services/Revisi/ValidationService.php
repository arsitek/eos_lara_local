<?php

namespace App\Services\Revisi;

use App\Models\Datarevisi\SemulaMenjadi;
use App\Models\Datarevisi\SisaSaldoValidasi;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Rekat;
use Illuminate\Support\Facades\DB;
use Exception;

class ValidationService
{
    public function __construct(
        protected SisaSaldoService $sisaSaldoService,
        protected CoaService $coaService,
        protected RekatService $rekatService,
    ) {}
    /**
     * Check if saldo is sufficient
     */
    public function validateSisaSaldo(int $idUnit, string $kdSumberdana, float $requiredAmount, string $jenisPerubahan): array {
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
        $sisaSaldo = $this->sisaSaldoService->getTotalSisaSaldo([
            'idunit' => $idUnit,
            'sd'     => $kdSumberdana,
            'tahun'  => $tahun
        ]);
        if ( $jenisPerubahan == 'Penambahan' || $jenisPerubahan == 'Penambahan Item Coa' ) {
            if ($sisaSaldo < $requiredAmount) {
                return [
                    'valid' => false,
                    'message' => "Saldo tidak mencukupi. Saldo tersedia: Rp " . number_format($sisaSaldo, 0, ',', '.'),
                    'current_saldo' => $sisaSaldo
                ];
            }
        }
        
        $cekPagu = cekPagu($idUnit, $kdSumberdana, $tahun, $sisaSaldo, true);
        if ( $cekPagu === "error" )
            return [ 'valid' => false, 'message' => "Saldo melebihi pagu yang tersedia.", 'current_saldo' => $sisaSaldo ];

        return [
            'valid' => true,
            'current_saldo' => $sisaSaldo
        ];
    }

    /**
     * Verify and update penambahan validation
     */
    public function verifyPenambahan(int $idSemula, array $requestData) : array {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $jenisRevisi = $requestData['jenis_revisi'] ?? 'KK';
            $status      = $requestData['status'] ?? 'Setuju';

            $result = DB::connection('sirekat')->select(function () use ($idSemula, $requestData, $tahun, $tahunAngka, $jenisRevisi, $status) {
                $semulaMenjadi = SemulaMenjadi::find($idSemula);
                if (!$semulaMenjadi)
                    throw new Exception("Data semula menjadi tidak ditemukan");

                // Resolve RAB & class
                $jenisRabClass = $semulaMenjadi->jenis_rab == "OPERASIONAL" ? RABKEG::class : ($semulaMenjadi->jenis_rab == "SARANA" ? RABPER::class : RABGDG::class);
                $rab = $jenisRabClass::where("id", $semulaMenjadi->id_rab)->first();
                if (!$rab)
                    throw new Exception("Data RAB tidak ditemukan");

                // Parse specs (support JSON and legacy)
                $spekAwal    = !empty($semulaMenjadi->spek_semula_json) ? json_decode($semulaMenjadi->spek_semula_json, true) : explode("~~~", $semulaMenjadi->spek_semula);
                $spekMenjadi = !empty($semulaMenjadi->spek_menjadi_json) ? json_decode($semulaMenjadi->spek_menjadi_json, true) : explode("~~~", $semulaMenjadi->spek_menjadi);

                $totalPenambahan = $semulaMenjadi->jumlah_menjadi - $semulaMenjadi->jumlah_semula;
                if ($totalPenambahan < 0)
                    throw new Exception("Total penambahan tidak valid");

                // Build saldo params
                $kodeIkk      = $requestData['kodeIkk'] ?? null;
                $kodeSs       = $requestData['kodeSs'] ?? null;
                $jenisSaldo   = in_array($semulaMenjadi->jenis_rab, ["SARANA", "PRASARANA"]) ? "sapras" : "operasional";
                $rekat      = Rekat::where("id", $rab->id_rekat)->first();
                if (!$rekat)
                    throw new Exception("Data rekat tidak ditemukan");

                $params = [
                    "idunit"      => $requestData['id_unit'],
                    "sd"          => $requestData['sd'],
                    "jenis"       => $jenisSaldo,
                    "tahun"       => $tahun,
                    "jenis_saldo" => $jenisRevisi
                ];
                if ($jenisRevisi == 'KK') {
                    $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(2, $rekat->kd_rk);
                    $params["kode_ikk"] = $kodeIkk;
                    $params["kode_komponen"] = $kodeKomponen;
                } elseif ($jenisRevisi == 'RO') {
                    $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
                    $params["kode_ss"] = $kodeSs;
                    $params["kode_komponen"] = $kodeKomponen;
                } elseif ($jenisRevisi == 'SS') {
                    $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
                    $params["kode_komponen"] = $kodeKomponen;
                } else {
                    throw new Exception("Jenis saldo revisi tidak dikenali");
                }

                // Lock saldo row to prevent race, create if missing
                $sisaSaldo = SisaSaldoValidasi::where($params)->lockForUpdate()->first();
                if (!$sisaSaldo)
                    $sisaSaldo = SisaSaldoValidasi::create(array_merge($params, ["sisa_saldo" => 0]));
                $currentSisaSaldo = $sisaSaldo->sisa_saldo;

                // Special case: Penambahan Item Coa (approve toggles draft, reject restores saldo)
                if ($semulaMenjadi->jenis_validasi == "Penambahan Item Coa") {
                    if ($status == "Setuju") {
                        $rab->update(["is_draft" => "false"]);
                    } else {
                        $rab->update(["is_deleted" => "true"]);
                        $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo + $totalPenambahan]);
                    }
                    $semulaMenjadi->update(['status' => $status]);
                    return ['success' => true, 'data' => $semulaMenjadi];
                }

                // Prepare RAB updates per jenis
                $rabUpdates = [];
                if ($jenisRabClass == RABKEG::class) {
                    $rabUpdates = [
                        'approve' => [
                            "kuantitas"          => $spekMenjadi['kuantitas'],
                            "satuan_kuantitas"   => $spekMenjadi['satuan_kuantitas'],
                            "durasi"             => $spekMenjadi['durasi'],
                            "satuan_durasi"      => $spekMenjadi['satuan_durasi'],
                            "kegiatan"           => $spekMenjadi['kegiatan'],
                            "satuan_kegiatan"    => $spekMenjadi['satuan_kegiatan'],
                            "biaya_satuan"       => $spekMenjadi['biaya_satuan'],
                            "jumlah_biaya"       => $semulaMenjadi->jumlah_menjadi,
                            "id_jenis_belanja"   => $spekMenjadi['id_jenis_belanja'],
                            "jenis_belanja"      => $spekMenjadi['jenis_belanja'],
                            "rpd"                => $spekMenjadi['rpd'],
                            "kebutuhan_kegiatan" => $spekMenjadi['kebutuhan_kegiatan'],
                        ],
                        'reject' => [
                            "kuantitas"        => $spekAwal['kuantitas'],
                            "satuan_kuantitas" => $spekAwal['satuan_kuantitas'],
                            "durasi"           => $spekAwal['durasi'],
                            "satuan_durasi"    => $spekAwal['satuan_durasi'],
                            "kegiatan"         => $spekAwal['kegiatan'],
                            "satuan_kegiatan"  => $spekAwal['satuan_kegiatan'],
                            "biaya_satuan"     => $spekAwal['biaya_satuan'],
                            "jumlah_biaya"     => $semulaMenjadi->jumlah_semula,
                            "id_jenis_belanja" => $spekAwal['id_jenis_belanja'],
                            "jenis_belanja"    => $spekAwal['jenis_belanja'],
                        ],
                    ];
                } elseif ($jenisRabClass == RABPER::class) {
                    $rabUpdates = [
                        'approve' => [
                            "kuantitas"          => $spekMenjadi['kuantitas'],
                            "satuan"             => $spekMenjadi['satuan'],
                            "harga_satuan"       => $spekMenjadi['harga_satuan'],
                            "biaya_pajak"        => $spekMenjadi['biaya_pajak'],
                            "biaya_lainnya"      => $spekMenjadi['biaya_lainnya'],
                            "jumlah_biaya"       => $semulaMenjadi->jumlah_menjadi,
                            "id_jenis_belanja"   => $spekMenjadi['id_jenis_belanja'],
                            "jenis_belanja"      => $spekMenjadi['jenis_belanja'],
                            "rpd"                => $spekMenjadi['rpd'],
                            "kebutuhan_kegiatan" => $spekMenjadi['kebutuhan_kegiatan'],
                            "kode_aset"          => $spekMenjadi['kode_aset'],
                            "aset"               => $spekMenjadi['aset'],
                        ],
                        'reject' => [
                            "kuantitas"          => $spekAwal['kuantitas'],
                            "satuan"             => $spekAwal['satuan'],
                            "harga_satuan"       => $spekAwal['harga_satuan'],
                            "biaya_pajak"        => $spekAwal['biaya_pajak'],
                            "biaya_lainnya"      => $spekAwal['biaya_lainnya'],
                            "jumlah_biaya"       => $semulaMenjadi->jumlah_semula,
                            "id_jenis_belanja"   => $spekAwal['id_jenis_belanja'],
                            "jenis_belanja"      => $spekAwal['jenis_belanja'],
                            "rpd"                => $spekMenjadi['rpd'],
                            "kebutuhan_kegiatan" => $spekMenjadi['kebutuhan_kegiatan'],
                            "kode_aset"          => $spekMenjadi['kode_aset'],
                            "aset"               => $spekMenjadi['aset'],
                        ],
                    ];
                } elseif ($jenisRabClass == RABGDG::class) {
                    $rabUpdates = [
                        'approve' => [
                            "jumlah_nilai"       => $semulaMenjadi->jumlah_menjadi,
                            "id_jenis_belanja"   => $spekMenjadi['id_jenis_belanja'],
                            "jenis_belanja"      => $spekMenjadi['jenis_belanja'],
                            "rpd"                => $spekMenjadi['rpd'],
                            "kebutuhan_kegiatan" => $spekMenjadi['kebutuhan_kegiatan'],
                            "jenis_pekerjaan"    => $spekMenjadi['jenis_pekerjaan'],
                            "kode_aset"          => $spekMenjadi['kode_aset'],
                            "aset"               => $spekMenjadi['aset'],
                        ],
                        'reject' => [
                            "jumlah_nilai"       => $semulaMenjadi->jumlah_semula,
                            "id_jenis_belanja"   => $spekAwal['id_jenis_belanja'],
                            "jenis_belanja"      => $spekAwal['jenis_belanja'],
                            "rpd"                => $spekAwal['rpd'],
                            "kebutuhan_kegiatan" => $spekAwal['kebutuhan_kegiatan'],
                            "jenis_pekerjaan"    => $spekAwal['jenis_pekerjaan'],
                            "kode_aset"          => $spekAwal['kode_aset'],
                            "aset"               => $spekAwal['aset'],
                        ],
                    ];
                } else {
                    throw new Exception("Jenis RAB tidak dikenali untuk penambahan");
                }

                // Apply RAB updates
                $rab->update($status == "Setuju" ? $rabUpdates['approve'] : $rabUpdates['reject']);

                // On rejection, return saldo; ensure it never goes minus
                if ($status != "Setuju") {
                    $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo + $totalPenambahan]);
                }

                $semulaMenjadi->update(['status' => $status, 'verify_by' => session()->get("id_user")]);

                return ['success' => true, 'data' => $semulaMenjadi];
            });

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify and update pengurangan validation
     */
    public function verifyPengurangan(int $idSemula, array $requestData){
        DB::connection('sirekat')->select();
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka] = getTahunData();
            $jenisRevisi = $requestData['jenis_revisi'] ?? 'KK';

            $semulaMenjadi = SemulaMenjadi::find($idSemula);
            if (!$semulaMenjadi)
                throw new Exception("Data semula menjadi tidak ditemukan");
            
            // Get status from request data, default to 'Setuju'
            $status        = $requestData['status'] ?? 'Setuju';
            $idRab         = $semulaMenjadi->id_rab;
            $jenisRabClass = $semulaMenjadi->jenis_rab == "OPERASIONAL" ? RABKEG::class : ($semulaMenjadi->jenis_rab == "SARANA" ? RABPER::class : RABGDG::class);
            $rab           = $jenisRabClass::where("id", $idRab)->first();
            
            if (!$rab)
                throw new Exception("Data RAB tidak ditemukan");
            
            // Parse JSON specifications
            $spekAwal    = null;
            $spekMenjadi = null;
            
            if (!empty($semulaMenjadi->spek_semula_json)) {
                $spekAwal = json_decode($semulaMenjadi->spek_semula_json, true);
            } else {
                $spekAwal = explode("~~~", $semulaMenjadi->spek_semula);
            }
            
            if (!empty($semulaMenjadi->spek_menjadi_json)) {
                $spekMenjadi = json_decode($semulaMenjadi->spek_menjadi_json, true);
            } else {
                $spekMenjadi = explode("~~~", $semulaMenjadi->spek_menjadi);
            }
            // Total nominal that will be released back to saldo when pengurangan is approved
            $totalPengurangan = $semulaMenjadi->jumlah_semula - $semulaMenjadi->jumlah_menjadi;
            $isApprove        = $status === "Setuju";

            // Get kode IKK and sisa saldo
            $kodeIkk    = $requestData['kodeIkk'] ?? null;
            $kodeSs     = $requestData['kodeSs'] ?? null;
            $jenisSaldo = in_array($semulaMenjadi->jenis_rab, ["SARANA", "PRASARANA"]) ? "sapras" : "operasional";
            $rekat      = Rekat::where("id", $rab->id_rekat)->first();
            if (!$rekat)
                throw new Exception("Data rekat tidak ditemukan");

            $params = [   
                "idunit"   => $requestData['idunit'],
                "sd"       => $requestData['sd'],
                "jenis"    => $jenisSaldo,
                "tahun"    => $tahun
            ];
            $params["jenis_saldo"] = $requestData['jenis_saldo'];

            if ($jenisRevisi == 'KK') {
                $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(2, $rekat->kd_rk);
                $params["kode_ikk"] = $kodeIkk;
                $params["kode_komponen"] = $kodeKomponen;
            } elseif ($jenisRevisi == 'RO') {
                $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
                $params["kode_ss"] = $kodeSs;
                $params["kode_komponen"] = $kodeKomponen;
            } elseif ($jenisRevisi == 'SS') {
                $kodeKomponen = $this->rekatService->getKodeKomponenMasterByLevel(1, $rekat->kd_rk);
                $params["kode_komponen"] = $kodeKomponen;
            } else {
                throw new Exception("Jenis saldo revisi tidak dikenali");
            }
            $sisaSaldo = SisaSaldoValidasi::firstOrCreate($params, ["sisa_saldo" => 0]);
            $currentSisaSaldo = $sisaSaldo->sisa_saldo;

            if (!$isApprove && $totalPengurangan > $currentSisaSaldo)
                throw new Exception("Penolakan gagal: saldo tidak mencukupi untuk mengembalikan dana");

            // Validate pagu only when approving pengurangan (saldo increases)
            if ($isApprove) {
                $paguValidation = cekPagu(
                    $requestData['idunit'], 
                    $requestData['sd'], 
                    $tahun, 
                    -$totalPengurangan, // Negative because saldo bertambah, pemakaian berkurang
                    true, // isRevisi
                    false, // isTambahItemCoa
                    null // statusRevisi
                );
                
                if ($paguValidation === "error") {
                    throw new Exception("Persetujuan gagal: Sisa saldo akan melebihi pagu yang tersedia");
                }
            } 
            // Prepare RAB updates per jenis; keeps logic compact and readable
            $rabUpdates = [];
            if ($jenisRabClass == RABKEG::class) {
                $rabUpdates = [
                    'approve' => [
                        "kuantitas"        => $spekMenjadi['kuantitas'],
                        "satuan_kuantitas" => $spekMenjadi['satuan_kuantitas'],
                        "durasi"           => $spekMenjadi['durasi'],
                        "satuan_durasi"    => $spekMenjadi['satuan_durasi'],
                        "kegiatan"         => $spekMenjadi['kegiatan'],
                        "satuan_kegiatan"  => $spekMenjadi['satuan_kegiatan'],
                        "biaya_satuan"     => $spekMenjadi['biaya_satuan'],
                        "jumlah_biaya"     => $semulaMenjadi->jumlah_menjadi,
                        "id_jenis_belanja" => $spekMenjadi['id_jenis_belanja'],
                        "jenis_belanja"    => $spekMenjadi['jenis_belanja'],
                    ],
                    'reject' => [
                        "kuantitas"        => $spekAwal['kuantitas'],
                        "satuan_kuantitas" => $spekAwal['satuan_kuantitas'],
                        "durasi"           => $spekAwal['durasi'],
                        "satuan_durasi"    => $spekAwal['satuan_durasi'],
                        "kegiatan"         => $spekAwal['kegiatan'],
                        "satuan_kegiatan"  => $spekAwal['satuan_kegiatan'],
                        "biaya_satuan"     => $spekAwal['biaya_satuan'],
                        "jumlah_biaya"     => $semulaMenjadi->jumlah_semula,
                        "id_jenis_belanja" => $spekAwal['id_jenis_belanja'],
                        "jenis_belanja"    => $spekAwal['jenis_belanja'],
                    ],
                ];
            } elseif ($jenisRabClass == RABPER::class) {
                $rabUpdates = [
                    'approve' => [
                        "kuantitas"       => $spekMenjadi['kuantitas'],
                        "satuan"          => $spekMenjadi['satuan'],
                        "harga_satuan"    => $spekMenjadi['harga_satuan'],
                        "biaya_pajak"     => $spekMenjadi['biaya_pajak'],
                        "biaya_lainnya"   => $spekMenjadi['biaya_lainnya'],
                        "jumlah_biaya"    => $semulaMenjadi->jumlah_menjadi,
                        "id_jenis_belanja"=> $spekMenjadi['id_jenis_belanja'],
                        "jenis_belanja"   => $spekMenjadi['jenis_belanja'],
                    ],
                    'reject' => [
                        "kuantitas"       => $spekAwal['kuantitas'],
                        "satuan"          => $spekAwal['satuan'],
                        "harga_satuan"    => $spekAwal['harga_satuan'],
                        "biaya_pajak"     => $spekAwal['biaya_pajak'],
                        "biaya_lainnya"   => $spekAwal['biaya_lainnya'],
                        "jumlah_biaya"    => $semulaMenjadi->jumlah_semula,
                        "id_jenis_belanja"=> $spekAwal['id_jenis_belanja'],
                        "jenis_belanja"   => $spekAwal['jenis_belanja'],
                    ],
                ];
            } elseif ($jenisRabClass == RABGDG::class) {
                $rabUpdates = [
                    'approve' => [
                        "jumlah_nilai"    => $semulaMenjadi->jumlah_menjadi,
                        "id_jenis_belanja"=> $spekMenjadi['id_jenis_belanja'],
                        "jenis_belanja"   => $spekMenjadi['jenis_belanja']
                    ],
                    'reject' => [
                        "jumlah_nilai"    => $semulaMenjadi->jumlah_semula,
                        "id_jenis_belanja"=> $spekAwal['id_jenis_belanja'],
                        "jenis_belanja"   => $spekAwal['jenis_belanja']
                    ],
                ];
            } else {
                throw new Exception("Jenis RAB tidak dikenali untuk pengurangan");
            }

            // Apply RAB change based on decision
            $rab->update($isApprove ? $rabUpdates['approve'] : $rabUpdates['reject']);

            // Update saldo once with a clear delta; guard above prevents negative saldo on rejection
            $saldoDelta = $isApprove ? $totalPengurangan : -$totalPengurangan; // approve: add saldo, reject: rollback saldo
            $sisaSaldo->update(["sisa_saldo" => $currentSisaSaldo + $saldoDelta]);

            // Update status
            $semulaMenjadi->update(['status' => $status, 'verify_by' => session()->get("id_user")]);
            DB::connection('sirekat')->select();
            return ['success' => true, 'data' => $semulaMenjadi];
        } catch (Exception $e) {
            DB::connection('sirekat')->select();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
