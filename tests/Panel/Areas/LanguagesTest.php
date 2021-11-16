<?php

namespace Kirby\Panel\Areas;

class LanguagesTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->enableMultilang();
    }

    public function testViewWithoutAuthentication(): void
    {
        $this->assertRedirect('languages', 'login');
    }

    public function testView(): void
    {
        $this->login();

        $view  = $this->view('languages');
        $props = $view['props'];

        $response = $this->response('languages', true);
        $this->assertTrue($response['$multilang']);

        $this->assertSame('languages', $view['id']);
        $this->assertSame('Languages', $view['title']);
        $this->assertSame('k-languages-view', $view['component']);
        $this->assertSame([], $props['languages']);
    }

    public function testViewWithLanguages(): void
    {
        $this->enableMultilang();
        $this->installLanguages();
        $this->login();

        $response = $this->response('languages', true);
        $view     = $response['$view'];
        $props    = $view['props'];

        $languages = [
            [
                'default' => true,
                'id' => 'en',
                'info' => 'en',
                'text' => 'English'
            ],
            [
                'default' => false,
                'id' => 'de',
                'info' => 'de',
                'text' => 'Deutsch'
            ]
        ];

        $this->assertTrue($response['$multilang']);
        $this->assertSame($languages, $props['languages']);
    }
}
