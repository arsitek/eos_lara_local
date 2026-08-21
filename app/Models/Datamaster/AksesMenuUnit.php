<?php

namespace App\Models\Datamaster;

use App\Models\MasterUnitApi;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AksesMenuUnit extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_akses_menu";
    protected $fillable = ["id_menu", "idunit", "status", "tahun", "created_at", "updated_at" ];

    public function unitApi(){
        return $this->belongsTo(MasterUnitApi::class, "idunit", "idunit");
    }

    public function menu(){
        return $this->belongsTo(Menu::class, "id_menu", "id_menu");
    }
}
