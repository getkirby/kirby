<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class OptionsApiTest extends TestCase
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

        Dir::make($this->fixtures = __DIR__ . '/fixtures/OptionsApi');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testProps()
    {
        $api = new OptionsApi([
            'data'  => $data = [],
            'url'   => $url  = 'https://api.getkirby.com',
            'fetch' => $fetch = 'data',
            'text'  => $text = '{{ item.text }}',
            'value' => $value = '{{ item.value }}'
        ]);

        $this->assertEquals([], $api->data());
        $this->assertEquals($url, $api->url());
        $this->assertEquals($fetch, $api->fetch());
        $this->assertEquals($text, $api->text());
        $this->assertEquals($value, $api->value());
    }

    public function testDynamicUrl()
    {
        $api = new OptionsApi([
            'data' => [
                'site' => ['url' => 'https://getkirby.com']
            ],
            'url' => '{{ site.url }}/api/companies.json'
        ]);

        $this->assertEquals('https://getkirby.com/api/companies.json', $api->url());
    }

    public function testOptions()
    {
        $source = $this->fixtures . '/test.json';

        Data::write($source, [
            'Companies' => [
                ['name' => 'Apple'],
                ['name' => 'Intel'],
                ['name' => 'Microsoft'],
            ]
        ]);

        $expected = [
            [
                'value' => 'apple',
                'text'  => 'Apple'
            ],
            [
                'value' => 'intel',
                'text'  => 'Intel'
            ],
            [
                'value' => 'microsoft',
                'text'  => 'Microsoft'
            ],
        ];

        // API from file
        $api = new OptionsApi([
            'data'  => [],
            'url'   => $source,
            'fetch' => 'Companies',
            'text'  => '{{ item.name }}',
            'value' => '{{ item.name.slug }}'
        ]);

        $this->assertEquals($expected, $api->options());
        $this->assertEquals($expected, $api->toArray());

        // API from URL (using cURL)
        $api = new OptionsApi([
            'data'  => [],
            'url'   => 'file://' . $source,
            'fetch' => 'Companies',
            'text'  => '{{ item.name }}',
            'value' => '{{ item.name.slug }}'
        ]);

        $this->assertEquals($expected, $api->options());
        $this->assertEquals($expected, $api->toArray());
    }

    public function testOptionsFileNotFound()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Local file /does/not/exist was not found');

        $api = new OptionsApi([
            'data'  => [],
            'url'   => '/does/not/exist',
            'fetch' => 'Companies',
            'text'  => '{{ item.name }}',
            'value' => '{{ item.name.slug }}'
        ]);

        $api->options();
    }

    public function testSortedOptions()
    {
        $source = $this->fixtures . '/test.json';

        Data::write($source, [
            'Companies' => [
                ['name' => 'Intel'],
                ['name' => 'Microsoft'],
                ['name' => 'Apple'],
            ]
        ]);

        $api = new OptionsApi([
            'data'  => [],
            'url'   => $source,
            'fetch' => 'Companies.sortBy("name", "asc")',
            'text'  => '{{ item.name }}',
            'value' => '{{ item.name.slug }}'
        ]);

        $expected = [
            [
                'value' => 'apple',
                'text'  => 'Apple'
            ],
            [
                'value' => 'intel',
                'text'  => 'Intel'
            ],
            [
                'value' => 'microsoft',
                'text'  => 'Microsoft'
            ],
        ];

        $this->assertEquals($expected, $api->options());
        $this->assertEquals($expected, $api->toArray());
    }
}
