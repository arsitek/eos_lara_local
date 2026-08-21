<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RABKEG;
use App\Models\RABGDG;
use App\Models\RABPER;

class RevisiRpd extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_revisi_rpd";
    protected $fillable = ["id_rab", "rpd_semula", "rpd_menjadi", "jenis"];
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
