<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sapras extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sapras";
    protected $fillable = ["kode", "nama"];
}
