<?php
  if (file_exists(common::config())) {
    require_once(common::config());
  }
   
  common::construct();
  class common
  {
    private static $cache = 60;
    private static $types = array('interface', 'class');
  
    public static function construct()
    {
      set_exception_handler(array('common', 'errorhandler'));
      define('COOKIE_LIFETIME', 60*60*24*7*4*3);
      
      define('BASE', dirname(dirname(realpath(__FILE__))));
      define('CONTEXT', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', BASE)));
      define('CFX', dirname(realpath(__FILE__)));
      define('CFXCONTEXT', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', CFX)));
      define('CLASSES', CFX.DIRECTORY_SEPARATOR.'classes');
      define('WEB', CFX.DIRECTORY_SEPARATOR.'web');
      define('PROVIDER', CFX.DIRECTORY_SEPARATOR.'provider');
      define('LANG', CFX.DIRECTORY_SEPARATOR.'lang');
      define('CACHE', CFX.DIRECTORY_SEPARATOR.'cache');
      define('DATA', CFX.DIRECTORY_SEPARATOR.'data');
            
      if (!defined('VERSION')) {
        $version = json_decode(file_get_contents(CFX.DIRECTORY_SEPARATOR.'VERSION'), true);
        define('VERSION', $version['version']);
      }
            
      define('URL_SEPARATOR', '/');
     
      common::load(CLASSES.DIRECTORY_SEPARATOR.'vendor');
      common::load(PROVIDER, true);
      common::load(CLASSES, true);
      foreach (module::read(false) as $key => $module) {
        common::load($module->path);
      }
      $module = module::selfread();
      if ($module != null) {
        common::load($module->path);
      }
      
      session::construct();
      db::instance()->construct();
    }
    
    public static function load($path, $recursive = false)
    {
      $map = array('files' => array(), 'folders' => array());
      foreach (scandir($path) as $object) {
        if (is_file($path.DIRECTORY_SEPARATOR.$object)) {
          array_push($map['files'], $path.DIRECTORY_SEPARATOR.$object);
        }
        if (is_dir($path.DIRECTORY_SEPARATOR.$object) && $object != '.' && $object != '..') {
          array_push($map['folders'], $path.DIRECTORY_SEPARATOR.$object);
        }
      }
      
      foreach ($map['files'] as $object) {
        foreach (common::$types as $type) {
          if (strpos($object, '.'.$type.'.') !== false && strtoupper(pathinfo($object, PATHINFO_EXTENSION)) == 'PHP') {
            if (!class_exists(rtrim(pathinfo($object, PATHINFO_FILENAME), '.'.$type))) {
              require_once($object);
            }
          }
        }
      }
      if ($recursive) {
        foreach ($map['folders'] as $object) {
          common::load($object, $recursive);
        }
      }
    }
    
    public static function run($target)
    {
      $offset = strpos($target, '?');
      if ($offset !== false) {
        $params = substr($target, $offset+1);
        $object = substr($target, 0, $offset);
        foreach (explode('&', $params) as $value) {
          $parts = explode('=', $value);
          if (sizeof($parts) == 2) {
            $_GET[$parts[0]] = $parts[1];
          } else {
            $_GET[$parts[0]] = '';
          }
        }
        include($object);
      } else {
        include($target);
      }
    }
    
    public static function config($item = 'config')
    {
      return dirname(dirname(realpath(__FILE__))).DIRECTORY_SEPARATOR.$item.'.php';
    }
    
    public static function debug()
    {
      if (defined('DEBUG')) {
        return DEBUG;
      } else {
        return false;
      }
    }
    
    public static function constant($item)
    {
      if (defined($item)) {
        return constant($item);
      } else {
        return '';
      }
    }
    
    public static function errorhandler($ex)
    {
      echo '<div style="background-color: white;"><h1>Error detected - '.$ex->getCode().'</h1>';
      echo '<b>Exception:</b> '.$ex->getMessage().'<br>';
      echo '<b>Trace:</b> ';
      echo $ex->getFile().' on line '.$ex->getLine().'<br>';
      foreach ($ex->getTrace() as $key => $value) {
        echo '- '.$value['file'].' on line '.$value['line'].'<br>';
      }
      echo '<br><hr><br><b>SERVER:</b><br>';
      foreach ($_SERVER as $key => $value) {
        echo $key.'='.$value.'<br>';
      }
      echo '</div>';
    }
  }
  
?>