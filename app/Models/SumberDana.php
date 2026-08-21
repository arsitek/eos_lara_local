<?php

namespace App\Models;

use App\Models\Datamaster\ProyeksiPenerimaan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberDana extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sumberdana";
    protected $fillable = ["id", "kd_sumberdana", "sumberdana", "pagu_alokasi", "pagu_tambahan", "jenis", "pagu_tahun_lalu", "realisasi", "proyeksi", "tahun",
     "is_show", "is_deleted", "catatan"];
    public function penggunaanggaran(){
        return $this->hasMany(PenggunaAnggaran::class, "id_sumberdana", "id")->where("is_deleted", "=", "false");;
    }
    public function alokasi(){
        return $this->hasMany(Alokasi::class, "kd_sumberdana", "kd_sumberdana")->where("is_deleted", "=", "false");
    }
    public function aksesrkat(){
        return $this->hasMany(AksesRkat::class, "id_sumberdana", "id");
    }
    public function proyeksiPenerimaan(){
        return $this->hasOne(ProyeksiPenerimaan::class, "kd_sumberdana", "kd_sumberdana");
    }
}
