<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodefikasiApi extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_kodefikasi_api";
    protected $fillable = ["kode","kode_parent","nama"];
}
