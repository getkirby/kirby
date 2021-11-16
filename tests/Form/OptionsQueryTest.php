<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class OptionsQueryTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Dir::make($this->fixtures = __DIR__ . '/fixtures/OptionsQuery');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testProps()
    {
        $query = new OptionsQuery([
            'query' => 'page.children',
            'data'  => [],
            'text'  => $text = '{{ item.text }}',
            'value' => $value = '{{ item.value }}'
        ]);

        $this->assertSame('page.children', $query->query());
        $this->assertEquals($text, $query->text());
        $this->assertEquals($value, $query->value());
    }

    public function testToArray()
    {
        $app = new App([
            'site' => [
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                    ['slug' => 'c'],
                ]
            ]
        ]);

        // simple
        $query = new OptionsQuery([
            'options' => 'query',
            'data'    => [
                'kirby' => $app,
                'site'  => $site = $app->site(),
            ],
            'query'   => 'site.children',
            'text'    => '{{ item.slug }}',
            'value'   => '{{ item.slug }}',
            'model'   => $site,
            'aliases' => $aliases = [
                'Page' => 'item'
            ]
        ]);

        $expected = [
            [
                'text'  => 'a',
                'value' => 'a'
            ],
            [
                'text'  => 'b',
                'value' => 'b'
            ],
            [
                'text'  => 'c',
                'value' => 'c'
            ],
        ];

        $this->assertSame($aliases, $query->aliases());
        $this->assertSame($expected, $query->toArray());

        // invalid query (should be collection)
        $query = new OptionsQuery([
            'options' => 'query',
            'data'    => [
                'kirby' => $app,
                'site'  => $site = $app->site(),
            ],
            'query'   => 'site.children.first',
            'text'    => '{{ item.slug }}',
            'value'   => '{{ item.slug }}',
            'model'   => $site,
            'aliases' => [
                'Kirby\Cms\Page' => 'item'
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid query result data');

        $query->toArray();
    }
}
