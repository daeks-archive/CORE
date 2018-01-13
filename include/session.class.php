<?php
  
class session
{
  public static function construct()
  {
    if (defined('COOKIE_LIFETIME')) {
      ini_set('session.cookie_lifetime', COOKIE_LIFETIME);
    }
    session_name(md5(BASE));
    session_start();
    
    if (isset($_GET['security-login'])) {
      security::login();
    }
    if (isset($_GET['security-modal'])) {
      security::modal();
    }
    if (isset($_GET['security-logout'])) {
      security::logout();
    }
  }
  
  public static function destroy()
  {
    session_destroy();
  }
  
  public static function read($key)
  {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    } else {
      return null;
    }
  }
  
  public static function write($key, $value)
  {
    $_SESSION[$key] = $value;
  }
  
  public static function delete($key)
  {
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
    }
  }
}
  
?>