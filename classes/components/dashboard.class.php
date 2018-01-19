<?php

class dashboard
{
  public static function widgetstart($title = '', $type = 'default')
  {
    echo '<div class="panel panel-'.$type.'">';
    echo '<div class="panel-heading"><h3 class="panel-title">'.$title.'</h3></div>';
    echo '<div class="panel-body">';
  }
          
  public static function widgetend()
  {
    echo '</div>';
    echo '</div>';
  }
  
  public static function construct($dashboard = '*')
  {
    $fieldset = array();
    $format = null;
    foreach (module::read() as $key => $tmp) {
      if ($dashboard == '*') {
        if (!isset($tmp->id)) {
          if (isset($tmp->dashboard)) {
            $format = $tmp->dashboard;
          }
        }
      } else {
        if (isset($tmp->id) && $tmp->id == $dashboard) {
          if (isset($tmp->dashboard)) {
            $format = $tmp->dashboard;
          }
        }
      }
    }
    if ($format != null) {
      foreach (module::read() as $key => $tmp) {
        if (isset($tmp->widgets)) {
          foreach ($tmp->widgets as $widget) {
            if ($widget->dashboard == $dashboard) {
              $target = $tmp->path.DIRECTORY_SEPARATOR.$widget->target;
              if (array_key_exists($widget->grid->row, $fieldset)) {
                if (isset($format[$widget->grid->row][$widget->grid->column])) {
                  $column = $widget->grid->column.'-col-sm-'.$format[$widget->grid->row][$widget->grid->column];
                  while (isset($fieldset[$widget->grid->row][$column][$widget->grid->order])) {
                    $widget->grid->order++;
                  }
                  $fieldset[$widget->grid->row][$column][$widget->grid->order] = $target;
                }
              } else {
                if (isset($format[$widget->grid->row])) {
                  $i = 0;
                  $fieldset[$widget->grid->row] = array();
                  foreach ($format[$widget->grid->row] as $size) {
                    $fieldset[$widget->grid->row][$i.'-col-sm-'.$size] = array();
                    $i++;
                  }
                  $column = $widget->grid->column.'-col-sm-'.$format[$widget->grid->row][$widget->grid->column];
                  $fieldset[$widget->grid->row][$column][$widget->grid->order] = $target;
                }
              }
            }
          }
        }
      }
      ksort($fieldset);
    }

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