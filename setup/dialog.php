<?php

require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'config.php');

if (network::get('action') != '') {
  switch (network::get('action')) {
    case 'setup':
      modal::start(rb::get('setup.title'), $controller.'?action=setup', 'POST', 'modal-content');
      if (!is_writeable(BASE)) {
        echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(BASE)).'</div>';
      }
      if (!is_writeable(CACHE)) {
        echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(CACHE)).'</div>';
      }
      if (!is_writeable(DATA)) {
        echo '<div class="alert alert-danger" role="alert"><b>'.rb::get('core.error').'</b> '.rb::get('setup.writable', array(DATA)).'</div>';
      }
      echo '<p>'.rb::get('setup.general').'</p>';
      echo form::get(array('id' => 'name', 'name' => rb::get('setup.name'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'brand', 'name' => rb::get('setup.brand'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo '<p>'.rb::get('setup.database').'</p>';
      echo form::get(array('id' => 'database_provider', 'name' => rb::get('setup.database_provider'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'database_host', 'name' => rb::get('setup.database_host'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'database_name', 'name' => rb::get('setup.database_name'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'database_user', 'name' => rb::get('setup.database_user'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'database_pwd', 'name' => rb::get('setup.database_pwd'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo form::get(array('id' => 'table_prefix', 'name' => rb::get('setup.table_prefix'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      echo '<p>'.rb::get('setup.security').'</p>';
      echo form::get(array('id' => 'security_provider', 'name' => rb::get('setup.security_provider'), 'validator' => 'data-fv-notempty', 'type' => 'string'), '');
      modal::end(rb::get('setup.name'), 'success');
      break;
    default:
      network::error(rb::get('core.invalid_action', array(network::get('action'))));
      break;
  }
}

?>