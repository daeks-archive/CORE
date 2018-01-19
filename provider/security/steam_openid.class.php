<?php
    
  class steam_openid implements security_interface
  {
    public static function construct()
    {
      if (strlen($_SERVER['QUERY_STRING']) == 0) {
        $_SERVER['REQUEST_URI'] .= '?security-login';
      } else {
        $_SERVER['REQUEST_URI'] .= '&security-login';
      }
      echo '<a href="'.$_SERVER['REQUEST_URI'].'" type="_self"><button type="button" class="btn btn-success" style="margin-top: 7px; margin-right: 5px">'.rb::get('global.login_with', array('<span class="fa navbar-fa fa-steam-square" aria-hidden="true"></span>', 'STEAM')).'</button></a>';
    }
    
    public static function authenticate()
    {
      $openid = new LightOpenID($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
      if (!$openid->mode) {
        $openid->identity = 'http://steamcommunity.com/openid';
        header('Location: '.$openid->authUrl());
      } elseif ($openid->mode == 'cancel') {
        // TODO
      } else {
        if ($openid->validate()) {
          $id = $openid->identity;
          $ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
          preg_match($ptn, $id, $matches);
          return $matches[1];
        }
      }
      return 0;
    }
    
    public static function modal()
    {
      echo "";
    }
  }

?>