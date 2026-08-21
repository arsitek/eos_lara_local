<?php

namespace App\Models\Datamaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ikv extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_ikv";
    protected $with  = ["ro"];
    protected $fillable = [
        "tahun",
        "kode_ikk",
        "kode_ikv",
        "ikv",
    ];
    public function subkomponen(){
        return $this->hasMany(Subkomponen::class, 'kode_ikv', 'kode_ikv');
    }
    public function ro(){
        return $this->belongsTo(Ro::class, "kode_ikk", "kode_ikk");
    }
}
