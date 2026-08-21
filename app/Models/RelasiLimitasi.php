<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiLimitasi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_limitasi";
    protected $fillable = ["id_limitasi", "id_komitmen"];
    public $timestamps  = false;
    public function limitasi() {
        return $this->belongsTo(LimitasiAnggaran::class, 'id_limitasi', 'id');
    }
}
