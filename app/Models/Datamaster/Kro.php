<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kro extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sasaran";
    protected $fillable = [
        "tahun",
        "kode_ss",
        "sasaran_program",
    ];
    public function ro(){
        return $this->hasMany(Ro::class, 'kode_ss', 'kode_ss');
    }
    
}
