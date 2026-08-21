<?php
namespace App\Services\Master;

use App\Models\MasterUnitApi;
use Illuminate\Support\Facades\DB;

class UnitkerjaService {
    public function getUnitkerjaWithParents( $idunit, $adminRoles = [] ) {
        $filterUnit = "AND rkt.unit_kerja = ?";
        $role = session('role');
        $nip  = session('id_user');

        $notAllowedRoles  = in_array($role, ['Wakil Rektor', 'Direktur', 'operator', 'Pimpinan Unit']);
        $isSuperadmin  = in_array($role, $adminRoles);

        $filterUnit = "";
        $params = [];

        if (!$isSuperadmin && $nip !== "196709261992031002" && $notAllowedRoles ) {
            if ( in_array($role, ['operator'])) {
                $filterUnit = "AND rkt.unit_kerja = ?";
                $params[] = $idunit;
            } else {
                $filterUnit = "AND rkt.unit_kerja LIKE ?";
                $params[] = $idunit . '%';
            }
        }
        $data = DB::connection('sirekat')->select("WITH RECURSIVE up AS (
            -- anchor: only the units that appear in tb_rekat (and not deleted)
            SELECT u.idunit, u.nama, u.kode_parent, CHAR_LENGTH(u.idunit) AS lvl_len, u.idunit AS starting_unit
            FROM tb_unit_api u
            JOIN ( SELECT rkt.unit_kerja FROM tb_rekat rkt WHERE rkt.is_deleted = 'false' $filterUnit ) r ON r.unit_kerja = u.idunit
            UNION ALL
            -- walk up to the parent until root
            SELECT p.idunit, p.nama, p.kode_parent, CHAR_LENGTH(p.idunit) AS lvl_len, up.starting_unit
            FROM up
            JOIN tb_unit_api p ON p.idunit = up.kode_parent
            WHERE up.lvl_len > 1
            )
            SELECT starting_unit,
            MIN(CASE WHEN lvl_len = 1  THEN idunit END) AS idunit_1, MIN(CASE WHEN lvl_len = 1  THEN nama   END) AS nama_1,
            MIN(CASE WHEN lvl_len = 5  THEN idunit END) AS idunit_5, MIN(CASE WHEN lvl_len = 5  THEN nama   END) AS nama_5,
            MIN(CASE WHEN lvl_len = 7  THEN idunit END) AS idunit_7, MIN(CASE WHEN lvl_len = 7  THEN nama   END) AS nama_7,
            MIN(CASE WHEN lvl_len = 9  THEN idunit END) AS idunit_9, MIN(CASE WHEN lvl_len = 9  THEN nama   END) AS nama_9,
            MIN(CASE WHEN lvl_len = 11 THEN idunit END) AS idunit_11, MIN(CASE WHEN lvl_len = 11 THEN nama   END) AS nama_11
            FROM up
            GROUP BY starting_unit
            ORDER BY idunit_1, idunit_5, idunit_7, idunit_9, idunit_11;
        ", $params);
        return empty($data) ? [] : $data;
    }
    public function getUnitKerjaById(?string $idunit): ?MasterUnitApi {
        $data = MasterUnitApi::where("idunit", $idunit)->first();
        return empty($data) ? null : $data;
    }
}
