<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Laporan\DatapaketController;
use App\Http\Controllers\Laporan\RekatByUnitController AS LaporanRekatUnit;
use App\Http\Controllers\Laporan\RkaCoaController AS RkaCoa;
use App\Http\Controllers\Laporan\PerkinController AS LapPerkin;
use App\Http\Controllers\Laporan\RpdController AS LapRPD;
use App\Http\Controllers\Laporan\AnalisisController AS LapAnalisis;
use App\Http\Controllers\Laporan\SubkomponenController AS LapSubkomponen;
use App\Http\Controllers\Laporan\TahunanController AS LapTahunan;
use App\Http\Controllers\Laporan\RkatLampiranController AS RkatLampiran;
use App\Http\Controllers\Laporan\PembiayaanController AS Pembiayaan;
use App\Http\Controllers\Laporan\PendapatanController AS Pendapatan;
use App\Http\Controllers\Laporan\ProporsiAnggaran AS ProporsiAnggaran;
use App\Http\Controllers\Laporan\LembarPengendalianController AS LembarPengendalian;
use App\Http\Controllers\Laporan\DayaSerapController AS DayaSerap;
use App\Http\Controllers\Laporan\UsulanRevisiController AS UsulanRevisi;
use App\Http\Controllers\Laporan\RevisiController AS RekapRevisi;

/*
* Route untuk laporan pemaketan 📦
*/
Route::get("/datapaket", [ DatapaketController::class, "index"])->name("laporan.datapaket.index");
Route::get("/datapaket/rka/rka-sapras", [ DatapaketController::class, "indexSapras"])->name("laporan.datapaket.indexSapras");
Route::get("/datapaket/rka/sapras/get-sumber-dana-ppk", [ DatapaketController::class, "getSumberDanaPPK"])->name("laporan.datapaket.getSumberDanaPPK");
Route::get("/datapaket/rka/sapras/pdf", [ DatapaketController::class, "indexSaprasPdf"]);
Route::get("/datapaket/rka/sapras/getItemTerpaketkan", [ DatapaketController::class, "getItemTerpaketkan"])->name("laporan.datapaket.getItemTerpaketkan");
Route::get("/datapaket/rka/paket", [ DatapaketController::class, "indexPaket"])->name("laporan.datapaket.indexPaket");
Route::get("/datapaket/rka/paket/pdf", [ DatapaketController::class, "indexPaketPdf"])->name("laporan.datapaket.indexPaketPdf");
Route::get("/datapaket/get/rka", [ DatapaketController::class, "getRka"])->name("laporan.datapaket.get.rka");
Route::get("/datapaket/show/{id}", [ DatapaketController::class, "show"])->name("laporan.datapaket.show");

/**
 * Route untuk laporan `Rencana Kerja & Anggaran`
*/
Route::get('/rktunit',[ LaporanRekatUnit::class, 'index' ])->name('rktReportUnit.index');
Route::get('/rktunit/pdf/{idunit}/{kd_sumberdana}',[ LaporanRekatUnit::class, 'pdf' ])->name('rktReportUnit.pdf');
Route::get('/rktunit/get/{idunit}/{kd_sumberdana}',[ LaporanRekatUnit::class, 'getRka' ]);
Route::get('/rktunit/get/realisasi',[ LaporanRekatUnit::class, 'getRealisasi' ])->name("rktReportUnit.realisasi");
Route::get('/rktunit/get/paket',[ LaporanRekatUnit::class, 'getPaket' ])->name("rktReportUnit.getPaket");
Route::get('/rktunit/get/ppkNull',[ LaporanRekatUnit::class, 'getPpkNull' ]);
Route::get('/rktunit/get/statusVerifikasi',[ LaporanRekatUnit::class, 'getStatusVerifikasi' ])->name("rktReportUnit.getStatusVerifikasi");
Route::get('/rktunit/get/baseData',[ LaporanRekatUnit::class, 'getBaseData' ])->name("rktReportUnit.getBaseData");
Route::post('/rktunit/get/sumberdanaParent',[ LaporanRekatUnit::class, 'getSumberdanaParent' ])->name("rktReportUnit.getSumberdanaParent");
Route::post('/rktunit/update/ppk',[ LaporanRekatUnit::class, 'updatePpk' ])->name('rktReportUnit.update.ppk');
Route::get('/rktunit/get/sumberdana',[ LaporanRekatUnit::class, 'getSumberdana' ])->name("rktReportUnit.getSumberdana");
Route::get('/rktunit/get/unitkerja',[ LaporanRekatUnit::class, 'getUnitkerja' ])->name("rktReportUnit.getUnitkerja");
Route::get('/rktunit/get/idrekats',[ LaporanRekatUnit::class, 'getIdRekats' ])->name("rktReportUnit.getIdRekats");


/**
 * Route untuk laporan `RKA COA`
*/
Route::get('/rka/coa', [RkaCoa::class, 'index'])->name('rka.coa.index');
Route::post('/rka/coa/getRka', [RkaCoa::class, 'getRkaJenisBelanja'])->name('rka.coa.getRka');


/**
 * Route untuk laporan `PERKIN`
*/
Route::get('/report-perkin', [ LapPerkin::class, 'index' ])->name('perkinReport.index');
Route::get('/report-perkin/get-data', [ LapPerkin::class, 'getData' ])->name('perkinReport.getData');
Route::get('/report-perkin/pdf', [ LapPerkin::class, 'pdf' ])->name('perkinReport.pdf');

/*
* Route untuk laporan RPD 🌙
*/
Route::get('/per-rpd', [ LapRPD::class, 'index'])->name("rpd.index");
Route::get('/per-rpd/pdf', [ LapRPD::class, 'indexPdf']);
Route::post('/per-rpd/getRPD',[ LapRPD::class, 'getRpd'])->name("rpd.getRPD");

/*
* Route untuk laporan RPD 🌙
*/
Route::get('/analisis', [ LapAnalisis::class, 'index'])->name("laporan.analisis.index");
Route::get('/analisis/pdf', [ LapAnalisis::class, 'indexPdf'])->name("laporan.analisis.indexPdf");
Route::get('/getItemCoa',[ LapAnalisis::class, 'getItemCoa'])->name("laporan.analisis.getItemCoa");
Route::get('/getTOR',[ LapAnalisis::class, 'getTOR'])->name("laporan.analisis.getTOR");
Route::get('/getAnalisis',[ LapAnalisis::class, 'getAnalisis'])->name("laporan.analisis.getAnalisis");
Route::post('/storeAnalisis',[ LapAnalisis::class, 'storeAnalisis'])->name("laporan.analisis.storeAnalisis");
Route::post('/storeAnalisisOperator',[ LapAnalisis::class, 'storeAnalisisOperator'])->name("laporan.analisis.storeAnalisisOperator");
Route::post('/storeTanggapan',[ LapAnalisis::class, 'storeTanggapan'])->name("laporan.analisis.storeTanggapan");

/*
* Route untuk laporan RPD 🌙
*/
Route::get('/subkomponen', [ LapSubkomponen::class, 'index'])->name("laporan.subkomponen.index");
Route::get('/subkomponen/pdf', [ LapSubkomponen::class, 'indexPdf'])->name("laporan.subkomponen.indexPdf");
Route::get('/subkomponen/get/{idunit}/{kd_sumberdana}',[ LapSubkomponen::class, 'getRka' ]);

/*
* Route untuk rekap baru🌙
*/
Route::get('/tahunan', [ LapTahunan::class, 'index'])->name("laporan.tahunan.index");
Route::get('/tahunan/getBaseData', [ LapTahunan::class, 'getBaseData']);
Route::get('/tahunan/getBaseDataBackup', [ LapTahunan::class, 'getBaseDataBackup']);
Route::get('/tahunan/getDataProyeksi', [ LapTahunan::class, 'getDataProyeksi']);
Route::get('/tahunan/pdf', [ LapTahunan::class, 'pdf']);
Route::post('/tahunan/storeRO', [ LapTahunan::class, 'storeRO']);
Route::post('/tahunan/storeProyeksiPenerimaan', [ LapTahunan::class, 'storeProyeksiPenerimaan']);

/*
* Route untuk lampiran RKAT🌙
*/
Route::get('/lampiranRkat', [ RkatLampiran::class, 'index'])->name('rkat.lampiran.index');
Route::get('/lampiranRkat/getData', [ RkatLampiran::class, 'getRkatLampiran'])->name('rkat.lampiran.get');
Route::get('/lampiranRkat/getRekapSemuaUnit', [ RkatLampiran::class, 'getRekapSemuaUnit'])->name('rkat.lampiran.get.semuaunit');
Route::get('/lampiranRkat/getParentSumberdana', [ RkatLampiran::class, 'getParentSumberdana'])->name('rkat.lampiran.get.semuaSd');
Route::get('/lampiranRkat/pdf', [ RkatLampiran::class, 'pdf'])->name('rkat.lampiran.pdf');
Route::get('/lampiranRkat/getCount', [ RkatLampiran::class, 'getCountKegiatan'])->name('rkat.lampiran.getKegiatan');
Route::post('/lampiranRkat/storeModif', [ RkatLampiran::class, 'storeModif'])->name('rkat.lampiran.storeModif');
Route::post('/lampiranRkat/checkData', [ RkatLampiran::class, 'checkModif'])->name('rkat.lampiran.checkData');
Route::get('/lampiranRkat/getModifiedData', [ RkatLampiran::class, 'getModifiedData'])->name('rkat.lampiran.getModifiedData');


/*
* Route untuk laporan pendapatan
*/
Route::get('/pendapatan', [ Pendapatan::class, 'index'])->name('laporan.pendapatan.index');
Route::get('/pendapatan/v2', [ Pendapatan::class, 'index2'])->name('laporan.pendapatan.index2');
Route::get('/pendapatan/get', [ Pendapatan::class, 'get'])->name('laporan.pendapatan.getData');
Route::get('/pendapatan/getRealtime', [ Pendapatan::class, 'getRealtime'])->name('laporan.pendapatan.getDataRealtime');
Route::get('/pendapatan/get-remake', [ Pendapatan::class, 'getRemake'])->name('laporan.pendapatan.getDataRemake');
Route::get('/pendapatan/pdf', [ Pendapatan::class, 'pdf'])->name('laporan.pendapatan.pdf');
/*
* Route untuk laporan pembiayaan
*/
Route::get('/pembiayaan', [ Pembiayaan::class, 'index'])->name('laporan.pembiayaan.index');
Route::get('/pembiayaan/get', [ Pembiayaan::class, 'get'])->name('laporan.pembiayaan.getData');

// laporan proporsi anggaran
Route::get('/proporsianggaran', [ProporsiAnggaran::class, 'index'])->name("proporsi.index");
Route::get('/proporsianggaran/pdf', [ProporsiAnggaran::class, 'pdf'])->name("proporsi.pdf");
Route::get('/proporsianggaran/ProporsiAlokasi/{idunit}', [ProporsiAnggaran::class, 'getProporsiAlokasi']);

// Lembar pengendalian
Route::get('/lembarpengendalian', [ LembarPengendalian::class, 'index'])->name("lembarpengendalian.index");
Route::get('/lembarpengendalian/getHistories', [ LembarPengendalian::class, 'getHistories'])->name("lembarpengendalian.getHistories");
Route::get('/lembarpengendalian/get-realisasi-deleted', [ LembarPengendalian::class, 'getRealisasiDeleted'])->name("lembarpengendalian.getRealisasiDeleted");

// Daya Serap
Route::get('/dayaserap', [ DayaSerap::class, 'index'])->name("dayaserap.index");
Route::get('/dayaserap/get-alokasi', [ DayaSerap::class, 'getAlokasiData'])->name("dayaserap.getAlokasi");
Route::get('/dayaserap/get-backup-alokasi', [ DayaSerap::class, 'getAlokasiBackup'])->name("dayaserap.getAlokasiBackup");

Route::get('/usulan-revisi', [ UsulanRevisi::class, 'index'])->name("laporan.usulanrevisi.index");
Route::get('/usulan-revisi/data', [ UsulanRevisi::class, 'getData'])->name("laporan.usulanrevisi.data");
Route::get('/usulanrevisi/pdf/{idunit}/{kodeSd}', [ UsulanRevisi::class, 'exportPdf'])->name("laporan.usulanrevisi.pdf");

Route::get('/rekap-revisi', [ RekapRevisi::class, 'index'])->name("laporan.rekaprevisi.index");
