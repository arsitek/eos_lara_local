<?php

namespace App\Models\Datacenter;

use App\Models\MasterUnit;
use App\Models\MasterUnitApi;
use App\Models\Pengguna;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiPimpinanHistori extends Model {
    protected $connection = 'sirekat';
    use HasFactory;

    protected $table = 'tb_alokasi_pimpinan_histori';

    protected $fillable = [
        'idunit',
        'kd_sumberdana',
        'tahun',
        'pagu_awal',
        'pagu_perubahan',
        'perubahan_ke',
        'diubah_oleh',
    ];

    public function pengguna() {
        return $this->belongsTo(Pengguna::class, 'diubah_oleh', 'nip');
    }
    public function unitApi(){
        return $this->belongsTo(MasterUnitApi::class, "idunit", "idunit");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "kd_sumberdana", "kd_sumberdana")->where('is_deleted', 'false')->where('is_show', 'true');
    }
}