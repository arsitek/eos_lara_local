<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $realisasiBackup = DB::connection('sirekat')->select('
    SELECT
        unit.nama AS unit_kerja,
        unit.idunit AS unit_kerja_rkt,
        sd.kd_sumberdana,
        sd.sumberdana,
        SUM(
            COALESCE(backupRkatDet.jumlah_amprahan, 0)
            + COALESCE(backupRkatDet.jumlah_realisasi, 0)
        ) AS realisasi
    FROM tb_backup_rkat backupRkat

    INNER JOIN tb_backup_rkat_detail backupRkatDet
        ON backupRkatDet.id_rekat = backupRkat.id_rekat
        AND backupRkatDet.id_duplikasi = backupRkat.id_duplikasi

    INNER JOIN tb_sumberdana sd
        ON sd.kd_sumberdana = backupRkat.sd
        AND sd.tahun = RIGHT(backupRkat.tahun, 4)
        AND sd.is_show = \'true\'
        AND sd.is_deleted = \'false\'

    INNER JOIN (
        SELECT
            idunit,
            MAX(nama) AS nama
        FROM tb_unit_api
        GROUP BY idunit
    ) unit
        ON unit.idunit = backupRkat.idunit

    WHERE backupRkat.id_duplikasi = ?
      AND backupRkat.tahun = ?
      AND backupRkatDet.is_deleted = \'false\'

    GROUP BY
        unit.idunit,
        backupRkat.sd
', [$idBackup, $backupTahun]);

        Log::info('DAYASERAP DEBUG', [
            'idBackup' => $idBackup,
            'backupTahun' => $backupTahun,
            'jumlah_realisasi_rows' => count($realisasiBackup),
            'total_realisasi_backup' => collect($realisasiBackup)->sum('realisasi'),
            'sample_realisasi' => array_slice($realisasiBackup, 0, 5),
        ]);

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
            'count' => 0,
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
            if (! isset($dataPerUnit[$unit])) {
                $dataPerUnit[$unit] = [
                    'unit_kerja' => $unit,
                    'total_pagu_alokasi' => 0,
                    'total_realisasi' => 0,
                    'total_daya_serap' => 0,
                    'avg_persentase' => 0,
                    'count' => 0,
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
        usort($dataPerUnit, function ($a, $b) {
            return $a['avg_persentase'] <=> $b['avg_persentase'];
        });

        $unitTerendah5 = array_slice($dataPerUnit, 0, 5);

        // Ambil unit dengan persentase daya serap > 100%
        $unitDiatas100 = [];
        foreach ($dataPerUnit as $unitData) {
            if ($unitData['avg_persentase'] > 100) {
                $unitDiatas100[] = $unitData;
            }
        }

        return view('statistik.dayaserap', compact('dataDayaSerapArray', 'backupKeterangan', 'unitTerendah5', 'totalSemua', 'backupTahun', 'unitDiatas100'));
    }

    public function rktUnit(): ViewContract
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
            return view('statistik.rktunit', [
                'dataRktUnit' => [],
                'backupKeterangan' => null,
                'backupTahun' => null,
                'totalSemua' => [],
                'statusStatistik' => [],
                'unitTertinggi5' => [],
                'unitTerendah5' => [],
                'distribusiJenisRab' => [],
                'distribusiSumberDana' => [],
                'unitDiatas100' => [],
            ]);
        }

        $idBackup = $latestBackup[0]->id;
        $backupTahun = $latestBackup[0]->tahun;
        $backupKeterangan = $latestBackup[0]->keterangan;

        // Query untuk mengambil data RKT dari backup
        // Menggunakan pattern dari RekatByUnitController dengan baseDataBackup
        //
        // QUERY INI MENDUKUNG 3 METRIK REALISASI:
        // 1. Realisasi Aktual Kegiatan - dihitung dengan mengagregasi 'realisasi' dari semua item per unit (lines 234-235)
        // 2. Pagu Kegiatan dengan Realisasi - dihitung dengan filter item di mana 'realisasi' > 0 (lines 295-299)
        // 3. Belum Realisasi & Draft - dihitung dengan filter item di mana 'realisasi' = 0 atau 'is_draft' = 'true' (lines 302-318)
        //
        // Granularity: Per unit kerja + sumber dana + jenis RAB + kegiatan (id_mak)
        // Ini adalah level paling granular yang memungkinkan agregasi ke level yang lebih tinggi
        $dataRktBackup = DB::connection('sirekat')->select("
        SELECT
            unit.nama AS unit_kerja,
            unit.idunit AS unit_kerja_rkt,
            sd.kd_sumberdana,
            sd.sumberdana,
            backupRkatDet.jenis AS rab_type,
            backupRkatDet.id_mak,
            MAX(backupRkatDet.kegiatan) AS kegiatan,
            SUM(backupRkatDet.jumlah_biaya) AS jumlah_biaya,
            SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) AS realisasi,
            SUM(CASE
                WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL)
                     AND backupRkatDet.terpakai_sisa IS NOT NULL
                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)
                     + backupRkatDet.terpakai_sisa
                ELSE backupRkatDet.jumlah_biaya
            END) AS jumlah_biaya_revisi,
            MAX(backupRkatDet.is_draft) AS is_draft
            FROM tb_backup_rkat backupRkat
            INNER JOIN tb_backup_rkat_detail backupRkatDet
                ON backupRkatDet.id_rekat = backupRkat.id_rekat
                AND backupRkatDet.id_duplikasi = backupRkat.id_duplikasi
            INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                AND sd.tahun = RIGHT(backupRkat.tahun, 4)
                AND sd.is_show = 'true'
                AND sd.is_deleted = 'false'
            INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit AND unit.tahun = RIGHT(backupRkat.tahun, 4)
            WHERE backupRkat.id_duplikasi = ?
              AND backupRkat.tahun = ?
              AND backupRkatDet.is_deleted = 'false'
            GROUP BY unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak
        ", [$idBackup, $backupTahun]);

        Log::info('RKTUNIT DEBUG - STAGE A: Raw SQL Result', [
            'idBackup' => $idBackup,
            'backupTahun' => $backupTahun,
            'raw_detail_count' => count($dataRktBackup),
            'total_jumlah_biaya' => collect($dataRktBackup)->sum('jumlah_biaya'),
            'total_realisasi' => collect($dataRktBackup)->sum('realisasi'),
            'total_sisa' => collect($dataRktBackup)->sum(function($item) { return $item->jumlah_biaya - $item->realisasi; }),
            'sample_data' => array_slice($dataRktBackup, 0, 3),
        ]);

        // DIAGNOSTIC 1: Log source rows for WCU before $dataPerUnit build
        $wcuSourceRows = [];
        foreach ($dataRktBackup as $index => $item) {
            if (stripos($item->unit_kerja, 'World Class University') !== false) {
                $wcuSourceRows[] = [
                    'array_index' => $index,
                    'unit_kerja' => $item->unit_kerja,
                    'unit_kerja_rkt' => $item->unit_kerja_rkt,
                    'kd_sumberdana' => $item->kd_sumberdana,
                    'rab_type' => $item->rab_type,
                    'id_mak' => $item->id_mak,
                    'jumlah_biaya' => $item->jumlah_biaya,
                    'realisasi' => $item->realisasi,
                ];
            }
        }
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 1: Source rows for WCU', [
            'total_wcu_rows' => count($wcuSourceRows),
            'distinct_unit_kerja_rkt' => count(array_unique(array_column($wcuSourceRows, 'unit_kerja_rkt'))),
            'distinct_kd_sumberdana' => count(array_unique(array_column($wcuSourceRows, 'kd_sumberdana'))),
            'wcu_rows' => $wcuSourceRows,
        ]);

        // DIAGNOSTIC 4: Check for $dataPerUnit key collision (same name, different unit_kerja_rkt)
        $unitNameToIds = [];
        foreach ($dataRktBackup as $item) {
            $unitNameToIds[$item->unit_kerja][] = $item->unit_kerja_rkt;
        }
        $nameCollision = [];
        foreach ($unitNameToIds as $unitName => $unitIds) {
            if (count(array_unique($unitIds)) > 1) {
                $nameCollision[$unitName] = [
                    'distinct_unit_kerja_rkt' => count(array_unique($unitIds)),
                    'unit_kerja_rkt_values' => array_unique($unitIds),
                ];
            }
        }
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 4: Names shared by multiple unit_kerja_rkt IDs', [
            'collision_count' => count($nameCollision),
            'collisions' => $nameCollision,
        ]);

        // Agregasi per unit kerja
        // PROSES INI MENGHASILKAN: Realisasi Aktual Kegiatan per unit
        // Mengagregasi data dari level granular (kegiatan) ke level unit kerja
        $dataPerUnit = [];
        $sourceRowCount = 0;
        $sourceRowContributionCount = []; // Track how many times each source row contributes
        foreach ($dataRktBackup as $item) {
            $sourceRowCount++;
            $sourceRowContributionCount[$sourceRowCount] = ($sourceRowContributionCount[$sourceRowCount] ?? 0) + 1;

            $unit = $item->unit_kerja;
            $unitRkt = $item->unit_kerja_rkt;
            if (! isset($dataPerUnit[$unit])) {
                $dataPerUnit[$unit] = [
                    'unit_kerja' => $unit,
                    'unit_kerja_rkt' => $unitRkt,
                    'total_jumlah_biaya' => 0,
                    'total_realisasi' => 0,
                    'total_sisa' => 0,
                    'avg_persentase' => 0,
                    'count' => 0,
                ];
            } else {
                // Log when we encounter a duplicate unit_kerja with different unit_kerja_rkt
                if ($dataPerUnit[$unit]['unit_kerja_rkt'] !== $unitRkt) {
                    Log::info('RKTUNIT DEBUG - Duplicate unit_kerja with different unit_kerja_rkt', [
                        'unit_kerja' => $unit,
                        'existing_unit_kerja_rkt' => $dataPerUnit[$unit]['unit_kerja_rkt'],
                        'new_unit_kerja_rkt' => $unitRkt,
                        'item_jumlah_biaya' => $item->jumlah_biaya,
                        'item_realisasi' => $item->realisasi,
                    ]);
                }
            }
            $dataPerUnit[$unit]['total_jumlah_biaya'] += $item->jumlah_biaya;
            $dataPerUnit[$unit]['total_realisasi'] += $item->realisasi; // Mengagregasi realisasi untuk Realisasi Aktual Kegiatan
            $dataPerUnit[$unit]['total_sisa'] += ($item->jumlah_biaya - $item->realisasi);
            $dataPerUnit[$unit]['count']++;
        }

        // DIAGNOSTIC 6: Check for source rows processed more than once
        $multiContributionRows = array_filter($sourceRowContributionCount, function($count) {
            return $count > 1;
        });
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 6: Source row contribution count', [
            'total_source_rows' => $sourceRowCount,
            'rows_processed_once' => count(array_filter($sourceRowContributionCount, function($c) { return $c === 1; })),
            'rows_processed_multiple_times' => count($multiContributionRows),
            'multi_contribution_details' => $multiContributionRows,
        ]);

        // Hitung persentase per unit
        foreach ($dataPerUnit as &$unitData) {
            if ($unitData['total_jumlah_biaya'] > 0) {
                $unitData['avg_persentase'] = round(($unitData['total_realisasi'] / $unitData['total_jumlah_biaya']) * 100, 2);
            }
        }
        unset($unitData);

        Log::info('RKTUNIT DEBUG - STAGE B: After $dataPerUnit build (before sorting)', [
            'unit_count' => count($dataPerUnit),
            'total_jumlah_biaya' => collect($dataPerUnit)->sum('total_jumlah_biaya'),
            'total_realisasi' => collect($dataPerUnit)->sum('total_realisasi'),
            'total_sisa' => collect($dataPerUnit)->sum('total_sisa'),
        ]);

        Log::info('RKTUNIT DEBUG - STAGE B: $dataPerUnit structure analysis', [
            'array_key_count' => count($dataPerUnit),
            'unit_kerja_keys' => array_keys($dataPerUnit),
            'duplicate_unit_kerja_check' => count($dataPerUnit) === count(array_unique(array_keys($dataPerUnit))) ? 'NO_DUPLICATES' : 'HAS_DUPLICATES',
            'unit_kerja_rkt_values' => array_column($dataPerUnit, 'unit_kerja_rkt'),
            'duplicate_unit_kerja_rkt_check' => count(array_column($dataPerUnit, 'unit_kerja_rkt')) === count(array_unique(array_column($dataPerUnit, 'unit_kerja_rkt'))) ? 'NO_DUPLICATES' : 'HAS_DUPLICATES',
        ]);

        // DIAGNOSTIC 2: Log actual $dataPerUnit WCU entries
        $wcuDataPerUnitEntries = [];
        foreach ($dataPerUnit as $key => $unitData) {
            if (stripos($key, 'World Class University') !== false) {
                $wcuDataPerUnitEntries[] = [
                    'array_key' => $key,
                    'unit_kerja' => $unitData['unit_kerja'],
                    'unit_kerja_rkt' => $unitData['unit_kerja_rkt'],
                    'total_jumlah_biaya' => $unitData['total_jumlah_biaya'],
                    'total_realisasi' => $unitData['total_realisasi'],
                    'count' => $unitData['count'],
                ];
            }
        }
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 2: Actual $dataPerUnit WCU entries', [
            'entry_count' => count($wcuDataPerUnitEntries),
            'entries' => $wcuDataPerUnitEntries,
        ]);

        // DIAGNOSTIC 5: Prove Stage B → Stage C financial delta (source vs dataPerUnit)
        $sourceSumPagu = collect($dataRktBackup)->sum('jumlah_biaya');
        $dataPerUnitSumPagu = array_sum(array_column($dataPerUnit, 'total_jumlah_biaya'));
        $sourceSumRealisasi = collect($dataRktBackup)->sum('realisasi');
        $dataPerUnitSumRealisasi = array_sum(array_column($dataPerUnit, 'total_realisasi'));
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 5: Source vs dataPerUnit totals', [
            'source_sum_pagu' => $sourceSumPagu,
            'dataPerUnit_sum_pagu' => $dataPerUnitSumPagu,
            'pagu_difference' => $dataPerUnitSumPagu - $sourceSumPagu,
            'source_sum_realisasi' => $sourceSumRealisasi,
            'dataPerUnit_sum_realisasi' => $dataPerUnitSumRealisasi,
            'realisasi_difference' => $dataPerUnitSumRealisasi - $sourceSumRealisasi,
            'pagu_invariant_holds' => $sourceSumPagu === $dataPerUnitSumPagu ? 'YES' : 'NO',
            'realisasi_invariant_holds' => $sourceSumRealisasi === $dataPerUnitSumRealisasi ? 'YES' : 'NO',
        ]);

        // Hitung total untuk semua unit
        // PROSES INI MENGHASILKAN: Realisasi Aktual Kegiatan untuk semua unit
        // Mengagregasi data dari level unit ke level institusi (semua unit)
        $totalSemua = [
            'total_jumlah_biaya' => 0,
            'total_realisasi' => 0,
            'total_sisa' => 0,
            'avg_persentase' => 0,
            'count' => 0,
        ];

        // DIAGNOSTIC 7: Check for mutation after $dataPerUnit (before totalSemua loop)
        $dataPerUnitSumBeforeLoop = collect($dataPerUnit)->sum('total_jumlah_biaya');
        $dataPerUnitRealisasiBeforeLoop = collect($dataPerUnit)->sum('total_realisasi');
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 7: dataPerUnit sum before totalSemua loop', [
            'dataPerUnit_sum_pagu_before_loop' => $dataPerUnitSumBeforeLoop,
            'dataPerUnit_sum_realisasi_before_loop' => $dataPerUnitRealisasiBeforeLoop,
            'dataPerUnit_count' => count($dataPerUnit),
        ]);

        $debugIteration = 0;
        foreach ($dataPerUnit as $unitKey => $unitData) {
            $debugIteration++;

            Log::info('RKTUNIT TOTAL LOOP', [
                'iteration' => $debugIteration,
                'array_key' => $unitKey,
                'unit_kerja' => $unitData['unit_kerja'],
                'unit_kerja_rkt' => $unitData['unit_kerja_rkt'],
                'jumlah_biaya' => $unitData['total_jumlah_biaya'],
                'realisasi' => $unitData['total_realisasi'],
            ]);

            $totalSemua['total_jumlah_biaya'] += $unitData['total_jumlah_biaya'];
            $totalSemua['total_realisasi'] += $unitData['total_realisasi']; // Final Realisasi Aktual Kegiatan
            $totalSemua['total_sisa'] += $unitData['total_sisa'];
            $totalSemua['avg_persentase'] += $unitData['avg_persentase'];
            $totalSemua['count']++;
        }

        // DIAGNOSTIC 7: Check for mutation after $dataPerUnit (after totalSemua loop)
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 7: totalSemua sum after loop', [
            'totalSemua_sum_pagu_after_loop' => $totalSemua['total_jumlah_biaya'],
            'totalSemua_sum_realisasi_after_loop' => $totalSemua['total_realisasi'],
            'pagu_match' => $totalSemua['total_jumlah_biaya'] === $dataPerUnitSumBeforeLoop ? 'MATCH' : 'MISMATCH',
            'realisasi_match' => $totalSemua['total_realisasi'] === $dataPerUnitRealisasiBeforeLoop ? 'MATCH' : 'MISMATCH',
            'dataPerUnit_count' => count($dataPerUnit),
            'actual_iteration_count' => $debugIteration,
            'iteration_match' => count($dataPerUnit) === $debugIteration ? 'MATCH' : 'MISMATCH',
        ]);

        // Hitung Tingkat Realisasi (weighted average): total_realisasi / total_jumlah_biaya × 100
        if ($totalSemua['total_jumlah_biaya'] > 0) {
            $totalSemua['avg_persentase'] = round(($totalSemua['total_realisasi'] / $totalSemua['total_jumlah_biaya']) * 100, 2);
        } else {
            $totalSemua['avg_persentase'] = 0;
        }

        Log::info('RKTUNIT DEBUG - STAGE C: Before statusStatistik', [
            'total_jumlah_biaya' => $totalSemua['total_jumlah_biaya'],
            'total_realisasi' => $totalSemua['total_realisasi'],
            'total_sisa' => $totalSemua['total_sisa'],
            'avg_persentase' => $totalSemua['avg_persentase'],
            'unit_count' => $totalSemua['count'],
        ]);

        // Hitung statistik berdasarkan status realisasi
        // PROSES INI MENGHASILKAN: Pagu Kegiatan dengan Realisasi, Belum Realisasi, dan Draft
        // Mengfilter data dari query berdasarkan kondisi realisasi dan status draft
        $statusStatistik = [
            'sudah' => [
                'total_jumlah_biaya' => 0,
                'total_realisasi' => 0,
                'total_sisa' => 0,
                'count' => 0,
            ],
            'belum' => [
                'total_jumlah_biaya' => 0,
                'total_realisasi' => 0,
                'total_sisa' => 0,
                'count' => 0,
            ],
            'draft' => [
                'total_jumlah_biaya' => 0,
                'total_realisasi' => 0,
                'total_sisa' => 0,
                'count' => 0,
            ],
        ];

        foreach ($dataRktBackup as $item) {
            // Pagu Kegiatan dengan Realisasi - Filter: realisasi > 0
            if ($item->realisasi > 0) {
                $statusStatistik['sudah']['total_jumlah_biaya'] += $item->jumlah_biaya;
                $statusStatistik['sudah']['total_realisasi'] += $item->realisasi;
                $statusStatistik['sudah']['total_sisa'] += ($item->jumlah_biaya - $item->realisasi);
                $statusStatistik['sudah']['count']++;
            }
            // Belum Realisasi - Filter: realisasi = 0 dan bukan draft
            if ($item->realisasi == 0 && $item->is_draft != 'true') {
                $statusStatistik['belum']['total_jumlah_biaya'] += $item->jumlah_biaya;
                $statusStatistik['belum']['total_sisa'] += $item->jumlah_biaya;
                $statusStatistik['belum']['count']++;
            }
            // Draft
            if ($item->is_draft == 'true') {
                $statusStatistik['draft']['total_jumlah_biaya'] += $item->jumlah_biaya;
                $statusStatistik['draft']['total_sisa'] += $item->jumlah_biaya;
                $statusStatistik['draft']['count']++;
            }
        }

        // Hitung persentase untuk status statistik
        foreach ($statusStatistik as &$status) {
            if ($status['total_jumlah_biaya'] > 0) {
                $status['persentase'] = round(($status['total_realisasi'] / $status['total_jumlah_biaya']) * 100, 2);
            } else {
                $status['persentase'] = 0;
            }
        }

        Log::info('RKTUNIT DEBUG - STAGE D: After statusStatistik', [
            'total_jumlah_biaya' => $totalSemua['total_jumlah_biaya'],
            'total_realisasi' => $totalSemua['total_realisasi'],
            'total_sisa' => $totalSemua['total_sisa'],
            'avg_persentase' => $totalSemua['avg_persentase'],
            'unit_count' => $totalSemua['count'],
        ]);

        // DIAGNOSTIC 8: Verify statusStatistik doesn't mutate totalSemua
        Log::info('RKTUNIT DEBUG - DIAGNOSTIC 8: After statusStatistik mutation check', [
            'totalSemua_pagu_unchanged' => $totalSemua['total_jumlah_biaya'] === $dataPerUnitSumBeforeLoop ? 'UNCHANGED' : 'MUTATED',
            'totalSemua_realisasi_unchanged' => $totalSemua['total_realisasi'] === $dataPerUnitRealisasiBeforeLoop ? 'UNCHANGED' : 'MUTATED',
        ]);

        Log::info('RKTUNIT DEBUG - STAGE E: statusStatistik', [
            'sudah' => [
                'count' => $statusStatistik['sudah']['count'],
                'total_jumlah_biaya' => $statusStatistik['sudah']['total_jumlah_biaya'],
                'total_realisasi' => $statusStatistik['sudah']['total_realisasi'],
            ],
            'belum' => [
                'count' => $statusStatistik['belum']['count'],
                'total_jumlah_biaya' => $statusStatistik['belum']['total_jumlah_biaya'],
                'total_realisasi' => $statusStatistik['belum']['total_realisasi'],
            ],
            'draft' => [
                'count' => $statusStatistik['draft']['count'],
                'total_jumlah_biaya' => $statusStatistik['draft']['total_jumlah_biaya'],
                'total_realisasi' => $statusStatistik['draft']['total_realisasi'],
            ],
        ]);

        // Ambil 5 unit dengan total biaya tertinggi
        usort($dataPerUnit, function ($a, $b) {
            return $b['total_jumlah_biaya'] <=> $a['total_jumlah_biaya'];
        });
        $unitTertinggi5 = array_slice($dataPerUnit, 0, 5);

        // Ambil 5 unit dengan total biaya terendah
        usort($dataPerUnit, function ($a, $b) {
            return $a['total_jumlah_biaya'] <=> $b['total_jumlah_biaya'];
        });
        $unitTerendah5 = array_slice($dataPerUnit, 0, 5);

        // Ambil unit dengan persentase realisasi > 100%
        $unitDiatas100 = [];
        foreach ($dataPerUnit as $unitData) {
            if ($unitData['avg_persentase'] > 100) {
                $unitDiatas100[] = $unitData;
            }
        }

        // Hitung distribusi per jenis RAB
        $distribusiJenisRab = [];
        foreach ($dataRktBackup as $item) {
            $jenis = $item->rab_type;
            if (! isset($distribusiJenisRab[$jenis])) {
                $distribusiJenisRab[$jenis] = [
                    'jenis' => $jenis,
                    'total_jumlah_biaya' => 0,
                    'count' => 0,
                ];
            }
            $distribusiJenisRab[$jenis]['total_jumlah_biaya'] += $item->jumlah_biaya;
            $distribusiJenisRab[$jenis]['count']++;
        }
        $distribusiJenisRab = array_values($distribusiJenisRab);

        Log::info('RKTUNIT DEBUG - STAGE F: After distribusiJenisRab', [
            'total_jumlah_biaya' => $totalSemua['total_jumlah_biaya'],
            'total_realisasi' => $totalSemua['total_realisasi'],
            'total_sisa' => $totalSemua['total_sisa'],
            'avg_persentase' => $totalSemua['avg_persentase'],
            'unit_count' => $totalSemua['count'],
        ]);

        // Hitung distribusi per sumber dana
        $distribusiSumberDana = [];
        foreach ($dataRktBackup as $item) {
            $kdSd = $item->kd_sumberdana;
            if (! isset($distribusiSumberDana[$kdSd])) {
                $distribusiSumberDana[$kdSd] = [
                    'kd_sumberdana' => $kdSd,
                    'sumberdana' => $item->sumberdana,
                    'total_jumlah_biaya' => 0,
                    'count' => 0,
                ];
            }
            $distribusiSumberDana[$kdSd]['total_jumlah_biaya'] += $item->jumlah_biaya;
            $distribusiSumberDana[$kdSd]['count']++;
        }
        $distribusiSumberDana = array_values($distribusiSumberDana);

        Log::info('RKTUNIT DEBUG - STAGE G: After distribusiSumberDana', [
            'total_jumlah_biaya' => $totalSemua['total_jumlah_biaya'],
            'total_realisasi' => $totalSemua['total_realisasi'],
            'total_sisa' => $totalSemua['total_sisa'],
            'avg_persentase' => $totalSemua['avg_persentase'],
            'unit_count' => $totalSemua['count'],
        ]);

        // Convert associative array to indexed array for DataTable
        $dataPerUnitArray = array_values($dataPerUnit);

        // Convert detail data to array for DataTables
        $dataRktDetailArray = [];
        foreach ($dataRktBackup as $item) {
            $dataRktDetailArray[] = [
                'unit_kerja' => $item->unit_kerja,
                'sumberdana' => $item->sumberdana,
                'rab_type' => $item->rab_type,
                'kode_keg' => $item->id_mak ?? '-', // Using id_mak as kode_kegiatan
                'rincian_kegiatan' => $item->kegiatan ?? '-',
                'jumlah_biaya' => $item->jumlah_biaya,
                'realisasi' => $item->realisasi,
                'sisa' => $item->jumlah_biaya - $item->realisasi,
                'persentase' => $item->jumlah_biaya > 0 ? round(($item->realisasi / $item->jumlah_biaya) * 100, 2) : 0,
                'is_draft' => $item->is_draft,
            ];
        }

        Log::info('RKTUNIT DEBUG - STAGE F: dataRktDetailArray', [
            'count' => count($dataRktDetailArray),
        ]);

        return view('statistik.rktunit', compact(
            'dataRktDetailArray',
            'backupKeterangan',
            'backupTahun',
            'totalSemua',
            'statusStatistik',
            'unitTertinggi5',
            'unitTerendah5',
            'distribusiJenisRab',
            'distribusiSumberDana',
            'unitDiatas100'
        ));
    }

    public function getRktUnitDataTable(Request $request)
    {
        // Query untuk mendapatkan idBackup terbaru dari tb_duplikasi_rkat
        $latestBackup = DB::connection('sirekat')->select("SELECT id, tahun
        FROM tb_duplikasi_rkat
        WHERE is_deleted = false
          AND duplikasi_ke = 0
          AND peruntukan = 'RKAT Awal'
        ORDER BY created_at DESC
        LIMIT 1");

        if (empty($latestBackup)) {
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $idBackup = $latestBackup[0]->id;
        $backupTahun = $latestBackup[0]->tahun;

        // Ambil parameter filter
        $filter = $request->input('filter', 'semua');

        // Tentukan filter condition berdasarkan parameter
        $filterCondition = '';
        if ($filter == 'realisasi') {
            $filterCondition = ' AND ( COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) ) > 0 ';
        } elseif ($filter == '!realisasi') {
            $filterCondition = ' AND ( COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) ) = 0 AND backupRkatDet.is_draft != \'true\' ';
        } elseif ($filter == 'draft') {
            $filterCondition = " AND backupRkatDet.is_draft = 'true' ";
        }

        // Query untuk mengambil data RKT dengan filter
        $query = "
        SELECT
            unit.nama AS unit_kerja,
            unit.idunit AS unit_kerja_rkt,
            sd.kd_sumberdana,
            sd.sumberdana,
            backupRkatDet.jenis AS rab_type,
            backupRkatDet.kode_keg,
            backupRkatDet.sub_judul AS rincian_kegiatan,
            backupRkatDet.jumlah_biaya,
            COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) AS realisasi,
            ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL)
                 AND backupRkatDet.terpakai_sisa IS NOT NULL
                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)
                     + backupRkatDet.terpakai_sisa
                ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya_revisi,
            backupRkatDet.is_draft
        FROM tb_backup_rkat backupRkat
        INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat AND backupRkatDet.id_duplikasi = backupRkat.id_duplikasi
        INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
            AND sd.tahun = RIGHT(backupRkat.tahun, 4)
            AND sd.is_show = 'true'
            AND sd.is_deleted = 'false'
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit AND unit.tahun = RIGHT(backupRkat.tahun, 4)
        WHERE backupRkat.id_duplikasi = ?
          AND backupRkatDet.id_duplikasi = ?
          AND backupRkat.tahun = ?
          AND backupRkatDet.is_deleted = 'false'
$filterCondition
    ";

        $data = DB::connection('sirekat')->select($query, [$idBackup, $idBackup, $backupTahun]);

        // Hitung sisa dan persentase
        foreach ($data as &$item) {
            $item->sisa = $item->jumlah_biaya - $item->realisasi;
            if ($item->jumlah_biaya > 0) {
                $item->persentase = round(($item->realisasi / $item->jumlah_biaya) * 100, 2);
            } else {
                $item->persentase = 0;
            }
        }

        // DataTables response
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }
}
