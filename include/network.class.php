<?php

class network
{
  public static $agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';

  public static function success($data = null, $event = null)
  {
    self::write(200, $data, $event);
  }

  public static function error($data, $event = null)
  {
    self::write(500, $data, $event);
  }

  public static function fatal($data, $event = null)
  {
    self::write(999, $data, $event);
  }

  public static function write($status, $data, $event)
  {
    $array = array();
    $array['status'] = $status;
    $array['data'] = (($data == null) ? '' : htmlentities($data));
    $array['event'] = (($event == null) ? '' : $event);
    echo json_encode($array);
  }
  
  public static function data($total, $data) {
    $array = array();
    $array['status'] = 200;
    $array['data'] = (($data == null) ? '' : $data);
    $array['total'] = (($total == null) ? '' : $total);
    echo json_encode($array);
  }

  public static function get($key)
  {
    if (isset($_GET[$key])) {
      return $_GET[$key];
    } else {
      return '';
    }
  }
  
  public static function post($key)
  {
    if (isset($_POST[$key])) {
      return $_POST[$key];
    } else {
      return '';
    }
  }

  public static function ping($url)
  {
    $nurl = parse_url($url);
    if (isset($nurl['host'])) {
      $socket = @fsockopen($nurl['host'], (isset($nurl['port'])? $nurl['port'] : 80), $errno, $errstr, 5);
      if (!$socket) {
          return false;
      } else {
          fclose($socket);
          return true;
      }
    } else {
      return false;
    }
  }
  
  public static function size($url)
  {
    return (isset(get_headers($url, 1)['Content-Length']) ? get_headers($url, 1)['Content-Length'] : 0);
  }

  public static function read($url, $getstartbytes = false)
  {
    if (extension_loaded('curl')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      if ($getstartbytes) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Range: bytes=0-32768'));
      }
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_USERAGENT, self::$agent);
      return curl_exec($ch);
    } else {
      $ctx = stream_context_create(array('http' => array('timeout' => 5)));
      return file_get_contents($url, false, $ctx);
    }
  }
}

?>