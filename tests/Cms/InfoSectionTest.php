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
                'en' => 'Informations',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('Informations', $section->headline());
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
                'en' => 'Informations',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('<p>Informations</p>', $section->text());
    }
}
