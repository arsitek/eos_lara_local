<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IkvSBM extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_ikv_sbm";
    protected $fillable = ["kode_ro_sbm", "kode_ikv_sbm", "ikv_sbm", "tahun", "is_deleted"];

    public function subkomponenSBM(){
        return $this->hasMany(SubkomponenSBM::class, 'kode_ikv_sbm', 'kode_ikv_sbm');
    }
    public function roSBM(){
        return $this->belongsTo(RoSBM::class, "kode_ro_sbm", "kode_ro_sbm");
    }
}
