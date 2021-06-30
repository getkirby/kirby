<?php

namespace Kirby\Panel\Areas;

class SiteDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testChangeTitle(): void
    {
        $dialog = $this->dialog('site/changeTitle');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Title', $props['fields']['title']['label']);
        $this->assertSame('Rename', $props['submitButton']);
        $this->assertNull($props['value']['title']);
    }

    public function testChangeTitleOnSubmit(): void
    {
        $this->submit([
            'title' => 'Test'
        ]);

        $dialog = $this->dialog('site/changeTitle');

        $this->assertSame('site.changeTitle', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('Test', $this->app->site()->title()->value());
    }
}
