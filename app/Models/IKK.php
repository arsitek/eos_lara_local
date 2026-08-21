<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IKK extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table;
    public function __construct() {
        parent::__construct();
        $tahunSession = session()->get('tahun');
        if ($tahunSession && strpos($tahunSession, '_') !== false) {
            $tahunArray = explode("_", $tahunSession);
            if (isset($tahunArray[1]) && $tahunArray[1] === "2025") {
                $this->table = 'iku_zi';
            } else {
                $this->table = 'iku_baru';
            }
        } else {
            $this->table = 'iku_baru';
        }
    }

    protected $fillable = ['id','kd_ss','sasaran_program','kode_ikk','indikator_kinerja_kegiatan','kd_ikv','ikv','kd_keg','rincian_kegiatan', 'jenis_rab'
    ];
}
