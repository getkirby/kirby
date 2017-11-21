<?php

require '../../vendor/autoload.php';

use Kirby\Http\Response\Download;

echo new Download(__FILE__);
