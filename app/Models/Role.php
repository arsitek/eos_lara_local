<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_role";
  protected $fillable = ["nama"];

  public function menus()
  {
    return $this->hasMany(RoleMenu::class,  'id_role', 'id');
  }
}
