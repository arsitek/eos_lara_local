<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisaSaldo extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_saldo_revisi_kro";
    protected $fillable = ["idunit", "sd", "status", "sisa_saldo"];
}
