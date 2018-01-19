<?php
    
  class security
  {
    private static $storage = 'user.id';
    
    public static function provider()
    {
      if (defined('SECURITY')) {
        if (in_array('security_interface', @class_implements(SECURITY))) {
          return SECURITY;
        } else {
          return null;
        }
      } else {
        return null;
      }
    }
  
    public static function login()
    {
      if (!user::isauthenicated()) {
        if (cookie::read(security::$storage) != null) {
          user::construct(cookie::read(security::$storage));
        } else {
          $provider = security::provider();
          if ($provider != null) {
            $id = $provider::authenticate();
            if ($id > 0) {
              cookie::write(security::$storage, $id);
              user::construct($id);
              security::redirect('security-login');
            } else {
              die(network::error(rb::get('global.invalid_login')));
            }
          }
        }
      } else {
        security::redirect('security-login');
      }
    }
    
    public static function modal()
    {
      $provider = security::provider();
      if ($provider != null) {
        die($provider::modal());
      }
    }
    
    public static function logout()
    {
      user::destroy();
      cookie::delete(security::$storage);
      session::destroy();
      security::redirect('security-logout');
    }
    
    private static function redirect($mode)
    {
      $tmp = explode(URL_SEPARATOR, $_SERVER['SCRIPT_NAME']);
      $query = array();
      foreach (explode('&', $_SERVER['QUERY_STRING']) as $key => $value) {
        if (substr($value, 0, 7) !== 'openid.' && $value != $mode) {
          array_push($query, $value);
        }
      }
      $querystring = '';
      if (sizeof($query) > 0) {
        $querystring = '?'.implode('&', $querystring);
      }
      header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].URL_SEPARATOR.implode(URL_SEPARATOR, array_splice($tmp, 1, -1)).URL_SEPARATOR.$querystring);
    }
  }
    
?>