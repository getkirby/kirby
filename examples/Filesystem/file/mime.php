<?php

require '../../vendor/autoload.php';

use Kirby\FileSystem\File\MimeType;
use Kirby\FileSystem\File;

var_dump(new MimeType(__FILE__));
