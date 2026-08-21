<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $connection = 'sirekat';
  use HasFactory;
  protected $table = "tb_hak_akses";
  protected $fillable = ["nip", "id_role"];

  // with role
  protected $with = ['role'];

  public function role()
  {
    return $this->belongsTo(Role::class,  'id_role', 'id');
  }

  public function pengguna()
  {
    return $this->belongsTo(Pengguna::class, 'nip', 'nip');
  }
}
