<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RKTUNIT-FIX-010: Status Semantics + DataTable Consistency Audit ===\n\n";

$idBackup = 73;
$backupTahun = 'Definitif_2026';

// =========================================================
// 1. STATUS CATEGORY EXACT COUNTS/TOTALS
// =========================================================

echo "1. STATUS CATEGORY EXACT COUNTS/TOTALS\n";
echo str_repeat("=", 60) . "\n";

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

echo "Total raw joined rows: " . count($rawData) . "\n\n";

$actualRealization = 0;
$zeroRealizationDraft = 0;
$zeroRealizationNotDraft = 0;
$negativeRealization = 0;
$nullIsDraft = 0;
$unexpectedIsDraft = 0;

$paguActualRealization = 0;
$paguZeroRealizationDraft = 0;
$paguZeroRealizationNotDraft = 0;
$realisasiActualRealization = 0;

foreach ($rawData as $item) {
    $realisasi = ($item->jumlah_amprahan ?? 0) + ($item->jumlah_realisasi ?? 0);

    if ($realisasi > 0) {
        $actualRealization++;
        $paguActualRealization += $item->jumlah_biaya;
        $realisasiActualRealization += $realisasi;
    } elseif ($realisasi < 0) {
        $negativeRealization++;
    } else {
        // realization = 0
        if ($item->is_draft === 'true') {
            $zeroRealizationDraft++;
            $paguZeroRealizationDraft += $item->jumlah_biaya;
        } elseif ($item->is_draft === 'false' || $item->is_draft === null) {
            $zeroRealizationNotDraft++;
            $paguZeroRealizationNotDraft += $item->jumlah_biaya;
        } else {
            $unexpectedIsDraft++;
        }
    }

    if ($item->is_draft === null) {
        $nullIsDraft++;
    } elseif ($item->is_draft !== 'true' && $item->is_draft !== 'false' && $item->is_draft !== null) {
        $unexpectedIsDraft++;
    }
}

echo "A. Actual realization (realisasi > 0):\n";
echo "   Count: $actualRealization\n";
echo "   Total pagu: Rp" . number_format($paguActualRealization, 0, ',', '.') . "\n";
echo "   Total realisasi: Rp" . number_format($realisasiActualRealization, 0, ',', '.') . "\n\n";

echo "B. Zero realization + draft (realisasi = 0 AND is_draft = 'true'):\n";
echo "   Count: $zeroRealizationDraft\n";
echo "   Total pagu: Rp" . number_format($paguZeroRealizationDraft, 0, ',', '.') . "\n\n";

echo "C. Zero realization + not draft (realisasi = 0 AND is_draft != 'true'):\n";
echo "   Count: $zeroRealizationNotDraft\n";
echo "   Total pagu: Rp" . number_format($paguZeroRealizationNotDraft, 0, ',', '.') . "\n\n";

echo "D. Unexpected:\n";
echo "   Negative realization: $negativeRealization\n";
echo "   NULL is_draft: $nullIsDraft\n";
echo "   Unexpected is_draft values: $unexpectedIsDraft\n\n";

// =========================================================
// 2. MIXED DRAFT/NON-DRAFT GROUP AUDIT
// =========================================================

echo "2. MIXED DRAFT/NON-DRAFT GROUP AUDIT\n";
echo str_repeat("=", 60) . "\n";

$mixedGroups = DB::connection('sirekat')->select("
    SELECT
        unit.idunit,
        sd.kd_sumberdana,
        backupRkatDet.jenis,
        backupRkatDet.id_mak,
        COUNT(*) as detail_count,
        COUNT(DISTINCT is_draft) as distinct_draft_values,
        GROUP_CONCAT(DISTINCT is_draft) as draft_values,
        SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) as total_realisasi,
        SUM(backupRkatDet.jumlah_biaya) as total_pagu
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
        GROUP BY unit.idunit, sd.kd_sumberdana, backupRkatDet.jenis, backupRkatDet.id_mak
        HAVING COUNT(DISTINCT is_draft) > 1
", [$idBackup, $backupTahun]);

echo "Groups with mixed draft/non-draft detail records: " . count($mixedGroups) . "\n\n";

if (count($mixedGroups) > 0) {
    echo "Mixed groups:\n";
    foreach ($mixedGroups as $group) {
        echo "  idunit: {$group->idunit}, sd: {$group->kd_sumberdana}, jenis: {$group->jenis}, id_mak: {$group->id_mak}\n";
        echo "  detail_count: {$group->detail_count}, draft_values: {$group->draft_values}\n";
        echo "  total_pagu: Rp" . number_format($group->total_pagu, 0, ',', '.') . "\n";
        echo "  total_realisasi: Rp" . number_format($group->total_realisasi, 0, ',', '.') . "\n\n";
    }
} else {
    echo "NO mixed draft/non-draft groups found.\n";
    echo "MAX(is_draft) is currently SAFE for this dataset.\n\n";
}

// =========================================================
// 3. CURRENT DATATABLE FILTER COUNTS
// =========================================================

echo "3. CURRENT DATATABLE FILTER COUNTS\n";
echo str_repeat("=", 60) . "\n";

// Current DataTable filter logic
$dataTableRealisasi = 0;
$dataTableNotRealisasi = 0;
$dataTableDraft = 0;
$dataTableSemua = count($rawData);

foreach ($rawData as $item) {
    if ($item->jumlah_amprahan !== null || $item->jumlah_realisasi !== null) {
        $dataTableRealisasi++;
    } else {
        $dataTableNotRealisasi++;
    }

    if ($item->is_draft === 'true') {
        $dataTableDraft++;
    }
}

echo "Current DataTable filter logic (NULL/non-NULL based):\n";
echo "  semua: $dataTableSemua\n";
echo "  realisasi (amprahan OR realisasi NOT NULL): $dataTableRealisasi\n";
echo "  !realisasi (both NULL): $dataTableNotRealisasi\n";
echo "  draft (is_draft = 'true'): $dataTableDraft\n\n";

// Proposed DataTable filter logic
$proposedRealisasi = 0;
$proposedNotRealisasi = 0;
$proposedDraft = 0;

foreach ($rawData as $item) {
    $realisasi = ($item->jumlah_amprahan ?? 0) + ($item->jumlah_realisasi ?? 0);

    if ($realisasi > 0) {
        $proposedRealisasi++;
    } elseif ($realisasi == 0 && $item->is_draft !== 'true') {
        $proposedNotRealisasi++;
    }

    if ($item->is_draft === 'true') {
        $proposedDraft++;
    }
}

echo "Proposed DataTable filter logic (business-value based):\n";
echo "  realisasi (COALESCE(...) > 0): $proposedRealisasi\n";
echo "  !realisasi (COALESCE(...) = 0 AND is_draft != 'true'): $proposedNotRealisasi\n";
echo "  draft (is_draft = 'true'): $proposedDraft\n\n";

// =========================================================
// 4. RAW VS ANALYTICAL GRAIN
// =========================================================

echo "4. RAW VS ANALYTICAL GRAIN\n";
echo str_repeat("=", 60) . "\n";

$analyticalGroups = DB::connection('sirekat')->select("
    SELECT
        unit.idunit,
        sd.kd_sumberdana,
        backupRkatDet.jenis,
        backupRkatDet.id_mak,
        SUM(backupRkatDet.jumlah_biaya) as jumlah_biaya,
        SUM(COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0)) as realisasi,
        MAX(backupRkatDet.is_draft) as is_draft
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
        GROUP BY unit.idunit, sd.kd_sumberdana, backupRkatDet.jenis, backupRkatDet.id_mak
", [$idBackup, $backupTahun]);

echo "Raw joined rows: " . count($rawData) . " (detail-level grain)\n";
echo "Analytical groups: " . count($analyticalGroups) . " (aggregated grain)\n";
echo "Difference: " . (count($rawData) - count($analyticalGroups)) . " rows\n\n";

echo "Explanation:\n";
echo "- rktUnit() operates at ANALYTICAL GROUP grain (GROUP BY unit.idunit, sd, jenis, id_mak)\n";
echo "- getRktUnitDataTable() currently operates at RAW DETAIL grain (no GROUP BY)\n";
echo "- This is intentional: DataTable shows individual detail records, dashboard shows aggregated analytics\n";
echo "- Row counts will differ, but STATUS FILTER SEMANTICS should be consistent\n\n";

// =========================================================
// 5. SEMANTIC LABEL CONFIRMATION
// =========================================================

echo "5. SEMANTIC LABEL CONFIRMATION\n";
echo str_repeat("=", 60) . "\n";

$sudahPagu = 0;
$sudahRealisasi = 0;

foreach ($analyticalGroups as $group) {
    if ($group->realisasi > 0) {
        $sudahPagu += $group->jumlah_biaya;
        $sudahRealisasi += $group->realisasi;
    }
}

echo "\"Sudah Realisasi\" financial card analysis:\n";
echo "  Displayed value (from statusStatistik.sudah.total_jumlah_biaya):\n";
echo "    = SUM(jumlah_biaya) for groups where realization > 0\n";
echo "    = Rp" . number_format($sudahPagu, 0, ',', '.') . "\n";
echo "  Actual realization for these groups:\n";
echo "    = SUM(realisasi) for groups where realization > 0\n";
echo "    = Rp" . number_format($sudahRealisasi, 0, ',', '.') . "\n";
echo "  Conclusion: This is PAGU of items with realization, NOT actual realization\n";
echo "  Recommended label: \"Pagu Kegiatan dengan Realisasi\"\n\n";

echo "=== END OF AUDIT ===\n";
