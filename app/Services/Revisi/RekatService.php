<?php

namespace App\Services\Revisi;

use App\Models\Rekat;
use App\Models\Datamaster\Subkomponen;
use App\Models\Datacreator\RelasiMasterIku;
use App\Models\Datamaster\SubkomponenMaster;

class RekatService {
    /**
     * Get subjudul with related data
     */
    public function getSubjudulWithRelations(int $id, int $tahunAngka): ?Rekat {
        if ($tahunAngka >= 2026) {
            $rekat = Rekat::with([
                'relasiMasterIku' => fn($q) => $q->with(['subkomponenMaster'])
            ])->where([ 'id' => $id, 'is_deleted' => 'false' ])->first();

            if ($rekat && isset($rekat->relasiMasterIku) && isset($rekat->relasiMasterIku->subkomponenMaster))
                $rekat->subkomponen = $rekat->relasiMasterIku->subkomponenMaster;
            return $rekat;
        }

        // Legacy behavior for years before 2026: load subkomponen by year
        return Rekat::with(['subkomponen' => function($query) use ($tahunAngka) {
            $query->where('tahun', $tahunAngka);
        }])->where([ 'id' => $id, 'is_deleted' => 'false' ])->first();
    }

    /**
     * Get subjudul by filters
     */
    public function getSubjudulByFilters(int $idUnit, string $tahun, string $kdSumberdana) {
        return Rekat::where(function($query) use ($idUnit, $tahun, $kdSumberdana) {
            $query->where('unit_kerja', $idUnit)
                  ->where('tahun', $tahun)
                  ->where('sd', $kdSumberdana)
                  ->where('is_deleted', 'false');
        })->get();
    }

    /**
     * Determine jenis RAB from kode kegiatan
     */
    public function getJenisRab(string $kodeKeg, int $tahunAngka): string {
        $jenisRab = cekJenisRab($kodeKeg, $tahunAngka);
        return $jenisRab == "" ? "RAB_KEGIATAN" : $jenisRab;
    }

    /**
     * Get kode IKK from rekat
     */
    public function getKodeIkkFromRekat(int $idRekat, int $tahunAngka): ?string {
        $rekat = $this->getSubjudulWithRelations($idRekat, $tahunAngka);
        if ($tahunAngka >= 2026) {
            $relasi = RelasiMasterIku::where('id_rekat', $idRekat)->first(); // Try to get direct relasi record by id_rekat
            if ($relasi && !empty($relasi->kode_iku))
                return $relasi->kode_iku;
        }
        if (!$rekat || !isset($rekat->subkomponen->ikv->ro))
            return null;

        return $rekat->subkomponen->ikv->ro->kode_ikk;
    }

    /**
     * Get kode sasaran (kode_ss) from rekat
     */
    public function getKodeSasaranFromRekat($idRekat, int $tahunAngka): ?string {
        if ($tahunAngka >= 2026) {
            $relasi = RelasiMasterIku::where('id_rekat', $idRekat)->first(); // Try to get direct relasi record by id_rekat
            if ($relasi && !empty($relasi->kode_ss))
                return $relasi->kode_ss;
            // If no relasi found, continue to fallback below.
        }

        // Fallback: traverse existing relations (compatible with pre-2026 data model)
        $rekat = Rekat::with([
            'subkomponen' => fn($q) => $q->where('tahun', $tahunAngka),
            'subkomponen.ikv' => fn($q) => $q->where('tahun', $tahunAngka),
            'subkomponen.ikv.ro' => fn($q) => $q->where('tahun', $tahunAngka),
            'subkomponen.ikv.ro.kro' => fn($q) => $q->where('tahun', $tahunAngka)
        ])->where('id', $idRekat)->first();

        if (!$rekat || !isset($rekat->subkomponen->ikv->ro->kro))
            return null;
        return $rekat->subkomponen->ikv->ro->kro->kode_ss;
    }

    /**
     * Get kode sasaran from RAB
     */
    public function getKodeSasaranFromRab(int $idRab, int $tahunAngka, string $jenisRab): ?string {
        $rabClass = match($jenisRab) {
            "RAB_KEGIATAN" => \App\Models\RABKEG::class,
            "OPERASIONAL" => \App\Models\RABKEG::class,
            "RAB_PERALATAN" => \App\Models\RABPER::class,
            "SARANA" => \App\Models\RABPER::class,
            "RAB_GEDUNG" => \App\Models\RABGDG::class,
            "PRASARANA" => \App\Models\RABGDG::class,
            default => \App\Models\RABKEG::class
        };

        $rab = $rabClass::where("id", $idRab)->first();
        if (!$rab)
            return null;
        return $this->getKodeSasaranFromRekat($rab->id_rekat, $tahunAngka);
    }

    /**
     * Get jenis RAB from rekat
     */
    public function getJenisRabFromRekat(int $idRekat, int $tahunAngka): ?string {
        if ($tahunAngka >= 2026) {
            $rekat = Rekat::with([
                'relasiMasterIku' => fn($q) => $q->with(['subkomponenMaster'])
            ])->where([ 'id' => $idRekat, 'is_deleted' => 'false' ])->first();

            if ($rekat && isset($rekat->relasiMasterIku) && isset($rekat->relasiMasterIku->subkomponenMaster))
                return $rekat->relasiMasterIku->subkomponenMaster->jenis_rab;
            return null;
        }
        $rekat = Rekat::with(['subkomponen' => fn($q) => $q->where('tahun', $tahunAngka)])->where("id", $idRekat)->first();
        if (!$rekat || !isset($rekat->subkomponen))
            return null;
        return $rekat->subkomponen->jenis_rab;
    }

    /**
     * Build spek menjadi data structure for penambahan kegiatan
     * 
     * @param array $data Request data containing kegiatan information
     * @return array Structured data array
     */
    public function buildSpekMenjadiData(array $data): array {
        return [
            'idunit' => $data['idunit'] ?? null,
            'kode_sd' => $data['kodeSd'] ?? null,
            'kode_ikv' => $data['kodeIkv'] ?? null,
            'kode_keg' => $data['kodeKeg'] ?? null,
            'sub_judul' => $data['subJudul'] ?? null,
            'unit_pelaksana' => $data['unitPelaksana'] ?? null,
            'rencana_pelaksanaan' => $data['rencanaPelaksanaan'] ?? null,
            'prioritas' => $data['prioritas'] ?? ''
        ];
    }

    /**
     * Parse spek menjadi data from JSON or delimiter format
     * 
     * @param string $spekMenjadi Spek menjadi string (JSON or delimiter format)
     * @return array Parsed data array
     */
    public function parseSpekMenjadi(string $spekMenjadi): array {
        // Try to decode as JSON first
        $decoded = json_decode($spekMenjadi, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) { // Already in JSON format
            return $decoded;
        }

        // Delimiter format (~~~)
        $parts = explode("~~~", $spekMenjadi);
        return [
            'idunit'              => $parts[0] ?? null,
            'kode_sd'             => $parts[1] ?? null,
            'kode_ikv'            => $parts[2] ?? null,
            'kode_keg'            => $parts[3] ?? null,
            'sub_judul'           => $parts[4] ?? null,
            'unit_pelaksana'      => $parts[5] ?? null,
            'rencana_pelaksanaan' => $parts[6] ?? null,
            'prioritas'           => $parts[7] ?? '',
            'id_rekat'            => $parts[8] ?? null // If exists in legacy format
        ];
    }

    /**
     * Encode spek menjadi data to JSON
     * 
     * @param array $data Spek menjadi data array
     * @return string JSON encoded string
     */
    public function encodeSpekMenjadi(array $data): string {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    public function getIdMakFromIdRab(int $idRab, string $jenisRab): ?string {
        $rabClass = match($jenisRab) {
            "RAB_KEGIATAN" => \App\Models\RABKEG::class,
            "OPERASIONAL" => \App\Models\RABKEG::class,
            "RAB_PERALATAN" => \App\Models\RABPER::class,
            "SARANA" => \App\Models\RABPER::class,
            "RAB_GEDUNG" => \App\Models\RABGDG::class,
            "PRASARANA" => \App\Models\RABGDG::class,
            default => \App\Models\RABKEG::class
        };

        $rab = $rabClass::where("id", $idRab)->where("is_deleted", "false")->first();
        if (!$rab)
            return null;
        return $rab->id_mak;
    }
    public function getKodeKomponenMasterByLevel(int $level, string $kodeKeg): ?string {
        $foundLevelColumn = match($level) {
            1 => 'kode_klasifikasi',
            2 => 'kode_sub_klasifikasi',
            3 => 'kode_keg',
            default => 'kode_keg',
        };
        $komponen = SubkomponenMaster::where("kode_keg", $kodeKeg)->where($foundLevelColumn, '!=', null)->first();
        return $komponen ? $komponen->$foundLevelColumn : null;
    }
}
