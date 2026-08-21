<?php
namespace App\Services\Revisi;

use App\Models\Datamaster\Ikv;
use App\Models\Datamaster\Kro;
use App\Models\Datamaster\Ro;
use App\Models\Datarevisi\SisaSaldo;
use App\Models\Datarevisi\SisaSaldoValidasi;
use Illuminate\Support\Facades\DB;

class SaldoService {
    public function validateSaldo($idunit, $kodeSd, $jenisRevisi, $tahun) {
        if (empty($idunit) || empty($kodeSd) || empty($tahun) || empty($jenisRevisi) )
            throw new \Exception("Parameter tidak lengkap.");
    }
    public function getSaldoAwal($idunit, $kodeSd, $jenisRevisi, $jenisSaldo, $tahun) {
        $filterJenis = " AND sv.jenis = ?  ";

        if ( $jenisSaldo == "alokasi" ) {
            $alokasi         = getAlokasi($idunit, $kodeSd, $tahun);
            $alokasiTerpakai = getPaguTerpakai($idunit, $kodeSd, $tahun, null, null );
            $sisaSaldo       = sumSisaSaldo($idunit, $kodeSd, $tahun);
            $sisaAlokasi     = ($alokasi ?? 0) - ( ( $alokasiTerpakai["total"] ?? 0 ) + (int)( $sisaSaldo["0"]->TOTAL ?? 0 ) );
            return $sisaAlokasi;
        } else if ( $jenisRevisi == "KK" ) {
            $tahunAngka = explode("_", $tahun)[1];
            $params = [ $tahunAngka, $tahun, $idunit, $kodeSd, $jenisRevisi, $jenisSaldo ];
            return DB::connection('sirekat')->select("WITH iku AS ( SELECT iku.kode_ikk AS kode FROM tb_iku iku WHERE iku.tahun = ? ),
                saldoValidasi AS (
                    SELECT sv.kode_ikk AS kode, sv.sisa_saldo, sv.jenis FROM tb_saldo_validasi sv
                    WHERE sv.tahun = ? AND sv.idunit = ?
                    AND sv.sd = ? AND sv.jenis_saldo = ? $filterJenis
                ) SELECT iku.kode, sv.sisa_saldo, sv.jenis FROM iku
            JOIN saldoValidasi sv ON sv.kode = iku.kode", $params);
        } else if ( $jenisRevisi == "RO" ) {
            $params = [ $tahun, $idunit, $kodeSd, $jenisRevisi, $jenisSaldo ];
            return DB::connection('sirekat')->select("SELECT sv.kode_ss AS kode, sv.kode_ss, sv.kode_komponen, sv.sisa_saldo, sv.jenis
                FROM tb_saldo_validasi sv
                WHERE sv.tahun = ? AND sv.idunit = ? AND sv.sd = ? AND sv.jenis_saldo = ? $filterJenis
                ORDER BY sv.kode_ss, sv.kode_komponen", $params);
        } else if ( $jenisRevisi == "SS" ) {
            $params = [ $tahun, $idunit, $kodeSd, $jenisRevisi, $jenisSaldo ];
            return DB::connection('sirekat')->select("SELECT sv.kode_ss AS kode, sv.kode_ss, sv.kode_komponen, sv.sisa_saldo, sv.jenis
                FROM tb_saldo_validasi sv
                WHERE sv.tahun = ? AND sv.idunit = ? AND sv.sd = ? AND sv.jenis_saldo = ? $filterJenis
                ORDER BY sv.kode_ss, sv.kode_komponen", $params);
        }
    }
    public function getSaldoMenjadi($idunit, $kodeSd, $jenisRevisi, $jenisSaldo, $tahun) {
        $filterJenis = " AND sv.jenis = ?  ";

        if ( $jenisSaldo == "alokasi" ) {
            $alokasi         = getAlokasi($idunit, $kodeSd, $tahun);
            $alokasiTerpakai = getPaguTerpakai($idunit, $kodeSd, $tahun, null, null );
            $sisaSaldo       = sumSisaSaldo($idunit, $kodeSd, $tahun);
            $sisaAlokasi     = ($alokasi ?? 0) - ( ( $alokasiTerpakai["total"] ?? 0 ) + (int)( $sisaSaldo["0"]->TOTAL ?? 0 ) );
            return $sisaAlokasi;
        }

        if ( $jenisRevisi == "KK" ) {
            $tahunAngka = explode("_", $tahun)[1];
            $params = [ $tahunAngka, $tahun, $idunit, $jenisRevisi, $kodeSd ];
            if ( $jenisSaldo != "semua" ) $params[] = $jenisSaldo;

            return DB::connection('sirekat')->select("WITH iku AS ( SELECT iku.kode_ikk AS kode FROM tb_iku iku WHERE iku.tahun = ? ),
                saldoValidasi AS (
                    SELECT sv.kode_ikk AS kode, sv.sisa_saldo, sv.jenis FROM tb_saldo_validasi sv
                    WHERE sv.tahun = ? AND sv.idunit = ?
                    AND sv.jenis_saldo = ?  AND sv.sd = ? " . ($jenisSaldo == "semua" ? "" : " $filterJenis ") . "
                ) SELECT iku.kode, sv.sisa_saldo, sv.jenis FROM iku
            LEFT JOIN saldoValidasi sv ON sv.kode = iku.kode", $params);
        } else if ( $jenisRevisi == "RO" ) {
            $params = [ $tahun, $idunit, $jenisRevisi, $kodeSd ];
            if ( $jenisSaldo != "semua" ) $params[] = $jenisSaldo;

            return DB::connection('sirekat')->select("SELECT sv.kode_ss AS kode, sv.kode_ss, sv.kode_komponen, sv.sisa_saldo, sv.jenis
                FROM tb_saldo_validasi sv
                WHERE sv.tahun = ? AND sv.idunit = ? AND sv.jenis_saldo = ? AND sv.sd = ?" . ($jenisSaldo == "semua" ? "" : " AND sv.jenis = ? ") . "
                ORDER BY sv.kode_ss, sv.kode_komponen", $params);
        } else if ( $jenisRevisi == "SS" ) {
            $params = [ $tahun, $idunit, $kodeSd ];
            $query = "SELECT sv.kode_ss AS kode, sv.kode_ss, sv.kode_komponen, sv.sisa_saldo, sv.jenis
                FROM tb_saldo_validasi sv
                WHERE sv.tahun = ? AND sv.idunit = ? AND sv.sd = ? AND sv.jenis_saldo = 'SS'";

            if ( $jenisSaldo && $jenisSaldo !== 'semua' ) {
                $query  .= " AND sv.jenis = ?";
                $params[] = $jenisSaldo;
            }

            $query .= " ORDER BY sv.kode_komponen, sv.kode_ss";
            return DB::connection('sirekat')->select($query, $params);
        }
    }
    public function pindahSaldo( $idunit, $kodeSd, $jenisSaldo, $jenisSaldoAwal, $jenisSaldoMenjadi, $tahun, $kodeAwal, $kodeMenjadi, $saldoAwal, $saldoMenjadi, $kodeKomponenAwal = null, $kodeKomponenMenjadi = null ) {
        // Pastikan saldo tujuan tidak melampaui pagu yang tersedia (kecuali jika tujuan alokasi)
        if ( $kodeMenjadi !== 'alokasi' ) {
            $paguStatus = cekPagu( $idunit, $kodeSd, $tahun, $saldoMenjadi, true );
            if ( $paguStatus === 'error' ) {
                throw new \Exception("Saldo melampaui pagu yang tersedia.");
            }
        }

        $defaultWhere = [ "idunit" => $idunit, "sd" => $kodeSd, "jenis_saldo" => $jenisSaldo, "tahun" => $tahun ];
        $whereAwal    = [];
        $whereMenjadi = [];

        if ( $jenisSaldo === "KK" ) {
            $whereAwal    = array_merge($defaultWhere, [ "kode_ikk" => $kodeAwal, "jenis" => $jenisSaldoAwal ]);
            $whereMenjadi = array_merge($defaultWhere, [ "kode_ikk" => $kodeMenjadi, "jenis" => $jenisSaldoMenjadi ]);

            if ( !is_null($kodeKomponenAwal) )    $whereAwal["kode_komponen"]    = $kodeKomponenAwal;
            if ( !is_null($kodeKomponenMenjadi) ) $whereMenjadi["kode_komponen"] = $kodeKomponenMenjadi;
        } else if ( $jenisSaldo === "RO" ) {
            $whereAwal    = array_merge($defaultWhere, [ "kode_ss" => $kodeAwal, "jenis" => $jenisSaldoAwal ]);
            $whereMenjadi = array_merge($defaultWhere, [ "kode_ss" => $kodeMenjadi, "jenis" => $jenisSaldoMenjadi ]);

            if ( !is_null($kodeKomponenAwal) )    $whereAwal["kode_komponen"]    = $kodeKomponenAwal;
            if ( !is_null($kodeKomponenMenjadi) ) $whereMenjadi["kode_komponen"] = $kodeKomponenMenjadi;
        } else if ( $jenisSaldo === "SS" ) {
            $whereAwal    = array_merge($defaultWhere, [ "jenis" => $jenisSaldoAwal ]);
            $whereMenjadi = array_merge($defaultWhere, [ "jenis" => $jenisSaldoMenjadi ]);

            if ( !is_null($kodeKomponenAwal) )    $whereAwal["kode_komponen"]    = $kodeKomponenAwal;
            if ( !is_null($kodeKomponenMenjadi) ) $whereMenjadi["kode_komponen"] = $kodeKomponenMenjadi;
        }
        if ( $jenisSaldoAwal === "alokasi" ) {
            return SisaSaldoValidasi::updateOrCreate($whereMenjadi, array_merge($whereMenjadi, [ "sisa_saldo" => $saldoMenjadi ]));
        } // else if ( $kodeMenjadi === "alokasi" ) {
        //     return SisaSaldoValidasi::updateOrCreate($whereAwal, array_merge($whereAwal, [ "sisa_saldo" => $saldoAwal ]));
        // }
        DB::connection('sirekat')->select(function() use ($whereAwal, $whereMenjadi, $saldoAwal, $saldoMenjadi) {
            SisaSaldoValidasi::updateOrCreate($whereAwal, array_merge($whereAwal, [ "sisa_saldo" => $saldoAwal ]));
            SisaSaldoValidasi::updateOrCreate($whereMenjadi, array_merge($whereMenjadi, [ "sisa_saldo" => $saldoMenjadi ]));
        });
    }
    public function getKomponen($tahun, $jenisRevisi) {
        if ( $jenisRevisi == "KK" ) {
            $komponen = DB::connection('sirekat')->select("SELECT kode_sub_klasifikasi AS kode_komponen, sub_klasifikasi AS komponen FROM tb_keg_master WHERE tahun = ? GROUP BY kode_sub_klasifikasi, sub_klasifikasi ORDER BY kode_sub_klasifikasi, sub_klasifikasi", [$tahun]);
            $sasaran  = DB::connection('sirekat')->select("SELECT kode_ss, sasaran_program FROM tb_sasaran WHERE tahun = ? GROUP BY kode_ss, sasaran_program ORDER BY kode_ss", [$tahun]);
            return [
                "komponen" => $komponen,
                "sasaran"  => $sasaran
            ];
        } if ( in_array($jenisRevisi, ["SS", "RO"] ) ) {
            $komponen = DB::connection('sirekat')->select("SELECT kode_klasifikasi AS kode_komponen, klasifikasi AS komponen FROM tb_keg_master WHERE tahun = ? GROUP BY kode_klasifikasi, klasifikasi ORDER BY kode_klasifikasi, klasifikasi", [$tahun]);
            return [
                "komponen" => $komponen
            ];
        }
        return [];
    }
}
