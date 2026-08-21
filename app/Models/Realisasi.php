<?php

namespace App\Models;

use App\Models\Datamaster\Subkomponen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Datapaket\Paket;

class Realisasi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_realisasi";
    protected $fillable = [ "id_rekat","id_mak","idunit","kd_sumberdana",
        "kd_rk","coa","rpd","nip_ppk","nip_bpp","jumlah_biaya","jumlah_realisasi","jumlah_amprahan",
        "is_deleted", "is_posting", "tahun", "nama_pumk", "tanggal_bayar"];
    public function rekat(){
        return $this->belongsTo(Rekat::class, "id_rekat", "id");
    }
    public function unit(){
        return $this->belongsTo(MasterUnit::class, "idunit", "id");
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, "kd_sumberdana", "kd_sumberdana");
    }
    public function coa(){
        return $this->belongsTo(CoaApi::class, "coa", "coa");
    }
    public function subkomponen(){
        return $this->belongsTo(Subkomponen::class, "kd_rk", "kode_keg");
    }
    public function paket(){
        return $this->belongsTo(Paket::class, "id_rekat", "id_mak");
    }
}
