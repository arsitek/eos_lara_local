<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiSumberDanaUser extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_relasi_sumberdana_user";
    protected $fillable = ["id_sumberdana", "id_user"];
    protected $timestamp = false;
    public function sumberDanaDetail() {
        return $this->belongsTo(SumberDana::class, 'id_sumberdana', 'id');
    }
}
