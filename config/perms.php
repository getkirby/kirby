<?php

use Kirby\Cms\Perms;

$root = realpath(__DIR__ . '/../perms');

return new Perms([], $this);
