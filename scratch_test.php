<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

session()->put('tahun', 'Indikatif_2025');
session()->put('role', 'Admin');

$testControllers = [
    'AnalisisController' => App\Http\Controllers\Laporan\AnalisisController::class,
    'DataErrorController' => App\Http\Controllers\Laporan\DataErrorController::class,
    'DatapaketController' => App\Http\Controllers\Laporan\DatapaketController::class,
    'DayaSerapController' => App\Http\Controllers\Laporan\DayaSerapController::class,
    'LembarPengendalianController' => App\Http\Controllers\Laporan\LembarPengendalianController::class,
    'PembiayaanController' => App\Http\Controllers\Laporan\PembiayaanController::class,
    'PendapatanController' => App\Http\Controllers\Laporan\PendapatanController::class,
    'PerkinController' => App\Http\Controllers\Laporan\PerkinController::class,
    'ProporsiAnggaran' => App\Http\Controllers\Laporan\ProporsiAnggaran::class,
    'RekapAnggaranUnitController' => App\Http\Controllers\Laporan\RekapAnggaranUnitController::class,
    'RekatByUnitController' => App\Http\Controllers\Laporan\RekatByUnitController::class,
    'RevisiController' => App\Http\Controllers\Laporan\RevisiController::class,
    'RkaCoaController' => App\Http\Controllers\Laporan\RkaCoaController::class,
    'RkaRoController' => App\Http\Controllers\Laporan\RkaRoController::class,
    'RkaSSsatuController' => App\Http\Controllers\Laporan\RkaSSsatuController::class,
    'RkaSSduaController' => App\Http\Controllers\Laporan\RkaSSduaController::class,
    'RkaSStigaController' => App\Http\Controllers\Laporan\RkaSStigaController::class,
    'RkaSSempatController' => App\Http\Controllers\Laporan\RkaSSempatController::class,
    'RkatController' => App\Http\Controllers\Laporan\RkatController::class,
    'RpdController' => App\Http\Controllers\Laporan\RpdController::class,
    'RpdUnitController' => App\Http\Controllers\Laporan\RpdUnitController::class,
    'SubkomponenController' => App\Http\Controllers\Laporan\SubkomponenController::class,
    'TahunanController' => App\Http\Controllers\Laporan\TahunanController::class,
    'UsulanRevisiController' => App\Http\Controllers\Laporan\UsulanRevisiController::class,
    'REKATREPORTController' => App\Http\Controllers\REKATREPORTController::class,
];

$successCount = 0;
$failCount = 0;

foreach ($testControllers as $name => $class) {
    try {
        $ctrl = app($class);
        // Call index or default method
        $res = null;
        if (method_exists($ctrl, 'index')) {
            $reflection = new ReflectionMethod($ctrl, 'index');
            if ($reflection->getNumberOfRequiredParameters() === 0) {
                $res = $ctrl->index();
            } else {
                $req = new Illuminate\Http\Request();
                $res = $ctrl->index($req);
            }
        }
        $viewName = is_object($res) && method_exists($res, 'name') ? $res->name() : (is_string($res) ? 'string' : gettype($res));
        echo "[OK] $name -> $viewName\n";
        $successCount++;
    } catch (\Throwable $e) {
        echo "[FAIL] $name: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
        $failCount++;
    }
}

echo "\nSummary: $successCount succeeded, $failCount failed.\n";
