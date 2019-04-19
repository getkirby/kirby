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

    public function testMarkdownCachedInstance()
    {
        $text     = '1st line
2nd line';
        $expected = '<p>1st line<br />
2nd line</p>';

        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, []));

        $expected = '<p>1st line
2nd line</p>';
        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, ['breaks' => false]));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsCachedInstance()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->kirby->component('smartypants')($this->kirby, $text, []));

        $expected = 'TestTest&#8221;';
        $this->assertEquals($expected, $this->kirby->component('smartypants')($this->kirby, $text, ['doublequote.open' => 'Test']));
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
