<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komitmen extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_komitmen";
    protected $fillable = ["nip", "nama_pejabat", "minimal_pengeluaran", "maksimal_pengeluaran", "is_active", "jenis"];
    public function sumberDana() {
        return $this->hasMany(RelasiSumberDana::class, 'id_komitmen', 'id')->where("is_deleted", "false");
    }
    public function unitKerja(){
        return $this->hasMany(RelasiUnit::class, 'id_komitmen', 'id')->where("is_deleted", "false");
    }
    public function coa(){
        return $this->hasMany(RelasiCoa::class, 'id_komitmen', 'id')->where("is_deleted", "false");
    }
    public function coaChild(){
        return $this->hasMany(RelasiCoaChild::class, 'id_komitmen', 'id')->where("is_deleted", "false");
    }


}
