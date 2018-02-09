<?php

namespace Kirby\Form\Exceptions;

class DisabledFieldException extends FieldException
{
    protected $message = 'The field is disabled and cannot be saved';
}
