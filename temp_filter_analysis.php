<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DataTable Filter Consistency Analysis ===\n\n";

// Get the same data as rktUnit
$idBackup = 73;
$backupTahun = 'Definitif_2026';

$data = DB::connection('sirekat')->select("
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
        GROUP BY unit.nama, unit.idunit, sd.kd_sumberdana, sd.sumberdana, backupRkatDet.jenis, backupRkatDet.id_mak
", [$idBackup, $backupTahun]);

echo "Total rows: " . count($data) . "\n\n";

// A. realization > 0 (dashboard "Sudah" logic)
$realizationGtZero = 0;
foreach ($data as $item) {
    if ($item->realisasi > 0) {
        $realizationGtZero++;
    }
}
echo "A. realization > 0: $realizationGtZero\n";

// B. realization = 0 but amprahan/realisasi column is NOT NULL
$realizationZeroNotNull = 0;
foreach ($data as $item) {
    if ($item->realisasi == 0 && ($item->jumlah_biaya_revisi != $item->jumlah_biaya)) {
        $realizationZeroNotNull++;
    }
}
echo "B. realization = 0 but has amprahan/realisasi data: $realizationZeroNotNull\n";

// C. both amprahan and realisasi are NULL (effectively)
$bothNull = 0;
foreach ($data as $item) {
    if ($item->jumlah_biaya_revisi == $item->jumlah_biaya && $item->realisasi == 0) {
        $bothNull++;
    }
}
echo "C. no amprahan/realisasi data: $bothNull\n\n";

// DataTable filter: realisasi (jumlah_amprahan IS NOT NULL OR jumlah_realisasi IS NOT NULL)
// This is harder to check from aggregated data, so let's query raw
$rawData = DB::connection('sirekat')->select("
    SELECT
        backupRkatDet.jumlah_biaya,
        backupRkatDet.jumlah_amprahan,
        backupRkatDet.jumlah_realisasi,
        backupRkatDet.is_draft
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
", [$idBackup, $backupTahun]);

echo "Raw detail rows: " . count($rawData) . "\n\n";

// DataTable filter: realisasi
$dataTableRealisasi = 0;
foreach ($rawData as $item) {
    if ($item->jumlah_amprahan !== null || $item->jumlah_realisasi !== null) {
        $dataTableRealisasi++;
    }
}
echo "DataTable 'realisasi' filter (amprahan OR realisasi NOT NULL): $dataTableRealisasi\n";

// DataTable filter: !realisasi
$dataTableNotRealisasi = 0;
foreach ($rawData as $item) {
    if ($item->jumlah_amprahan === null && $item->jumlah_realisasi === null) {
        $dataTableNotRealisasi++;
    }
}
echo "DataTable '!realisasi' filter (both NULL): $dataTableNotRealisasi\n";

// DataTable filter: draft
$dataTableDraft = 0;
foreach ($rawData as $item) {
    if ($item->is_draft === 'true') {
        $dataTableDraft++;
    }
}
echo "DataTable 'draft' filter: $dataTableDraft\n\n";

// Dashboard status logic
$sudahCount = 0;
$belumCount = 0;
$draftCount = 0;
foreach ($rawData as $item) {
    $realisasi = ($item->jumlah_amprahan ?? 0) + ($item->jumlah_realisasi ?? 0);
    if ($item->is_draft === 'true') {
        $draftCount++;
    } elseif ($realisasi > 0) {
        $sudahCount++;
    } else {
        $belumCount++;
    }
}

echo "Dashboard status logic:\n";
echo "Sudah (realisasi > 0): $sudahCount\n";
echo "Belum (realisasi = 0, not draft): $belumCount\n";
echo "Draft (is_draft = true): $draftCount\n\n";

echo "=== END OF ANALYSIS ===\n";
