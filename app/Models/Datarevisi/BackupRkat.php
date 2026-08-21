<?php

namespace App\Models\Datarevisi;

use App\Models\Datamaster\Subkomponen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_backup_rkat";
    protected $fillable = ["id_rekat", "sd", "idunit", "kode_ss", "kode_ikk", "kode_ikv", "kode_keg", "id_duplikasi", "tor","sub_judul", "unit_pelaksana",
        "tahun", "is_revisi_keg", "is_revisi_kro", "is_deleted"];
    public function detail(){
        return $this->hasMany(BackupRkatDetail::class, "id_rekat", "id_rekat");
    }
    public function subkomponen(){
        return $this->hasMany(Subkomponen::class, "kode_keg", "kode_keg");
    }
}
