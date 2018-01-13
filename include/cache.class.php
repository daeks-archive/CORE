<?php

class cache
{
  public static function write($key, $value, $read = false, $cache = 24 * 3600)
  {
    $output = CACHE.DIRECTORY_SEPARATOR.md5($key);
    if (file_exists($output)) {
      if (time() - filemtime($output) > $cache || filesize($output) == 0) {
        if (network::ping($value)) {
          file_put_contents($output, network::read($value), LOCK_EX);
        } else {
          file_put_contents($output, $value, LOCK_EX);
        }
      }
    } else {
      if (network::ping($value)) {
        file_put_contents($output, network::read($value), LOCK_EX);
      } else {
        file_put_contents($output, $value, LOCK_EX);
      }
    }
    if ($read) {
      return cache::read($value);
    }
  }
  
  public static function valid($key, $cache = 24 * 3600)
  {
    $output = CACHE.DIRECTORY_SEPARATOR.md5($key);
    if (file_exists($output)) {
      if (time() - filemtime($output) > $cache || filesize($output) == 0) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }
  }
  
  public static function delete($key)
  {
    $output = CACHE.DIRECTORY_SEPARATOR.md5($key);
    if (file_exists($output)) {
      unlink($output);
    }
  }
  
  public static function read($key)
  {
    $output = CACHE.DIRECTORY_SEPARATOR.md5($key);
    if (file_exists($output)) {
      return file_get_contents($output);
    } else {
      return null;
    }
  }
}

?>