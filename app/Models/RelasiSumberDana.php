<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiSumberDana extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_sumberdana";
    protected $fillable = ["id_sumberdana", "id_komitmen", "tahun", "is_deleted"];

    public function sumberdana() {
        return $this->belongsTo(SumberDana::class, 'id_sumberdana', 'id');
    }
}
