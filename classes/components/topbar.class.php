<?php

class topbar
{
  public static function construct($menu = '*')
  {
    $menuright = array('public' => array(), 'private' => array());
    $menuleft = array('public' => array(), 'private' => array());
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->navbits)) {
        foreach ($tmp->navbits as $item) {
          if ($item->menu == $menu) {
            if ($item->position == 'topbar' || $item->position == 'topbar-left') {
              if (!array_key_exists($item->order, $menuleft)) {
                if ($item->authenticated) {
                  $menuleft['private'][$item->order] = $tmp->path.DIRECTORY_SEPARATOR.$item->target;
                } else {
                  $menuleft['public'][$item->order] = $tmp->path.DIRECTORY_SEPARATOR.$item->target;
                }
              }
            } elseif ($item->position == 'topbar-right') {
              if (!array_key_exists($item->order, $menuright)) {
                if ($item->authenticated) {
                  $menuright['private'][$item->order] = $tmp->path.DIRECTORY_SEPARATOR.$item->target;
                } else {
                  $menuright['public'][$item->order] = $tmp->path.DIRECTORY_SEPARATOR.$item->target;
                }
              }
            }
          }
        }
      }
    }
    
    echo '<div class="navbar navbar-inverse navbar-fixed-top display-xs display-sm display-md display-lg" role="navigation">';
    echo '<div class="navbar-header">';
    if (common::constant('BRAND') != '') {
      echo '<a class="navbar-brand" href="'.CONTEXT.URL_SEPARATOR.'"><img style="max-width:30px; margin-top: -7px;" src="'.CONTEXT.URL_SEPARATOR.BRAND.'"> '.common::constant('NAME').'</a>';
    } else {
      echo '<a class="navbar-brand" href="'.CONTEXT.URL_SEPARATOR.'">'.common::constant('NAME').'</a>';
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
  
  public static function bit($id, $name, $target, $options = array())
  {
    $module = module::selfread();
    echo '<ul class="nav navbar-nav">';
    if (isset($module->id) && $module->id == $id) {
      echo '<li class="active">';
    } else {
      if (isset($module->id)) {
        $tmp = explode('.', $module->id);
        if (sizeof($tmp) == 2) {
          if ($tmp[0] == $id) {
            echo '<li class="active">';
          } else {
            echo '<li>';
          }
        } else {
          echo '<li>';
        }
      } else {
        echo '<li>';
      }
    }
    echo '<a href="'.$target.'">';
    if (isset($options['icon']) && $options['icon'] != '') {
      echo '<i class="fa fa-'.$options['icon'].' fa-fw"></i> ';
    }
    echo $name;
    if (isset($options['badge'])) {
      echo '<span style="padding-left: 10px"><span class="badge">'.$options['badge'].'</span></span>';
    }
    echo '</a>';
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
    
    $target = $_SERVER['REQUEST_URI'];
    if (strlen($_SERVER['QUERY_STRING']) == 0) {
      $target .= '?config';
    } else {
      $target .= '&config';
    }
    echo '<li><a href="'.$target.'"><span class="fa navbar-fa fa-gear" aria-hidden="true"></span> '.rb::get('global.config').'</a></li>';
    
    $target = $_SERVER['REQUEST_URI'];
    if (strlen($_SERVER['QUERY_STRING']) == 0) {
      $target .= '?security-logout';
    } else {
      $target .= '&security-logout';
    }
    echo '<li><a href="'.$target.'"><span class="fa navbar-fa fa-sign-out" aria-hidden="true"></span> '.rb::get('global.logout').'</a></li>';
    echo '</ul>';
    echo '</div>';
  }
}

?>