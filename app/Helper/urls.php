<?php

// get just path file from url

use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function get_filename($url) {
  if ($url == '') return '';
  $urls = explode('/', $url);
  return end($urls);
}


function get_sidebar_menu() {
  $now = Carbon::now();
  $role  = session('id_role');
  $exemptRoles = ['admin', 'superadmin'];
  // get all menu where role_id = $role join menu_role
  $defaultDateTime = '2025-01-01 00:00:00';
  $params = [
    'role' => $role,
  ];

  $whereOpenClose = '';

  if (!in_array(session('role'), $exemptRoles)) {
    $whereOpenClose = "
            AND (
                (
                    mt.open_at <= :now
                    AND mt.close_at >= :now2
                )
                OR mt.open_at = :defaultDateTime
            )
        ";

    $params['now'] = $now;
    $params['now2'] = $now;
    $params['defaultDateTime'] = $defaultDateTime;
  }

  $sql = "
      WITH RECURSIVE menu_tree AS (
          SELECT
              m.*,
              CAST(
                  CONCAT(
                      LPAD(CAST(m.urutan AS UNSIGNED), 5, '0'),
                      '-',
                      LPAD(CAST(m.id AS UNSIGNED), 5, '0')
                  ) AS CHAR(1000)
              ) AS sort_path
          FROM tb_menu m
          WHERE m.id_parent = 0

          UNION ALL

          SELECT
              child.*,
              CAST(
                  CONCAT(
                      parent.sort_path,
                      '/',
                      LPAD(CAST(child.urutan AS UNSIGNED), 5, '0'),
                      '-',
                      LPAD(CAST(child.id AS UNSIGNED), 5, '0')
                  ) AS CHAR(1000)
              ) AS sort_path
          FROM tb_menu child
          INNER JOIN menu_tree parent ON parent.id = child.id_parent
      )

      SELECT mt.*
      FROM menu_tree mt
      INNER JOIN tb_menu_roles mr ON mr.id_menu = mt.id
      WHERE mr.id_role = :role
      $whereOpenClose
      ORDER BY mt.sort_path
    ";

  $menus = Menu::hydrate(DB::connection('sirekat')->select($sql, $params));
  return $menus;
}
function get_class_slide($level) {
  $slide = [
    'slide',
    'sub-slide'
  ];
  return $slide[$level - 1];
}

function get_class_menu($level) {
  $menu = [
    'side-menu',
    'sub-side-menu'
  ];
  return $menu[$level - 1];
}

function get_user_location() {
  return Http::get("https://geolocation-db.com/json/")->json();
}
function getPlatform($userAgent) {
  $platforms = array(
    'Windows' => 'Windows',
    'Macintosh' => 'Mac',
    'iPhone' => 'iPhone',
    'iPad' => 'iPad',
    'Android' => 'Android',
    'Linux' => 'Linux'
  );

  foreach ($platforms as $platform => $value) {
    if (stripos($userAgent, $platform) !== false) {
      return $value;
    }
  }
  return 'Unknown';
}
