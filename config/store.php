<?php

use Kirby\Cms\Store;

$root = realpath(__DIR__ . '/../store');

return new Store(
      (require $root . '/avatar.php')
    + (require $root . '/file.php')
    + (require $root . '/page.php')
    + (require $root . '/site.php')
    + (require $root . '/user.php')
    + (require $root . '/users.php'),
$this);
