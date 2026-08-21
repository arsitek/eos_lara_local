<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function dayaSerap(): ViewContract
    {
        // Query untuk mendapatkan data daya serap dari backup Mei 2026 (idBackup = 73)
        $idBackup = 73;

        // Alokasi Backup
        $alokasiBackup = DB::connection('sirekat')->select("SELECT
            unit.nama AS unit_kerja,
            sd.kd_sumberdana,
            sd.sumberdana,
            ba.pagu AS pagu_alokasi
        FROM tb_backup_alokasi ba
        INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = ba.kode_sd
            AND sd.is_show = 'true'
            AND sd.is_deleted = 'false'
        INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit
        WHERE ba.id_duplikasi = ?
        ORDER BY sd.kd_sumberdana, ba.idunit", [$idBackup]);

        Log::info('StatistikController - alokasiBackup count: '.count($alokasiBackup));

        // Realisasi Backup
        $realisasiBackup = DB::connection('sirekat')->select("SELECT
            unit.nama AS unit_kerja,
            unit.idunit AS unit_kerja_rkt,
            sd.kd_sumberdana,
            sd.sumberdana,
            SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS realisasi
        FROM tb_backup_rkat backupRkat
        INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
        INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
        WHERE backupRkat.id_duplikasi = ?
          AND backupRkatDet.id_duplikasi = ?
          AND backupRkat.tahun = 'Definitif_2026'
        GROUP BY unit.idunit, backupRkat.sd", [$idBackup, $idBackup]);

        Log::info('StatistikController - realisasiBackup count: '.count($realisasiBackup));

        // Gabungkan data alokasi dan realisasi
        $dataDayaSerap = [];
        foreach ($alokasiBackup as $alokasi) {
            $key = $alokasi->unit_kerja.'_'.$alokasi->kd_sumberdana;
            $dataDayaSerap[$key] = [
                'unit_kerja' => $alokasi->unit_kerja,
                'kd_sumberdana' => $alokasi->kd_sumberdana,
                'sumberdana' => $alokasi->sumberdana,
                'pagu_alokasi' => $alokasi->pagu_alokasi,
                'realisasi' => 0,
                'daya_serap' => $alokasi->pagu_alokasi,
                'persentase' => 0,
            ];
        }

        foreach ($realisasiBackup as $realisasi) {
            $key = $realisasi->unit_kerja.'_'.$realisasi->kd_sumberdana;
            if (isset($dataDayaSerap[$key])) {
                $dataDayaSerap[$key]['realisasi'] = $realisasi->realisasi;
                $dataDayaSerap[$key]['daya_serap'] = $dataDayaSerap[$key]['pagu_alokasi'] - $realisasi->realisasi;
                $dataDayaSerap[$key]['persentase'] = $dataDayaSerap[$key]['pagu_alokasi'] > 0
                    ? round(($realisasi->realisasi / $dataDayaSerap[$key]['pagu_alokasi']) * 100, 2)
                    : 0;
            }
        }

        return view('statistik.dayaserap', compact('dataDayaSerap'));
    }
}
