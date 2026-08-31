<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RKTUNIT-FIX-008: tb_unit_api Cardinality Audit ===\n\n";

// STEP 1: Get tb_unit_api schema
echo "STEP 1: tb_unit_api Schema\n";
echo str_repeat('=', 50)."\n";

$columns = DB::connection('sirekat')->select('SHOW COLUMNS FROM tb_unit_api');
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}
echo "\n";

// STEP 2: Get all idunit values in current backup scope
echo "STEP 2: idunit values in backup scope (id_duplikasi=73, tahun='Definitif_2026')\n";
echo str_repeat('=', 50)."\n";

$backupIdunits = DB::connection('sirekat')->select("
    SELECT DISTINCT idunit
    FROM tb_backup_rkat
    WHERE id_duplikasi = 73 AND tahun = 'Definitif_2026'
");

echo 'Total distinct idunit in backup: '.count($backupIdunits)."\n";
echo 'idunit values: ';
foreach ($backupIdunits as $row) {
    echo $row->idunit.' ';
}
echo "\n\n";

// STEP 3: Check for duplicates in tb_unit_api for these idunit values
echo "STEP 3: Duplicate analysis for backup scope idunit values\n";
echo str_repeat('=', 50)."\n";

$idunitList = array_column($backupIdunits, 'idunit');
$idunitListString = "'".implode("','", $idunitList)."'";

$duplicates = DB::connection('sirekat')->select("
    SELECT idunit, COUNT(*) as count
    FROM tb_unit_api
    WHERE idunit IN ($idunitListString)
    GROUP BY idunit
    HAVING COUNT(*) > 1
    ORDER BY idunit
");

echo 'idunit values with duplicates: '.count($duplicates)."\n\n";

foreach ($duplicates as $dup) {
    echo "idunit: {$dup->idunit}, count: {$dup->count}\n";

    // Get all rows for this idunit
    $rows = DB::connection('sirekat')->select('
        SELECT * FROM tb_unit_api WHERE idunit = ? ORDER BY id
    ', [$dup->idunit]);

    echo "  All rows:\n";
    foreach ($rows as $i => $row) {
        echo '    Row '.($i + 1).': ';
        $rowArray = (array) $row;
        unset($rowArray['id']); // Skip ID column for brevity
        echo json_encode($rowArray)."\n";
    }
    echo "\n";
}

// STEP 4: Check for business key columns
echo "STEP 4: Business key column analysis\n";
echo str_repeat('=', 50)."\n";

$businessKeyCandidates = ['is_active', 'is_primary', 'is_deleted', 'status', 'tahun', 'version', 'updated_at', 'created_at'];
$columnNames = array_column($columns, 'Field');

echo "Checking for business key columns:\n";
foreach ($businessKeyCandidates as $candidate) {
    if (in_array($candidate, $columnNames)) {
        echo "- FOUND: $candidate\n";

        // Get distinct values for this column
        $distinctValues = DB::connection('sirekat')->select("
            SELECT DISTINCT $candidate, COUNT(*) as count
            FROM tb_unit_api
            WHERE idunit IN ($idunitListString)
            GROUP BY $candidate
        ");
        echo "  Distinct values:\n";
        foreach ($distinctValues as $val) {
            echo "    {$candidate} = {$val->$candidate}: {$val->count} rows\n";
        }
    } else {
        echo "- NOT FOUND: $candidate\n";
    }
}
echo "\n";

// STEP 5: Current JOIN result
echo "STEP 5: Current JOIN result (with tb_unit_api)\n";
echo str_repeat('=', 50)."\n";

$currentJoin = DB::connection('sirekat')->select("
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
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
        WHERE backupRkat.id_duplikasi = 73
            AND backupRkat.tahun = 'Definitif_2026'
        GROUP BY
            unit.nama,
            unit.idunit,
            sd.kd_sumberdana,
            sd.sumberdana,
            backupRkatDet.jenis,
            backupRkatDet.id_mak
");

echo 'Row count: '.count($currentJoin)."\n";

$totalPagu = collect($currentJoin)->sum('jumlah_biaya');
$totalRealisasi = collect($currentJoin)->sum('realisasi');

echo 'Total pagu: Rp'.number_format($totalPagu, 0, ',', '.')."\n";
echo 'Total realisasi: Rp'.number_format($totalRealisasi, 0, ',', '.')."\n\n";

// STEP 6: Safe one-row-per-idunit derived table (using tahun = 2026)
echo "STEP 6: Safe one-row-per-idunit derived table (using tahun = 2026)\n";
echo str_repeat('=', 50)."\n";

$safeJoin = DB::connection('sirekat')->select("
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
        INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit AND unit.tahun = 2026
        WHERE backupRkat.id_duplikasi = 73
            AND backupRkat.tahun = 'Definitif_2026'
        GROUP BY
            unit.nama,
            unit.idunit,
            sd.kd_sumberdana,
            sd.sumberdana,
            backupRkatDet.jenis,
            backupRkatDet.id_mak
");

echo 'Row count: '.count($safeJoin)."\n";

$safeTotalPagu = collect($safeJoin)->sum('jumlah_biaya');
$safeTotalRealisasi = collect($safeJoin)->sum('realisasi');

echo 'Total pagu: Rp'.number_format($safeTotalPagu, 0, ',', '.')."\n";
echo 'Total realisasi: Rp'.number_format($safeTotalRealisasi, 0, ',', '.')."\n\n";

// STEP 7: BASE without any master joins
echo "STEP 7: BASE without master joins (tb_backup_rkat + tb_backup_rkat_detail only)\n";
echo str_repeat('=', 50)."\n";

$baseOnly = DB::connection('sirekat')->select("
    SELECT
        backupRkat.idunit,
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
        WHERE backupRkat.id_duplikasi = 73
            AND backupRkat.tahun = 'Definitif_2026'
        GROUP BY
            backupRkat.idunit,
            backupRkatDet.jenis,
            backupRkatDet.id_mak
");

echo 'Row count: '.count($baseOnly)."\n";

$baseTotalPagu = collect($baseOnly)->sum('jumlah_biaya');
$baseTotalRealisasi = collect($baseOnly)->sum('realisasi');

echo 'Total pagu: Rp'.number_format($baseTotalPagu, 0, ',', '.')."\n";
echo 'Total realisasi: Rp'.number_format($baseTotalRealisasi, 0, ',', '.')."\n\n";

// STEP 8: Summary comparison
echo "STEP 8: Summary Comparison\n";
echo str_repeat('=', 50)."\n";
echo "BASE (no master joins):\n";
echo '  Rows: '.count($baseOnly)."\n";
echo '  Total pagu: Rp'.number_format($baseTotalPagu, 0, ',', '.')."\n";
echo '  Total realisasi: Rp'.number_format($baseTotalRealisasi, 0, ',', '.')."\n\n";

echo "Current JOIN (with tb_unit_api):\n";
echo '  Rows: '.count($currentJoin)."\n";
echo '  Total pagu: Rp'.number_format($totalPagu, 0, ',', '.')."\n";
echo '  Total realisasi: Rp'.number_format($totalRealisasi, 0, ',', '.')."\n";
echo '  Pagu multiplier: '.($totalPagu / $baseTotalPagu)."x\n";
echo '  Realisasi multiplier: '.($totalRealisasi / $baseTotalRealisasi)."x\n\n";

echo "Safe JOIN (one-row-per-idunit):\n";
echo '  Rows: '.count($safeJoin)."\n";
echo '  Total pagu: Rp'.number_format($safeTotalPagu, 0, ',', '.')."\n";
echo '  Total realisasi: Rp'.number_format($safeTotalRealisasi, 0, ',', '.')."\n";
echo '  Pagu multiplier: '.($safeTotalPagu / $baseTotalPagu)."x\n";
echo '  Realisasi multiplier: '.($safeTotalRealisasi / $baseTotalRealisasi)."x\n\n";

echo "=== END OF AUDIT ===\n";
