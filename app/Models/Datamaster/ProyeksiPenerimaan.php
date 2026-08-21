<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyeksiPenerimaan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_proyeksi_penerimaan";
    protected $fillable = ["unit_kerja","kd_sumberdana", "proyeksi_penerimaan", "tahun"];
}
