<?php

class setup
{
  public static function construct()
  {
    if (network::get('type') != '') {
      switch (network::get('type')) {
        case 'controller':
          self::controller();
          page::destroy();
          break;
        case 'dialog':
          self::dialog();
          page::destroy();
          break;
        default:
          break;
      }
    }
  }
  
  public static function controller()
  {
    if (network::get('action') != '') {
      switch (network::get('action')) {
        case 'init':
          network::success(''.!file_exists(common::config()));
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
            modal::start(rb::get('setup.title'), '?setup&type=controller&action=end', 'GET');
            echo rb::get('setup.success');
            modal::end(rb::get('setup.ok'), 'success');
            $output = ob_get_clean();
            network::success($output, null);
          } else {
            ob_start();
            modal::start(rb::get('setup.title'), '?setup&type=controller&action=end', 'GET');
            if (!is_writeable(BASE)) {
              echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(BASE)).'</div>';
            }
            if (!is_writeable(CACHE)) {
              echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(CACHE)).'</div>';
            }
            if (!is_writeable(DATA)) {
              echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(DATA)).'</div>';
            }
            if (!db::instance()->ping()) {
              echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.invalid_connection').'</div>';
            }
            modal::end(rb::get('setup.retry'), 'danger');
            $output = ob_get_clean();
            network::success($output, null);
          }
          break;
        case 'end':
          network::success('', 'global.reload();');
          break;
        default:
          network::error(rb::get('global.invalid_action', array(network::get('action'))));
          break;
      }
    }
  }
  
  public static function dialog()
  {
    if (network::get('action') != '') {
      switch (network::get('action')) {
        case 'setup':
          modal::start(rb::get('setup.title'), '?setup&type=controller&action=setup', 'POST', 'modal-content');
          if (!is_writeable(BASE)) {
            echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(BASE)).'</div>';
          }
          if (!is_writeable(CACHE)) {
            echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(CACHE)).'</div>';
          }
          if (!is_writeable(DATA)) {
            echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('global.error').'</b> '.rb::get('setup.writable', array(DATA)).'</div>';
          }
          if (!defined('NAME') || !defined('BRAND')) {
            echo '<p>'.rb::get('setup.general').'</p>';
          }
          if (!defined('NAME')) {
            echo form::construct(array('id' => 'name', 'name' => rb::get('setup.appname'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          }
          if (!defined('BRAND')) {
            echo form::construct(array('id' => 'brand', 'name' => rb::get('setup.brand'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          }
          echo '<p>'.rb::get('setup.database').'</p>';
          if (!defined('DATABASE')) {
            echo form::construct(array('id' => 'database_provider', 'name' => rb::get('setup.database_provider'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          }
          echo form::construct(array('id' => 'database_host', 'name' => rb::get('setup.database_host'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          echo form::construct(array('id' => 'database_name', 'name' => rb::get('setup.database_name'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          echo form::construct(array('id' => 'database_user', 'name' => rb::get('setup.database_user'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          echo form::construct(array('id' => 'database_pwd', 'name' => rb::get('setup.database_pwd'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          echo form::construct(array('id' => 'table_prefix', 'name' => rb::get('setup.table_prefix'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          if (!defined('SECURITY')) {
            echo '<p>'.rb::get('setup.security').'</p>';
            echo form::construct(array('id' => 'security_provider', 'name' => rb::get('setup.security_provider'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
          }
          modal::end(rb::get('setup.name'), 'success');
          break;
        default:
          network::error(rb::get('global.invalid_action', array(network::get('action'))));
          break;
      }
    }
  }
}

?>