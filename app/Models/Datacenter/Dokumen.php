<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model {
    protected $connection = 'sirekat';
    use HasFactory;

    protected $table = 'tb_dokumen';

    protected $fillable = [
        'judul',
        'perihal',
        'masa_berlaku',
        'tahun',
        'file',
        'tipe_file',
        'ukuran_file',
        'is_deleted',
        'uploaded_by',
        'deleted_by',
        'deleted_at'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'ukuran_file' => 'integer'
    ];

    protected $attributes = [
        'is_deleted' => 'false',
    ];
}
