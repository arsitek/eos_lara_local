<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana6 extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_sumberdana_6";
    protected $fillable = ["kd_sumberdana", "sumberdana", "tahun"];

    public function child8(){
        return $this->hasMany(SumberDana8::class, "kd_parent", "kd_sumberdana" );
    }
}
