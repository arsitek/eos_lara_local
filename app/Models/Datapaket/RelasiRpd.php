<?php

namespace App\Models\Datapaket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiRpd extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_relasi_paket_rpd';
    protected $fillable = ["id_paket", "rpd"];
    public $timestamps = false;
    public function paket() {
        return $this->belongsTo(Paket::class, 'id_paket', 'id');
    }
}
