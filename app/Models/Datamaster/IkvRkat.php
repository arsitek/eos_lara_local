<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IkvRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_ikv_rkat";
    protected $fillable = ["kode_ro_rkat", "kode_ikv_rkat", "ikv_rkat", "tahun"];

    public function subkomponenRkat(){
        return $this->hasMany(SubkomponenRkat::class, 'kode_ikv_rkat', 'kode_ikv_rkat');
    }
    public function roRkat(){
        return $this->belongsTo(RoRkat::class, "kode_ro_rkat", "kode_ro_rkat");
    }
}
