<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaApi extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_api_coa";
    protected $fillable = ["id_coa", "id_parent", "coa_parent", "nama_parent", "coa", "nama", "jenis_coa", "jenis_coa_parent"];
}
