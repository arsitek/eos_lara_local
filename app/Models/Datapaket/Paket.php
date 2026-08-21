<?php

namespace App\Models\Datapaket;

use App\Models\CoaApi;
use App\Models\Datamaster\Subkomponen;
use App\Models\Datamaster\SubkomponenMaster;
use App\Models\Kodefikasi;
use App\Models\Komitmen;
use App\Models\MasterUnitApi;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table    = 'tb_paket';
    protected $with     = ['subkomponen', 'relasiPaket', 'unitApi', 'coaApi', 'sumberdana', 'rpd', 'detail'];
    protected $fillable = ["id_bpp", "id_pejabat", "id_mak", "coa", "nama_coa", "kd_sumberdana", "jenis",
    "idunit", "kode_ss", "kode_ikk", "kode_ikv", "kode_keg", "sub_judul", "rpd", "kuantitas",
    "satuan_kuantitas", "durasi", "satuan_durasi", "kegiatan", "satuan_kegiatan", "jumlah_biaya",
    "created_at", "updated_at", "is_deleted", "is_posting", "tahun", "is_rup", "nilai_kontrak" ];
    public function relasiPaket(){
        return $this->hasMany(RelasiPaket::class, 'id_paket', 'id');
    }
    public function subkomponen(){
        return $this->belongsTo(Subkomponen::class, 'kode_keg', 'kode_keg');
    }
    public function subkomponenMaster(){
        return $this->belongsTo(SubkomponenMaster::class, 'kode_keg', 'kode_keg');
    }
    public function unitApi(){
        return $this->belongsTo(MasterUnitApi::class, 'idunit', 'idunit');
    }
    public function coaApi(){
        return $this->belongsTo(CoaApi::class, 'coa', 'coa');
    }
    public function coa(){
        return $this->belongsTo(Kodefikasi::class, 'akun', 'coa');
    }
    public function ppk(){
        return $this->belongsTo(Komitmen::class, 'id_pejabat', 'id');
    }
    public function sumberdana(){
        return $this->belongsTo(SumberDana::class, 'kd_sumberdana', 'kd_sumberdana');
    }
    public function rpd(){
        return $this->hasMany(RelasiRpd::class, 'id_paket', 'id');
    }
    public function detail(){
        return $this->hasMany(PaketDetail::class, 'id_paket', 'id');
    }
}
