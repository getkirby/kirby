<?php

use Kirby\Cms\Rules;

$root = realpath(__DIR__ . '/../rules');

return new Rules(
      (require $root . '/page.php')
    + (require $root . '/user.php'),
$this);
