<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Datamaster\RoRkat;

class KroRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sasaran_rkat";
    protected $fillable = [ "kode_ss_rkat", "sasaran_rkat", "tahun" ];

    public function roRkat(){
        return $this->hasMany(RoRkat::class, 'kode_ss_rkat', 'kode_ss_rkat');
    }
}
