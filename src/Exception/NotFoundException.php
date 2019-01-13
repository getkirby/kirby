<?php

namespace Kirby\Exception;

class NotFoundException extends Exception
{
    protected static $defaultKey = 'notFound';
    protected static $defaultFallback = 'Not found';
    protected static $defaultHttpCode = 404;
}
