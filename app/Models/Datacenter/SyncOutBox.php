<?php

namespace App\Models\Datacenter;

use App\Jobs\SyncToFinanceJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncOutBox extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_sync_outbox";
    protected $fillable = [ "idempotency_key", "id_rab", "event_type", "status", "payload", "attempts", "next_retry_at", "last_error", "version"];

    protected $attributes = [
        'status'   => 'pending',
        'attempts' => 0,
    ];

    protected $casts = [
        'payload'      => 'array',
        'next_retry_at'=> 'datetime',
        'attempts'     => 'integer',
        'version'      => 'integer',
    ];

    protected static function booted() {
        static::saved(function (SyncOutBox $syncOutBox) {
            if ($syncOutBox->status !== 'pending') {
                return; // hentikan jika bukan pending
            }
            SyncToFinanceJob::dispatch($syncOutBox->id)->onQueue('default')->afterCommit(); // pastikan dikirim setelah transaksi komit
        });
    }
}
