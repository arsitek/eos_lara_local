<?php

namespace App\Models\Datacenter;

use App\Models\MasterUnitApi;
use App\Models\PenggunaAnggaran;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AksesPrioritas extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_akses_prioritas";
    protected $fillable = ["id_sumberdana", "idunit", "status", "tahun"];

    public function penggunaanggaran(){
        return $this->belongsTo(PenggunaAnggaran::class, "idunit", "idunit");
    }
    public function unit(){
        return $this->belongsTo(MasterUnitApi::class, "idunit", "idunit");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "id_sumberdana", "id");
    }
}
