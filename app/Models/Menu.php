<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_menu";
  protected $fillable = ["level_menu", "is_nested", 'icon', 'urutan', 'route', 'nama', 'id_parent', 'open_at', 'close_at'];
  protected $casts = [
    'open_at' => 'datetime',
    'close_at' => 'datetime',
  ];
}
