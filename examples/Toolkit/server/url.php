<?php

require '../../vendor/autoload.php';

use Kirby\Toolkit\Server;

var_dump(Server::port());
var_dump(Server::https());
var_dump(Server::host());
