<?php

namespace App\Models;

use App\Models\Datacreator\VariabelAnalisis;
use App\Models\Datamaster\AksesMenuUnit;
use App\Models\Datamaster\Subkomponen;
use App\Models\Datamaster\SubkomponenMaster;
use App\Models\Datacreator\RelasiMasterIku;
use App\Models\Datapaket\RelasiPaket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RABKEG;
use App\Models\RABGDG;
use App\Models\RABPER;
class Rekat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_rekat";
    protected $fillable = [
        "unit_kerja", "tor", "kd_rk",
        "rencana_pelaksanaan", "tahun", "sub_judul", "unit_pelaksana",
        "sd", "tahun", "is_deleted", "prioritas"
    ];
    public function unit(){
        // where status = 1
        return $this->belongsTo(MasterUnit::class, "unit_kerja", "idunit")->where("status", 1);
    }
    public function rabkeg(){
        return $this->hasMany(RABKEG::class, "id_rekat", "id");
    }
    public function rabper(){
        return $this->hasMany(RABPER::class, "id_rekat", "id");
    }
    public function rabgdg(){
        return $this->hasMany(RABGDG::class, "id_rekat", "id");
    }
    public function rencana(){
        return $this->hasMany(RencanaPelaksanaan::class, "id_rekat", "id");
    }
    public function aksesrkat(){
        return $this->hasMany(AksesRkat::class, "idunit", "unit_kerja")->where('status', 1);
    }
    public function unitApi(){
        // where status = 1
        return $this->belongsTo(MasterUnitApi::class, "unit_kerja", "idunit")->where("status", 1);
    }
    public function subkomponen(){
        return $this->belongsTo(Subkomponen::class, 'kd_rk', 'kode_keg');
    }
    public function subkomponenMaster(){
        return $this->belongsTo(SubkomponenMaster::class, 'kd_rk', 'kode_keg');
    }
    public function relasiMasterIku(){
        return $this->hasOne(RelasiMasterIku::class, 'id_rekat', 'id');
    }
    public function analisis(){
        return $this->hasOne(VariabelAnalisis::class, "id_rekat", "id");
    }
    public function aksesMenuUnit(){
        return $this->hasMany(AksesMenuUnit::class, "idunit", "unit_kerja");
    }
    public function sumberdana(){
        return $this->hasOne(SumberDana::class, "kd_sumberdana", "sd");
    }
}
