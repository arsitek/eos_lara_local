<?php

namespace App\Models\Datarevisi;

use App\Models\Rekat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidasiKegiatan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_validasi_kegiatan";
    protected $fillable = ["idunit", "id_item_coa", "jenis", "jenis_rab","id_rekat", "jumlah_biaya", "tahun", "jenis_revisi"];
    public function rekat(){
        return $this->belongsTo(Rekat::class, 'id_rekat', 'id');
    }
}
