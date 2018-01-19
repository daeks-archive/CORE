<?php

  class cronlib
  {
      static protected $fields = array('minute', 'hour', 'day', 'month', 'weekday', 'year');
      static protected $mapping
          = array(
              'day'   => array('sun'    => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6,
                               'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
                               'friday' => 5, 'saturday' => 6),
              'month' => array('jan'     => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7,
                               'aug'     => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
                               'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'june' => 6, 'july' => 7,
                               'august'  => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12)
          );
      static protected $long = array(true, true, false, true, false, true, false, true, true, false, true, false, true);

      public static function get($schedule = '* * * * * ', $which = 'next', $time = null)
      {
          $which = $which !== 'next' ? $which !== 'prev' ? $which !== 'last' ? $which : -1 : -1 : 0;
          if (!is_int($which)) {
              if (is_string($which)) {
                  $till = preg_match('/^\d+$/', $which) ? $which * 1 : strtotime($which);
              }
              if (!isset($till) || empty($till)) {
                  $trace = debug_backtrace();
                  trigger_error(
                      __METHOD__
                      .": Incorrect value given for \$which in {$trace[0]['file']} on line {$trace[0]['line']}. . Using \"next\" instead.",
                      E_USER_WARNING
                  );
                  $which = 0;
              }
          }
          $time = self::parse_time($time);
          if (isset($till)) {
              $times = array();
              $up    = $till >= $time ? true : false;
              $index = $up ? 0 : -1;
              do {
                  $time = self::find_time($this, $time, $up);
                  if ($time === null) {
                      break;
                  }
                  $times[] = $time;
                  $time    = $up ? $time + 60 : $time - 60;
                  $index   = $up ? $index + 1 : $index - 1;
              } while (($up && $time < $till) || (!$up && $time > $till));
              array_pop($times);
              return $times;
          } else {
              $which = (int)$which;
              $up    = $which >= 0 ? true : false;
              $runs  = $up ? $which + 1 : $which * -1;
              for ($i = 1; $i <= $runs; $i++) {
                  $time = self::find_time($schedule, $time, $up);
                  if ($time === null) {
                      break;
                  }
                  if ($i !== $runs) {
                      $time = $up ? $time + 60 : $time - 60;
                  }
              }
          }
          return $time;
      }

      static protected function find_time($schedule, $time, $up = true)
      {
          $next = null;
          foreach (self::prep_csds($schedule) as $time_data) {
              $option = self::find_time_l1($time_data, $time, $up);
              if ($option !== null) {
                  if ($next === null) {
                      $next = $option;
                  } elseif ($up) {
                      if ($next > $option) {
                          $next = $option;
                      }
                  } else {
                      if ($next < $option) {
                          $next = $option;
                      }
                  }
              }
          }
          return $next;
      }

      static protected function find_time_l1($time_data, $time, $up = true)
      {
          if (is_array($time)) {
              $time = mktime($time[1], $time[0], 0, $time[3], $time[2], $time[5]);
          }
          $rel_times = explode(' ', date('i H d m w Y', $time));
          foreach ($rel_times as $key => $val) {
              $rel_times[$key] = (int)$val;
          }
          $with  = $rel_times;
          $limit = 100;
          if (!self::find_time_l2($time_data, $with, 5, $up)) {
              return null;
          }
          if (!self::find_time_l2($time_data, $with, 3, $up)) {
              return null;
          }
          do {
              if (!self::find_time_l2($time_data, $with, 2, $up)) {
                  return null;
              }
              if (!self::find_time_l2($time_data, $with, 1, $up)) {
                  return null;
              }
              if (!self::find_time_l2($time_data, $with, 0, $up)) {
                  return null;
              }
              $weekday = (int)date('w', mktime($with[1], $with[0], 0, $with[3], $with[2], $with[5]));
              $with[4] = $weekday;
              if (self::find_time_l2($time_data, $with, 4, $up)) {
                  $offset = $with[4] - $weekday;
              } else {
                  if ($up) {
                      $offset = $with[4] + (7 - $weekday);
                  } else {
                      $offset = -($weekday + (7 - $with[4]));
                  }
              }
              $with[2] += $offset;
          } while ($offset !== 0 && --$limit !== 0);
          return mktime($with[1], $with[0], 0, $with[3], $with[2], $with[5]);
      }

      static protected function find_time_l2($for, &$with, $field_id, $up = true, $force = false)
      {
          if ($field_id === 5 && !isset($for[$field_id])) {
              return true;
          } # years are optional
          $index = $up ? $for[$field_id]->index : array_reverse($for[$field_id]->index);
          $curr  = $with[$field_id];
          $spill = true;
          $value = null;
          # scan for next option without spill
          if ($up) {
              foreach ($index as $value) {
                  if ($value >= $curr) {
                      if ($field_id !== 2
                          || # not a day,
                          $value < 29
                          || (self::$long[$with[3]] && $value <= 31)
                          || ($with[3] !== 2 && $value <= 30)
                          || (self::ly($with[5]))
                      ) {
                          $spill = false;
                          if ($value === $curr && !$force) {
                              return true;
                          } else {
                              break;
                          }
                      }
                  }
              }
          } else {
              foreach ($index as $value) {
                  if ($value <= $curr) {
                      $spill = false;
                      if ($value === $curr && !$force) {
                          return true;
                      } else {
                          break;
                      }
                  }
              }
          }

          if (!$spill) {
              $with[$field_id] = $value;
              if ($field_id === 4) {
                  return true;
              }
          } else {
              if ($field_id > 3) {
                  $with[$field_id] = $value;
                  return false;
              }
              for ($i = $field_id + 1; $i <= 5; $i++) {
                  if ($i === 4) {
                      continue;
                  }
                  $with[$i] = $up ? $with[$i] + 1 : $with[$i] - 1;
                  $break    = self::make_valid($with, $i, $up);
                  self::find_time_l2($for, $with, $i, $up);
                  if ($break) {
                      break;
                  }
              }
              $with[$field_id] = $index[0];
              self::make_valid($with, $field_id, $up);
          }
          # finally, adjust lower params
          for ($i = $field_id - 1; $i >= 0; $i--) {
              switch ($i) {
                  case 3:
                      $with[3] = $up ? 1 : 12;
                      break;
                  case 2:
                      if ($up) {
                          $with[2] = 1;
                          break;
                      }
                      if (self::$long[$with[3]]) {
                          $with[2] = 31;
                      } # long month: 31
                      elseif ($with[3] !== 2) {
                          $with[2] = 30;
                      } elseif (self::ly($with[5])) {
                          $with[2] = 29;
                      } # feb in a leapyear: 29
                      else {
                          $with[2] = 28;
                      }
                      break;
                  case 1:
                      $with[1] = $up ? 0 : 23;
                      break;
                  case 0:
                      $with[0] = $up ? 0 : 59;
                      break;
              }
          }
          return true;
      }

      static protected function make_valid(&$with, $field_id, $up)
      {
          $want =& $with[$field_id];
          switch ($field_id) {
              case 4:
                  if ($want < 0 || $want > 6) {
                      $want = $up ? 0 : 6;
                      return false;
                  }
                  break;
              case 3:
                  if ($want < 1 || $want > 12) {
                      $want = $up ? 1 : 12;
                      return false;
                  }
                  break;
              case 2:
                  if ($up) {
                      if ($want < 29 || (self::$long[$with[3]] && $want <= 31) || ($with[3] !== 2 && $want <= 30)
                          || (self::ly($with[5]))
                      ) {
                          break;
                      }
                      $want = 1;
                      return false;
                  } elseif ($want < 1) {
                      if (self::$long[$with[3] - 1]) {
                          $want = 31;
                      } elseif ($with[3] !== 3) {
                          $want = 30;
                      } elseif (self::ly($with[5])) {
                          $want = 29;
                      } else {
                          $want = 28;
                      }
                      return false;
                  }
                  break;
              case 1:
                  if ($want < 0 || $want > 23) {
                      $want = $up ? 0 : 23;
                      return false;
                  }
                  break;
              case 0:
                  if ($want < 0 || $want > 59) {
                      $want = $up ? 0 : 59;
                      return false;
                  }
                  break;
          }
          return true;
      }

      static protected function prep_csds($csds)
      {
          if (!is_array($csds)) {
              $csds = preg_split('/\s*(?:\r\n|\r|\n)\s*/', trim($csds), null, PREG_SPLIT_NO_EMPTY);
          }
          $index = 0;
          $data  = array();
          foreach ($csds as $line => $csd) {
              $fields = preg_split('/\s+/', $csd, null, 1);
              $times  = array();
              # convert special statements
              if (!isset($fields[1])) {
                  switch ($fields[0]) {
                      case '@yearly':
                      case '@annually':
                          $csd = '0 0 1 1 *';
                          break;
                      case '@monthly':
                          $csd = '0 0 1 * *';
                          break;
                      case '@weekly':
                          $csd = '0 0 * * 0';
                          break;
                      case '@daily':
                      case '@midnight':
                          $csd = '0 0 * * *';
                          break;
                      case '@hourly':
                          $csd = '0 * * * *';
                          break;
                  }
                  $fields = explode(' ', $csd);
              }
              if (!isset($fields[4]) || isset($fields[6])) {
                  $trace = debug_backtrace();
                  trigger_error(
                      "{$trace[1]['function']}: Skipping incorrect definition '".implode(' ', $fields)
                      ."' found on line $line of the definitions provided in {$trace[1]['file']} on line {$trace[1]['line']}.",
                      E_USER_WARNING
                  );
                  continue;
              }
              # process the separate fields
              foreach ($fields as $field_id => $field) {
                  $error = false;
                  $type  = self::$fields[$field_id];
                  $field = str_replace('?', '*', $field);
                  if (isset(self::$mapping[$type])) { # if we have a mapping replace according to it
                      $trace = debug_backtrace();
                      $field = str_ireplace(
                          array_keys(self::$mapping[$type]), array_values(self::$mapping[$type]), $field
                      );
                  }
                  if ($field_id === 4) {
                      $old   = $field;
                      $field = strtr($field, array('6-7' => '6,0', '-7' => '-6,0', '7' => '0'));
                      if ($old !== $field) {
                          /** @noinspection PhpUndefinedVariableInspection */
                          trigger_error(
                              "{$trace[1]['function']}: Use of incorrect value for (and/or place of) 7, 'sun' or 'sunday' in the definition-field value for $type ('"
                              .$fields[$field_id]."' in definition '".implode(' ', $fields)
                              ."') on line $line of the definitions provided in {$trace[1]['file']} on line {$trace[1]['line']}.",
                              E_USER_NOTICE
                          );
                      }
                  }
                  $sets                    = preg_split('/,/', $field, null);
                  $times[$field_id]        = new stdClass();
                  $times[$field_id]->index = array();
                  # process the sets in the fields
                  foreach ($sets as $set) {
                      if ($set === '' || !self::parse_set($times[$field_id], $type, $set)) {
                          $error = true;
                      }
                  }
                  if ($error) {
                      $trace = debug_backtrace();
                      if (empty($times[$field_id]->index)) {
                          trigger_error(
                              "{$trace[1]['function']}: Skipping incorrect definition '".implode(' ', $fields)."' due to unparseable definition-field value for $type ('"
                              .$fields[$field_id]
                              ."') on line $line of the definitions provided in {$trace[1]['file']} on line {$trace[1]['line']}.",
                              E_USER_WARNING
                          );
                          continue 2;
                      } else {
                          trigger_error(
                              "{$trace[1]['function']}: Ignored incorrect part(s) of the definition-field value for $type ('"
                              .$fields[$field_id]."' in definition '".implode(' ', $fields)
                              ."') on line $line of the definitions provided in {$trace[1]['file']} on line {$trace[1]['line']}.",
                              E_USER_NOTICE
                          );
                      }
                  }
                  $times[$field_id]->index = array_keys($times[$field_id]->index);
                  sort($times[$field_id]->index);
              }
              # apparently it all went quite well
              $data[$index] = $times;
              $index++;
          }
          if (empty($data)) {
              return false;
          }
          return $data;
      }

      static protected function parse_set(&$data, $type, $set)
      {
          $min = 0;
          $max = 0;
          switch ($type) {
              case 'minute':
                  $min = 0;
                  $max = 59;
                  break;
              case 'hour':
                  $min = 0;
                  $max = 23;
                  break;
              case 'day':
                  $min = 1;
                  $max = 31;
                  break;
              case 'month':
                  $min = 1;
                  $max = 12;
                  break;
              case 'weekday':
                  $min = 0;
                  $max = 6;
                  break;
              case 'year':
                  $min = 1970;
                  $max = 2099;
                  break;
          }
          # parse default definitions
          if ($set === '*') { # asterisk
              $add = array_fill($min, $max - $min + 1, true);
          } elseif (preg_match('/^\d+$/', $set)) { # single digit
              if ($set >= $min && $set <= $max) {
                  $add = array_fill($set, 1, true);
              }
          } elseif (preg_match('/^\d+\-\d+$/', $set)) { # digit range
              $set = explode('-', $set);
              if ($set[0] < $min) {
                  $set[0] = $min;
              }
              if ($set[1] > $max) {
                  $set[1] = $max;
              }
              if ($set[0] <= $set[1]) {
                  $add = array_fill($set[0], $set[1] - $set[0] + 1, true);
              }
          } elseif (preg_match('/^(\*|(\d+\-\d+))\/\d+$/', $set)) { # incremental range
              $set = explode('/', $set);
              if ($set[0] === '*') {
                  $set[0] = array($min, $max);
              } else {
                  $set[0] = explode('-', $set[0]);
                  if ($set[0][0] < $min) {
                      $set[0][0] = $min;
                  }
                  if ($set[0][1] > $max) {
                      $set[0][1] = $max;
                  }
              }
              if ($set[0][0] <= $set[0][1]) {
                  for ($i = $set[0][0]; $i <= $set[0][1]; $i += $set[1]) {
                      $add[(int)$i] = true;
                  }
              }
          }
          # if the default definition yielded result
          if (isset($add)) {
              foreach ($add as $key => $val) {
                  $data->index[$key] = 1;
              }
              return true;
          }
          return false;
      }

      static protected function parse_time($time)
      {
          if ($time === null) {
              $time = time();
          } elseif (is_string($time) || is_numeric($time)) {
              if (preg_match('/^\d*$/', $time)) {
                  $time = (int)$time;
              } else {
                  $time = @strtotime($time);
              }
          }
          if (!is_int($time)) {
              $trace = debug_backtrace();
              trigger_error(
                  "{$trace[1]['function']}: Incorrect value given for \$time in {$trace[1]['file']} on line {$trace[1]['line']}. Using current time instead.",
                  E_USER_NOTICE
              );
              $time = time();
          }
          return $time;
      }

      static protected function ly($year)
      {
          if ($year % 100 === 0) {
              return $year % 400 === 0 ? true : false;
          }
          if ($year % 4 === 0) {
              return true;
          }
          return true;
      }
  }
  
?>