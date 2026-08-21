<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function dayaSerap(): ViewContract
    {
        // Query untuk mendapatkan idBackup terbaru dari tb_duplikasi_rkat
        $latestBackup = DB::connection('sirekat')->select("SELECT id, keterangan, tahun
            FROM tb_duplikasi_rkat
            WHERE is_deleted = false
              AND duplikasi_ke = 0
              AND peruntukan = 'RKAT Awal'
            ORDER BY created_at DESC
            LIMIT 1");

        if (empty($latestBackup)) {
            return view('statistik.dayaserap', ['dataDayaSerap' => []]);
        }

        $idBackup = $latestBackup[0]->id;
        $backupTahun = $latestBackup[0]->tahun;
        $backupKeterangan = $latestBackup[0]->keterangan;

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
          AND backupRkat.tahun = ?
        GROUP BY unit.idunit, backupRkat.sd", [$idBackup, $idBackup, $backupTahun]);

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

        // Convert associative array to indexed array for DataTable
        $dataDayaSerapArray = array_values($dataDayaSerap);

        // Hitung total untuk semua unit
        $totalSemua = [
            'total_pagu_alokasi' => 0,
            'total_realisasi' => 0,
            'total_daya_serap' => 0,
            'avg_persentase' => 0,
            'count' => 0
        ];

        foreach ($dataDayaSerapArray as $item) {
            $totalSemua['total_pagu_alokasi'] += $item['pagu_alokasi'];
            $totalSemua['total_realisasi'] += $item['realisasi'];
            $totalSemua['total_daya_serap'] += $item['daya_serap'];
            $totalSemua['avg_persentase'] += $item['persentase'];
            $totalSemua['count']++;
        }

        // Hitung rata-rata persentase untuk semua unit
        if ($totalSemua['count'] > 0) {
            $totalSemua['avg_persentase'] = round($totalSemua['avg_persentase'] / $totalSemua['count'], 2);
        }

        // Hitung data akumulasi per unit untuk 5 unit dengan daya serap terendah
        $dataPerUnit = [];
        foreach ($dataDayaSerapArray as $item) {
            $unit = $item['unit_kerja'];
            if (!isset($dataPerUnit[$unit])) {
                $dataPerUnit[$unit] = [
                    'unit_kerja' => $unit,
                    'total_pagu_alokasi' => 0,
                    'total_realisasi' => 0,
                    'total_daya_serap' => 0,
                    'avg_persentase' => 0,
                    'count' => 0
                ];
            }
            $dataPerUnit[$unit]['total_pagu_alokasi'] += $item['pagu_alokasi'];
            $dataPerUnit[$unit]['total_realisasi'] += $item['realisasi'];
            $dataPerUnit[$unit]['total_daya_serap'] += $item['daya_serap'];
            $dataPerUnit[$unit]['avg_persentase'] += $item['persentase'];
            $dataPerUnit[$unit]['count']++;
        }

        // Hitung rata-rata persentase per unit
        foreach ($dataPerUnit as &$unitData) {
            if ($unitData['count'] > 0) {
                $unitData['avg_persentase'] = round($unitData['avg_persentase'] / $unitData['count'], 2);
            }
        }

        // Ambil 5 unit dengan daya serap terendah (berdasarkan persentase)
        usort($dataPerUnit, function($a, $b) {
            return $a['avg_persentase'] <=> $b['avg_persentase'];
        });

        $unitTerendah5 = array_slice($dataPerUnit, 0, 5);

        return view('statistik.dayaserap', compact('dataDayaSerapArray', 'backupKeterangan', 'unitTerendah5', 'totalSemua'));
    }
}
