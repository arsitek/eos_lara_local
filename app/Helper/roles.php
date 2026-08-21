<?php


function checkRole($roles, $role)
{
  foreach ($roles as $row) {
    if ($row->id_role == $role)
      return true;
  }
  return false;
}
function checkMenu($menus, $menu, $akses)
{
  foreach ($menus as $row) {
    if ($row->id_menu == $menu && $row->is_crud == $akses)
      return true;
  }
  return false;
}


// generate spacing html
function generateSpacing($level)
{
  $spacing = "";
  for ($i = 0; $i < $level; $i++) {
    $spacing .= "&nbsp;&nbsp;&nbsp;&nbsp;";
  }
  return $spacing;
}
