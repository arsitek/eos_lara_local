<?php

namespace App\Models\Datarevisi;

use App\Models\Rekat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisiKro extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_revisi_kro";
    protected $fillable = ["id_user", "status", "idunit", "kd_sumberdana", "id_rekat",
         "kd_kro", "id_rekat_menjadi", "kd_kro_menjadi", "jumlah_biaya", "tahun"];
    public function rekatMenjadi(){
        return $this->belongsTo(Rekat::class, 'id_rekat_menjadi', 'id');
    }
    public function rekatSebelum(){
        return $this->belongsTo(Rekat::class, 'id_rekat', 'id');
    }
}
