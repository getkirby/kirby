<?php

namespace Kirby\Cms;

use Exception;

class Rules
{

    protected $app;
    protected $rules = [];

    public function __construct(array $rules = [], App $app)
    {
        $this->app   = $app;
        $this->rules = $rules;
    }

    public function check(string $rule, ...$arguments)
    {
        if (isset($this->rules[$rule]) === false) {
            return true;
        }

        $this->rules[$rule]->call($this->app, ...$arguments);
    }

}
