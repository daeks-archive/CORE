<?php

class mysql implements database_interface
{
  public static $jdb = 'JDB';
  private $handle;
     
  public function __construct()
  {
    if (defined('DATABASE_HOST') && defined('DATABASE_NAME') && defined('DATABASE_USER') && defined('DATABASE_PWD')) {
      $this->handle = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, DATABASE_USER, DATABASE_PWD);
      $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->handle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
  }
  
  public function construct()
  {
    foreach (array_slice(scandir(DATA), 2) as $item) {
      if (strtoupper(pathinfo($item, PATHINFO_EXTENSION)) == mysql::$jdb) {
        $this->setup(DATA.DIRECTORY_SEPARATOR.$item);
      }
    }
    foreach (module::read() as $key => $module) {
      foreach (scandir($module->path) as $include) {
        if (strtoupper(pathinfo($include, PATHINFO_EXTENSION)) == mysql::$jdb) {
          $this->setup($module->path.DIRECTORY_SEPARATOR.$include);
        }
      }
    }
  }
  
  public function ping()
  {
    try {
      if (defined('DATABASE_HOST') && defined('DATABASE_NAME') && defined('DATABASE_USER') && defined('DATABASE_PWD')) {
        $test = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, DATABASE_USER, DATABASE_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        return true;
      } else {
        return false;
      }
    } catch (PDOException $e) {
      return false;
    }
  }
  
  public function query($query, $data = null)
  {
    if ($this->handle != null) {
      $stmt = $this->handle->prepare($query);
      $stmt->execute($data);
      return $stmt;
    } else {
      return null;
    }
  }
  
  public function read($table, $fields = '*', $conditions = null, $options = array())
  {
    $query = 'SELECT '.$fields.' FROM `'.common::constant('TABLE_PREFIX').$table.'`';
    if ($conditions != null) {
      $query .= ' WHERE '.$conditions;
    }
    
    if (sizeof($options) > 0) {
      if (isset($options['group_by'])) {
        $query .= " GROUP BY ".$options['group_by'];
      }

      if (isset($options['order_by'])) {
        $query .= " ORDER BY ".$options['order_by'];
        if (isset($options['order_dir'])) {
          $query .= " ".strtoupper($options['order_dir']);
        }
      }

      if (isset($options['limit_start']) && isset($options['limit'])) {
        $query .= " LIMIT ".$options['limit_start'].", ".$options['limit'];
      } elseif (isset($options['limit'])) {
        $query .= " LIMIT ".$options['limit'];
      }
    }
    
    return $this->query($query);
  }
  
  public function write($table, $data, $conditions = null)
  {
    if ($conditions != null) {
      if ($this->query('SELECT * FROM `'.common::constant('TABLE_PREFIX').$table.'` WHERE '.$conditions)->rowCount() > 0) {
        $fields = '';
        foreach ($data as $key => $value) {
          $fields .= $key.'=?,';
        }
      
        $query = 'UPDATE `'.common::constant('TABLE_PREFIX').$table.'` SET '.rtrim($fields, ',');
        if ($conditions != null) {
          $query .= ' WHERE '.$conditions;
        }
        return $this->query($query, array_values($data));
      } else {
        $fields = '';
        $keys = array();
        foreach ($data as $key => $value) {
          $fields .= '?,';
          array_push($keys, '`'.$key.'`');
        }
        
        $query = 'INSERT INTO `'.common::constant('TABLE_PREFIX').$table.'` ('.implode(',', $keys).') VALUES ('.rtrim($fields, ',').')';
        return $this->query($query, array_values($data));
      }
    } else {
      $fields = '';
      $keys = array();
      foreach ($data as $key => $value) {
        $fields .= '?,';
        array_push($keys, '`'.$key.'`');
      }
      
      $query = 'INSERT INTO `'.common::constant('TABLE_PREFIX').$table.'` ('.implode(',', $keys).') VALUES ('.rtrim($fields, ',').')';
      return $this->query($query, array_values($data));
    }
  }

  public function delete($table, $conditions = null)
  {
    if ($conditions != null) {
      return $this->query('DELETE FROM `'.common::constant('TABLE_PREFIX').$table.'` WHERE '.$conditions);
    } else {
      return $this->query('DELETE FROM `'.common::constant('TABLE_PREFIX').$table.'`');
    }
  }
  
  public function setup($schema)
  {
    if (!$this->exists('schema')) {
      $this->query('CREATE TABLE `'.common::constant('TABLE_PREFIX').'schema` ( `ID` INT NOT NULL AUTO_INCREMENT , `NAME` VARCHAR(255) NOT NULL , `VERSION` INT NOT NULL , PRIMARY KEY (`ID`)) ENGINE = MyISAM');
    }
    
    $array = json_decode(file_get_contents($schema), true);
    $name = pathinfo($schema, PATHINFO_FILENAME);
    if (!$this->exists($name)) {
      foreach (explode(' ', $array['type']) as $type) {
        switch ($type) {
          case 'create':
            $stmt = $this->write('schema', array('NAME' => $name, 'VERSION' => $array['version']));
            if (isset($array['schema'])) {
              $columns = array();
              $primarykey = '';
              foreach ($array['schema'] as $item) {
                array_push($columns, '`'.strtoupper($item['name']).'` '.strtoupper($item['type']));
                if (isset($item['primary']) && $item['primary'] == true) {
                  $primarykey = ', PRIMARY KEY (`'.strtoupper($item['name']).'`)';
                }
              }
              $stmt = $this->query('CREATE TABLE `'.common::constant('TABLE_PREFIX').$name.'` ('.implode(',', $columns).$primarykey.') ENGINE = '.$array['engine']);
              if (isset($array['index'])) {
                foreach ($array['index'] as $item) {
                  $stmt = $this->query('ALTER TABLE `'.common::constant('TABLE_PREFIX').$name.'` ADD INDEX `'.$item['name'].'` ('.implode(',', $item['schema']).')');
                }
              }
            }
            break;
          default:
            break;
        }
      }
    } else {
      if (isset($array['type']) && isset($array['version'])) {
        $stmt = $this->read('schema', 'version', 'name = '.$this->quote($name));
        if ($stmt->rowCount() == 1) {
          $result = $stmt->fetchAll()[0];
          if ($result['version'] < $array['version']) {
            foreach (explode(' ', $array['type']) as $type) {
              switch ($type) {
                case 'recreate':
                  $stmt = $this->query('DROP TABLE `'.common::constant('TABLE_PREFIX').$name.'`');
                  if (isset($array['schema'])) {
                    $columns = array();
                    $primarykey = '';
                    foreach ($array['schema'] as $item) {
                      array_push($columns, '`'.strtoupper($item['name']).'` '.strtoupper($item['type']));
                      if (isset($item['primary']) && $item['primary'] == true) {
                        $primarykey = ', PRIMARY KEY (`'.strtoupper($item['name']).'`)';
                      }
                    }
                    $stmt = $this->query('CREATE TABLE `'.common::constant('TABLE_PREFIX').$name.'` ('.implode(',', $columns).$primarykey.') ENGINE = '.$array['engine']);
                    if (isset($array['index'])) {
                      foreach ($array['index'] as $item) {
                        $stmt = $this->query('ALTER TABLE `'.common::constant('TABLE_PREFIX').$name.'` ADD INDEX `'.$item['name'].'` ('.implode(',', $item['schema']).')');
                      }
                    }
                  }
                  break;
                case 'alter':
                  $fields = array();
                  foreach ($this->query('desc `'.common::constant('TABLE_PREFIX').$name.'`')->fetchAll() as $item) {
                    array_push($fields, strtoupper($item['Field']));
                  }
                  $indexes = array();
                  foreach ($this->query('SHOW INDEX FROM `'.common::constant('TABLE_PREFIX').$name.'`')->fetchAll() as $item) {
                    if (strtoupper($item['Key_name']) != 'PRIMARY' && !in_array($item['Key_name'], $indexes)) {
                      array_push($indexes, $item['Key_name']);
                    }
                  }

                  if (isset($array['schema'])) {
                    foreach ($array['schema'] as $item) {
                      if (!in_array(strtoupper($item['name']), $fields)) {
                        $stmt = $this->handle->exec('ALTER TABLE `'.common::constant('TABLE_PREFIX').$name.'` ADD `'.strtoupper($item['name']).'` '.strtoupper($item['type']).';');
                      } else {
                        unset($fields[array_search(strtoupper($item['name']), $fields)]);
                      }
                    }
                    foreach ($indexes as $key => $index) {
                      $stmt = $this->handle->exec('ALTER TABLE `'.common::constant('TABLE_PREFIX').$name.'` DROP INDEX `'.$index.'`');
                    }
                    if (isset($array['index'])) {
                      foreach ($array['index'] as $item) {
                        $stmt = $this->query('ALTER TABLE `'.common::constant('TABLE_PREFIX').$name.'` ADD INDEX `'.$item['name'].'` ('.implode(',', $item['schema']).')');
                      }
                    }
                  }
                  break;
                case 'truncate':
                  $stmt = $this->query('TRUNCATE `'.common::constant('TABLE_PREFIX').$name.'`');
                  break;
                case 'drop':
                  $stmt = $this->query('DROP TABLE `'.common::constant('TABLE_PREFIX').$name.'`');
                  break;
                default:
                  break;
              }
            }
            if (in_array('drop', explode(' ', $array['type']))) {
              $stmt = $this->delete('schema', 'name = '.$this->quote($name));
            } else {
              $stmt = $this->write('schema', array('VERSION' => $array['version']), 'name = '.$this->quote($name));
            }
          }
        }
      }
    }
  }
  
  public function quote($text)
  {
    return $this->handle->quote($text);
  }
  
  private function exists($name)
  {
    $result = false;
    try {
        $result = $this->query('SELECT 1 FROM '.common::constant('TABLE_PREFIX').$name);
    } catch (Exception $e) {
        return $result;
    }
    return $result;
  }
}

?>