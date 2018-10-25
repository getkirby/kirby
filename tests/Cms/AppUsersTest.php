<?php

namespace Kirby\Cms;

class AppUsersTest extends TestCase
{

    public function testImpersonateAsKirby()
    {

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('kirby@getkirby.com', $app->user()->email());
        $this->assertTrue($app->user()->isKirby());
    }

    public function testImpersonateAsNull()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $app->impersonate('kirby');

        $this->assertEquals('kirby@getkirby.com', $app->user()->email());
        $this->assertTrue($app->user()->isKirby());

        $app->impersonate();

        $this->assertEquals(null, $app->user());
    }

    public function testImpersonateAsExistingUser()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
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
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $app->impersonate('homer@simpsons.com');
    }

    public function testLoad()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertCount(1, $app->users());
        $this->assertEquals('user@getkirby.com', $app->users()->first()->email());
    }

    public function testSet()
    {
        $app = new App([
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

        $app    = $this->basicAuthApp();
        $header = 'Basic ' . base64_encode('test@getkirby.com:test');
        $user   = $app->currentUserFromBasicAuth($header);

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

        $header = 'Basic ' . base64_encode('test@getkirby.com:test');
        $user   = $app->currentUserFromBasicAuth($header);
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

        $header = 'Basic ' . base64_encode('test@getkirby.com:test');
        $user   = $app->currentUserFromBasicAuth($header);
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

        $header = 'Bearer ' . base64_encode('test@getkirby.com:test');
        $user   = $app->currentUserFromBasicAuth($header);
    }

}
