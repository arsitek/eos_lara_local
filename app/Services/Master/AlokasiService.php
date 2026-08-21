<?php
namespace App\Services\Master;

use App\Models\Alokasi;
use App\Models\SumberDana;
use Illuminate\Support\Facades\DB;

class AlokasiService {
    //*** Control pagu  */
    public function validatePagu( $paguAlokasi, $paguTambahan, $kodeSd, $idunit, $tahun, $tahunAngka) {
        if ( !$tahun || !$tahunAngka || !$idunit || !$kodeSd ) {
            throw new \Exception("Parameter tidak lengkap untuk validasi pagu.");
        }

        if ($paguAlokasi < 0 || $paguTambahan < 0) {
            throw new \Exception("Pagu tidak boleh kurang dari 0.");
        }

        // Cek pagu maksmium
        $defaultWhereSd   = [ "is_deleted" => "false", "tahun" => $tahunAngka, "kd_sumberdana" => $kodeSd, "is_show" => "true" ];
        $totalPagu = SumberDana::where($defaultWhereSd)->sum(DB::connection('sirekat')->select('COALESCE(pagu_alokasi,0) + COALESCE(pagu_tambahan,0)'));

        // cek pagu terpakai
        $defaultWhereAlokasi = [ "is_deleted" => "false", "tahun" => $tahun, "kd_sumberdana" => $kodeSd ];
        $totalAlokasi        = Alokasi::where($defaultWhereAlokasi)->where("unit_kerja", "!=", $idunit)
                                ->sum(DB::connection('sirekat')->select('COALESCE(pagu,0) + COALESCE(pagu_tambahan,0)'));
        $totalPerubahan      = $paguTambahan + $paguAlokasi;

        // Cek apakah total pagu melebihi pagu yang ada
        if ( $totalPagu < ( $totalAlokasi + $totalPerubahan ) ) {
            throw new \Exception("Pagu tidak mencukupi untuk penambahan ini.");
        }
    }
}
