<?php

namespace Kirby\Cms;

use Exception;

class Rules
{

    protected $bind;
    protected $rules = [];

    public function __construct(array $rules = [], $bind = null)
    {
        $this->bind  = $bind ?? $this;
        $this->rules = $rules;
    }

    public function check(string $rule, ...$arguments)
    {
        if (isset($this->rules[$rule]) === false) {
            return true;
        }

        $this->rules[$rule]->call($this->bind, ...$arguments);
    }

}
