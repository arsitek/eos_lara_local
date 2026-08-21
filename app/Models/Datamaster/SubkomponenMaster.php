<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubkomponenMaster extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_keg_master";
    protected $fillable = ["kode_klasifikasi", "klasifikasi", "kode_sub_klasifikasi", "sub_klasifikasi", "kode_keg", "keg", "jenis", "jenis_rab", "tahun"];
}
