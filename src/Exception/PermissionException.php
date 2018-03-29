<?php

namespace Kirby\Exception;

class PermissionException extends Exception
{

    protected static $defaultKey = 'permission.missing';
    protected static $defaultFallback = 'Missing required permission';
    protected static $defaultHttpCode = 403;

}
