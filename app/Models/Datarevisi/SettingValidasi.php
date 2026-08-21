<?php

namespace App\Models\Datarevisi;

use App\Models\CoaApi;
use App\Models\MasterUnitApi;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingValidasi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_setting_validasi';
    protected $fillable = ["kd_sumberdana", "idunit", "coa_parent", "is_deleted", "tahun", "nilai", "persentase"];

    public function unit() {
        return $this->belongsTo(MasterUnitApi::class, 'idunit', 'idunit');
    }
    public function sumberdana() {
        return $this->belongsTo(SumberDana::class, 'kd_sumberdana', 'kd_sumberdana');
    }
    public function coa() {
        return $this->belongsTo(CoaApi::class, 'coa_parent', 'coa_parent');
    }
}
