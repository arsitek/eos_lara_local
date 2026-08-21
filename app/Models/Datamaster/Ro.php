<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ro extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_iku";
    protected $with  = ["kro"];
    protected $fillable = [
        "tahun",
        "kode_ss",
        "kode_ikk",
        "indikator_kinerja_kegiatan",
    ];
    public function ikv(){
        return $this->hasMany(Ikv::class, 'kode_ikk', 'kode_ikk');
    }
    public function kro(){
        return $this->belongsTo(Kro::class, 'kode_ss', 'kode_ss');
    }
}
