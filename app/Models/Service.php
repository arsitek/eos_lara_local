<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    protected $connection = 'sirekat';

  function scopeAkses($query, $role, $menu)
  {
    $result = DB::select("SELECT is_crud FROM tb_menu_roles WHERE id_role = :role AND id_menu = :menu LIMIT 1", ['role' => $role, 'menu' => $menu]);
    return $result[0]->is_crud ?? 0;
  }
}
