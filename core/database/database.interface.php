<?php

  interface database_interface
  {
    public function construct();
    public function ping();
    public function query($query, $data = null);
    public function read($table, $fields = '*', $conditions = null, $options = array());
    public function write($table, $data, $conditions = null);
    public function delete($table, $conditions = null);
    public function setup($schema);
    public function quote($text);
  }

?>