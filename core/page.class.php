<?php

class page
{
  public static $time;
  public static $devices = 'hidden-xs hidden-sm display-md display-lg';

  public static function start($infobox = '', $onload = null)
  {
    self::$time = microtime(true);
    echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    echo '<html xmlns="http://www.w3.org/1999/xhtml">';
    echo '<meta charset="utf-8">';
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<head>';

    $module = module::selfread();
    if (defined('NAME')) {
      if ($module != null) {
        echo '<title>'.NAME.' - '.rb::get($module->id.'.name').'</title>';
      } else {
        echo '<title>'.NAME.'</title>';
      }
    }
    
    echo '<link rel="icon" type="image/x-icon" href="'.CONTEXT.'/favicon.ico" />';
    echo '<meta name="robots" content="noindex">';
    
    $jsinclude = array(CORE.DIRECTORY_SEPARATOR.'js', CORE);
    if ($module != null) {
      array_push($jsinclude, $module->path);
    }
    foreach ($jsinclude as $path) {
      foreach (scandir($path) as $include) {
        if (is_file($path.DIRECTORY_SEPARATOR.$include) && strpos($include, '..') == 0 && strpos($include, 'min') == 0  && strtoupper(pathinfo($include, PATHINFO_EXTENSION)) == 'JS') {
          $ref = str_replace(DIRECTORY_SEPARATOR, URL_SEPARATOR, str_replace(BASE.DIRECTORY_SEPARATOR, '', $path)).URL_SEPARATOR.$include;
          echo '<script type="text/javascript" src="'.CONTEXT.URL_SEPARATOR.$ref.(DEBUG ? '?v='.time() : '?v='.VERSION).'"></script>';
        }
      }
    }
  
    $cssinclude = array(CORE.DIRECTORY_SEPARATOR.'css', CORE);
    if ($module != null) {
      array_push($cssinclude, $module->path);
    }
    foreach ($cssinclude as $path) {
      foreach (scandir($path) as $include) {
        if (is_file($path.DIRECTORY_SEPARATOR.$include) && strpos($include, '..') == 0 && strpos($include, 'min') == 0  && strtoupper(pathinfo($include, PATHINFO_EXTENSION)) == 'CSS') {
          $ref = str_replace(DIRECTORY_SEPARATOR, URL_SEPARATOR, str_replace(BASE.DIRECTORY_SEPARATOR, '', $path)).URL_SEPARATOR.$include;
          echo '<link type="text/css" href="'.CONTEXT.URL_SEPARATOR.$ref.(DEBUG ? '?v='.time() : '?v='.VERSION).'" rel="stylesheet" media="screen" />';
        }
      }
    }
        
    echo '</head>';
    echo '<body '.(isset($onload)?'onload="'.$onload.'"':'').'>';
    topbar::construct();
    echo '<div class="modal" id="modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content" id="modal-content"><br>&nbsp;&nbsp;<i class="fa fa-spinner fa-spin"></i> '.rb::get('core.loading').'<br><br></div></div></div>';
    echo '<div class="container-fluid '.self::$devices.'">';
    echo '<div id="infobox" class="infobox">'.$infobox.'</div>';
  }
          
  public static function end()
  {
    echo '</div>';
    echo '<div class="container-fluid display-xs display-sm hidden-md hidden-lg">';
    echo '<div class="display-xs hidden-sm hidden-md hidden-lg not-supported">';
    echo '<div class="alert alert-danger"><b>'.rb::get('core.mobile_not_supported').'</div>';
    echo '</div>';
    echo '<div class="hidden-xs display-sm hidden-md hidden-lg not-supported">';
    echo '<div class="alert alert-danger"><b>'.rb::get('core.tablets_not_supported').'</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="footer navbar-fixed-bottom">';
    echo '<div class="container-fluid">';
    echo '<p class="text-muted"> <i id="loading" style="margin-left: -10px" class="fa fa-spinner fa-spin hidden"></i> <span id="async">'.rb::get('core.footer', array(date('Y', time()), number_format(microtime(true) - self::$time, 5))).'</span></p>';
    echo '</div>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
  }
}

?>