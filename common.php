<?php
  if (file_exists(common::config())) {
    require_once(common::config());
  }
   
  common::construct();
  class common
  {
    private static $cache = 60;
  
    public static function construct()
    {
      set_exception_handler(array('common', 'errorhandler'));
      define('COOKIE_LIFETIME', 60*60*24*7*4*3);
      
      define('BASE', dirname(dirname(realpath(__FILE__))));
      define('CONTEXT', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', BASE)));
      define('CFX', dirname(realpath(__FILE__)));
      define('CFXCONTEXT', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', CFX)));
      define('CORE', CFX.DIRECTORY_SEPARATOR.'core');
      define('CACHE', CFX.DIRECTORY_SEPARATOR.'cache');
      define('DATA', CFX.DIRECTORY_SEPARATOR.'data');
            
      if (!defined('VERSION')) {
        $version = json_decode(file_get_contents(CFX.DIRECTORY_SEPARATOR.'VERSION'), true);
        define('VERSION', $version['version']);
      }
            
      define('URL_SEPARATOR', '/');
      
      if (!defined('DEBUG')) {
        define('DEBUG', false);
      }
     
      common::load(CORE.DIRECTORY_SEPARATOR.'include');
      common::load(CORE.DIRECTORY_SEPARATOR.'database');
      common::load(CORE.DIRECTORY_SEPARATOR.'security');
      common::load(CORE);
      foreach (module::read(false) as $key => $module) {
        common::load($module->path);
      }
      $module = module::selfread();
      if ($module != null) {
        common::load($module->path);
      }
      
      common::defaults();
      session::construct();
      db::instance()->construct();
    }
    
    public static function load($path)
    {
      foreach (scandir($path) as $include) {
        if (is_file($path.DIRECTORY_SEPARATOR.$include) && strpos($path.DIRECTORY_SEPARATOR.$include, '.interface.') !== false && strtoupper(pathinfo($include, PATHINFO_EXTENSION)) == 'PHP') {
          if (!class_exists(rtrim(pathinfo($include, PATHINFO_FILENAME), '.interface'))) {
            require_once($path.DIRECTORY_SEPARATOR.$include);
          }
        }
      }
      foreach (scandir($path) as $include) {
        if (is_file($path.DIRECTORY_SEPARATOR.$include) && strpos($path.DIRECTORY_SEPARATOR.$include, '.class.') !== false && strtoupper(pathinfo($include, PATHINFO_EXTENSION)) == 'PHP') {
          if (!class_exists(rtrim(pathinfo($include, PATHINFO_FILENAME), '.class'))) {
            require_once($path.DIRECTORY_SEPARATOR.$include);
          }
        }
      }
    }
    
    public static function run($target)
    {
      $offset = strpos($target, '?');
      if ($offset !== false) {
        $params = substr($target, $offset+1);
        $include = substr($target, 0, $offset);
        foreach (explode('&', $params) as $value) {
          $parts = explode('=', $value);
          if (sizeof($parts) == 2) {
            $_GET[$parts[0]] = $parts[1];
          } else {
            $_GET[$parts[0]] = '';
          }
        }
        include($include);
      } else {
        include($target);
      }
    }
    
    public static function defaults()
    {
      if (!defined('NAME')) {
        define('NAME', rb::get('core.name'));
      }
      if (!defined('NAME')) {
        define('NAME', cache::read('NAME'));
      } else {
        cache::write('NAME', NAME, common::$cache);
      }
      if (!defined('BRAND')) {
        define('BRAND', cache::read('BRAND'));
      } else {
        cache::write('BRAND', BRAND, common::$cache);
      }
      if (!defined('DATABASE')) {
        define('DATABASE', cache::read('DATABASE'));
      } else {
        cache::write('DATABASE', DATABASE, common::$cache);
      }
      if (!defined('SECURITY')) {
        define('SECURITY', cache::read('SECURITY'));
      } else {
        cache::write('SECURITY', SECURITY, common::$cache);
      }
    }
    
    public static function config()
    {
      return dirname(dirname(realpath(__FILE__))).DIRECTORY_SEPARATOR.'config.php';
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