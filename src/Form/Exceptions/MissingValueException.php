<?php

namespace Kirby\Form\Exceptions;

class MissingValueException extends FieldException
{
    protected $message = 'The field is required';
}
