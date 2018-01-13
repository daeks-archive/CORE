<?php
  
class user
{

  public static function construct($id)
  {
    session::write('user.id', $id);
    $permissions = array();
    foreach (module::read() as $key => $tmp) {
      if (isset($tmp->permissions)) {
        if (isset($tmp->permissions->default)) {
          foreach ($tmp->permissions->default as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
              array_push($permissions, $key.'.'.$subvalue);
            }
          }
        }
      }
    }
    session::write('user.permissions', implode(",", $permissions));
  }
  
  public static function id()
  {
    if (session::read('user.id') != null) {
      return session::read('user.id');
    } else {
      return null;
    }
  }

  public static function isauthenicated()
  {
    return ((session::read('user.id') != null) ? true : false);
  }
  
  public static function read($key)
  {
    if (session::read('user.'.$key) != null) {
      return session::read('user.'.$key);
    } else {
      return null;
    }
  }
  
  public static function haspermission($permission)
  {
    if (session::read('user.permissions') != null) {
      return (in_array($permission, explode(",", session::read('user.permissions'))) ? true : false);
    } else {
      return false;
    }
  }
    
  public static function destroy()
  {
    session::delete('user.id');
  }
}
  
?>