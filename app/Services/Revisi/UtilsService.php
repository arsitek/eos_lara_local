<?php

namespace App\Services\Revisi;

use App\Models\Datamaster\AksesMenuUnit;
use App\Models\Datapaket\RelasiPaket;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Realisasi;
use Illuminate\Support\Facades\DB;

class UtilsService
{
    /**
     * Check if unit has access to menu
     */
    public function checkAkses(int $idUnit, string $tahun, int $idMenu = 93): bool
    {
        $aksesMenu = AksesMenuUnit::where([
            "id_menu" => $idMenu,
            "idunit"  => $idUnit,
            "tahun"   => $tahun
        ])->first();

        if (!$aksesMenu) {
            return false;
        }

        return $aksesMenu->status == 1;
    }

    /**
     * Check if item exists in paket
     */
    public function cekItemPaket(int $idRab, string $jenisRab): bool
    {
        return RelasiPaket::where([
            "id_rab"     => $idRab,
            "is_deleted" => "false",
            "jenis_rab"  => $jenisRab
        ])->exists();
    }

    /**
     * Check if RAB has realisasi
     */
    public function cekRealisasi(int $idMak): bool
    {
        $realisasi = Realisasi::where([
            "id_mak"     => $idMak,
            "is_deleted" => "false",
            "is_posting" => "true"
        ])->first();

        if ($realisasi) {
            if ($realisasi->jumlah_amprahan != 0 || $realisasi->jumlah_realisasi != 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get total realisasi for a MAK
     */
    public function getTotalRealisasi(int $idMak): array {
        $realisasi = Realisasi::where([
            "id_mak"     => $idMak,
            "is_deleted" => "false",
            "is_posting" => "true"
        ]);

        $totalJumlahRincian = 0;
        if ($realisasi->exists()) {
            $realisasiData = $realisasi->first();
            $totalJumlahRincian = ( $realisasiData->jumlah_amprahan + $realisasiData->jumlah_realisasi );
        }

        return [ 'total_jumlah_rincian' => $totalJumlahRincian ];
    }

    /**
     * Calculate sisa realisasi after deduction
     */
    public function calculateSisaRealisasi(int $idMak, ?string $jenisRab = null): array {
        // Get RAB configuration
        $rabConfig = $this->getRabConfigForRealisasi($jenisRab);
        $rab = $rabConfig['model']::where(["id_mak" => $idMak])->first();
        
        if (!$rab) {
            return [
                'selisih' => 0,
                'is_sufficient' => false,
                'total_jumlah_rincian' => 0,
                'realisasi_terpakai' => 0,
                'jumlah_biaya_rab' => 0
            ];
        }
        
        // Get realisasi data from tb_realisasi
        $realisasiData           = $this->getTotalRealisasi($idMak);
        $realisasiTerpakaiRecord = DB::connection('sirekat')->select("tb_realisasi_terpakai")->where(["id_rab" => $rab->id, "jenis_rab" => $rabConfig['jenisRabTerpakai'] ])->first();
        $realisasiTerpakai       = $realisasiTerpakaiRecord ? $realisasiTerpakaiRecord->dipakai : 0;
        $jumlahBiayaRab          = $rab->{$rabConfig['biayaField']};
        // Calculate remaining budget
        $selisih = ($jumlahBiayaRab - $realisasiData['total_jumlah_rincian']) - $realisasiTerpakai;

        return [
            'selisih' => $selisih,
            'total_jumlah_rincian' => $realisasiData['total_jumlah_rincian'],
            'realisasi_terpakai' => $realisasiTerpakai,
            'jumlah_biaya_rab' => $jumlahBiayaRab
        ];
    }

    /**
     * Get RAB configuration for realisasi calculation
     * 
     * @param string|null $jenisRab RAB type
     * @return array Configuration array
     */
    private function getRabConfigForRealisasi(?string $jenisRab): array {
        return match($jenisRab) {
            "RAB_GEDUNG" => [
                'model' => RABGDG::class,
                'jenisRabTerpakai' => 'prasarana',
                'biayaField' => 'jumlah_nilai'
            ],
            "RAB_PERALATAN" => [
                'model' => RABPER::class,
                'jenisRabTerpakai' => 'sarana',
                'biayaField' => 'jumlah_biaya'
            ],
            default => [ // RAB_KEGIATAN or null
                'model' => RABKEG::class,
                'jenisRabTerpakai' => 'operasional',
                'biayaField' => 'jumlah_biaya'
            ]
        };
    }

    /**
     * Validate numeric value
     */
    public function validateNumeric($value, string $fieldName = 'value'): bool
    {
        if (!is_numeric($value)) {
            throw new \Exception("Field {$fieldName} harus berupa angka");
        }

        return true;
    }

    /**
     * Get tahun angka from tahun session
     */
    public function getTahunAngka(?string $tahun = null): int {
        $tahun = $tahun ?? session('tahun');
        return (int) explode("_", $tahun)[1];
    }

    /**
     * Check if user role is authorized
     */
    public function isAuthorizedRole(array $allowedRoles): bool
    {
        $role = session('role');
        return in_array($role, $allowedRoles);
    }

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
?>