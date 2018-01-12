<?php

class cron extends cronlib
{
  public static $time;
  private static $format = 'd.M.Y - H:i:s';
  private static $offset = 100;
  
  public static function next($schedule = '* * * * *', $time = null)
  {
    return self::get($schedule, 0, $time);
  }
  
  public static function prev($schedule = '* * * * *', $time = null)
  {
    return self::get($schedule, -1, $time);
  }
      
  public static function exec($cmd)
  {
    exec($cmd.' 2>&1', $out, $retval);
    array_unshift($out, 'code: '.$retval.' - msg: ');
    return $out;
  }
  
  public static function call($cmd)
  {
    return eval($cmd);
  }
  
  public static function start()
  {
    self::$time = microtime(true);
    header('Content-Type: text/plain');
  }
  
  public static function end($output = 'OK')
  {
    echo date(cron::$format).' '.basename($_SERVER["SCRIPT_FILENAME"]).' '.number_format(microtime(true) - self::$time, 5).'s -> '.$output;
  }
  
  public static function check($tmp, $path)
  {
    if (isset($tmp->tasks)) {
      foreach ($tmp->tasks as $task) {
        $pid = CACHE.DIRECTORY_SEPARATOR.$task->id.'.cron';
        $date = null;
        if (file_exists($pid)) {
          $date = cron::next($task->schedule, filemtime($pid) + cron::$offset);
        } else {
          $date = cron::next($task->schedule);
          file_put_contents($pid, '', LOCK_EX);
        }
        if (time() >= $date) {
          if (isset($task->target) && strlen($task->target) > 0) {
            $task->target = $path.DIRECTORY_SEPARATOR.$task->target;
            echo date(cron::$format).' Executing TASKID #'.$task->id.' scheduled for '.date(cron::$format, $date).PHP_EOL;
            ob_start();
            common::run($task->target);
            $out = ob_get_clean();
            touch($pid);
            echo 'TASK: '.strip_tags(trim(preg_replace('/\r\n|\r|\n/', ' ', $out))).PHP_EOL;
            echo date(cron::$format).' Executed TASKID #'.$task->id.' "'.$task->target.'"'.PHP_EOL;
          }
        } else {
          echo date(cron::$format).' Skipped TASKID #'.$task->id.' scheduled for '.date(cron::$format, $date).PHP_EOL;
        }
      }
    }
  }
}

?>