<?php

namespace Kirby\Cms;

class AppComponentsTest extends TestCase
{
    public function setUp(): void
    {
        $this->kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testMarkdown()
    {
        $text     = 'Test';
        $expected = '<p>Test</p>';

        $this->assertEquals($expected, $this->kirby->markdown($text));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->kirby->smartypants($text));
    }

    public function testSnippet()
    {
        $this->assertEquals('', $this->kirby->snippet('default'));
    }

    public function testTemplate()
    {
        $this->assertInstanceOf(Template::class, $this->kirby->template('default'));
    }

    public function testCssPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'css' => function ($kirby, $url, $options) {
                    return '/test.css';
                }
            ]
        ]);

        $expected = '<link href="/test.css" rel="stylesheet">';
        $this->assertEquals($expected, css('something.css'));
    }

    public function testJsPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'js' => function ($kirby, $url, $options) {
                    return '/test.js';
                }
            ]
        ]);

        $expected = '<script src="/test.js"></script>';
        $this->assertEquals($expected, js('something.js'));
    }

    public function testUrlPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'url' => function ($kirby, $path, $options, $original) {
                    return 'test';
                }
            ]
        ]);

        $this->assertEquals('test', url('anything'));
    }
}
