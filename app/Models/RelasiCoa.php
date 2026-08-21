<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiCoa extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_coa";
    protected $fillable = ["coa_parent", "id_komitmen", "tahun", "is_deleted"];
    public function coa() {
        return $this->belongsTo(CoaApi::class, 'coa_parent', 'coa_parent');
    }
}
