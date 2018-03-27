<?php

namespace Kirby\Exception;

class MissingPermissionException extends Exception
{

    protected static $defaultKey = 'permission';
    protected static $defaultFallback = 'Missing required permission';
    protected static $defaultCode = 403;

}
