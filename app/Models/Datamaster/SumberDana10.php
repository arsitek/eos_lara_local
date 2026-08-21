<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana10 extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_sumberdana_10";
    protected $fillable = ["kd_sumberdana", "sumberdana", "tahun"];
}
