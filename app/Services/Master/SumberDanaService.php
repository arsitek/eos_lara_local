<?php
namespace App\Services\Master;

use App\Models\SumberDana;
use Illuminate\Support\Collection;

class SumberDanaService {
    public function getAllSumberDana(?int $tahunAngka ): SumberDana|Collection {
        $sumberDana = SumberDana::where(["is_deleted" => "false", "is_show" => "true"])->where(["tahun" => $tahunAngka])->orderBy("kd_sumberdana")->get();
        return $sumberDana;
    }
}