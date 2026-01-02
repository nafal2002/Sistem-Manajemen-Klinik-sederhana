<?php

function is_active($menu = '')
{
  if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
    
    // Handle appointment submenu
    if ($menu === 'appointment') {
      return in_array($currentPage, ['appointment', 'tambah-appointment', 'queue-management']) ? 'active' : '';
    }
    
    // Handle queue-management specifically
    if ($menu === 'queue-management') {
      return $currentPage === 'queue-management' ? 'active' : '';
    }
    
    // Default behavior for other menus
    if ($currentPage === $menu || $currentPage === 'tambah-' . $menu || $currentPage === 'edit-' . $menu) {
      return 'active';
    }
  } else {
    if ($menu === '') {
      return 'active';
    }
  }
  
  return '';
}
