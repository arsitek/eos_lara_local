<?php
namespace App\Services\Master;

use App\Models\Alokasi;
use Illuminate\Support\Facades\DB;

class MenuService {
    const CHUNK_SIZE = 100;
    public function duplicate( string $tahun, string $bulanIni ): array {
        $chunkSize      = self::CHUNK_SIZE; // Process data in chunks to avoid memory issues
        $processedCount = 0;
            
        // Use transaction to ensure data integrity
        DB::connection('sirekat')->select(function() use ($tahun, $bulanIni, $chunkSize, &$processedCount) {
            // Only delete for this month because we are duplicating monthly using current month as reference 
            
            // Fetch and process data in chunks
            Alokasi::where([ "is_deleted" => "false", "tahun" => $tahun ])
                ->chunk($chunkSize, function ($dataChunk) use ($bulanIni, &$processedCount) {
                    $insertData = [];
                    foreach( $dataChunk as $item ) {
                        $insertData[] = [
                            "kode_sd"       => $item->kd_sumberdana,
                            "jenis"         => $item->jenis,
                            "idunit"        => $item->unit_kerja,
                            "pagu_tambahan" => $item->pagu_tambahan,
                            "pagu"          => $item->pagu,
                            "pagu_relokasi" => $item->pagu_relokasi,
                            "tahun"        => $item->tahun,
                            "is_deleted"   => $item->is_deleted,
                            "id_duplikasi" => 69,
                            "created_at"  => now(),
                            "updated_at"  => now()
                        ];
                    }
                    
                    // Insert this chunk's data
                    DB::connection('sirekat')->select("tb_backup_alokasi")->insert($insertData);
                    $processedCount += count($dataChunk);
                });
        });
        return [ "processed" => $processedCount ];
    }
}