<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisiCoa extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_revisi_coa";
    protected $fillable = ["id_rab", "coa_semula", "coa_menjadi", "jenis"];

    public function rab(){
        if ($this->jenis == "operasional") {
            return $this->belongsTo(RABKEG::class, "id_rab", "id");
        } else if ( $this->jenis == "sarana") {
            return $this->belongsTo(RABPER::class, "id_rab", "id");
        } else {
            return $this->belongsTo(RABGDG::class, "id_rab", "id");
        }
    }
}
