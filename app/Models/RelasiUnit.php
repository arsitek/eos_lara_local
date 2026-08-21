<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiUnit extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_unit";
    protected $fillable = ["idunit", "id_komitmen", "tahun", "is_deleted"];

    public function unitDetail(){
        return $this->belongsTo(MasterUnitApi::class, "idunit", "idunit")->select('idunit', 'nama')->where("status", 1);
    }
}
