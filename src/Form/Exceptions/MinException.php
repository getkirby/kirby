<?php

namespace Kirby\Form\Exceptions;

class MinException extends FieldException
{
    protected $message = 'The value is below the accepted minimum';
}
