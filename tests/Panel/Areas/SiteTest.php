<?php

namespace Kirby\Panel\Areas;

class SiteTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testPageWithoutAuthentication(): void
    {
        $this->assertRedirect('pages/home', 'login');
    }

    public function testPageWithMissingModel(): void
    {
        $this->login();
        $this->assertErrorView('pages/does-not-exist', 'The page "does-not-exist" cannot be found');
    }

    public function testPage(): void
    {
        $this->login();

        $this->app->site()->createChild([
            'slug' => 'test',
            'content' => [
                'title' => 'Test'
            ]
        ]);

        $view  = $this->view('pages/test');
        $props = $view['props'];
        $model = $props['model'];

        $this->assertSame('Page', $props['blueprint']);
        $this->assertSame(['state' => null], $props['lock']);

        $this->assertSame([], $props['tab']);
        $this->assertSame([], $props['tabs']);

        // model
        $this->assertSame(['title' => 'Test'], $model['content']);
        $this->assertSame('test', $model['id']);
        $this->assertSame('draft', $model['status']);
        $this->assertSame('Test', $model['title']);

        $this->assertNull($props['next']);
        $this->assertNull($props['prev']);

        $this->assertSame('Draft', $props['status']['label']);
        $this->assertSame('The page is in draft mode and only visible for logged in editors or via secret link', $props['status']['text']);
    }

    public function testPageFileWithoutModel(): void
    {
        $this->login();

        $this->app->site()->createChild([
            'slug' => 'test',
            'content' => [
                'title' => 'Test'
            ]
        ]);

        $this->assertErrorView('pages/test/files/does-not-exist.jpg', 'The file "does-not-exist.jpg" cannot be found');
    }

    public function testPageFile(): void
    {
        $this->login();

        $this->app->site()->createChild([
            'slug' => 'test',
            'content' => [
                'title' => 'Test'
            ],
            'files' => [
                [
                    'filename' => 'test.jpg'
                ]
            ]
        ]);

        $view  = $this->view('pages/test/files/test.jpg');
        $props = $view['props'];
        $model = $props['model'];

        $this->assertSame('File', $props['blueprint']);
        $this->assertSame(['state' => null], $props['lock']);

        $this->assertSame([], $props['tab']);
        $this->assertSame([], $props['tabs']);

        // model
        $this->assertSame([], $model['content']);
        $this->assertSame('jpg', $model['extension']);
        $this->assertSame('test.jpg', $model['filename']);
        $this->assertSame('test/test.jpg', $model['id']);
        $this->assertSame('image/jpeg', $model['mime']);
        $this->assertSame('0 KB', $model['niceSize']);
        $this->assertSame('pages/test', $model['parent']);
        $this->assertSame('image', $model['type']);

        $this->assertNull($props['next']);
        $this->assertNull($props['prev']);
    }

    public function testSiteWithoutAuthentication(): void
    {
        $this->assertRedirect('site', 'login');
    }

    public function testSiteRedirectFromHome(): void
    {
        $this->login();
        $this->assertRedirect('/', 'site');
    }

    public function testSite(): void
    {
        $this->login();

        $view  = $this->view('site');
        $props = $view['props'];

        $this->assertSame('site', $view['id']);
        $this->assertSame('Site', $view['title']);
        $this->assertSame('k-site-view', $view['component']);

        // TODO: add more props tests
    }

    public function testSiteFile(): void
    {
        $this->app([
            'site' => [
                'files' => [
                    [
                        'filename' => 'test.jpg'
                    ]
                ]
            ]
        ]);

        $this->login();

        $view  = $this->view('site/files/test.jpg');
        $props = $view['props'];
        $model = $props['model'];

        $this->assertSame('File', $props['blueprint']);
        $this->assertSame(['state' => null], $props['lock']);

        $this->assertSame([], $props['tab']);
        $this->assertSame([], $props['tabs']);

        // model
        $this->assertSame([], $model['content']);
        $this->assertSame('jpg', $model['extension']);
        $this->assertSame('test.jpg', $model['filename']);
        $this->assertSame('test.jpg', $model['id']);
        $this->assertSame('image/jpeg', $model['mime']);
        $this->assertSame('0 KB', $model['niceSize']);
        $this->assertSame('site', $model['parent']);
        $this->assertSame('image', $model['type']);

        $this->assertNull($props['next']);
        $this->assertNull($props['prev']);
    }
}
