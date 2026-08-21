<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rekat;

class Kodefikasi extends Model
{
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_kodefikasi_jenisbelanja";
    protected $fillable = ["ekuivalensi", "akun", "jenis_belanja", "identitas"];
    public function akun(){
        return $this->hasMany(Rekat::class, "jenis_belanja", "jenis_belanja");
    }
}
