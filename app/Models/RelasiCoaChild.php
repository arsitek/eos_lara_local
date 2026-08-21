<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiCoaChild extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_coa_child";
    protected $fillable = ["coa_child", "id_komitmen", "min_anggaran", "max_anggaran", "tahun", "is_deleted"];
    public function coa() {
        return $this->belongsTo(CoaApi::class, 'coa', 'coa');
    }
}
