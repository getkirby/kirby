<?php

namespace Kirby\Exception;

class LogicException extends Exception
{
    protected static $defaultKey = 'logic';
    protected static $defaultFallback = 'This task cannot be finished';
    protected static $defaultHttpCode = 400;
}
