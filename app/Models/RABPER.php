<?php

namespace App\Models;

use App\Models\Datamaster\SubkomponenSBM;
use App\Models\Datapaket\Paket;
use App\Models\Datapaket\PaketDetail;
use App\Models\Datapaket\RelasiPaket;
use App\Models\Datarevisi\RevisiRpd;
use App\Models\Mutasi\Percetakan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;
use App\Models\Rekat;
class RABPER extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $with = ["ppk", "bpp"];
    protected $table = "tb_rabperalatan";
    protected $fillable = [
        "id_rekat", "id_mak","unit_kerja","id_jenis_belanja",
        "kebutuhan_kegiatan", "jenis_belanja", "merk", "type", "url", "status_produk", "berkefungsian", "kode_sbm",
        "kuantitas", "satuan", "harga_satuan", "jumlah_biaya","rpd",
        "verifikasi_tim", "verifikasi_pimpinan_unit", "verifikasi_pimpinan_univ", "verifikasi_aset", "verifikasi_keu", "verifikasi_spi", "tanggapan",
        "kode_aset", "aset", "rpd", "kriteria_khusus", "is_revisi_rpd", "nip_ppk", "nip_bpp", "biaya_lainnya",
        "biaya_pajak", "is_tagih", "is_draft", "is_deleted", "version", "file_pendukung_rpd", "catatan"
    ];
    public function unit(){
        // where status = 1
        return $this->belongsTo(MasterUnit::class, "unit_kerja", "idunit")->where("status", 1);
    }
    public function tanggapan(){
        return $this->hasMany(TanggapanRabPeralatan::class, "id_rabperalatan", "id");
    }
    public function rekat() {
        return $this->belongsTo(Rekat::class, "id_rekat", "id");
    }
    public function unitApi(){
        // where status = 1
        return $this->belongsTo(MasterUnitApi::class, "unit_kerja", "idunit")->where("status", 1);
    }
    public function revisiRpd(){
        return $this->hasOne(RevisiRpd::class, "id_rab", "id");
    }
    public function ppk(){
        return $this->belongsTo(Komitmen::class, "nip_ppk", "nip")->where("jenis", "PPK");
    }
    public function bpp(){
        return $this->belongsTo(Komitmen::class, "nip_bpp", "nip")->where("jenis", "BPP");
    }
    public function sbm(){
        return $this->belongsTo(SubkomponenSBM::class, "kode_sbm", "kode_keg_sbm");
    }
    public function relasiPaket() {
        return $this->hasOne(RelasiPaket::class, "id_rab", "id")->where("jenis_rab", "SARANA")->where("is_deleted", "false");
    }
    public function realisasi() {
        return $this->hasOne(Realisasi::class, "id_mak", "id_mak")->where(["is_deleted" => "false", "is_posting" => "true"]);
    }
    public function mutasiPercetakan() {
        return $this->hasMany(Percetakan::class, "id_rab", "id")->where(["is_deleted" => "false", "jenis" => "SARANA"]);
    }
}
