<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubkomponenRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_keg_rkat";
    protected $fillable = ["kode_ikv_rkat", "kode_keg", "kode_keg_rkat", "keg_rkat"];

    public function ikvRkat() {
        return $this->belongsTo(IkvRkat::class, "kode_ikv_rkat", "kode_ikv_rkat");
    }
    public function subkomponen() {
        return $this->belongsTo(Subkomponen::class, "kode_keg", "kode_keg");
    }
}
