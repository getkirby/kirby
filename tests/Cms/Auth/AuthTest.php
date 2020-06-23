<?php

namespace Kirby\Cms;

use Kirby\Session\AutoSession;
use Throwable;

/**
 * @coversDefaultClass Kirby\Cms\Auth
 */
class AuthTest extends TestCase
{
    protected $app;
    protected $auth;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/AuthTest'
            ],
            'options' => [
                'api' => [
                    'basicAuth'     => true,
                    'allowInsecure' => true
                ]
            ],
            'users' => [
                [
                    'email'    => 'marge@simpsons.com',
                    'id'       => 'marge',
                    'password' => password_hash('springfield123', PASSWORD_DEFAULT)
                ],
                [
                    'email'    => 'homer@simpsons.com',
                    'id'       => 'homer',
                    'password' => password_hash('springfield123', PASSWORD_DEFAULT)
                ]
            ]
        ]);
        Dir::make($this->fixtures . '/site/accounts');

        $this->auth = new Auth($this->app);
    }

    public function tearDown(): void
    {
        $this->app->session()->destroy();
        Dir::remove($this->fixtures);
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
     * @covers ::currentUserFromImpersonation
     * @covers ::impersonate
     * @covers ::user
     */
    public function testImpersonate()
    {
        $this->assertSame(null, $this->auth->user());

        $user = $this->auth->impersonate('kirby');
        $this->assertSame('kirby', $user->id());
        $this->assertSame('kirby@getkirby.com', $user->email());
        $this->assertSame('admin', $user->role()->name());
        $this->assertSame($user, $this->auth->user());
        $this->assertSame($user, $this->auth->currentUserFromImpersonation());
        $this->assertNull($this->auth->user(null, false));

        $user = $this->auth->impersonate('homer@simpsons.com');
        $this->assertSame('homer@simpsons.com', $user->email());
        $this->assertSame($user, $this->auth->user());
        $this->assertSame($user, $this->auth->currentUserFromImpersonation());
        $this->assertNull($this->auth->user(null, false));

        $this->assertNull($this->auth->impersonate(null));
        $this->assertNull($this->auth->user());
        $this->assertNull($this->auth->currentUserFromImpersonation());
        $this->assertNull($this->auth->user(null, false));

        $this->assertNull($this->auth->impersonate());
        $this->assertNull($this->auth->user());
        $this->assertNull($this->auth->currentUserFromImpersonation());
        $this->assertNull($this->auth->user(null, false));
    }

    /**
     * @covers ::impersonate
     */
    public function testImpersonateInvalidUser()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

        $this->auth->impersonate('lisa@simpsons.com');
    }

    /**
     * @covers ::user
     */
    public function testUserSession1()
    {
        $session = $this->app->session();
        $session->set('user.id', 'marge');

        $user = $this->auth->user();
        $this->assertSame('marge@simpsons.com', $user->email());

        // impersonation is not set
        $this->assertNull($this->auth->currentUserFromImpersonation());

        // value is cached
        $session->set('user.id', 'homer');
        $user = $this->auth->user();
        $this->assertSame('marge@simpsons.com', $user->email());
    }

    /**
     * @covers ::user
     */
    public function testUserSession2()
    {
        $session = (new AutoSession($this->app->root('sessions')))->createManually();
        $session->set('user.id', 'homer');

        $user = $this->auth->user($session);
        $this->assertSame('homer@simpsons.com', $user->email());
    }

    /**
     * @covers ::user
     */
    public function testUserBasicAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('homer@simpsons.com:springfield123');

        $user = $this->auth->user();
        $this->assertSame('homer@simpsons.com', $user->email());
    }

    /**
     * @covers ::user
     */
    public function testUserBasicAuthInvalid1()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Invalid email or password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('homer@simpsons.com:invalid');

        $this->auth->user();
    }

    /**
     * @covers ::user
     */
    public function testUserBasicAuthInvalid2()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Invalid email or password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('homer@simpsons.com:invalid');

        try {
            $this->auth->user();
        } catch (Throwable $e) {
            // tested above, this check is for the second call
        }

        $this->auth->user();
    }
}
