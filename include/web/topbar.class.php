<?php

class topbar
{
  public static function construct()
  {
    $menuright = array('public' => array(), 'private' => array());
    $menuleft = array('public' => array(), 'private' => array());
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->navbits)) {
        foreach ($tmp->navbits as $menu) {
          if ($menu->menu == '*') {
            if ($menu->position == 'topbar' || $menu->position == 'topbar-left') {
              if (!array_key_exists($menu->order, $menuleft)) {
                if ($menu->authenticated) {
                  $menuleft['private'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                } else {
                  $menuleft['public'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                }
              }
            } elseif ($menu->position == 'topbar-right') {
              if (!array_key_exists($menu->order, $menuright)) {
                if ($menu->authenticated) {
                  $menuright['private'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                } else {
                  $menuright['public'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                }
              }
            }
          }
        }
      }
    }
    
    echo '<div class="navbar navbar-inverse navbar-fixed-top display-xs display-sm display-md display-lg" role="navigation">';
    echo '<div class="navbar-header">';
    if (defined('BRAND') && BRAND != '') {
      echo '<a class="navbar-brand" href="'.CONTEXT.URL_SEPARATOR.'"><img style="max-width:30px; margin-top: -7px;" src="'.CONTEXT.URL_SEPARATOR.BRAND.'"> '.NAME.'</a>';
    } else {
      echo '<a class="navbar-brand" href="'.CONTEXT.URL_SEPARATOR.'">'.NAME.'</a>';
    }
    echo '</div>';

    echo '<div class="navbar-left '.page::$devices.'" stlye="display: none!important;">';
    if (user::isauthenicated()) {
      $menuleft = $menuleft['private'];
    } else {
      $menuleft = $menuleft['public'];
    }
    echo '<ul class="nav navbar-nav">';
    ksort($menuleft);
    foreach ($menuleft as $item) {
      common::run($item);
    }
    echo '<li><div style="width:10px"></div></li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="navbar-right '.page::$devices.'">';
    if (user::isauthenicated()) {
      $menuright = $menuright['private'];
      topbar::profile();
    } else {
      $menuright = $menuright['public'];
      $provider = security::provider();
      if ($provider != null) {
        $provider::construct();
      }
    }
    echo '<ul class="nav navbar-nav">';
    ksort($menuright);
    foreach ($menuright as $item) {
      common::run($item);
    }
    echo '</ul>';
    echo '</div>';
    
    echo '</div>';
  }
  
  public static function renderbit($id, $name, $target, $options = array())
  {
    $module = module::selfread();
    echo '<ul class="nav navbar-nav">';
    if (isset($module->id) && $module->id == $id) {
      echo '<li class="active">';
    } else {
      echo '<li>';
    }
    echo '<a href="'.$target.'">';
    if (isset($options['icon']) && $options['icon'] != '') {
      echo '<i class="fa fa-'.$options['icon'].' fa-fw"></i> ';
    }
    echo $name.'</a>';
    echo '</li>';
    echo '</ul>';
  }
   
  public static function profile()
  {
    echo '<div class="user-img-box pull-right dropdown">';
    echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
    if (user::read('avatar') != '') {
      echo '<img id="profile-img" class="user-img" src="'.user::read('avatar').'" />';
    } else {
      echo '<img id="profile-img" class="user-img" src="'.CFXCONTEXT.URL_SEPARATOR,'web'.URL_SEPARATOR.'img'.URL_SEPARATOR.'avatar.png" />';
    }
    echo '</a>';
    echo '<ul class="dropdown-menu" aria-labelledby="usermenu">';
    if (user::read('name') != '') {
      echo '<li class="user-name">'.user::read('name').'</li>';
      echo '<li role="separator" class="divider"></li>';
    }
    
    if (strlen($_SERVER['QUERY_STRING']) == 0) {
      $_SERVER['REQUEST_URI'] .= '?security-logout';
    } else {
      $_SERVER['REQUEST_URI'] .= '&security-logout';
    }
    echo '<li><a href="'.$_SERVER['REQUEST_URI'].'"><span class="fa navbar-fa fa-sign-out" aria-hidden="true"></span> '.rb::get('core.logout').'</a></li>';
    echo '</ul>';
    echo '</div>';
  }
}

?>