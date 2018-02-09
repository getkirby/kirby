<?php

namespace Kirby\Form\Exceptions;

use Exception;

class FieldException extends Exception
{
    public function getType()
    {
        $className = get_called_class();
        $className = substr($className, strrpos($className, '\\') + 1);
        $className = str_replace('Exception', '', $className);
        $className = strtolower(substr($className, 0, 1)) . substr($className, 1);

        return $className;
    }
}
