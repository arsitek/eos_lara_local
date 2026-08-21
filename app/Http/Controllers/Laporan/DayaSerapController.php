<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Datamaster\DuplikasiRkat;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            
            // Check if idBackup exists in sirekat connection
            $exists = DB::connection('sirekat')->select("SELECT COUNT(*) as count FROM tb_duplikasi_rkat WHERE id = ?", [$idBackup]);
            if (empty($exists) || $exists[0]->count == 0) {
                return response()->json(['success' => false, 'message' => 'ID Backup tidak ditemukan'], 422);
            }
            
            // Debug logging
            Log::info('getAlokasiBackup - idBackup: ' . $idBackup);
            Log::info('getAlokasiBackup - tahun: ' . $tahun . ', tahunAngka: ' . $tahunAngka);
            
            $alokasiBackup = DB::connection('sirekat')->select("SELECT sd.sumberdana, ba.*, unit.nama FROM tb_backup_alokasi ba
                INNER JOIN tb_sumberdana sd ON sd.kd_sumberdana = ba.kode_sd AND sd.is_show = 'true' AND sd.is_deleted = 'false' AND sd.tahun = ?
                INNER JOIN tb_unit_api unit ON unit.idunit = ba.idunit WHERE ba.id_duplikasi = ? ORDER BY sd.kd_sumberdana, ba.idunit",
                [$tahunAngka, $idBackup]);
            
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
            $realisasi = getBaseData($query, $tahun, $tahunAngka, null, null, [$idBackup, $idBackup, $tahun]); // 2 null parameter karena tidak perlu fitler unit dan sumber dana
            
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
