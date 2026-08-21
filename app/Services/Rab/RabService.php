<?php

namespace App\Services\Rab;

use App\Models\Datacreator\Rab;
use App\Models\Komitmen;
use App\Models\Rekat;
use Illuminate\Http\Request;

class RabService {
    private const JENIS_LANGGANAN = 'langganan';
    private const JENIS_BHP = 'bhp';

    public function validateIndexRequest(Request $req): mixed {
        return $req->validate([
            "id_rekat" => "required|integer",
        ], [
            "id_rekat.required" => "ID Rekat harus diisi",
            "id_rekat.integer" => "ID Rekat harus berupa angka",
        ]);
    }

    public function getLayananJasaByRekat(int $idRekat) {
        return $this->getRabItemsByRekat($idRekat, self::JENIS_LANGGANAN);
    }

    public function getBhpByRekat(int $idRekat) {
        return $this->getRabItemsByRekat($idRekat, self::JENIS_BHP);
    }

    public function normalizeLayananJasaInput(Request $req): array {
        $data = $req->only([
            'id', 'idRekat', 'idJenisBelanja', 'jenisBelanja', 'kebutuhanKegiatan',
            'kuantitas', 'satuanKuantitas', 'durasi', 'satuanDurasi', 'kegiatan',
            'satuanKegiatan', 'biayaSatuan', 'rpd', 'kodeSbm', 'userAgent',
            'screenSize', 'platform', 'lang'
        ]);

        foreach (['kuantitas', 'durasi', 'kegiatan', 'biayaSatuan'] as $field) {
            $data[$field] = $this->cleanNumber($data[$field] ?? null);
        }

        foreach (['jenisBelanja', 'kebutuhanKegiatan', 'satuanKuantitas', 'satuanDurasi', 'satuanKegiatan', 'kodeSbm'] as $field) {
            $data[$field] = $this->cleanText($data[$field] ?? '');
        }

        return $data;
    }

    public function normalizeBhpInput(Request $req): array {
        $data = $req->only([
            'id', 'idRekat', 'idJenisBelanja', 'jenisBelanja', 'kodeSbm', 'rpd',
            'kodeAset', 'aset', 'kebutuhanKegiatan', 'merk', 'type', 'url',
            'kuantitas', 'satuanKuantitas', 'hargaDasar', 'userAgent',
            'screenSize', 'platform', 'lang'
        ]);

        foreach (['kuantitas', 'hargaDasar'] as $field) {
            $data[$field] = $this->cleanNumber($data[$field] ?? null);
        }

        foreach (['jenisBelanja', 'kodeSbm', 'kodeAset', 'aset', 'kebutuhanKegiatan', 'merk', 'type', 'url', 'satuanKuantitas'] as $field) {
            $data[$field] = $this->cleanText($data[$field] ?? '');
        }

        return $data;
    }

    public function layananJasaRules(): array {
        return [
            'id' => 'nullable|integer',
            'idRekat' => 'required|integer',
            'idJenisBelanja' => 'required|string|max:30',
            'jenisBelanja' => 'required|string|max:255',
            'kebutuhanKegiatan' => 'required|string|max:500',
            'kuantitas' => 'required|integer|min:1|max:1000000',
            'satuanKuantitas' => 'required|string|max:50',
            'durasi' => 'required|integer|min:1|max:1000000',
            'satuanDurasi' => 'required|string|max:50',
            'kegiatan' => 'required|integer|min:1|max:1000000',
            'satuanKegiatan' => 'required|string|max:50',
            'biayaSatuan' => 'required|integer|min:1|max:999999999999',
            'rpd' => ['required', 'regex:/^(0?[1-9]|1[0-2])$/'],
            'kodeSbm' => 'nullable|string|max:50',
            'userAgent' => 'nullable|string|max:500',
            'screenSize' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'lang' => 'nullable|string|max:50',
        ];
    }

    public function bhpRules(): array {
        return [
            'id' => 'nullable|integer',
            'idRekat' => 'required|integer',
            'idJenisBelanja' => 'required|string|max:30',
            'jenisBelanja' => 'required|string|max:255',
            'kodeSbm' => 'nullable|string|max:50',
            'rpd' => ['required', 'regex:/^(0?[1-9]|1[0-2])$/'],
            'kodeAset' => 'nullable|string|max:100',
            'aset' => 'nullable|string|max:255',
            'kebutuhanKegiatan' => 'required|string|max:500',
            'merk' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
            'kuantitas' => 'required|integer|min:1|max:1000000',
            'satuanKuantitas' => 'required|string|max:50',
            'hargaDasar' => 'required|integer|min:1|max:999999999999',
            'userAgent' => 'nullable|string|max:500',
            'screenSize' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'lang' => 'nullable|string|max:50',
        ];
    }

    public function rabMessages(): array {
        return [
            'required' => 'Kolom :attribute tidak boleh kosong',
            'integer' => 'Kolom :attribute harus berupa angka',
            'min' => 'Kolom :attribute tidak valid',
            'max' => 'Kolom :attribute terlalu panjang',
            'rpd.regex' => 'RPD harus berisi bulan yang valid',
        ];
    }

    public function layananJasaMessages(): array {
        return $this->rabMessages();
    }

    public function saveLayananJasa(array $data): array {
        return $this->saveRabItem($data, self::JENIS_LANGGANAN, 'RAB Langganan Jasa', 'volume');
    }

    public function saveBhp(array $data): array {
        return $this->saveRabItem($data, self::JENIS_BHP, 'RAB BHP', 'bhp');
    }

    public function deleteLayananJasa(array $data): array {
        return $this->deleteRabItem($data, self::JENIS_LANGGANAN, 'RAB langganan');
    }

    public function deleteBhp(array $data): array {
        return $this->deleteRabItem($data, self::JENIS_BHP, 'RAB BHP');
    }

    public function getCurrentSisaPagu(string $unitkerja, string $kdSumberdana, string $tahun): int {
        $alokasi         = (int) getAlokasi($unitkerja, $kdSumberdana, $tahun);
        $paguDigunakan   = (int) getPaguTerpakai($unitkerja, $kdSumberdana, session()->get('tahun'), false, null)['total'];
        $sisaSaldo       = (int) getSisaSaldo($unitkerja, $kdSumberdana, $tahun);
        return (int) round($alokasi - ($paguDigunakan + $sisaSaldo));
    }

    private function getRabItemsByRekat(int $idRekat, string $jenisRab) {
        return Rab::with('rekat.sumberdana')
            ->where('id_rekat', $idRekat)
            ->where('jenis_rab', $jenisRab)
            ->where(function ($query) {
                $query->where('is_deleted', 'false')->orWhere('is_deleted', 0)->orWhereNull('is_deleted');
            })
            ->get()
            ->map(fn ($item) => $this->appendDisplayAttributes($item));
    }

    private function saveRabItem(array $data, string $jenisRab, string $label, string $mode): array {
        ["tahun" => $tahun] = getTahunData();
        $tahunText = explode('_', $tahun)[0] ?? $tahun;

        $rekatResult = $this->getAuthorizedRekat((int) $data['idRekat']);
        if (!$rekatResult['success']) return $rekatResult;

        $rekat = $rekatResult['data'];
        $allowZeroPaguAdd = $this->allowZeroPaguForRekat($rekat, $tahun);
        $existingRab = null;
        if (!empty($data['id'])) {
            $existingRab = $this->findRabItem((int) $data['id'], (int) $rekat->id, $jenisRab);
            if (!$existingRab) return ['success' => false, 'message' => "Data {$label} tidak ditemukan", 'code' => 404];
            if ($this->isLockedRab($existingRab)) return ['success' => false, 'message' => 'Data tidak dapat diubah karena sudah disetujui atau diproses', 'code' => 400];
        }

        $jumlahBiaya = $this->calculateJumlahBiaya($data, $rekat->sd, $mode);
        $sisaPagu = $this->getCurrentSisaPagu($rekat->unit_kerja, $rekat->sd, $tahun);
        $nominalTambahan = $existingRab ? max($jumlahBiaya - (int) $existingRab->jumlah_biaya, 0) : $jumlahBiaya;
        if (!$allowZeroPaguAdd && $nominalTambahan > $sisaPagu) {
            return ['success' => false, 'message' => 'Sisa pagu tidak mencukupi', 'code' => 400];
        }

        $pejabatResult = $this->resolvePejabat($rekat->unit_kerja, $rekat->sd, $data['idJenisBelanja'], $jumlahBiaya);
        if (!$pejabatResult['success'] && $tahunText !== 'Indikatif') return $pejabatResult;

        $payload = $this->buildRabPayload($data, $rekat->id, $jenisRab, $jumlahBiaya, $tahunText, $pejabatResult, $existingRab, $mode, $rekat);
        if (!$existingRab) {
            $payload['id_mak'] = $this->generateIdMak($jenisRab);
            $rab = Rab::create($payload);
            $status = 'INSERT';
        } else {
            $existingRab->fill($payload);
            $existingRab->save();
            $rab = $existingRab->fresh('rekat.sumberdana');
            $status = 'UPDATE';
        }

        return [
            'success' => true,
            'message' => 'Berhasil menyimpan data',
            'code' => 201,
            'status' => $status,
            'data' => [
                'rab' => $this->appendDisplayAttributes($rab),
                'sisa_pagu' => $this->getCurrentSisaPagu($rekat->unit_kerja, $rekat->sd, $tahun),
            ],
        ];
    }

    public function allowZeroPaguForRekat(Rekat $rekat, string $tahun): bool {
        $tahunAngka = (int) (explode('_', $tahun)[1] ?? 0);

        return $tahunAngka === 2027
            && !isFakultas((string) $rekat->unit_kerja)
            && isSumberDanaAllowZeroPagu((string) $rekat->sd);
    }

    private function deleteRabItem(array $data, string $jenisRab, string $label): array {
        ["tahun" => $tahun] = getTahunData();

        $rekatResult = $this->getAuthorizedRekat((int) $data['idRekat']);
        if (!$rekatResult['success']) return $rekatResult;

        $rekat = $rekatResult['data'];
        $rab = $this->findRabItem((int) $data['id'], (int) $rekat->id, $jenisRab);
        if (!$rab) return ['success' => false, 'message' => "Data {$label} tidak ditemukan", 'code' => 404];
        if ($this->isLockedRab($rab)) return ['success' => false, 'message' => 'Data tidak dapat dihapus karena sudah disetujui atau diproses', 'code' => 400];

        $rab->update(['is_deleted' => 'true']);

        return [
            'success' => true,
            'message' => 'Berhasil menghapus data',
            'code' => 201,
            'data' => [
                'rab' => $rab,
                'sisa_pagu' => $this->getCurrentSisaPagu($rekat->unit_kerja, $rekat->sd, $tahun),
            ],
        ];
    }

    private function buildRabPayload(array $data, int $idRekat, string $jenisRab, int $jumlahBiaya, string $tahunText, array $pejabatResult, ?Rab $existingRab, string $mode, ?Rekat $rekat): array {
        $payload = [
            'jenis_rab' => $jenisRab,
            'id_rekat'  => $idRekat,
            'unit_kerja' => $rekat->unit_kerja,
            'nip_ppk'   => $tahunText === 'Indikatif' ? '-' : $pejabatResult['data']['nip_ppk'],
            'nip_bpp'   => $tahunText === 'Indikatif' ? '-' : $pejabatResult['data']['nip_bpp'],
            'id_jenis_belanja' => $data['idJenisBelanja'],
            'jenis_belanja' => $data['jenisBelanja'],
            'kebutuhan_kegiatan' => $data['kebutuhanKegiatan'],
            'kuantitas' => $data['kuantitas'],
            'biaya_satuan' => $mode === 'bhp' ? $data['hargaDasar'] : $data['biayaSatuan'],
            'jumlah_biaya' => $jumlahBiaya,
            'rpd' => $data['rpd'],
            'kode_sbm' => $data['kodeSbm'] ?? '',
            'is_deleted' => 'false',
            'is_draft' => 'false',
            'version' => $existingRab ? ((int) $existingRab->version + 1) : 1,
        ];

        if ($mode === 'bhp') {
            $payload += [
                'kode_aset' => $data['kodeAset'] ?? '',
                'aset' => $data['aset'] ?? '',
                'merk' => $data['merk'] ?? '',
                'type' => $data['type'] ?? '',
                'url' => $data['url'] ?? '',
                'satuan_kuantitas' => $data['satuanKuantitas'],
            ];
            return $payload;
        }

        $payload += [
            'satuan_kuantitas' => $data['satuanKuantitas'],
            'durasi' => $data['durasi'],
            'satuan_durasi' => $data['satuanDurasi'],
            'kegiatan' => $data['kegiatan'],
            'satuan_kegiatan' => $data['satuanKegiatan'],
        ];

        return $payload;
    }

    private function getAuthorizedRekat(int $idRekat): array {
        $rekat = Rekat::where('id', $idRekat)->where('is_deleted', 'false')->first();
        if (!$rekat) return ['success' => false, 'message' => 'Data rekat tidak ditemukan', 'code' => 404];

        $role = session('role');
        $sessionUnit = session('unitkerja') ?: session('idunit');
        if (!in_array($role, ['superadmin', 'admin']) && (string) $rekat->unit_kerja !== (string) $sessionUnit) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses ke data ini', 'code' => 403];
        }

        return ['success' => true, 'data' => $rekat];
    }

    private function findRabItem(int $id, int $idRekat, string $jenisRab): ?Rab {
        return Rab::where('id', $id)
            ->where('id_rekat', $idRekat)
            ->where('jenis_rab', $jenisRab)
            ->where(function ($query) {
                $query->where('is_deleted', 'false')->orWhere('is_deleted', 0)->orWhereNull('is_deleted');
            })
            ->first();
    }

    private function calculateJumlahBiaya(array $data, string $kodeSumberdana, string $mode): int {
        $jumlahBiaya = $mode === 'bhp'
            ? (int) $data['kuantitas'] * (int) $data['hargaDasar']
            : (int) $data['kuantitas'] * (int) $data['durasi'] * (int) $data['kegiatan'] * (int) $data['biayaSatuan'];

        $excludedSd = ["42010999", "42010801", "42010913", "42010901", "42010204", "42010915", "41050105", "41050201", "41010201", "41050103"];
        if (!in_array($kodeSumberdana, $excludedSd)) {
            $jumlahBiaya = (int) ceil($jumlahBiaya / 1000) * 1000;
        }
        return $jumlahBiaya;
    }

    private function resolvePejabat(string $unitkerja, string $kdSumberdana, string $coa, int $jumlahBiaya): array {
        $tahun                      = session('tahun');
        [ $tahunText, $tahunAngka ] = explode("_", $tahun);
        $dataPPK = [
            'jumlah_biaya' => $jumlahBiaya,
            'unitkerja' => $unitkerja,
            'kd_sumberdana' => $kdSumberdana,
            'coa' => $coa,
        ];

        $komitmen = Komitmen::select('id', 'nip', 'nama_pejabat')->where('jenis', 'ppk');
        $ppk = getPPK($komitmen, $dataPPK);
        $bpp = getBPP($dataPPK);

        // Jika tahun "Indikatif", lewatkan pengecekan PPK & BPP dan siapkan nilai default
        if ($tahunText != "Indikatif") {
            if (count($ppk) === 0) return ['success' => false, 'message' => 'Maaf, Data Pejabat Pembuat Komitmen tidak ditemukan', 'code' => 400];
            if (!$bpp) return ['success' => false, 'message' => 'Maaf, Data Bendahara Pengeluaran Pembantu tidak ditemukan', 'code' => 400];
        }

        return [
            'success' => true,
            'data' => [
                'nip_ppk' => $ppk[0]->nip,
                'nip_bpp' => $bpp->nip,
            ],
        ];
    }

    private function generateIdMak(string $jenisRab): string {
        $prefix = $jenisRab === self::JENIS_BHP ? '55' : '44';
        do {
            $idMak = $prefix . random_int(1000000, 9999999);
        } while (Rab::where('id_mak', $idMak)->exists());
        return $idMak;
    }

    private function isLockedRab(Rab $rab): bool {
        $verifikasiKeu = $rab->verifikasi_keu ?? $rab->verifikasi_keuangan ?? null;
        $isApproved = $rab->verifikasi_pimpinan_unit === 'Setuju'
            && $rab->verifikasi_pimpinan_univ === 'Setuju'
            && $rab->verifikasi_tim === 'Setuju'
            && $verifikasiKeu === 'Setuju'
            && $rab->verifikasi_aset === 'Setuju';

        return $isApproved || (!empty($rab->id_mak) && function_exists('cekAmprah') && cekAmprah($rab->id_mak));
    }

    private function appendDisplayAttributes(Rab $rab): Rab {
        $rab->setAttribute('formatted_jumlah_biaya', 'Rp ' . number_format((int) $rab->jumlah_biaya, 0, ',', ','));
        $rab->setAttribute('formatted_biaya_satuan', 'Rp ' . number_format((int) $rab->biaya_satuan, 0, ',', ','));
        $rab->setAttribute('formatted_harga_dasar', 'Rp ' . number_format((int) $rab->biaya_satuan, 0, ',', ','));
        $rab->setAttribute('unit_kerja', $rab->rekat->unit_kerja ?? null);
        return $rab;
    }

    private function cleanNumber($value): int {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        return $clean === '' ? 0 : (int) $clean;
    }

    private function cleanText($value): string {
        return trim(strip_tags((string) $value));
    }
}
