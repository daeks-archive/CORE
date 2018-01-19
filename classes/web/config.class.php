<?php

class config
{
  public static function construct()
  {
    page::start();
    echo '<div class="row">';
    echo '<div class="col-sm-12">';

    echo '<ul class="nav nav-tabs">';
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->config)) {
        foreach ($tmp->config as $config) {
          if (network::get('module') != '' && network::get('module') == $config->id) {
            echo '<li class="active">';
          } else {
            echo '<li>';
          }
          echo '<a href="#" data-toggle="tab" data-query="'.network::convert($tmp->path).URL_SEPARATOR.$config->target.'" data-target="#panel">';
          if (isset($config->icon)) {
            echo '<i class="fa fa-'.$config->icon.' fa-fw"></i> ';
          }
          if (isset($tmp->id)) {
            echo rb::get($tmp->id.'.name');
          } else {
            echo rb::get('global.name');
          }
          echo '</a>';
          echo '</li>';
        }
      }
    }
    echo '</ul>';
    echo '<br>';
    echo '<div class="row">';
    echo '<div class="col-sm-12" id="panel" name="panel">';
    if (network::get('module') != '') { 
      foreach (module::read() as $key => $tmp) {
        if (isset($tmp->config)) {
          foreach ($tmp->config as $config) {
            if (network::get('module') == $config->id) {
              ob_start();
              common::run($tmp->path.DIRECTORY_SEPARATOR.$config->target);
              $output = ob_get_clean();
              $object = json_decode($output);
              if (isset($object->data)) {
                echo html_entity_decode($object->data);
              } else {
                echo html_entity_decode($output);
              }
            }
          }
        }
      }
    }
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</div>';
    page::end();
    page::destroy();
  }
}

?>