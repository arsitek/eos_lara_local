<?php

namespace App\Models\Datacreator;

use App\Models\Datamaster\SubkomponenSBM;
use App\Models\Rekat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rab extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_rab';
    protected $fillable = ["id","jenis_rab", "id_mak", "id_rekat", "unit_kerja", "nip_ppk", "nip_bpp", "id_jenis_belanja", "jenis_belanja", "kebutuhan_kegiatan", "rpd", "kode_aset", "aset", "kode_sbm", "jenis_pekerjaan", 
        "kuantitas", "satuan_kuantitas", "durasi", "satuan_durasi", "kegiatan", "satuan_kegiatan", "biaya_satuan", "biaya_pajak", "biaya_lainnya", "jumlah_biaya", 
        "kriteria_khusus", "merk", "type", "url", "status_produk", "berkefungsian", "verifikasi_pimpinan_unit", "verifikasi_pimpinan_univ", "verifikasi_tim",
        "verifikasi_spi", "verifikasi_keu", "verifikasi_aset", "tanggapan", "is_deleted", "is_draft", "is_revisi_rpd", "is_tagih", "version", "created_at", "updated_at"
    ];
    public function rekat() {
        return $this->belongsTo(Rekat::class, "id_rekat", "id");
    }
    public function sbm(){
        return $this->belongsTo(SubkomponenSBM::class, "kode_sbm", "kode_keg_sbm");
    }

}
