<?php

namespace App\Models\Datacreator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Datamaster\SubkomponenMaster;
use App\Models\Datamaster\Ikv;
use App\Models\Datamaster\Ro;
use App\Models\Datamaster\Kro;

class RelasiMasterIku extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_relasi_iku_rekat";
    protected $fillable = ["id_rekat", "kode_ss", "kode_iku", "kode_ikv", "kode_keg"];
    public $timestamps = false;

    public function subkomponenMaster(){
        return $this->belongsTo(SubkomponenMaster::class, 'kode_keg', 'kode_keg');
    }

    public function ikv(){
        return $this->belongsTo(Ikv::class, 'kode_ikv', 'kode_ikv');
    }

    public function ro(){
        return $this->belongsTo(Ro::class, 'kode_iku', 'kode_ikk');
    }

    public function kro(){
        return $this->belongsTo(Kro::class, 'kode_ss', 'kode_ss');
    }
}
