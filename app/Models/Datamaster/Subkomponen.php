<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subkomponen extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_keg";
    protected $with     = ["ikv"];
    protected $fillable = ["kode_ikv", "kode_keg", "rincian_kegiatan", "jenis_rab", "tahun", "is_deleted"];

    public function ikv() {
        return $this->belongsTo(Ikv::class, "kode_ikv", "kode_ikv");
    }
}
