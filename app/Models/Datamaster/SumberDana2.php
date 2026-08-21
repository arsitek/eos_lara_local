<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana2 extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_sumberdana_2";
    protected $fillable = ["kd_sumberdana", "sumberdana", "tahun"];

    public function child4(){
        return $this->hasMany(SumberDana4::class, "kd_parent", "kd_sumberdana" );
    }
}
