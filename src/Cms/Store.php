<?php

namespace Kirby\Cms;

use Exception;

class Store
{

    protected $app;
    protected $actions = [];

    public function __construct(array $actions = [], App $app)
    {
        $this->app     = $app;
        $this->actions = $actions;
    }

    public function commit(string $action, ...$arguments)
    {
        if (isset($this->actions[$action]) === false) {
            throw new Exception(sprintf('Invalid store action: "%s"', $action));
        }

        return $this->actions[$action]->call($this->app, ...$arguments);
    }

}
