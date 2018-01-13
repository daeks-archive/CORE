<?php

class panel
{
  public static function start($title = '', $type = 'default')
  {
    echo '<div class="panel panel-'.$type.'">';
    echo '<div class="panel-heading"><h3 class="panel-title">'.$title.'</h3></div>';
    echo '<div class="panel-body">';
  }
          
  public static function end()
  {
    echo '</div>';
    echo '</div>';
  }
  
  public static function dashboard($dashboard = '*')
  {
    $fieldset = array();
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->widgets)) {
        foreach ($tmp->widgets as $widget) {
          if ($widget->dashboard == $dashboard) {
            $target = $tmp->path.DIRECTORY_SEPARATOR.$widget->target;
            if (array_key_exists($widget->grid->row, $fieldset)) {
              $position = $widget->grid->position.'-col-sm-'.$widget->grid->size[$widget->grid->position];
              while (isset($fieldset[$widget->grid->row][$position][$widget->grid->order])) {
                $widget->grid->order++;
              }
              $fieldset[$widget->grid->row][$position][$widget->grid->order] = $target;
            } else {
              $i = 0;
              $fieldset[$widget->grid->row] = array();
              foreach ($widget->grid->size as $size) {
                $fieldset[$widget->grid->row][$i.'-col-sm-'.$size] = array();
                $i++;
              }
              $position = $widget->grid->position.'-col-sm-'.$widget->grid->size[$widget->grid->position];
              $fieldset[$widget->grid->row][$position][$widget->grid->order] = $target;
            }
          }
        }
      }
    }
    ksort($fieldset);

    foreach ($fieldset as $key => $row) {
      echo '<div class="row">';
      foreach ($row as $key => $column) {
        $tmp = explode('-', $key);
        unset($tmp[0]);
        echo '<div class="'.implode('-', $tmp).'">';
        ksort($column);
        foreach ($column as $key => $panel) {
          common::run($panel);
        }
        echo '</div>';
      }
      echo '</div>';
    }
  }
}

?>