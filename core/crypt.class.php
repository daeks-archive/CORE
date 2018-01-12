<?php

class crypt
{
  public static function encrypt($key, $data)
  {
    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);
    return $iv.openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
  }
  
  public static function decrypt($key, $data)
  {
    $key = substr(sha1($key, true), 0, 16);
    return openssl_decrypt(substr($data, 16), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, substr($data, 0, 16));
  }
  
  public static function hash($data, $key = 'sha1')
  {
    return hash($key, $data);
  }
  
  public static function write($id, $key, $data)
  {
    file_put_contents(DATA.DIRECTORY_SEPARATOR.md5($id).'.php', '<?php'.PHP_EOL.'/*|'.crypt::encrypt($key, $data).'|*/'.PHP_EOL.'?>', LOCK_EX);
  }
  
  public static function read($id, $key)
  {
    if (file_exists(DATA.DIRECTORY_SEPARATOR.md5($id).'.php')) {
      $data = file_get_contents(DATA.DIRECTORY_SEPARATOR.md5($id).'.php');
      $data = str_replace('|*/?>', '', str_replace('<?php/*|', '', str_replace(PHP_EOL, '', $data)));
      return crypt::decrypt($key, $data);
    } else {
      return null;
    }
  }
  
  public static function delete($id)
  {
    if (file_exists(DATA.DIRECTORY_SEPARATOR.md5($id).'.php')) {
      unlink(DATA.DIRECTORY_SEPARATOR.md5($id).'.php');
    }
  }
}

?>