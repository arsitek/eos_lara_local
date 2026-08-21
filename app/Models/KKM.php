<?php

namespace App\Models;

use App\Models\Datamaster\Ro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KKM extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_kkm";
    protected $fillable = [ "kode_ikk","kk_mendikbud","kk_menkeu","satuan","bobot","tahun" ];
    public function ro(){
        return $this->belongsTo(Ro::class, "kode_ikk", "kode_ikk");
    }
}
