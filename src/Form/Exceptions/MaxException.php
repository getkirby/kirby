<?php

namespace Kirby\Form\Exceptions;

class MaxException extends FieldException
{
    protected $message = 'The value is above the accepted maximum';
}
