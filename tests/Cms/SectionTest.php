<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testPropsDefaults()
    {
        Section::$types = [
            'test' => [
                'props' => [
                    'example' => function ($example = 'default') {
                        return $example;
                    },
                    'buttons' => function ($buttons = ['one', 'two']) {
                        return $buttons;
                    },
                ]
            ]
        ];

        $section = new Section('test', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('default', $section->example());
        $this->assertEquals(['one', 'two'], $section->buttons());
    }
}
