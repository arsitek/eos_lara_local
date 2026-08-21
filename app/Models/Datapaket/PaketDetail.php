<?php

namespace App\Models\Datapaket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketDetail extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_paket_detail";
    protected $fillable = [ "id_paket", "provinsi_kabupaten_kota", "tahun_anggaran", "detail_lokasi",
        "uraian_pekerjaan", "spek_pekerjaan", "vol_pekerjaan", "satuan", "produk_dalam_negeri"
        ,"usaha_kecil", "pra_dipa", "izin_tahun_jamak", "metode_pengadaan", "jenis_pengadaan",
        "rencana_pemilihan_penyedia_start", "rencana_pemilihan_penyedia_end",
        "rencana_pelaksanaan_kontrak_start", "rencana_pelaksanaan_kontrak_end",
        "rencana_pemanfaatan_barang_start", "rencana_pemanfaatan_barang_end"];

    public function paket() {
        return $this->belongsTo(Paket::class, 'id_paket', 'id');
    }
}
