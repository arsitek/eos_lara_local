<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_menu_roles";
  protected $fillable = ["id_menu", 'id_role', 'is_crud'];
}
