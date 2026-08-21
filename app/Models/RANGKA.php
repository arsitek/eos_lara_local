<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RANGKA extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "rangka";
    protected $fillable =
    [
        "kd_beban", "nama_beban", "kd_kro", "nama_kro", "kd_ro", "nama_ro", "kd_kp", "nama_kp", "kd_sk", "nama_sk", "ekuivalensi", "kd_ak", "nama_ak", "kd_mak"
    ];
}
