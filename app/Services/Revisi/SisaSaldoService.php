<?php

namespace App\Services\Revisi;

use App\Models\Datarevisi\SisaSaldoValidasi;
use Exception;

class SisaSaldoService {
    /**
     * Get sisa saldo for specific unit and sumber dana
     */
    public function getSisaSaldo(string $tahun, int $idUnit, string $kdSumberdana, string $jenisSaldo = 'KK') {
        $query = SisaSaldoValidasi::where([
            'idunit'      => $idUnit,
            'sd'          => $kdSumberdana,
            'jenis_saldo' => $jenisSaldo,
            'tahun'       => $tahun
        ]);

        switch ($jenisSaldo) {
            case 'KK':
                $query->groupBy('kode_komponen', 'kode_ikk', 'jenis');
                break;
            case 'SS':
                $query->groupBy('kode_komponen', 'jenis');
                break;
            case 'RO':
                $query->groupBy('kode_komponen', 'kode_ss', 'jenis');
                break;
            default:
                $query->groupBy('kode_komponen', 'jenis');
        }

        return $query->get();
    }

    /**
     * Update or create sisa saldo
     */
    public function updateOrCreateSisaSaldo(array $params, float $amount): SisaSaldoValidasi {
        $sisaSaldo = $this->getSisaSaldo(
            $params['idunit'],
            $params['kd_sumberdana'],
            $params['jenis_saldo']
        );

        $currentSaldo = $sisaSaldo ? $sisaSaldo->sisa_saldo : 0;

        return SisaSaldoValidasi::updateOrCreate([
            'idunit' => $params['idunit'],
            'sd' => $params['kd_sumberdana'],
            'jenis_saldo' => $params['jenis_saldo'],
            'tahun' => $params['tahun']
        ], [
            'sisa_saldo' => $currentSaldo + $amount,
            'tahun' => $params['tahun']
        ]);
    }
    /**
     * Get total sisa saldo for multiple records
     */
    public function getTotalSisaSaldo(array $conditions): float {
        $data = SisaSaldoValidasi::where($conditions)->get();
        return $data->sum('sisa_saldo');
    }
}
