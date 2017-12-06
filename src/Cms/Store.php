<?php

namespace Kirby\Cms;

use Exception;

class Store
{

    protected $bind;
    protected $actions = [];

    public function __construct(array $actions = [], $bind = null)
    {
        $this->bind    = $bind ?? $this;
        $this->actions = $actions;
    }

    public function commit(string $action, ...$arguments)
    {
        if (isset($this->actions[$action]) === false) {
            throw new Exception(sprintf('Invalid store action: "%s"', $action));
        }

        return $this->actions[$action]->call($this->bind, ...$arguments);
    }

}
