<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KroSBM extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sasaran_sbm";
    protected $fillable = [ "kode_ss_sbm", "sasaran_sbm", "tahun", "is_deleted" ];

    public function roSBM(){
        return $this->hasMany(RoSBM::class, 'kode_ss_sbm', 'kode_ss_sbm');
    }
}
