<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_iku_rkat";
    protected $fillable = ["kode_ss_rkat", "kode_ro_rkat", "ro_rkat", "tahun"];

    public function ikvRkat(){
        return $this->hasMany(IkvRkat::class, 'kode_ro_rkat', 'kode_ro_rkat');
    }
    public function kroRkat(){
        return $this->belongsTo(KroRkat::class, 'kode_ss_rkat', 'kode_ss_rkat');
    }
}
