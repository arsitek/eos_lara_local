<?php

namespace App\Models;

use App\Models\Datamaster\Ro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class Perkin extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_perkin";
    protected $fillable = [
        "unit_kerja", "kode_ikk", "kk_mendikbud", "kk_menkeu", "satuan", "capaian","tw_1", "tw_2", "tw_3", "tw_4", "bobot",
        "status", "verifikasi_tim", "verifikasi_pimpinan", "tanggapan", "tahun", "is_deleted"
    ];
    protected $with = ["unit", "unitApi", "ro"];
    public function ro(){
        return $this->belongsTo(Ro::class, "kode_ikk", "kode_ikk");
    }
    public function unit() {
        // where status = 1
        return $this->belongsTo(MasterUnit::class, "unit_kerja", "idunit")->where("status", 1);
    }
    public function unitApi() {
        // where status = 1
        return $this->belongsTo(MasterUnitApi::class, "unit_kerja", "idunit")->where("status", 1);
    }
}
