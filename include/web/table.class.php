<?php

class table
{
  public static function toolbar($buttons)
  {
    echo '<div id="toolbar">';
    echo '<div class="form-inline" role="form">';
    foreach ($buttons as $button) {
      echo $button.' ';
    }
    echo '</div>';
    echo '</div>';
  }
  
  public static function construct($url, $fields, $size = 10, $search = true)
  {
    echo '<table id="table"';
    echo ' data-toggle="table"';
    echo ' data-ajax="core.table.refresh"';
    if ($size > 0) {
      echo ' data-pagination="true"';
      echo ' data-page-size="'.$size.'"';
      echo ' data-page-list="[10, 25, 50, 100]"';
    }
    echo ' data-side-pagination="server"';
    echo ' data-query="'.$url.'"';
    echo ' data-search="'.$search.'"';
    echo ' data-toolbar="#toolbar"';
    echo ' data-show-refresh="true"';
    echo ' data-show-export="true"';
    echo ' data-show-columns="true"';
    echo ' data-resizable="true"';
    echo ' data-id-field="id"';
    echo ' data-row-style="rowStyle"';
    echo '><thead>';
    echo '<tr>';
    foreach ($fields as $field) {
      echo '<th data-field="'.$field['id'].'"';
      if (isset($field['sort'])) {
        echo ' data-sortable="'.$field['sort'].'"';
      }
      if (isset($field['width'])) {
        echo ' data-width="'.$field['width'].'"';
      }
      if (isset($field['format'])) {
        echo ' data-formatter="'.$field['format'].'"';
      }
      if (isset($field['align'])) {
        echo ' data-align="'.$field['align'].'"';
      }
      if (isset($field['halign'])) {
        echo ' data-halign="'.$field['halign'].'"';
      }
      echo '>'.$field['name'].'</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '</table>';
  }
}

?>