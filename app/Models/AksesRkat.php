<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AksesRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_akses_rkat";
    protected $fillable = ["idunit", "id_sumberdana", "status"];
    public function penggunaanggaran(){
        return $this->belongsTo(PenggunaAnggaran::class, "idunit", "idunit");
    }
    public function unit(){
        return $this->belongsTo(MasterUnit::class, "idunit", "idunit");
    }
    public function rekat(){
        return $this->belongsTo(Rekat::class, "unit_kerja", "idunit");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "id_sumberdana", "id")->where("is_deleted", "false");
    }
}
