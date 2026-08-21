<?php

namespace App\Models\Datamaster;

use App\Models\Datarevisi\BackupRkatDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplikasiRkat extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_duplikasi_rkat";
    protected $fillable = ["opsi","peruntukan","duplikasi_ke","keterangan","is_deleted", "tahun"];
    public function backupDetail(){
        return $this->hasMany(BackupRkatDetail::class, "id_duplikasi", "id");
    }
}
