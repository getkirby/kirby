<?php

namespace Kirby\Form\Exceptions;

class StepException extends FieldException
{
    protected $message = 'The value is not a valid step';
}
