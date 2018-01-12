<?php

require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'config.php');

header('Content-Type: text/plain');
foreach (module::read() as $key => $module) {
  cron::check($module, $module->path);
}
  
?>