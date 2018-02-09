<?php

namespace Kirby\Form\Exceptions;

class MinLengthException extends FieldException
{
    protected $message = 'The value is too short';
}
