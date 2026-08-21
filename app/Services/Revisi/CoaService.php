<?php

namespace App\Services\Revisi;

use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Komitmen;
use App\Models\Rekat;
use Exception;

class CoaService
{
    protected array $rabModels = [
        "RAB_KEGIATAN"  => RABKEG::class,
        "RAB_GEDUNG"    => RABGDG::class,
        "RAB_PERALATAN" => RABPER::class
    ];

    protected array $sumFieldNames = [
        "RAB_KEGIATAN"  => "jumlah_biaya",
        "RAB_GEDUNG"    => "jumlah_nilai",
        "RAB_PERALATAN" => "jumlah_biaya"
    ];

    /**
     * Get total budget for subjudul
     */
    public function getSubjudulTotalBudget(int $idRekat, string $jenisRab): float
    {
        $model = $this->rabModels[$jenisRab] ?? RABKEG::class;
        $fieldName = $this->sumFieldNames[$jenisRab] ?? 'jumlah_biaya';

        return $model::where([
            'is_deleted' => 'false',
            'id_rekat' => $idRekat,
            'is_draft' => 'false'
        ])->sum($fieldName);
    }

    /**
     * Get COA items for a subjudul
     */
    public function getCoaItems(int $idRekat, string $jenisRab): array {
        $model = $this->rabModels[$jenisRab] ?? RABKEG::class;

        return $model::with(["realisasi"])->where([
            'is_deleted' => 'false',
            'id_rekat'   => $idRekat,
            'is_draft'   => 'false'
        ])->get()->toArray();
    }

    /**
     * Generate unique ID MAK
     */
    public function generateUniqueMakId(): int
    {
        do {
            $id_mak = rand(1000000000, 9999999999);
            $existInRabKegiatan = RABKEG::where("id_mak", "=", $id_mak)->exists();
            $existInRabPeralatan = RABPER::where("id_mak", "=", $id_mak)->exists();
            $existInRabGedung = RABGDG::where("id_mak", "=", $id_mak)->exists();
        } while ($existInRabKegiatan || $existInRabPeralatan || $existInRabGedung);

        return $id_mak;
    }

    /**
     * Get PPK and BPP data for RAB
     */
    public function getPejabatData(array $dataPPK): array {
        $komitmen = Komitmen::select("id", "nip", "nama_pejabat")->where("jenis", "ppk");

        $ppk = getPPK($komitmen, $dataPPK);
        $bpp = getBPP($dataPPK);

        if (!$ppk)
            throw new Exception("Data PPK tidak ditemukan");
        if (!$bpp)
            throw new Exception("Data BPP tidak ditemukan");

        return [
            'ppk' => $ppk,
            'bpp' => $bpp
        ];
    }

    /**
     * Calculate biaya for RAB Peralatan
     */
    public function calculateBiayaPeralatan(array $data, int $tahunAngka): array {
        $kuantitas = (int) $data['kuantitas'];
        $hargaSatuan = (int) $data['hargaSatuan'];
        $biayaLainnya = (int) ($data['biayaLainnya'] ?? 0);

        $jumlah_biaya = $kuantitas * $hargaSatuan;
        $biaya_pajak = $tahunAngka == 2025 ? round($jumlah_biaya * 0.12) : 0;
        $max_biaya_lainnya = round($jumlah_biaya * 0.20);

        if ($biayaLainnya > $max_biaya_lainnya) {
            throw new Exception("Biaya lainnya tidak boleh lebih dari 20% dari jumlah biaya");
        }

        $total = $jumlah_biaya + $biaya_pajak + $biayaLainnya;

        if ($total % 1000 !== 0) {
            throw new Exception("Total biaya harus kelipatan 1000");
        }

        return [
            'jumlah_biaya' => $jumlah_biaya,
            'biaya_pajak' => $biaya_pajak,
            'biaya_lainnya' => $biayaLainnya,
            'total' => $total,
            'max_biaya_lainnya' => $max_biaya_lainnya
        ];
    }

    /**
     * Validate excluded sumber dana
     */
    public function validateExcludedSumberDana(string $kdSumberdana): bool {
        $excludedSd = [
            "42010999", "42010801", "42010913", "42010901",
            "42010204", "42010915", "41050105", "41050201", "42010915"
        ];

        return !in_array($kdSumberdana, $excludedSd);
    }
}
