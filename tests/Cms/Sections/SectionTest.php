<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    protected $app;
    protected $sectionTypes;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->sectionTypes = Section::$types;
    }

    public function tearDown(): void
    {
        Section::$types = $this->sectionTypes;
    }

    public function testMissingModel()
    {
        Section::$types['test'] = [];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Undefined section model');

        $section = new Section('test', []);
    }

    public function testPropsDefaults()
    {
        Section::$types['test'] = [
            'props' => [
                'example' => function ($example = 'default') {
                    return $example;
                },
                'buttons' => function ($buttons = ['one', 'two']) {
                    return $buttons;
                },
            ]
        ];

        $section = new Section('test', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('default', $section->example());
        $this->assertEquals(['one', 'two'], $section->buttons());
    }

    public function testToResponse()
    {
        Section::$types['test'] = [
            'props' => [
                'a' => function ($a) {
                    return $a;
                },
                'b' => function ($b) {
                    return $b;
                }
            ]
        ];

        $section = new Section('test', [
            'model' => new Page(['slug' => 'test']),
            'a' => 'A',
            'b' => 'B'
        ]);


        $expected = [
            'status' => 'ok',
            'code'   => 200,
            'name'   => 'test',
            'type'   => 'test',
            'a'      => 'A',
            'b'      => 'B'
        ];

        $this->assertEquals($expected, $section->toResponse());
    }
}
