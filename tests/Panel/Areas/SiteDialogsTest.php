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

    public function testDuplicate(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/duplicate');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('URL appendix', $props['fields']['slug']['label']);
        $this->assertSame('slug', $props['fields']['slug']['type']);

        $this->assertSame('Duplicate', $props['submitButton']);

        $this->assertFalse($props['value']['children']);
        $this->assertFalse($props['value']['files']);
        $this->assertSame('test-copy', $props['value']['slug']);
    }

    public function testDuplicateWithChildren(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'children' => [
                            ['slug' => 'test-child']
                        ]
                    ]
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/duplicate');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('toggle', $props['fields']['children']['type']);
        $this->assertSame('Copy pages', $props['fields']['children']['label']);
        $this->assertSame('1/1', $props['fields']['children']['width']);
    }

    public function testDuplicateWithFiles(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/duplicate');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('toggle', $props['fields']['files']['type']);
        $this->assertSame('Copy files', $props['fields']['files']['label']);
        $this->assertSame('1/1', $props['fields']['files']['width']);
    }

    public function testDuplicateWithChildrenAndFiles(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'children' => [
                            ['slug' => 'test-child']
                        ],
                        'files' => [
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/duplicate');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('toggle', $props['fields']['children']['type']);
        $this->assertSame('Copy pages', $props['fields']['children']['label']);
        $this->assertSame('1/2', $props['fields']['children']['width']);

        $this->assertSame('toggle', $props['fields']['files']['type']);
        $this->assertSame('Copy files', $props['fields']['files']['label']);
        $this->assertSame('1/2', $props['fields']['files']['width']);
    }

    public function testDuplicateOnSubmit(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        // store the dummy page on disk
        // otherwise it cannot be duplicated
        $this->app->page('test')->update();

        $this->submit([
            'slug' => 'new-test'
        ]);

        $dialog = $this->dialog('pages/test/duplicate');

        $this->assertSame('page.duplicate', $dialog['event']);
        $this->assertSame('/pages/new-test', $dialog['redirect']);
        $this->assertSame(200, $dialog['code']);

        $this->assertCount(1, $this->app->site()->drafts());
    }
}
