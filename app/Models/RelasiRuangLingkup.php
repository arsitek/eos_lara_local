<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiRuangLingkup extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_relasi_ruanglingkup";
    protected $fillable = ["id_ruanglingkup", "id_komitmen"];
    protected $timestamp = false;
    public function ruanglingkup() {
        return $this->belongsTo(RuangLingkup::class, 'id_ruanglingkup', 'id');
    }
}
