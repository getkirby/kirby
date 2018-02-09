<?php

namespace Kirby\Form\Exceptions;

class MissingValueException extends FieldException
{
    protected $message = 'The value is missing';
}
