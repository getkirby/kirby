<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class PageTemplateTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'templates' => [
                'template' => __DIR__ . '/fixtures/PageTemplateTest/template.php'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'with-template',
                        'template' => 'template'
                    ],
                    [
                        'slug' => 'without-template',
                        'template' => 'does-not-exist'
                    ]
                ]
            ]
        ]);
    }

    public function testIntendedTemplate()
    {
        $page = $this->app->page('with-template');
        $this->assertInstanceOf(Template::class, $page->intendedTemplate());
        $this->assertEquals('template', $page->intendedTemplate()->name());

        $page = $this->app->page('without-template');
        $this->assertInstanceOf(Template::class, $page->intendedTemplate());
        $this->assertEquals('does-not-exist', $page->intendedTemplate()->name());
    }

    public function testTemplate()
    {
        $page = $this->app->page('with-template');
        $this->assertInstanceOf(Template::class, $page->template());
        $this->assertEquals('template', $page->template()->name());

        $page = $this->app->page('without-template');
        $this->assertInstanceOf(Template::class, $page->template());
        $this->assertEquals('default', $page->template()->name());
    }
}
