<?php

namespace Kirby\Exception;

class DuplicateException extends Exception
{
    protected static $defaultKey = 'duplicate';
    protected static $defaultFallback = 'The entry exists';
    protected static $defaultHttpCode = 400;
}
