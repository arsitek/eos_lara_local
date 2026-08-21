<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahun extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_tahun";
  protected $fillable = ["tahun"];
}
