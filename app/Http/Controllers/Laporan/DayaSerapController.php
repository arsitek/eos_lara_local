<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\DuplikasiRkat;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DayaSerapController extends Controller
{
    public function index(): ViewContract
    {
        ['tahun' => $tahun, 'tahunAngka' => $tahunAngka] = getTahunData(); // from Helper/rekat.php
        $dataBackup = DuplikasiRkat::select('id', 'keterangan')->orderBy('id', 'desc')->get();

        return view('content.laporan.DAYASERAP.index', compact('dataBackup'));
    }

    public function getAlokasiData(): JsonResponse
    {
        try {
            ['tahun' => $tahun, 'tahunAngka' => $tahunAngka] = getTahunData();
            $alokasi = DB::connection('sirekat')->select("SELECT sd.sumberdana, alokasi.kd_sumberdana, alokasi.unit_kerja, unit.nama, COALESCE(alokasi.pagu, 0) + COALESCE(alokasi.pagu_tambahan, 0) AS pagu
                FROM tb_alokasi alokasi
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = alokasi.kd_sumberdana AND sd.is_show = 'true' AND sd.is_deleted = 'false' AND sd.tahun = ?
                INNER JOIN tb_unit_api unit ON unit.idunit = alokasi.unit_kerja
                WHERE alokasi.is_deleted = 'false' AND alokasi.tahun = ? ORDER BY sd.kd_sumberdana, unit.idunit", [$tahunAngka, $tahun]);
            $realisasi = getRekapRealisasi($tahun, 'unit');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data',
                'data' => [
                    'alokasi' => $alokasi,
                    'realisasi' => $realisasi,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAlokasiBackup(Request $req): JsonResponse
    {
        try {
            ['tahun' => $tahun, 'tahunAngka' => $tahunAngka] = getTahunData();

            // Manual validation to ensure sirekat connection is used
            $idBackup = $req->idBackup;
            if (!$idBackup || !is_numeric($idBackup)) {
                return response()->json(['success' => false, 'message' => 'ID Backup harus diisi dan berupa angka'], 422);
            }

            // Get backup info to get the correct year
            $backupInfo = DB::connection('sirekat')->select("SELECT id, keterangan, tahun FROM tb_duplikasi_rkat WHERE id = ?", [$idBackup]);
            if (empty($backupInfo)) {
                return response()->json(['success' => false, 'message' => 'ID Backup tidak ditemukan'], 422);
            }
            $backupTahun = $backupInfo[0]->tahun;

            // Extract year number from backup tahun (e.g., "Definitif_2026" -> "2026")
            $backupTahunAngka = $backupTahun;
            if (strpos($backupTahun, '_') !== false) {
                $parts = explode('_', $backupTahun);
                $backupTahunAngka = end($parts);
            }

            // Debug logging
            Log::info('getAlokasiBackup - idBackup: ' . $idBackup);
            Log::info('getAlokasiBackup - session tahun: ' . $tahun . ', session tahunAngka: ' . $tahunAngka);
            Log::info('getAlokasiBackup - backup tahun: ' . $backupTahun . ', backup tahunAngka: ' . $backupTahunAngka);

            // Check if tb_backup_alokasi has data for this idBackup
            $checkAlokasi = DB::connection('sirekat')->select("SELECT COUNT(*) as count FROM tb_backup_alokasi WHERE id_duplikasi = ?", [$idBackup]);
            Log::info('getAlokasiBackup - tb_backup_alokasi count for idBackup ' . $idBackup . ': ' . $checkAlokasi[0]->count);

            // Check if tb_sumberdana has data for this year
            $checkSumberdana = DB::connection('sirekat')->select("SELECT COUNT(*) as count FROM tb_sumberdana WHERE tahun = ?", [$backupTahunAngka]);
            Log::info('getAlokasiBackup - tb_sumberdana count for tahun ' . $backupTahunAngka . ': ' . $checkSumberdana[0]->count);

            $alokasiBackup = DB::connection('sirekat')->select("SELECT sd.sumberdana, ba.*, unit.nama FROM tb_backup_alokasi ba
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = ba.kode_sd AND sd.is_show = 'true' AND sd.is_deleted = 'false'
                INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit WHERE ba.id_duplikasi = ? ORDER BY sd.kd_sumberdana, ba.idunit",
                [$idBackup]);

            Log::info('getAlokasiBackup - alokasiBackup count: '.count($alokasiBackup));

            $query = ' SELECT unit.nama AS nama_unit, unit.idunit AS unit_kerja_rkt,
                        sd.kd_sumberdana, sd.sumberdana,
                        SUM( COALESCE(backupRkatDet.jumlah_amprahan, 0) + COALESCE(backupRkatDet.jumlah_realisasi, 0) ) AS jumlah_amprah
                    FROM tb_backup_rkat backupRkat
                    INNER JOIN tb_backup_rkat_detail backupRkatDet ON backupRkatDet.id_rekat = backupRkat.id_rekat
                    INNER JOIN sumberdana sd ON sd.kd_sumberdana = backupRkat.sd
                    INNER JOIN tb_unit_api unit ON unit.idunit = backupRkat.idunit
                    WHERE ( backupRkat.id_duplikasi = ? AND backupRkatDet.id_duplikasi = ? )
                AND backupRkat.tahun = ?
                GROUP BY unit.idunit, backupRkat.sd';
            $realisasi = getBaseData($query, $backupTahun, $backupTahunAngka, null, null, [$idBackup, $idBackup, $backupTahun]);

            Log::info('getAlokasiBackup - realisasi count: '.(is_array($realisasi) ? count($realisasi) : 'not array'));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data',
                'data' => [
                    'alokasi' => $alokasiBackup,
                    'realisasi' => $realisasi,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
