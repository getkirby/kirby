<?php

namespace Kirby\Exception;

class PermissionException extends Exception
{
    protected static $defaultKey = 'permission';
    protected static $defaultFallback = 'You are not allowed to do this';
    protected static $defaultHttpCode = 403;
}
