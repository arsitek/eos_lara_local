<?php

namespace App\Models\Datacreator;

use App\Models\Rekat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariabelAnalisis extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = "tb_variabel_analisis";
    protected $fillable = [ "id_rekat", "kendala", "tujuan", "resiko", "alternatif", "hasil", "dampak",
        "tanggapan_kendala", "tanggapan_tujuan", "tanggapan_resiko", "tanggapan_alternatif", "tanggapan_hasil", "tanggapan_dampak" ];
    protected $with     = [ "rekat" ];

    public function rekat(){
        return $this->belongsTo(Rekat::class, "id_rekat", "id");
    }
}
