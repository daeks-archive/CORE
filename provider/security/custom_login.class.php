<?php
    
  class custom_login implements security_interface
  {
    public static function construct()
    {
      if (strlen($_SERVER['QUERY_STRING']) == 0) {
        $_SERVER['REQUEST_URI'] .= '?security-modal';
      } else {
        $_SERVER['REQUEST_URI'] .= '&security-modal';
      }
      echo '<button type="button" class="btn btn-success" data-toggle="modal" href="'.$_SERVER['REQUEST_URI'].'" data-target="#modal" style="margin-top: 7px; margin-right: 5px">'.rb::get('global.login').'</button>';
    }
    
    public static function authenticate()
    {
      if (isset($_POST['username']) && isset($_POST['password'])) {
        $stmt = db::instance()->read('user', '*', 'username='.db::instance()->quote($_POST['username']).' and password='.db::instance()->quote(crypt::hash($_POST['password'])));
        if ($stmt->rowCount() == 1) {
          $row = $stmt->fetch();
          return $row['ID'];
        }
      }
      return 0;
    }
    
    public static function modal()
    {
      modal::start(rb::get('global.login'), str_replace('security-modal', 'security-login', $_SERVER['REQUEST_URI']));
      echo form::construct(array('id' => 'username', 'name' => rb::get('global.username'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::construct(array('id' => 'password', 'name' => rb::get('global.password'), 'validator' => 'data-fv-notempty', 'type' => 'password'), '');
      modal::end(rb::get('global.login'));
    }
  }

?>