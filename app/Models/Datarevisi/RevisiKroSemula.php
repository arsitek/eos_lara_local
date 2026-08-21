<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisiKroSemula extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_revisi_kro_semula";
    protected $fillable = ["id", "id_rekat", "sd", "idunit","kode_keg", "status", "sub_judul", "unit_pelaksana",
        "tahun", "is_revisi_keg", "is_revisi_kro", "is_deleted"];
    public function rab(){
        return $this->hasMany(RevisiKroSemulaDetail::class, "id_rekat", "id_rekat");
    }
}
