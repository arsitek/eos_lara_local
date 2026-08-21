<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AksesMenu extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_akses";
    protected $fillable = ["nama_menu", "akses"];
}
