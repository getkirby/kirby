<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class MaxSectionMixinTest extends TestCase
{
    protected $app;
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->page = new Page(['slug' => 'test']);

        Section::$types['test'] = [
            'mixins'   => ['max'],
            'computed' => [
                'total' => function () {
                    return 10;
                }
            ]
        ];
    }

    public function testDefaultMax()
    {
        $section = new Section('test', [
            'model' => $this->page,
        ]);

        $this->assertEquals(null, $section->max());
    }

    public function testMax()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'max'   => 1
        ]);

        $this->assertEquals(1, $section->max());
    }

    public function testIsNotFull()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'max'   => 100
        ]);

        $this->assertFalse($section->isFull());
        $this->assertTrue($section->validateMax());
    }

    public function testIsFull()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'max'   => 1
        ]);

        $this->assertTrue($section->isFull());
        $this->assertFalse($section->validateMax());
    }

    public function testIsExactlyFull()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'max'   => 10
        ]);

        $this->assertTrue($section->isFull());
        $this->assertTrue($section->validateMax());
    }
}
