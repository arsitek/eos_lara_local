<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RABKEG;
use App\Models\RABGDG;
use App\Models\RABPER;

class Revisi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_rekat";
    protected $fillable = [
        "id_rekat","unit_kerja", "sasaran_program", "indikator_kinerja_kegiatan", "kd_keg", 
        "rincian_kegiatan", "tor", "kd_rk", "rincian_komponen", "tahun", "verifikasi_tim", "verifikasi_pimpinan", "tanggapan","id_sub_judul", "sub_judul", 
        "unit_pelaksana","rpd", "tahun", "is_revisi"
    ];
    public function unit(){
        // where status = 1
        return $this->belongsTo(MasterUnit::class, "unit_kerja", "idunit")->where("status", 1);
    }
}
