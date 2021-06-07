<?php

namespace Kirby\Panel;

use Exception;

class Redirect extends Exception
{
    public function location(): string
    {
        return $this->getMessage();
    }
}
