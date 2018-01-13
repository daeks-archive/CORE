# CORE

PHP, JQuery and Bootstrap based framework for web applications

# Installation

- Download Framework and place it into a subdirectory below your project
- Integrate ```common.php``` into your project
- Schedule cron job for ```cron/index.php```

# Example

- Create ```wrapper.php```
```
<?php
  define('FRAMEWORK', 'cfx');
  require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.FRAMEWORK.DIRECTORY_SEPARATOR.'common.php');
?>
```

- Create ```index.php```
```
<?php

  require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'wrapper.php');
 
  page::start();
  panel::dashboard();
  page::end();

?>
```

# Contributions

Contributions to this project are welcome. Please follow the coding standard PSR2 before merging. Please note that the indent has to be 2 spaces instead of PSR2's default 4.

Distributed under the MIT-Style License. See `LICENSE` file for more information.

[![Build Status](https://travis-ci.org/daeks/DNET-CORE.svg?branch=master)](https://travis-ci.org/daeks/DNET-CORE)