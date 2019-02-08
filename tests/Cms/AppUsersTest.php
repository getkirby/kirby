<?php

namespace Kirby\Cms;

use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Toolkit\Dir;

class AppUsersTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/AppUsersTest'
            ]
        ]);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testImpersonateAsKirby()
    {
        $app = $this->app;
        $app->impersonate('kirby');
        $this->assertEquals('kirby@getkirby.com', $app->user()->email());
        $this->assertTrue($app->user()->isKirby());
    }

    public function testImpersonateAsNull()
    {
        $app = $this->app;
        $app->impersonate('kirby');

        $this->assertEquals('kirby@getkirby.com', $app->user()->email());
        $this->assertTrue($app->user()->isKirby());

        $app->impersonate();

        $this->assertEquals(null, $app->user());
    }

    public function testImpersonateAsExistingUser()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'homer@simpsons.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $app->impersonate('homer@simpsons.com');
        $this->assertEquals('homer@simpsons.com', $app->user()->email());
    }

    /**
     * @expectedException Kirby\Exception\NotFoundException
     */
    public function testImpersonateAsMissingUser()
    {
        $this->app->impersonate('homer@simpsons.com');
    }

    public function testLoad()
    {
        $app = $this->app->clone([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertCount(1, $app->users());
        $this->assertEquals('user@getkirby.com', $app->users()->first()->email());
    }

    public function testSet()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'user@getkirby.com'
                ]
            ]
        ]);

        $this->assertCount(1, $app->users());
        $this->assertEquals('user@getkirby.com', $app->users()->first()->email());
    }

    public function basicAuthApp()
    {
        return new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'api' => [
                    'basicAuth' => true
                ]
            ],
            'users' => [
                [
                    'email'    => 'test@getkirby.com',
                    'password' => User::hashPassword('test')
                ]
            ],
            'request' => [
                'url' => 'https://getkirby.com/login'
            ]
        ]);
    }

    public function testUserFromBasicAuth()
    {
        $app  = $this->basicAuthApp();
        $auth = new BasicAuth(base64_encode('test@getkirby.com:test'));
        $user = $app->auth()->currentUserFromBasicAuth($auth);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@getkirby.com', $user->email());
    }

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionMessage Basic authentication is not activated
     */
    public function testUserFromBasicAuthDisabled()
    {
        $app = $this->basicAuthApp()->clone([
            'options' => [
                'api' => [
                    'basicAuth' => false
                ]
            ]
        ]);

        $auth = new BasicAuth(base64_encode('test@getkirby.com:test'));
        $user = $app->auth()->currentUserFromBasicAuth($auth);
    }

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionMessage Basic authentication is only allowed over HTTPS
     */
    public function testUserFromBasicAuthOverHttp()
    {
        $app = $this->basicAuthApp()->clone([
            'request' => [
                'url' => 'http://getkirby.com/login'
            ]
        ]);

        $auth = new BasicAuth(base64_encode('test@getkirby.com:test'));
        $user = $app->auth()->currentUserFromBasicAuth($auth);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid authorization header
     */
    public function testUserFromBasicAuthWithInvalidHeader()
    {
        $app = $this->basicAuthApp()->clone([
            'request' => [
                'url' => 'http://getkirby.com/login'
            ]
        ]);

        $user = $app->auth()->currentUserFromBasicAuth();
    }
}
