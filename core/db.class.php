<?php

class db
{
  private static $instance = array();
  
  public function construct()
  {
    return false;
  }
  
  public function ping()
  {
    return false;
  }
  
  public static function provider()
  {
    if (defined('DATABASE')) {
      if (in_array('database_interface', @class_implements(DATABASE))) {
        return DATABASE;
      } else {
        return null;
      }
    } else {
      return null;
    }
  }
  
  public static function instance($index = 'default')
  {
    $provider = db::provider();
    if ($provider != null) {
      if (!isset(db::$instance[$index]) || !db::$instance[$index] instanceof self) {
        db::$instance[$index] = new $provider($index);
      }
      return db::$instance[$index];
    } else {
      return new db();
    }
  }
}

?>