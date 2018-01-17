<?php

class rb
{
  private static $defaultlanguage = 'en';

  public static function get($index, $variables = array())
  {
    $tmp = explode('.', $index);
    $module = '';
    foreach (array_slice($tmp, 0, count($tmp) - 1) as $key => $value) {
      $module .= $value.'.';
    }
    $module = rtrim($module, '.');
    $paths = array(BASE, BASE.DIRECTORY_SEPARATOR.$module, BASE.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $module), LANG.DIRECTORY_SEPARATOR, CFX.DIRECTORY_SEPARATOR.$module);
    
    foreach ($paths as $path) {
      if (sizeof($tmp) > 1) {
        $rb = $path.DIRECTORY_SEPARATOR.$module.'.'.substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2).'.txt';
        if (file_exists($rb)) {
          $bundle = json_decode(utf8_encode(file_get_contents($rb)), true);
          if (isset($bundle[end($tmp)])) {
            return rb::parse($bundle[end($tmp)], $variables);
          } else {
            $rb = $path.DIRECTORY_SEPARATOR.$module.'.'.rb::$defaultlanguage.'.txt';
            if (file_exists($rb)) {
              $bundle = json_decode(utf8_encode(file_get_contents($rb)), true);
              if (isset($bundle[end($tmp)])) {
                return rb::parse($bundle[end($tmp)], $variables);
              } else {
                continue;
              }
            } else {
              continue;
            }
          }
        } else {
          $rb = $path.DIRECTORY_SEPARATOR.$module.'.'.rb::$defaultlanguage.'.txt';
          if (file_exists($rb)) {
            $bundle = json_decode(utf8_encode(file_get_contents($rb)), true);
            if (isset($bundle[end($tmp)])) {
              return rb::parse($bundle[end($tmp)], $variables);
            } else {
              continue;
            }
          } else {
            continue;
          }
        }
      } else {
        continue;
      }
    }
    return $index;
  }
  
  private static function parse($message, $variables)
  {
    foreach ($variables as $key => $value) {
      $message = str_replace('{'.$key.'}', $value, $message);
    }
    return $message;
  }
}

?>