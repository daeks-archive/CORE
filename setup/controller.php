<?php

require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'config.php');

if (network::get('action') != '') {
  switch (network::get('action')) {
    case 'init':
      network::success(''.!db::instance()->ping());
      break;
    case 'setup':
      if (!defined('DATABASE') && network::post('database_provider') != '') {
        define('DATABASE', network::post('database_provider'));
      }
      define('DATABASE_HOST', network::post('database_host'));
      define('DATABASE_NAME', network::post('database_name'));
      define('DATABASE_USER', network::post('database_user'));
      define('DATABASE_PWD', network::post('database_pwd'));
      if (!defined('TABLE_PREFIX') && network::post('table_prefix') != '') {
        define('TABLE_PREFIX', network::post('table_prefix'));
      }
      
      if (is_writable(BASE) && is_writeable(CACHE) && db::instance()->ping()) {
        $config = '<?php'.PHP_EOL;
        if (network::post('name') != '') {
          $config .= '  define(\'NAME\', \''.network::post('name').'\');'.PHP_EOL;
        }
        if (network::post('brand') != '') {
          $config .= '  define(\'BRAND\', \''.network::post('brand').'\');'.PHP_EOL;
        }
        $config .= '  define(\'APIKEY\', \''.strtoupper(md5(sha1(uniqid().$_SERVER['SERVER_NAME'].microtime()))).'\');'.PHP_EOL;
        if (network::post('database_provider') != '') {
          $config .= '  define(\'DATABASE\', \''.network::post('database_provider').'\');'.PHP_EOL;
        }
        $config .= '  define(\'DATABASE_HOST\', \''.network::post('database_host').'\');'.PHP_EOL;
        $config .= '  define(\'DATABASE_NAME\', \''.network::post('database_name').'\');'.PHP_EOL;
        $config .= '  define(\'DATABASE_USER\', \''.network::post('database_user').'\');'.PHP_EOL;
        $config .= '  define(\'DATABASE_PWD\', \''.network::post('database_pwd').'\');'.PHP_EOL;
        if (network::post('table_prefix') != '') {
          $config .= '  define(\'TABLE_PREFIX\', \''.network::post('table_prefix').'\');'.PHP_EOL;
        }
        if (network::post('security_provider') != '') {
          $config .= '  define(\'SECURITY\', \''.network::post('security_provider').'\');'.PHP_EOL;
        }
        $config .= '?>';
        file_put_contents(BASE.DIRECTORY_SEPARATOR.'config.php', $config, LOCK_EX);
        
        ob_start();
        modal::start(rb::get('setup.title'), $controller.'?action=end', 'GET');
        echo rb::get('setup.success');
        modal::end(rb::get('setup.ok'), 'success');
        $output = ob_get_clean();
        network::success($output, null);
      } else {
        ob_start();
        modal::start(rb::get('setup.title'), $controller.'?action=end', 'GET');
        if (!is_writeable(BASE)) {
          echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(BASE)).'</div>';
        }
        if (!is_writeable(CACHE)) {
          echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(CACHE)).'</div>';
        }
        if (!is_writeable(DATA)) {
          echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(DATA)).'</div>';
        }
        if (!db::instance()->ping()) {
          echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.invalid_connection').'</div>';
        }
        modal::end(rb::get('setup.retry'), 'danger');
        $output = ob_get_clean();
        network::success($output, null);
      }
      break;
    case 'end':
      network::success('', 'core.reload();');
      break;
    default:
      network::error(rb::get('core.invalid_action', array(network::get('action'))));
      break;
  }
}

?>