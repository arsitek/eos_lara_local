<?php

namespace App\Services\Master;

use App\Models\Rekat;

class RekatService {
    public function getRekatById(?string $idRekat): Rekat|array {
        $data = Rekat::where(["is_deleted" => "false", "id" => $idRekat])->first();
        return empty($data) ? [] : $data;
    }
}