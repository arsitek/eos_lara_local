<?php

namespace App\Models\Datarevisi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupRkatDetail extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_backup_rkat_detail";
    protected $fillable = [ "id_rekat", "id_mak", "id_duplikasi","jenis", "status", "rpd", "id_coa", "coa",
        "id_item_coa", "item_coa", "kuantitas", "satuan_kuantitas", "durasi", "satuan_durasi",
        "kegiatan", "satuan_kegiatan", "kode_aset", "aset", "kriteria_khusus", "merk", "type", "terpakai", "terpakai_sisa",
        "status_produk", "berkefungsian", "harga_satuan", "jumlah_biaya", "ded_awal", "ded_review", "jumlah_amprahan", "jumlah_realisasi",
        "verifikasi_tim", "verifikasi_keu", "verifikasi_aset", "verifikasi_pimpinan_unit", "jenis_pekerjaan",
        "verifikasi_pimpinan_univ", "is_deleted", "is_revisi_kro", "nip_ppk", "nip_bpp", "biaya_pajak", "biaya_lainnya", "url",
        "jenis_pekerjaan", "is_deleted", "is_draft", "is_posting", "version"];
}
