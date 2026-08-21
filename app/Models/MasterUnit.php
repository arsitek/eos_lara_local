<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUnit extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_unit";
  protected $fillable = [
    "idunit", "unitkerja", "alias", "jenis_unit_kerja", "id_parent", "status"
  ];
}
