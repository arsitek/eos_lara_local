<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiSumberdanaBpp extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_sumberdana_bpp";
    protected $fillable = ["kd_sumberdana", "kd_bendahara"];
    protected $timestamp = false;
}
