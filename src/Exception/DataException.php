<?php

namespace Kirby\Exception;

class DataException extends Exception
{

    protected static $defaultKeyPrefix = 'exception.data';
    protected static $defaultKey = 'missing';
    protected static $defaultFallback = 'Missing data';
    protected static $defaultCode = 404;

}
