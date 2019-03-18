<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class InfoSectionTest extends TestCase
{
    public function setUp(): void
    {
        new App([
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

        // single headline
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'text'     => 'Test'
        ]);

        $this->assertEquals('<p>Test</p>', $section->text());

        // translated headline
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

        // single help
        $section = new Section('info', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'theme' => 'notice'
        ]);

        $this->assertEquals('notice', $section->theme());
    }

    public function testHelp()
    {

        // single help
        $section = new Section('info', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'help'  => 'Test'
        ]);

        $this->assertEquals('Test', $section->help());

        // translated help
        $section = new Section('info', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'help' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('Information', $section->help());
    }
}
