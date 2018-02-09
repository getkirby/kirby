<?php

namespace Kirby\Form\Exceptions;

class MaxLengthException extends FieldException
{
    protected $message = 'The value is too long';
}
