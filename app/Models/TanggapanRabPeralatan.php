<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TanggapanRabPeralatan extends Model {
    protected $connection = 'sirekat';
    use HasFactory;
    protected $table = "tb_tanggapan_rabperalatan";
    protected $fillable = ["id_rabperalatan", "role", "tanggapan"];

    public static function getRejectionInfo($rabPeralatan, $tanggapan): array
    {
        $statusVerifikator = [
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

        $tanggapan = collect($tanggapan);
        $pesanPenolakan = [];
        $penolakTanpaTanggapan = [];

        foreach ($statusVerifikator as $verifikator) {
            if ($rabPeralatan->{$verifikator["column"]} !== "Tolak") {
                continue;
            }

            $tanggapanPenolak = $tanggapan->whereIn("role", $verifikator["roles"]);
            $jumlahPesanSebelumnya = count($pesanPenolakan);

            foreach ($tanggapanPenolak as $itemTanggapan) {
                $isiTanggapan = trim(strip_tags((string) $itemTanggapan->tanggapan));

                if ($isiTanggapan !== "") {
                    $pesanPenolakan[] = ($itemTanggapan->role ?: $verifikator["label"]) . ": " . $isiTanggapan;
                }
            }

            if (count($pesanPenolakan) === $jumlahPesanSebelumnya) {
                $penolakTanpaTanggapan[] = $verifikator["label"];
            }
        }

        $isRejected = count($pesanPenolakan) > 0 || count($penolakTanpaTanggapan) > 0;

        if (!$isRejected) {
            return ["is_rejected" => false, "tooltip" => null];
        }

        if (count($pesanPenolakan) === 0) {
            return ["is_rejected" => true, "tooltip" => "Item ini ditolak"];
        }

        foreach ($penolakTanpaTanggapan as $penolak) {
            $pesanPenolakan[] = $penolak . ": Item ini ditolak";
        }

        return [
            "is_rejected" => true,
            "tooltip" => implode("\n", $pesanPenolakan),
        ];
    }
}
