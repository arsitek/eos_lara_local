<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RencanaPelaksanaan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_rencana_pelaksanaan";
    protected $fillable = ["id_rekat", "rencana_pelaksanaan"];
    public function rencana(){
        return $this->belongsTo(Rekat::class, "id_rekat", "id");
    }
}
