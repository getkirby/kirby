<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class UsersSectionTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                [
                    'name' => 'admin'
                ],
                [
                    'name' => 'editor'
                ],
                [
                    'name' => 'user'
                ]
            ],
            'users' => [
                [
                    'email' => 'admin@domain.com',
                    'role'  => 'admin'
                ],
                [
                    'email' => 'editor@domain.com',
                    'role'  => 'editor'
                ],
                [
                    'email' => 'user@domain.com',
                    'role'  => 'user'
                ]
            ],
        ]);
    }

    public function testHeadline()
    {

        // single headline
        $section = new Section('users', [
            'name'     => 'test',
            'headline' => 'Test'
        ]);

        $this->assertEquals('Test', $section->headline());

        // translated headline
        $section = new Section('users', [
            'name'     => 'test',
            'headline' => [
                'en' => 'Users',
                'de' => 'Benutzer'
            ]
        ]);

        $this->assertEquals('Users', $section->headline());
    }

    public function testAdd()
    {
        $section = new Section('users', [
            'name'  => 'test'
        ]);

        $this->assertTrue($section->add());
    }

    public function testEmpty()
    {
        $section = new Section('users', [
            'name'  => 'test',
            'query' => 'kirby.users.filterBy("name", "john")',
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testTranslatedEmpty()
    {
        $section = new Section('users', [
            'name'  => 'test',
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testHelp()
    {
        // single help
        $section = new Section('users', [
            'name'  => 'test',
            'help'  => 'Test'
        ]);

        $this->assertEquals('<p>Test</p>', $section->help());

        // translated help
        $section = new Section('users', [
            'name'     => 'test',
            'help' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('<p>Information</p>', $section->help());
    }

    public function testSortBy()
    {
        $locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, ['de_DE.ISO8859-1', 'de_DE']);

        // no settings
        $section = new Section('users', [
            'name'  => 'test',
        ]);
        $this->assertEquals('admin@domain.com', $section->data()[0]['username']);
        $this->assertEquals('editor@domain.com', $section->data()[1]['username']);
        $this->assertEquals('user@domain.com', $section->data()[2]['username']);

        // custom sorting direction
        $section = new Section('users', [
            'name'   => 'test',
            'sortBy' => 'username desc'
        ]);
        $this->assertEquals('user@domain.com', $section->data()[0]['username']);
        $this->assertEquals('editor@domain.com', $section->data()[1]['username']);
        $this->assertEquals('admin@domain.com', $section->data()[2]['username']);

        setlocale(LC_ALL, $locale);
    }

    public function testFlip()
    {
        $section = new Section('users', [
            'name'  => 'test',
            'flip'  => true
        ]);

        $this->assertEquals('user@domain.com', $section->data()[0]['username']);
        $this->assertEquals('editor@domain.com', $section->data()[1]['username']);
        $this->assertEquals('admin@domain.com', $section->data()[2]['username']);
    }
}
