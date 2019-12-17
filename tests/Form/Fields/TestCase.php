<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $app;

    public function setUp(): void
    {
        // start with a fresh set of fields
        Field::$types = [];

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function app()
    {
        return $this->app;
    }

    public function field(string $type, array $attr = [])
    {
        $page = new Page(['slug' => 'test']);
        return new Field($type, array_merge(['model' => $page], $attr));
    }
}
