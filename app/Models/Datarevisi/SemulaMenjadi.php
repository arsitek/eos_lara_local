<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemulaMenjadi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_semula_menjadi';
    protected $fillable = [ "id_rab", "jenis_rab", "jenis_validasi", "jenis_revisi", "tor", "should_verify_by", "verify_by",
        "jumlah_semula", "jumlah_menjadi", "status", "spek_semula", "spek_semula_json", "spek_menjadi", "spek_menjadi_json", "relasi_pergeseran", "is_deleted"];
}
