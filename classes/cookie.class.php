<?php
  
class cookie
{
  public static function write($key, $val, $time = COOKIE_LIFETIME)
  {
    @setcookie(md5($key), base64_encode(rawurlencode($val)), time() + $time, URL_SEPARATOR);
  }
  
  public static function delete($key)
  {
    setcookie(md5($key), '', -1, URL_SEPARATOR);
  }
  
  public static function read($key)
  {
    if (isset($_COOKIE[md5($key)])) {
      return base64_decode(rawurldecode($_COOKIE[md5($key)]));
    } else {
      return null;
    }
  }
}
  
?>