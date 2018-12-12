<?php

namespace Kirby\Cms;

class AppComponentsTest extends TestCase
{

    public function testMarkdown()
    {
        $app      = new App();
        $text     = 'Test';
        $expected = '<p>Test</p>';

        $this->assertEquals($expected, $app->markdown($text));
    }

    public function testSmartypants()
    {
        $app      = new App();
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $app->smartypants($text));
    }

    public function testSnippet()
    {
        $app = new App();
        $snippet = $app->snippet('default');

        $this->assertEquals('', $snippet);
    }

    public function testTemplate()
    {
        $app      = new App();
        $template = $app->template('default');

        $this->assertInstanceOf(Template::class, $template);
    }

}
