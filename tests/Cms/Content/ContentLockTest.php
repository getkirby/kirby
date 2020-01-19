<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class ContentLockTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function app()
    {
        return new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/ContentLockTest'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'test'],
                    ['slug' => 'foo']
                ]
            ],
            'users' => [
                ['email' => 'test@getkirby.com'],
                ['email' => 'homer@simpson.com'],
                ['email' => 'peter@lustig.de']
            ]
        ]);
    }

    public function setUp(): void
    {
        $this->app = $this->app();
        Dir::make($this->fixtures . '/content/test');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testClearLock()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $page->lock()->create();
        $oldData = $page->lock()->get();
        $app->users()->remove($app->user()->id());

        $app->impersonate('homer@simpson.com');
        $page->lock()->get();
        $page->lock()->create();
        $newData = $page->lock()->get();

        $this->assertTrue(empty($oldData));
        $this->assertFalse(empty($newData));
        $this->assertFalse($newData['unlockable']);
        $this->assertEquals('homer@simpson.com', $newData['email']);
        $this->assertArrayHasKey('time', $newData);
    }

    public function testCreate()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());
        $this->assertTrue($page->lock()->create());

        $this->assertFalse(empty($app->locks()->get($page)));
    }

    public function testCreateWithExistingLock()
    {
        $this->expectException('Kirby\Exception\DuplicateException');
        $this->expectExceptionMessage('/test is already locked');

        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $page->lock()->create();
    }

    public function testCreateUnauthenticated()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('No user authenticated');

        $app = $this->app;
        $page = $app->page('test');
        $page->lock()->create();
    }

    public function testGetWithNoLock()
    {
        $app = $this->app;
        $page = $app->page('test');

        $this->assertFalse($page->lock()->get());
    }

    public function testGetWithSameUser()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $page->lock()->create();

        $this->assertFalse($page->lock()->get());
    }

    public function testGet()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $page->lock()->create();

        $app->impersonate('homer@simpson.com');
        $data = $page->lock()->get();

        $this->assertFalse(empty($data));
        $this->assertFalse($data['unlockable']);
        $this->assertEquals('test@getkirby.com', $data['email']);
        $this->assertArrayHasKey('time', $data);
    }

    public function testIsLocked()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $page->lock()->create();
        $this->assertFalse($page->lock()->isLocked());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->isLocked());
    }

    public function testRemoveWithNoLock()
    {
        $app = $this->app;
        $page = $app->page('test');
        $app->impersonate('test@getkirby.com');

        $this->assertTrue($page->lock()->remove());
    }

    public function testRemoveFormOtherUser()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionMessage('The content lock can only be removed by the user who created it. Use unlock instead.');

        $app = $this->app;
        $page = $app->page('test');
        $app->impersonate('test@getkirby.com');
        $page->lock()->create();

        $app->impersonate('homer@simpson.com');
        $page->lock()->remove();
    }

    public function testRemove()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');

        $this->assertTrue($page->lock()->create());
        $this->assertFalse(empty($app->locks()->get($page)));

        $this->assertTrue($page->lock()->remove());
        $this->assertTrue(empty($app->locks()->get($page)));
    }

    public function testUnlockWithNoLock()
    {
        $app = $this->app;
        $page = $app->page('test');
        $app->impersonate('test@getkirby.com');

        $this->assertTrue($page->lock()->unlock());
    }

    public function testUnlock()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->unlock());

        $this->assertFalse(empty($app->locks()->get($page)['unlock']));
    }

    public function testIsUnlocked()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->unlock());
        $this->assertFalse($page->lock()->isUnlocked());

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->isUnlocked());
    }

    public function testResolveWithNoUnlock()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->resolve());
    }

    public function testResolve()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->unlock());
        $this->assertFalse(empty($app->locks()->get($page)['unlock']));

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->isUnlocked());
        $this->assertTrue($page->lock()->resolve());
        $this->assertFalse($page->lock()->isUnlocked());
        $this->assertTrue(empty($app->locks()->get($page)['unlock']));
    }

    public function testResolveWithRemainingUnlocks()
    {
        $app = $this->app;
        $page = $app->page('test');

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->create());

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->unlock());
        $this->assertEquals(count($app->locks()->get($page)['unlock']), 1);
        $this->assertTrue($page->lock()->create());

        $app->impersonate('peter@lustig.de');
        $this->assertTrue($page->lock()->unlock());
        $this->assertEquals(count($app->locks()->get($page)['unlock']), 2);

        $app->impersonate('test@getkirby.com');
        $this->assertTrue($page->lock()->isUnlocked());
        $this->assertTrue($page->lock()->resolve());
        $this->assertFalse($page->lock()->isUnlocked());
        $this->assertEquals(count($app->locks()->get($page)['unlock']), 1);

        $app->impersonate('homer@simpson.com');
        $this->assertTrue($page->lock()->isUnlocked());
    }
}
