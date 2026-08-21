<?php

namespace App\Models;

use App\Models\Datacenter\AksesPrioritas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggunaAnggaran extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_pengguna_anggaran";
    protected $fillable = ["idunit", "id_sumberdana", "is_deleted", "tahun"];
    public function unitApi(){
        return $this->belongsTo(MasterUnitApi::class, "idunit", "idunit");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "id_sumberdana", "id");
    }
    public function aksesrkat(){
        return $this->hasMany(AksesRkat::class, 'idunit', 'idunit');
    }
    public function aksesprioritas(){
        return $this->hasMany(AksesPrioritas::class, 'idunit', 'idunit');
    }

}
