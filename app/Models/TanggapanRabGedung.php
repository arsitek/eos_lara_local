<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TanggapanRabGedung extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_tanggapan_rabgedung";
    protected $fillable = ["id_rabgedung", "role", "tanggapan"];

    /**
     * Membentuk status dan isi tooltip berdasarkan verifikator yang benar-benar menolak.
     */
    public static function getRejectionInfo($rabGedung, $tanggapan): array
    {
        $verificationMap = [
            [
                "column" => "verifikasi_pimpinan_unit",
                "label" => "Pimpinan Unit",
                "roles" => ["Pimpinan Unit", "Kuasa Pimpinan Unit", "Direktur"],
            ],
            [
                "column" => "verifikasi_tim",
                "label" => "Verifikator RKAT",
                "roles" => ["Verifikator RKAT"],
            ],
            [
                "column" => "verifikasi_keu",
                "label" => "Verifikator Keuangan",
                "roles" => ["Verifikator Keuangan"],
            ],
            [
                "column" => "verifikasi_aset",
                "label" => "Verifikator Aset",
                "roles" => ["Verifikator Aset"],
            ],
            [
                "column" => "verifikasi_pimpinan_univ",
                "label" => "Pimpinan USK",
                "roles" => ["Pimpinan USK", "Wakil Rektor"],
            ],
            [
                "column" => "verifikasi_spi",
                "label" => "Pengawasan Internal / Auditor",
                "roles" => ["Pengawasan Internal", "Auditor"],
            ],
        ];

        $responses = collect($tanggapan);
        $messages = [];
        $rejectorsWithoutResponse = [];

        foreach ($verificationMap as $verification) {
            if ($rabGedung->{$verification["column"]} !== "Tolak") {
                continue;
            }

            $rejectingResponses = $responses->filter(function ($response) use ($verification) {
                return in_array($response->role, $verification["roles"], true);
            });
            $hasResponse = false;

            foreach ($rejectingResponses as $response) {
                $responseText = trim(strip_tags((string) $response->tanggapan));
                if ($responseText === "") {
                    continue;
                }

                $messages[] = ($response->role ?: $verification["label"]).": ".$responseText;
                $hasResponse = true;
            }

            if (!$hasResponse) {
                $rejectorsWithoutResponse[] = $verification["label"];
            }
        }

        $isRejected = count($messages) > 0 || count($rejectorsWithoutResponse) > 0;
        if (!$isRejected) {
            return ["is_rejected" => false, "tooltip" => null];
        }

        if (count($messages) === 0) {
            return ["is_rejected" => true, "tooltip" => "Item ini ditolak"];
        }

        foreach ($rejectorsWithoutResponse as $rejector) {
            $messages[] = $rejector.": Item ini ditolak";
        }

        return [
            "is_rejected" => true,
            "tooltip" => implode("\n", $messages),
        ];
    }
}
