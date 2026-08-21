<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubkomponenSBM extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_keg_sbm";
    protected $fillable = ["kode_ikv_sbm", "kode_keg_sbm", "keg_sbm", "satuan", "durasi", "jumlah_biaya", "limit", "ket", "tahun", "is_deleted"];

    public function ikvSBM() {
        return $this->belongsTo(IkvSBM::class, "kode_ikv_sbm", "kode_ikv_sbm");
    }
}
