<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisiKroSemulaDetail extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_revisi_kro_semula_detail';
    protected $fillable = [ "id_rekat", "id_mak", "jenis", "status", "rpd", "id_coa", "coa",
        "id_item_coa", "item_coa", "kuantitas", "satuan_kuantitas", "durasi", "satuan_durasi",
        "kegiatan", "satuan_kegiatan", "kodefikasi_aset", "kriteria_khusus", "merk", "type",
        "eCatalog", "status_produk", "berkefungsian", "harga_satuan", "jumlah_biaya",
        "verifikasi_tim", "verifikasi_keu", "verifikasi_aset", "verifikasi_pimpinan_unit",
        "verifikasi_pimpinan_univ", "is_deleted", "is_revisi_kro"];
}
