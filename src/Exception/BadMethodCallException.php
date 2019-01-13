<?php

namespace Kirby\Exception;

class BadMethodCallException extends Exception
{
    protected static $defaultKey = 'invalidMethod';
    protected static $defaultFallback = 'The method "{ method }" does not exist';
    protected static $defaultHttpCode = 400;
    protected static $defaultData = ['method' => null];
}
