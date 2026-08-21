<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoSBM extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_iku_sbm";
    protected $fillable = [ "kode_ss_sbm", "kode_ro_sbm", "ro_sbm", "tahun", "is_deleted" ];
    public function ikvSBM(){
        return $this->hasMany(IkvSBM::class, 'kode_ro_sbm', 'kode_ro_sbm');
    }
    public function kroSBM(){
        return $this->belongsTo(KroSBM::class, 'kode_ss_sbm', 'kode_ss_sbm');
    }
}
