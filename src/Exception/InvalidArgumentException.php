<?php

namespace Kirby\Exception;

class InvalidArgumentException extends Exception
{
    protected static $defaultKey = 'invalidArgument';
    protected static $defaultFallback = 'Invalid argument "{ argument }" in method "{ method }"';
    protected static $defaultHttpCode = 400;
    protected static $defaultData = ['argument' => null, 'method' => null];
}
