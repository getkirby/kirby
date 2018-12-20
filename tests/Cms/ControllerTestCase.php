<?php

namespace Kirby\Cms;

use ReflectionClass;

class ControllerTestCase extends TestCase
{
    public $controller = null;

    public function controller(Page $page = null)
    {
        return $this->kirby()->controller($this->controllerName(), [
            'kirby' => $this->kirby(),
            'site'  => $this->site(),
            'pages' => $this->site()->children(),
            'page'  => $page ?? $this->page()
        ]);
    }

    public function controllerRoot(): string
    {
        return $this->kirby()->root('controllers') . '/' . $this->controllerName() . '.php';
    }

    public function controllerName(): string
    {
        if ($this->controller !== null) {
            return $this->controller;
        }

        $reflect   = new ReflectionClass($this);
        $className = $reflect->getShortName();

        return strtolower(str_replace('ControllerTest', '', $className));
    }

    public function testControllerExists()
    {
        $this->assertFileExists($this->controllerRoot());
    }
}
