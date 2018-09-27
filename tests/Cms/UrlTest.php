<?php

namespace Kirby\Cms;

class UrlTest extends TestCase
{

    public function setUp()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);
    }

    public function testHome()
    {
        $this->assertEquals('https://getkirby.com', Url::home());
    }

    public function testTo()
    {
        $this->assertEquals('https://getkirby.com/projects', Url::to('projects'));
    }

    public function testToTemplateAsset()
    {

        $page = new Page([
            'slug' => 'test',
            'template' => 'test'
        ]);

        $expected = 'https://getkirby.com/assets/css/templates/test.css';

        $this->assertEquals($expected, Url::toTemplateAsset('css/templates', 'css'));

        $expected = 'https://getkirby.com/assets/js/templates/test.js';

        $this->assertEquals($expected, Url::toTemplateAsset('js/templates', 'js'));

    }

}
