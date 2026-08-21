<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana8 extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_sumberdana_8";
    protected $fillable = ["kd_sumberdana", "sumberdana", "tahun"];

    public function child10(){
        return $this->hasMany(SumberDana10::class, "kd_parent", "kd_sumberdana" );
    }
}
