<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PHP Aggregation Key Audit ===\n\n";

// Check if unit_kerja (name) is unique vs unit_kerja_rkt (ID)
echo "Checking unit name uniqueness in current query result...\n";

$result = DB::connection('sirekat')->select("
    SELECT
        unit.nama AS unit_kerja,
        unit.idunit AS unit_kerja_rkt,
        COUNT(*) as count
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
        GROUP BY unit.nama, unit.idunit
        ORDER BY unit.nama
");

echo "Total distinct (unit_kerja, unit_kerja_rkt) pairs: " . count($result) . "\n\n";

// Check if any unit name maps to multiple IDs
$unitNameToIds = [];
foreach ($result as $row) {
    $unitNameToIds[$row->unit_kerja][] = $row->unit_kerja_rkt;
}

$duplicateNames = [];
foreach ($unitNameToIds as $unitName => $ids) {
    if (count(array_unique($ids)) > 1) {
        $duplicateNames[$unitName] = array_unique($ids);
    }
}

echo "Unit names with multiple IDs: " . count($duplicateNames) . "\n";
if (count($duplicateNames) > 0) {
    echo "Duplicate examples:\n";
    foreach ($duplicateNames as $name => $ids) {
        echo "  '$name' maps to IDs: " . implode(", ", $ids) . "\n";
    }
} else {
    echo "No duplicate unit names found - unit_kerja is unique.\n";
}
echo "\n";

// Check the reverse: do multiple unit names map to the same ID?
$idToNames = [];
foreach ($result as $row) {
    $idToNames[$row->unit_kerja_rkt][] = $row->unit_kerja;
}

$duplicateIds = [];
foreach ($idToNames as $id => $names) {
    if (count(array_unique($names)) > 1) {
        $duplicateIds[$id] = array_unique($names);
    }
}

echo "IDs with multiple unit names: " . count($duplicateIds) . "\n";
if (count($duplicateIds) > 0) {
    echo "Duplicate examples:\n";
    foreach ($duplicateIds as $id => $names) {
        echo "  ID '$id' maps to names: " . implode(", ", $names) . "\n";
    }
} else {
    echo "No duplicate ID mappings - unit_kerja_rkt is unique.\n";
}
echo "\n";

echo "=== END OF AUDIT ===\n";
