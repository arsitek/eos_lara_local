<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisaSaldoValidasi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_saldo_validasi";
    protected $fillable = ["idunit", "sd", "kode_ikk", "kode_komponen", "kode_ss", "id_rekat", "kode_coa", "jenis", "sisa_saldo", "jenis_saldo", "tahun"];

}
