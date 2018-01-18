<?php

class sidebar
{
  public static function start($sidebar = '*')
  {
    $menuleft = array('public' => array(), 'private' => array());
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->navbits)) {
        foreach ($tmp->navbits as $menu) {
          if ($menu->menu == $sidebar) {
            if ($menu->position == 'sidebar') {
              if (!array_key_exists($menu->order, $menuleft)) {
                if ($menu->authenticated) {
                  $menuleft['private'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                } else {
                  $menuleft['public'][$menu->order] = $tmp->path.DIRECTORY_SEPARATOR.$menu->target;
                }
              }
            }
          }
        }
      }
    }
    ksort($menuleft);
  
    echo '<div class="row">';
    echo '<div class="col-md-2 col-lg-2"><nav class="navbar navbar-default navbar-fixed-side display-xs display-sm display-md display-lg">';
    echo '<div class="container pull-sm-left"><div class="collapse navbar-collapse hidden-xs hidden-sm display-md display-lg">';
    echo '<ul class="nav navbar-nav hidden-xs hidden-sm display-md display-lg">';
    
    if (user::isauthenicated()) {
      $menuleft = $menuleft['private'];
    } else {
      $menuleft = $menuleft['public'];
    }
    
    foreach ($menuleft as $item) {
      common::run($item);
    }
        
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';
    echo '</div>';
    echo '<div class="col-sm-10 col-lg-10 hidden-xs hidden-sm display-md display-lg">';
  }
  
  public static function bit($id, $name, $target, $options = array())
  {
    $module = module::selfread();
    if (isset($module->id) && $module->id == $id) {
      echo '<li class="navbar-fixed-side-active">';
    } else {
      echo '<li>';
    }
    echo '<a href="'.$target.'">';
    if (isset($options['icon']) && $options['icon'] != '') {
      echo '<i class="fa fa-'.$options['icon'].' fa-fw"></i> ';
    }
    echo $name;
    if (isset($options['beta']) && $options['beta'] == true) {
      echo '<i class="fa fa-flask fa-fw pull-right" data-title="tooltip" data-placement="left" title="'.rb::get('core.beta').'"></i>';
    }
    echo '</a>';
    echo '</li>';
  }
  
  public static function menustart($id, $name, $options = array())
  {
    echo '<li>';
    echo '<a data-toggle="collapse" data-target="#'.$id.'" href="#">';
    if (isset($options['icon']) && $options['icon'] != '') {
      echo '<i class="fa fa-'.$options['icon'].' fa-fw"></i> ';
    }
    echo $name;
    echo '<b class="dropdown-caret glyphicon glyphicon-chevron-down pull-right"></b>';
    echo '</a>';
    echo '<form id="'.$id.'" class="dropdown-menu dropdown-icon collapse-icon collapse in">';
  }
  
  public static function menuend()
  {
    echo '</form>';
    echo '</li>';
  }
  
  public static function menubit($id, $name, $target, $options = array())
  {
    $module = module::selfread();
    if (isset($module->id) && $module->id == $id) {
      echo '<li class="dropdown-active">';
    } else {
      echo '<li>';
    }
    echo '<a href="'.$target.'">';
    if (isset($options['icon']) && $options['icon'] != '') {
      echo '<i class="fa fa-'.$options['icon'].' fa-fw"></i> ';
    }
    echo $name;
    echo '</a>';
    echo '</li>';
  }
    
  public static function end()
  {
    echo '</div>';
    echo '</div>';
  }
}

?>