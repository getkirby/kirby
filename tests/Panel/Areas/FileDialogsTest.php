<?php

namespace Kirby\Panel\Areas;

use Kirby\Toolkit\F;

class FileDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    protected function createPageFile(): void
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

        // pretend the file exists
        F::write($this->app->page('test')->file('test.jpg')->root(), '');

        $this->login();
    }

    protected function createSiteFile(): void
    {
        $this->app([
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        // pretend the file exists
        F::write($this->app->site()->file('test.jpg')->root(), '');

        $this->login();
    }

    protected function createUserFile(): void
    {
        $this->app([
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        ['filename' => 'test.jpg']
                    ]
                ],
                [
                    'id'    => 'admin',
                    'email' => 'admin@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        // pretend the file exists
        F::write($this->app->user('test')->file('test.jpg')->root(), '');

        $this->login();
    }

    public function testChangeNameForPageFile(): void
    {
        $this->createPageFile();

        $dialog = $this->dialog('pages/test/files/test.jpg/changeName');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('slug', $props['fields']['name']['type']);
        $this->assertSame('Rename', $props['submitButton']);
        $this->assertSame('test', $props['value']['name']);
    }

    public function testChangeNameForPageFileOnSubmit(): void
    {
        $this->createPageFile();
        $this->submit([
            'name' => 'new-test'
        ]);
        $this->login();

        $dialog = $this->dialog('pages/test/files/test.jpg/changeName');

        $this->assertSame('file.changeName', $dialog['event']);
        $this->assertSame([
            'content/move' => [
                '/pages/test/files/test.jpg',
                '/pages/test/files/new-test.jpg'
            ]
        ], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertSame('new-test', $this->app->page('test')->file('new-test.jpg')->name());
    }

    public function testChangeNameForPageFileOnSubmitWithReferrer(): void
    {
        $this->createPageFile();
        $this->submit([
            'name'      => 'new-test',
            '_referrer' => 'pages/test/files/test.jpg'
        ]);
        $this->login();

        $dialog = $this->dialog('pages/test/files/test.jpg/changeName');
        $this->assertSame('/pages/test/files/new-test.jpg', $dialog['redirect']);
    }

    public function testChangeNameForSiteFile(): void
    {
        $this->createSiteFile();

        $dialog = $this->dialog('site/files/test.jpg/changeName');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('slug', $props['fields']['name']['type']);
        $this->assertSame('Rename', $props['submitButton']);
        $this->assertSame('test', $props['value']['name']);
    }

    public function testChangeNameForSiteFileOnSubmit(): void
    {
        $this->createSiteFile();
        $this->submit([
            'name' => 'new-test'
        ]);
        $this->login();

        $dialog = $this->dialog('site/files/test.jpg/changeName');

        $this->assertSame('file.changeName', $dialog['event']);
        $this->assertSame([
            'content/move' => [
                '/site/files/test.jpg',
                '/site/files/new-test.jpg'
            ]
        ], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertSame('new-test', $this->app->site()->file('new-test.jpg')->name());
    }

    public function testChangeNameForSiteFileOnSubmitWithReferrer(): void
    {
        $this->createSiteFile();
        $this->submit([
            'name' => 'new-test',
            '_referrer' => 'site/files/test.jpg'
        ]);
        $this->login();

        $dialog = $this->dialog('site/files/test.jpg/changeName');
        $this->assertSame('/site/files/new-test.jpg', $dialog['redirect']);
    }

    public function testChangeNameForUserFile(): void
    {
        $this->createUserFile();

        $dialog = $this->dialog('users/test/files/test.jpg/changeName');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('slug', $props['fields']['name']['type']);
        $this->assertSame('Rename', $props['submitButton']);
        $this->assertSame('test', $props['value']['name']);
    }

    public function testChangeNameForUserFileOnSubmit(): void
    {
        $this->createUserFile();
        $this->submit([
            'name' => 'new-test'
        ]);
        $this->login('admin@getkirby.com');

        $dialog = $this->dialog('users/test/files/test.jpg/changeName');

        $this->assertSame('file.changeName', $dialog['event']);
        $this->assertSame([
            'content/move' => [
                '/users/test/files/test.jpg',
                '/users/test/files/new-test.jpg'
            ]
        ], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertSame('new-test', $this->app->user('test')->file('new-test.jpg')->name());
    }

    public function testChangeNameForUserFileOnSubmitWithReferrer(): void
    {
        $this->createUserFile();
        $this->submit([
            'name'      => 'new-test',
            '_referrer' => 'users/test/files/test.jpg'
        ]);
        $this->login('admin@getkirby.com');

        $dialog = $this->dialog('users/test/files/test.jpg/changeName');
        $this->assertSame('/users/test/files/new-test.jpg', $dialog['redirect']);
    }

    public function testChangeSortForPageFile(): void
    {
        $this->createPageFile();

        $dialog = $this->dialog('pages/test/files/test.jpg/changeSort');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Change position', $props['fields']['position']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame(1, $props['value']['position']);
    }

    public function testChangeSortForPageFileOnSubmit(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'a.jpg'],
                            ['filename' => 'b.jpg'],
                            ['filename' => 'c.jpg'],
                        ]
                    ]
                ]
            ]
        ]);

        $page = $this->app->page('test');

        // pretend the file exists
        F::write($page->file('a.jpg')->root(), '');
        F::write($page->file('b.jpg')->root(), '');
        F::write($page->file('c.jpg')->root(), '');

        $this->submit([
            'position' => 2
        ]);

        $this->login();

        $dialog = $this->dialog('pages/test/files/a.jpg/changeSort');

        $this->assertSame('file.sort', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame(1, $page->file('b.jpg')->sort()->toInt());
        $this->assertSame(2, $page->file('a.jpg')->sort()->toInt());
        $this->assertSame(3, $page->file('c.jpg')->sort()->toInt());
    }

    public function testChangeSortForSiteFile(): void
    {
        $this->createSiteFile();

        $dialog = $this->dialog('site/files/test.jpg/changeSort');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Change position', $props['fields']['position']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame(1, $props['value']['position']);
    }

    public function testChangeSortForSiteFileOnSubmit(): void
    {
        $this->app([
            'site' => [
                'files' => [
                    ['filename' => 'a.jpg'],
                    ['filename' => 'b.jpg'],
                    ['filename' => 'c.jpg'],
                ]
            ]
        ]);

        $site = $this->app->site();

        // pretend the file exists
        F::write($site->file('a.jpg')->root(), '');
        F::write($site->file('b.jpg')->root(), '');
        F::write($site->file('c.jpg')->root(), '');

        $this->submit([
            'position' => 2
        ]);

        $this->login();

        $dialog = $this->dialog('site/files/a.jpg/changeSort');

        $this->assertSame('file.sort', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame(1, $site->file('b.jpg')->sort()->toInt());
        $this->assertSame(2, $site->file('a.jpg')->sort()->toInt());
        $this->assertSame(3, $site->file('c.jpg')->sort()->toInt());
    }

    public function testChangeSortForUserFile(): void
    {
        $this->createUserFile();

        $dialog = $this->dialog('users/test/files/test.jpg/changeSort');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Change position', $props['fields']['position']['label']);
        $this->assertSame('Change', $props['submitButton']);
        $this->assertSame(1, $props['value']['position']);
    }

    public function testChangeSortForUserFileOnSubmit(): void
    {
        $this->app([
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        ['filename' => 'a.jpg'],
                        ['filename' => 'b.jpg'],
                        ['filename' => 'c.jpg'],
                    ]
                ]
            ]
        ]);

        $user = $this->app->user('test');

        // pretend the file exists
        F::write($user->file('a.jpg')->root(), '');
        F::write($user->file('b.jpg')->root(), '');
        F::write($user->file('c.jpg')->root(), '');

        $this->submit([
            'position' => 2
        ]);

        $this->login();

        $dialog = $this->dialog('users/test/files/a.jpg/changeSort');

        $this->assertSame('file.sort', $dialog['event']);
        $this->assertSame(200, $dialog['code']);

        $this->assertSame(1, $user->file('b.jpg')->sort()->toInt());
        $this->assertSame(2, $user->file('a.jpg')->sort()->toInt());
        $this->assertSame(3, $user->file('c.jpg')->sort()->toInt());
    }

    public function testDeletePageFile(): void
    {
        $this->createPageFile();

        $dialog = $this->dialog('pages/test/files/test.jpg/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete <br><strong>test.jpg</strong>?', $props['text']);
    }

    public function testDeletePageFileOnSubmit(): void
    {
        $this->createPageFile();
        $this->submit([]);
        $this->login();

        $dialog = $this->dialog('pages/test/files/test.jpg/delete');

        $this->assertSame('file.delete', $dialog['event']);
        $this->assertSame(['content/remove' => ['/pages/test/files/test.jpg']], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertFalse($dialog['redirect']);
        $this->assertCount(0, $this->app->page('test')->files());
    }

    public function testDeletePageFileOnSubmitWithReferrer(): void
    {
        $this->createPageFile();
        $this->submit([
            '_referrer' => '/pages/test/files/test.jpg'
        ]);
        $this->login();

        $dialog = $this->dialog('pages/test/files/test.jpg/delete');

        $this->assertSame('/pages/test', $dialog['redirect']);
    }

    public function testDeleteSiteFile(): void
    {
        $this->createSiteFile();

        $dialog = $this->dialog('site/files/test.jpg/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete <br><strong>test.jpg</strong>?', $props['text']);
    }

    public function testDeleteSiteFileOnSubmit(): void
    {
        $this->createSiteFile();
        $this->submit([]);
        $this->login();

        $dialog = $this->dialog('site/files/test.jpg/delete');

        $this->assertSame('file.delete', $dialog['event']);
        $this->assertSame(['content/remove' => ['/site/files/test.jpg']], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertFalse($dialog['redirect']);
        $this->assertCount(0, $this->app->site()->files());
    }

    public function testDeleteSiteFileOnSubmitWithReferrer(): void
    {
        $this->createSiteFile();
        $this->submit([
            '_referrer' => '/site/files/test.jpg'
        ]);
        $this->login();

        $dialog = $this->dialog('site/files/test.jpg/delete');

        $this->assertSame('/site', $dialog['redirect']);
    }

    public function testDeleteUserFile(): void
    {
        $this->createUserFile();

        $dialog = $this->dialog('users/test/files/test.jpg/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete <br><strong>test.jpg</strong>?', $props['text']);
    }

    public function testDeleteUserFileOnSubmit(): void
    {
        $this->createUserFile();
        $this->submit([]);
        $this->login('admin@getkirby.com');

        $dialog = $this->dialog('users/test/files/test.jpg/delete');

        $this->assertSame('file.delete', $dialog['event']);
        $this->assertSame(['content/remove' => ['/users/test/files/test.jpg']], $dialog['dispatch']);
        $this->assertSame(200, $dialog['code']);
        $this->assertFalse($dialog['redirect']);
        $this->assertCount(0, $this->app->user('test')->files());
    }

    public function testDeleteUserFileOnSubmitWithReferrer(): void
    {
        $this->createUserFile();
        $this->submit([
            '_referrer' => '/users/test/files/test.jpg'
        ]);
        $this->login('admin@getkirby.com');

        $dialog = $this->dialog('users/test/files/test.jpg/delete');

        $this->assertSame('/users/test', $dialog['redirect']);
    }
}
