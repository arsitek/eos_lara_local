<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
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
        $realisasiBackup = DB::connection('sirekat')->select('SELECT
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
        GROUP BY unit.idunit, backupRkat.sd', [$idBackup, $idBackup, $backupTahun]);

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
        $dataRktBackup = DB::connection('sirekat')->select("
        SELECT
            unit.nama AS unit_kerja,
            unit.idunit AS unit_kerja_rkt,
            sd.kd_sumberdana,
            sd.sumberdana,
            backupRkatDet.jenis AS rab_type,
            backupRkatDet.id_mak,
            backupRkatDet.kegiatan,
            backupRkatDet.jumlah_biaya,
            COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) AS realisasi,
            ( CASE WHEN (backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL)
                 AND backupRkatDet.terpakai_sisa IS NOT NULL
                THEN COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)
                     + backupRkatDet.terpakai_sisa
                ELSE backupRkatDet.jumlah_biaya END ) AS jumlah_biaya_revisi,
            backupRkatDet.is_draft
        FROM tb_backup_rkat backupRkat
        INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
        INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
            AND sd.is_show = 'true'
            AND sd.is_deleted = 'false'
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
        WHERE backupRkat.id_duplikasi = ?
          AND backupRkatDet.id_duplikasi = ?
          AND backupRkat.tahun = ?
          AND backupRkatDet.is_deleted = 'false'
        GROUP BY unit.idunit, backupRkat.sd, backupRkatDet.jenis, backupRkatDet.id_mak
    ", [$idBackup, $idBackup, $backupTahun]);

        // Agregasi per unit kerja
        $dataPerUnit = [];
        foreach ($dataRktBackup as $item) {
            $unit = $item->unit_kerja;
            if (! isset($dataPerUnit[$unit])) {
                $dataPerUnit[$unit] = [
                    'unit_kerja' => $unit,
                    'unit_kerja_rkt' => $item->unit_kerja_rkt,
                    'total_jumlah_biaya' => 0,
                    'total_realisasi' => 0,
                    'total_sisa' => 0,
                    'avg_persentase' => 0,
                    'count' => 0,
                ];
            }
            $dataPerUnit[$unit]['total_jumlah_biaya'] += $item->jumlah_biaya;
            $dataPerUnit[$unit]['total_realisasi'] += $item->realisasi;
            $dataPerUnit[$unit]['total_sisa'] += ($item->jumlah_biaya - $item->realisasi);
            $dataPerUnit[$unit]['count']++;
        }

        // Hitung persentase per unit
        foreach ($dataPerUnit as &$unitData) {
            if ($unitData['total_jumlah_biaya'] > 0) {
                $unitData['avg_persentase'] = round(($unitData['total_realisasi'] / $unitData['total_jumlah_biaya']) * 100, 2);
            }
        }

        // Hitung total untuk semua unit
        $totalSemua = [
            'total_jumlah_biaya' => 0,
            'total_realisasi' => 0,
            'total_sisa' => 0,
            'avg_persentase' => 0,
            'count' => 0,
        ];

        foreach ($dataPerUnit as $unitData) {
            $totalSemua['total_jumlah_biaya'] += $unitData['total_jumlah_biaya'];
            $totalSemua['total_realisasi'] += $unitData['total_realisasi'];
            $totalSemua['total_sisa'] += $unitData['total_sisa'];
            $totalSemua['avg_persentase'] += $unitData['avg_persentase'];
            $totalSemua['count']++;
        }

        // Hitung rata-rata persentase untuk semua unit
        if ($totalSemua['count'] > 0) {
            $totalSemua['avg_persentase'] = round($totalSemua['avg_persentase'] / $totalSemua['count'], 2);
        }

        // Hitung statistik berdasarkan status realisasi
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
            // Sudah Realisasi
            if ($item->realisasi > 0) {
                $statusStatistik['sudah']['total_jumlah_biaya'] += $item->jumlah_biaya;
                $statusStatistik['sudah']['total_realisasi'] += $item->realisasi;
                $statusStatistik['sudah']['total_sisa'] += ($item->jumlah_biaya - $item->realisasi);
                $statusStatistik['sudah']['count']++;
            }
            // Belum Realisasi
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
                'is_draft' => $item->is_draft
            ];
        }

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
            $filterCondition = ' AND ( backupRkatDet.jumlah_amprahan IS NOT NULL OR backupRkatDet.jumlah_realisasi IS NOT NULL ) ';
        } elseif ($filter == '!realisasi') {
            $filterCondition = ' AND ( backupRkatDet.jumlah_amprahan IS NULL AND backupRkatDet.jumlah_realisasi IS NULL ) ';
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
        INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
        INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
            AND sd.is_show = 'true'
            AND sd.is_deleted = 'false'
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
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
