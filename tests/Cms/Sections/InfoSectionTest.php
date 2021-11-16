<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class InfoSectionTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testHeadline()
    {
        // single headline
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => 'Test'
        ]);

        $this->assertEquals('Test', $section->headline());

        // translated headline
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('Information', $section->headline());
    }

    public function testText()
    {
        // single language text
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'text'     => 'Test'
        ]);

        $this->assertEquals('<p>Test</p>', $section->text());

        // translated text
        $section = new Section('info', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'text'  => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('<p>Information</p>', $section->text());
    }

    public function testTheme()
    {
        $section = new Section('info', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'theme' => 'notice'
        ]);

        $this->assertEquals('notice', $section->theme());
    }

    public function testToArray()
    {
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => 'Test Headline',
            'text'     => 'Test Text',
            'theme'    => 'notice'
        ]);

        $expected = [
            'headline' => 'Test Headline',
            'text'     => '<p>Test Text</p>',
            'theme'    => 'notice'
        ];

        $this->assertEquals($expected, $section->toArray());
    }
}
