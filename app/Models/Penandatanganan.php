<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penandatanganan extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_penandatangan";
    protected $fillable =
    [
        "PP_TPT", "PP_TGL", "PP_REKTOR", "PP_JBT", "PP_NIP", "PK_NAMA", "PK_JBT", "PK_NIP",
        "unit_kerja", "operator", "tahun"
    ];

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_kerja', 'idunit')->where("status", 1);
    }
}
