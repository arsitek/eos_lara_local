<?php

namespace App\Http\Controllers\Laporan;

use App\Events\UserPerformedAction;
use App\Http\Controllers\Controller;
use App\Models\AksesMenu;
use App\Models\Datacreator\VariabelAnalisis;
use App\Models\Datamaster\Kro;
use App\Models\Datamaster\Subkomponen;
use App\Models\MasterUnitApi;
use App\Models\RABGDG;
use App\Models\RABKEG;
use App\Models\RABPER;
use App\Models\Rekat;
use App\Models\SumberDana;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AnalisisController extends Controller {
    private function checkAkses() {
        $aksesMenu = AksesMenu::where("id", "12")->first(); // menu laporan analisis
        if (!$aksesMenu || ($aksesMenu->akses !== "TRUE" && !in_array(session('role'), ["superadmin", "admin"]))) {
            return false;
        }
        return true;
    }
    public function Index(){
        // 📦 Init variable
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $role = session("role");
        $sumberdana = SumberDana::where(["tahun" => $tahunAngka, "is_deleted" => "false", "is_show" => "true"])->get();
        $nip  = session()->get('id_user', '');
        $role = session()->get('role', 'Admin');
        $unitkerja = Rekat::with([
            'unitApi' => function ($query) use ($role, $nip) {
                if ($nip != "196709261992031002" && in_array($role, ["Wakil Rektor", "Direktur"])) {
                    $query->where('idunit', 'like', session()->get('unitkerja', '') . '%');
                }
            }
        ])->select('unit_kerja')->distinct()->get();

        // 📋 Check Access
        if ( $this->checkAkses() === false ) {
            return redirect("/")->with("Pesan", "Anda tidak memiliki akses ke halaman ini");
        }
        return view('content.laporan.ANALISIS.index', compact("unitkerja", "sumberdana", "role", "tahunAngka"));
    }
    public function IndexPdf( Request $req ){
        // 📦 Init variable
        [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
        $role = session("role");
        $unitkerja  = MasterUnitApi::where(["idunit" => $req->idunit])->first();
        // 📋 Check Access
        if ( $this->checkAkses() === false )
            return redirect("/")->with("Pesan", "Anda tidak memiliki akses ke halaman ini");
        return view('content.laporan.ANALISIS.pdf', compact("tahunAngka", "unitkerja", "role"));
    }
    public function getAnalisis( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $kodeSd = $req->kodeSd;
            $idunit = $req->idunit;

            $filterKodeSd = "AND rkat.kd_sumberdana = '$kodeSd' ";
            $filterUnitKerja = "AND rkat.unit_kerja_rkt = '$idunit' ";
            $joinDataMaster = $tahunAngka >= 2026 ? "LEFT JOIN tb_relasi_iku_rekat rik ON rik.id_rekat = rkat.id_rekat
                LEFT JOIN tb_keg_master keg ON keg.kode_keg = rik.kode_keg AND keg.tahun = '$tahunAngka'
                LEFT JOIN tb_ikv ikv ON ikv.kode_ikv = rik.kode_ikv AND ikv.tahun = '$tahunAngka'
                LEFT JOIN tb_iku iku ON iku.kode_ikk = rik.kode_iku AND iku.tahun = '$tahunAngka'
                LEFT JOIN tb_sasaran ss ON ss.kode_ss = rik.kode_ss AND ss.tahun = '$tahunAngka'" : "JOIN dataMaster dm ON dm.kode_keg = rkat.kd_rk";
            $selectDataMaster = $tahunAngka >= 2026 ?
                "ss.kode_ss, ss.sasaran_program AS ss, iku.kode_ikk, iku.indikator_kinerja_kegiatan AS ikk, ikv.kode_ikv, ikv.ikv, keg.kode_keg, keg.keg AS rincian_kegiatan"
                : "dm.*";
            $baseData = getBaseData("SELECT
                        rkat.*, sd.sumberdana, $selectDataMaster, unit.nama as nama_unit,
                        va.kendala, va.tanggapan_kendala, va.tujuan, va.tanggapan_tujuan, va.resiko, va.tanggapan_resiko,
                        va.alternatif, va.tanggapan_alternatif, va.hasil, va.tanggapan_hasil, va.dampak, va.tanggapan_dampak
                    FROM BaseData rkat
                    $joinDataMaster
                    JOIN tb_unit_api unit ON unit.idunit = rkat.unit_kerja_rkt
                    JOIN sumberdana sd ON sd.kd_sumberdana = rkat.kd_sumberdana
                    LEFT JOIN tb_variabel_analisis va ON va.id_rekat = rkat.id_rekat
                    LEFT JOIN (
                        SELECT id_rab, jenis, SUM(jumlah_tagihan) AS jumlah_tagihan
                        FROM tb_mutasi_percetakan
                        WHERE is_deleted = 'false'
                        GROUP BY id_rab, jenis
                    ) AS relo_sum ON relo_sum.id_rab = rkat.id AND relo_sum.jenis = rkat.rab_type
                    WHERE rkat.is_deleted = 'false' AND rkat.is_deleted_rkt = 'false' $filterKodeSd $filterUnitKerja
            ORDER BY rkat.id_rekat", $tahun, $tahunAngka, null, null, []);
            return response()->json([
                "success" => true,
                "data"    => [
                    "baseData" => $baseData
                ],
                "message" => "Berhasil mengambil data analisis"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data analisis"
            ], 500);
        }
    }
    public function getItemCoa( Request $req ) {
        try {
            [ "tahun" => $tahun, "tahunAngka" => $tahunAngka ] = getTahunData();
            $idRekat    = $req->id;

            $rabWithAggregate = [
                "rabkeg" => function($q) {
                    $q->selectRaw("id_rekat, id_jenis_belanja, jenis_belanja, SUM(jumlah_biaya) as total_biaya")
                      ->where("is_deleted", "false")
                      ->groupBy("id_jenis_belanja", "id_rekat");
                },
                "rabper" => function($q) {
                    $q->selectRaw("id_rekat, id_jenis_belanja, jenis_belanja, SUM(jumlah_biaya) as total_biaya")
                      ->where("is_deleted", "false")
                      ->groupBy("id_jenis_belanja", "id_rekat");
                },
                "rabgdg" => function($q) {
                    $q->selectRaw("id_rekat, id_jenis_belanja, jenis_belanja, SUM(jumlah_nilai) as total_biaya")
                      ->where("is_deleted", "false")
                      ->groupBy("id_jenis_belanja", "id_rekat");
                }
            ];

            $rabWithDetail = [
                "rabkeg" => function($q) {
                    $q->where("is_deleted","false");
                },
                "rabper" => function($q) {
                    $q->where("is_deleted","false");
                },
                "rabgdg" => function($q) {
                    $q->where("is_deleted","false");
                }
            ];

            if ( $tahunAngka >= 2026 ) {
                $relasiWithMaster = function($rel) use ($tahunAngka) {
                    $rel->with([
                        "subkomponenMaster" => function($q) use ($tahunAngka) {
                            $q->where("tahun", $tahunAngka);
                        },
                        "ikv" => function($q) use ($tahunAngka) {
                            $q->where("tahun", $tahunAngka)
                              ->with(["ro" => function($q2) use ($tahunAngka) {
                                    $q2->where("tahun", $tahunAngka)
                                       ->with(["kro" => function($q3) use ($tahunAngka) {
                                            $q3->where("tahun", $tahunAngka);
                                       }]);
                              }]);
                        },
                        "ro" => function($q) use ($tahunAngka) {
                            $q->where("tahun", $tahunAngka)
                              ->with(["kro" => function($q2) use ($tahunAngka) {
                                    $q2->where("tahun", $tahunAngka);
                              }]);
                        },
                        "kro" => function($q) use ($tahunAngka) {
                            $q->where("tahun", $tahunAngka);
                        }
                    ]);
                };

                $coa = Rekat::with(array_merge([
                    "relasiMasterIku" => $relasiWithMaster,
                ], $rabWithAggregate))
                    ->where("id", $idRekat)
                    ->groupBy("kd_rk")
                    ->first();

                $rekat = Rekat::with(array_merge([
                    "relasiMasterIku" => $relasiWithMaster,
                ], $rabWithDetail))
                    ->where("id", $idRekat)
                    ->first();

                $coa   = $this->attachMasterHierarchy($coa);
                $rekat = $this->attachMasterHierarchy($rekat);
            } else {
                $coa = Rekat::with(array_merge([
                    "subkomponen" => function( $x ) use ($tahunAngka){
                        $x->where("tahun", $tahunAngka);
                    }
                ], $rabWithAggregate))
                    ->where("id", $idRekat)
                    ->groupBy("kd_rk")
                    ->first();

                $rekat = Rekat::with(array_merge([
                    "subkomponen" => function( $x ) use ($tahunAngka){
                        $x->where("tahun", $tahunAngka);
                    }
                ], $rabWithDetail))
                    ->where("id", $idRekat)
                    ->first();
            }

            return response()->json([
                "success" => true,
                "data"    => [
                    "coa" => $coa,
                    "rekat" => $rekat
                ],
                "message" => "Berhasil mengambil data item coa"
            ], 200);
        } catch ( \Exception $e ) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal mengambil data analisis"
            ], 500);
        }
    }

    /**
     * Menyatukan data relasi master ke struktur subkomponen (agar kompatibel dengan FE)
     */
    private function attachMasterHierarchy($rekat) {
        if (!$rekat || !$rekat->relasiMasterIku) {
            return $rekat;
        }

        $relasi = $rekat->relasiMasterIku;

        $subkomponen = $relasi->subkomponenMaster;
        if ($subkomponen) {
            $ikv = $relasi->ikv ?? null;
            if ($ikv) {
                $ro = $ikv->ro ?? null;
                if ($ro) {
                    $kro = $ro->kro ?? null;
                    if ($kro) {
                        $ro->setRelation('kro', $kro);
                    }
                    $ikv->setRelation('ro', $ro);
                }
                $subkomponen->setRelation('ikv', $ikv);
            }
            $rekat->setRelation('subkomponen', $subkomponen);
        }

        return $rekat;
    }
    public function storeAnalisis( Request $req ) {
        try {
            // 📦 Init variable
            $role      = session("role");
            $tahun     = session()->get("tahun", "tahun_2025");
            $id_rekat  = $req->id;
            $jenis     = $req->jenis;
            $tanggapan = $req->tanggapan;

            if ( !in_array($role, ["Reviewer", "Pengawasan Internal", "superadmin"]  ) ) {
                Log::channel('security')->warning("Mencoba memberikan tanggapan diluar role yang diizinkan", array_merge([
                    'user_id' => session()->get('id_user', ''),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now(),
                ]));
                return response()->json([ "success" => false, "message" => "Anda tidak memiliki akses untuk memberikan tanggapan"], 403);
            }
            // ⤴️ Save data
            VariabelAnalisis::updateOrCreate(["id_rekat" => $id_rekat] ,[
                "tanggapan_$jenis" => $tanggapan,
            ]);

            // 💬 Log the action
            event(new UserPerformedAction("106", session()->get("id_role"), "Pemberian Tanggapan",
                "Pemberian tanggapan mengenai $jenis pada laporan analisis dengan id rekat: $id_rekat", $req->ip(), $req->userAgent,
                $req->platform, $req->screenSize, $req->lang, "UPDATE"
            ));

            // ✅ Return response
            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data analisis",
                "jenis"   => $jenis
            ], 200);
        } catch ( \Exception $e ) {
            // ⛔️ Return response
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data analisis"
            ], 500);
        }
    }
    public function storeAnalisisOperator( Request $req ) {
        try {
            // 📦 Init variable
            $role      = session("role");
            $tahun     = session("tahun", "tahun_2025");
            $id_rekat  = $req->id;
            $jenis     = $req->jenis;
            $tanggapan = $req->tanggapan;

            if ( in_array( $role, ["Reviewer", "Pengawasan Internal"] ) ) {
                Log::channel('security')->warning("Mencoba memberikan tanggapan diluar role yang diizinkan", array_merge([
                    'user_id' => session()->get('id_user', ''),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now(),
                ]));
                return response()->json([ "success" => false, "message" => "Anda tidak memiliki akses untuk memberikan tanggapan"], 403);
            }

            // ⤴️ Save data
            VariabelAnalisis::updateOrCreate(["id_rekat" => $id_rekat] ,[
                "$jenis" => $tanggapan,
            ]);

            // 💬 Log the action
            event(new UserPerformedAction("106", session()->get("id_role"), "Pemberian Tanggapan",
                "Mengisi analisis resiko melalui laporan mengenai $jenis pada laporan analisis dengan id rekat: $id_rekat, berupa: $tanggapan", $req->ip(), $req->userAgent,
                $req->platform, $req->screenSize, $req->lang, "UPDATE"
            ));

            // ✅ Return response
            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data analisis",
                "jenis"   => $jenis
            ], 200);
        } catch ( \Exception $e ) {
            // ⛔️ Return response
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data analisis"
            ], 500);
        }
    }
    public function storeTanggapan( Request $req ) {
        try {
            // 📦 Init variable
            $role      = session("role");
            $tahun     = session()->get("tahun", "tahun_2025");
            $id        = $req->idItemCoa;
            $jenis     = $req->jenisRab;
            $tanggapan = $req->tanggapan;
            $jenisRab  = $jenis === "rabkeg" ? RABKEG::class : ($jenis === "rabper" ? RABPER::class : RABGDG::class);

            if ( !in_array($role, ["Reviewer", "Pengawasan Internal", "superadmin"] ) ) {
                Log::channel('security')->warning("Mencoba memberikan tanggapan diluar role yang diizinkan", array_merge([
                    'user_id' => session()->get('id_user', ''),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now(),
                ]));
                return response()->json([ "success" => false, "message" => "Anda tidak memiliki akses untuk memberikan tanggapan"], 403);
            }

            // ⤴️ Save data
            $jenisRab::updateOrCreate(["id" => $id], [
                "tanggapan" => $tanggapan
            ]);

            // 💬 Log the action
            event(new UserPerformedAction("106", session()->get("id_role"), "Pemberian Tanggapan",
                "Pemberian tanggapan item coa $jenis pada laporan analisis dengan id item: $id", $req->ip(), $req->userAgent,
                $req->platform, $req->screenSize, $req->lang, "UPDATE"
            ));

            // ✅ Return response
            return response()->json([
                "success" => true,
                "message" => "Berhasil menyimpan data tanggapan",
            ], 200);
        } catch ( \Exception $e ) {
            // ⛔️ Return response
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage(),
                "message" => "Gagal menyimpan data analisis"
            ], 500);
        }
    }
    public function getTOR( Request $req ) {
        try {
            $id = $req->query('id');
            if (!$id)
                abort(404, 'ID tidak ditemukan');
            if (!is_numeric($id) || !ctype_digit((string)$id))
                abort(400, 'Parameter tidak valid');

            $id = intval($id);
            if ($id <= 0 || $id > 2147483647)
                abort(400, 'Parameter tidak valid');

            $documentData = Rekat::select("tor")->where("id", $id)->first();
            if ( !$documentData || !$documentData->tor )
                return response()->json([ "success" => false, "message" => "Dokumen tidak ditemukan"], 404);

            // Construct secure file path
            $fileName = basename($documentData->tor); // Prevent directory traversal
            $filePath = storage_path('app/privatee/torkak/' . $fileName);

            // Additional security checks
            if (!file_exists($filePath))
                return response()->json([ "success" => false, "message" => "Dokumen tidak ditemukan"], 404);

            // Verify file is within allowed directory (prevent directory traversal)
            $realPath = realpath($filePath);
            $allowedPath = realpath(storage_path('app/privatee/torkak/'));
            if (!$realPath || !Str::startsWith($realPath, $allowedPath))
                return response()->json([ "success" => false, "message" => "Dokumen tidak diizinkan"], 403);

            // Get MIME type safely
            $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';

            // Security headers
            $headers = [
                'Content-Type' => $mimeType,
                'Content-Security-Policy' => "default-src 'none'; script-src 'none'; object-src 'none';",
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];

            return response()->file($realPath, $headers);
        } catch (\Exception $e) {
            $message = "Terjadi kesalahan saat mencoba menampilkan dokumen tor";
            $errorMessages = ["Dokumen tidak ditemukan", "Parameter tidak valid", "Akses ditolak"];
            if (in_array( $e->getMessage(), $errorMessages ) ) {
                $message = $e->getMessage();
            }
            return response()->json(["success" => false, "message" => $message, "error" => $e->getMessage() ], 500);
        }
    }
}
