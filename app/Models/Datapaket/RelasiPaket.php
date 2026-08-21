<?php

namespace App\Models\Datapaket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiPaket extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_relasi_paket";
    protected $fillable = ["id_paket", "id_rab", "jenis_rab","is_deleted"];
    public function paket(){
        return $this->belongsTo(Paket::class, "id_paket", "id");
    }
}
