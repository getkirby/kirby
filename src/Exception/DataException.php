<?php

namespace Kirby\Exception;

class DataException extends Exception
{

    protected static $defaultKey = 'data.missing';
    protected static $defaultFallback = 'Missing data';
    protected static $defaultHttpCode = 404;

}
