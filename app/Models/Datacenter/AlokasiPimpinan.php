<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiPimpinan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;

    protected $table = 'tb_alokasi_pimpinan';

    protected $fillable = [
        'kd_sumberdana',
        'sumberdana',
        'idunit',
        'pagu',
        'tahun'
    ];
}
