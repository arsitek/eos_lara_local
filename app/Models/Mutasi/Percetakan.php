<?php

namespace App\Models\Mutasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Percetakan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_mutasi_percetakan";
    protected $fillable = ["id_rekat", "id_rab", "jenis", "jumlah_tagihan", "tahun", "is_deleted"];
}
