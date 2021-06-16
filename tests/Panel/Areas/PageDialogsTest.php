<?php

namespace Kirby\Panel\Areas;

class PageDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testChangeSort(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/changeSort');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Please select a position', $props['fields']['position']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame(1, $props['value']['position']);
    }

    public function testChangeSortOnSubmit(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->submit([
            'status' => 'listed'
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/changeSort');

        $this->assertSame('page.sort', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('listed', $this->app->page('test')->status());
        $this->assertSame(1, $this->app->page('test')->num());
    }

    public function testChangeStatus(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/changeStatus');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Select a new status', $props['fields']['status']['label']);

        $this->assertSame('Draft', $props['fields']['status']['options'][0]['text']);
        $this->assertSame('Unlisted', $props['fields']['status']['options'][1]['text']);
        $this->assertSame('Public', $props['fields']['status']['options'][2]['text']);

        $this->assertSame('Please select a position', $props['fields']['position']['label']);
        $this->assertSame(['status' => 'listed'], $props['fields']['position']['when']);

        $this->assertSame('Change', $props['submitButton']);

        $this->assertSame('unlisted', $props['value']['status']);
        $this->assertSame(1, $props['value']['position']);
    }

    public function testChangeStatusOnSubmit(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->submit([
            'status' => 'listed'
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/changeStatus');

        $this->assertSame('page.changeStatus', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame('listed', $this->app->page('test')->status());
        $this->assertSame(1, $this->app->page('test')->num());
    }

    public function testDelete(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete <strong>test</strong>?', $props['text']);
    }

    public function testDeleteWithChildren(): void
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

        $dialog = $this->dialog('pages/test/delete');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);
        $this->assertSame('info', $props['fields']['info']['type']);
        $this->assertSame('text', $props['fields']['check']['type']);
        $this->assertSame('Do you really want to delete <strong>test</strong>?', $props['text']);
        $this->assertSame('Delete', $props['submitButton']);
        $this->assertSame('medium', $props['size']);
    }

    public function testDeleteOnSubmit(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->submit([]);
        $this->login();

        $dialog = $this->dialog('pages/test/delete');

        $this->assertSame('page.delete', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertCount(0, $this->app->site()->children());
    }

    public function testDeleteOnSubmitWithChildrenWithoutCheck(): void
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

        $this->submit([]);
        $this->login();

        $dialog = $this->dialog('pages/test/delete');

        $this->assertSame(400, $dialog['code']);
        $this->assertSame('Please enter the page title to confirm', $dialog['error']);
    }

    public function testDeleteOnSubmitWithChildren(): void
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

        $this->submit(['check' => 'test']);
        $this->login();

        $dialog = $this->dialog('pages/test/delete');

        $this->assertSame('page.delete', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertCount(0, $this->app->site()->children());
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
