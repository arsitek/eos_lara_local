<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Datamaster\AksesMenuUnit;

class MasterUnitApi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_unit_api";

    protected $fillable = [
        "idunit", "nama", "alias", "level_unit", "kode_kategori", "keterangan_kategori",
        "kode_parent", "nama_parent", "alias_parent", "level_unit_parent", "status", "is_add"
    ];
    public function unitApi(){
        return $this->belongsTo(RelasiUnit::class, "idunit", "idunit");
    }
    public function aksesMenuUnit(){
        return $this->hasMany(AksesMenuUnit::class, "idunit", "idunit");
    }
}
