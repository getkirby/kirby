<?php

namespace Kirby\Cms;

class UserAuthTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);
    }

    public function testGlobalUserState()
    {
        $user = $this->app->user('test@getkirby.com');

        $this->assertNull($this->app->user());
        $user->loginPasswordless();
        $this->assertSame($user, $this->app->user());
        $user->logout();
        $this->assertNull($this->app->user());
    }

    public function testLoginLogoutHooks()
    {
        $phpunit = $this;

        $calls         = 0;
        $logoutSession = false;
        $app = $this->app->clone([
            'hooks' => [
                'user.login:before' => function ($user, $session) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test@getkirby.com', $user->email());
                    $phpunit->assertSame($session, App::instance()->session());

                    $calls += 1;
                },
                'user.login:after' => function ($user, $session) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test@getkirby.com', $user->email());
                    $phpunit->assertSame($session, App::instance()->session());

                    $calls += 2;
                },
                'user.logout:before' => function ($user, $session) use ($phpunit, &$calls) {
                    $phpunit->assertSame('test@getkirby.com', $user->email());
                    $phpunit->assertSame($session, App::instance()->session());

                    $calls += 4;
                },
                'user.logout:after' => function ($user, $session) use ($phpunit, &$calls, &$logoutSession) {
                    $phpunit->assertSame('test@getkirby.com', $user->email());

                    if ($logoutSession === true) {
                        $phpunit->assertSame($session, App::instance()->session());
                        $phpunit->assertSame('value', App::instance()->session()->get('some'));
                    } else {
                        $phpunit->assertNull($session);
                    }

                    $calls += 8;
                }
            ]
        ]);

        // without prepopulated session
        $user = $app->user('test@getkirby.com');
        $user->loginPasswordless();
        $user->logout();

        // with a session with another value
        App::instance()->session()->set('some', 'value');
        $logoutSession = true;
        $user->loginPasswordless();
        $user->logout();

        // each hook needs to be called exactly twice
        $this->assertSame((1 + 2 + 4 + 8) * 2, $calls);
    }
}
