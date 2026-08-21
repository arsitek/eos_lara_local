<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alokasi extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_alokasi';
    protected $fillable = ["kd_sumberdana","jenis","unit_kerja","nama_unit","pagu","pagu_tambahan","pagu_relokasi","tahun", "is_deleted"];
    public function unit(){
        return $this->belongsTo(MasterUnit::class, "idunit", "unit_kerja");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "kd_sumberdana", "kd_sumberdana");
    }
    public function penggunaanggaran(){
        return $this->sumberdana->penggunaanggaran();
    }
}
