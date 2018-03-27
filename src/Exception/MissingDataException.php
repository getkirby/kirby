<?php

namespace Kirby\Exception;

class MissingDataException extends Exception
{

    protected static $defaultKey = 'data';
    protected static $defaultFallback = 'Missing data';
    protected static $defaultCode = 404;

}
