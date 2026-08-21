<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usul extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_usul";
    protected $fillable = [
        "sasaran_program", "indikator_kinerja_kegiatan", "rincian_kegiatan","kriteria", "verifikasi_tim", "verifikasi_pimpinan", "tanggapan"
    ];
}
