<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = 'tb_log';
    protected $fillable = ["nip","id_menu","id_role","event","keterangan","tahun",
                            "ip_address","user_agent","platform","screen_size","latitude",
                            "longtitude","kota","provinsi","negara", "lang", "status", "json_data"];
    public $timestamps = false;
}
