<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Dir;

require_once __DIR__ . '/../mocks.php';

/**
 * @coversDefaultClass Kirby\Cms\Auth
 */
class AuthProtectionTest extends TestCase
{
    public $failedEmail;

    protected $app;
    protected $auth;
    protected $fixtures;

    public function setUp(): void
    {
        $self = $this;

        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/AuthTest'
            ],
            'users' => [
                [
                    'email'    => 'marge@simpsons.com',
                    'password' => password_hash('springfield123', PASSWORD_DEFAULT)
                ],
                [
                    'email'    => 'homer@simpsons.com',
                    'password' => password_hash('springfield123', PASSWORD_DEFAULT)
                ],
                [
                    'email'    => 'test@exämple.com',
                    'password' => password_hash('springfield123', PASSWORD_DEFAULT)
                ]
            ],
            'hooks' => [
                'user.login:failed' => function ($email) use ($self) {
                    $self->failedEmail = $email;
                }
            ]
        ]);
        Dir::make($this->fixtures . '/site/accounts');

        $this->auth = new Auth($this->app);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
        $this->failedEmail = null;
    }

    /**
     * @covers ::logfile
     */
    public function testLogfile()
    {
        $this->assertSame($this->fixtures . '/site/accounts/.logins', $this->auth->logfile());
    }

    /**
     * @covers ::log
     */
    public function testLog()
    {
        copy(__DIR__ . '/fixtures/logins.cleanup.json', $this->fixtures . '/site/accounts/.logins');

        // should delete expired and old entries and add by-email array
        $this->assertSame([
            'by-ip' => [
                '38f0a08519792a17e18a251008f3a116977907f921b0b287c8' => [
                    'time'   => 9999999999,
                    'trials' => 5
                ]
            ],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileEquals(__DIR__ . '/fixtures/logins.cleanup-cleaned.json', $this->fixtures . '/site/accounts/.logins');

        // should handle missing .logins file
        unlink($this->fixtures . '/site/accounts/.logins');
        $this->assertSame([
            'by-ip'    => [],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileDoesNotExist($this->fixtures . '/site/accounts/.logins');

        // should handle invalid .logins file
        file_put_contents($this->fixtures . '/site/accounts/.logins', 'some gibberish');
        $this->assertSame([
            'by-ip'    => [],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileDoesNotExist($this->fixtures . '/site/accounts/.logins');
    }

    /**
     * @covers ::ipHash
     */
    public function testIpHash()
    {
        $this->app->visitor()->ip('10.1.123.234');

        $this->assertSame('87084f11690867b977a611dd2c943a918c3197f4c02b25ab59', $this->auth->ipHash());
    }

    /**
     * @covers ::isBlocked
     */
    public function testIsBlocked()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.1.123.234');
        $this->assertFalse($this->auth->isBlocked('marge@simpsons.com'));
        $this->assertTrue($this->auth->isBlocked('homer@simpsons.com'));
        $this->assertFalse($this->auth->isBlocked('lisa@simpsons.com'));

        $this->app->visitor()->ip('10.2.123.234');
        $this->assertTrue($this->auth->isBlocked('marge@simpsons.com'));
        $this->assertTrue($this->auth->isBlocked('homer@simpsons.com'));
        $this->assertTrue($this->auth->isBlocked('lisa@simpsons.com'));
    }

    /**
     * @covers ::track
     */
    public function testTrack()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.1.123.234');
        $this->assertTrue($this->auth->track('homer@simpsons.com'));
        $this->assertTrue($this->auth->track('homer@simpsons.com'));
        $this->assertTrue($this->auth->track('marge@simpsons.com'));
        $this->assertTrue($this->auth->track('lisa@simpsons.com'));
        $this->assertTrue($this->auth->track('lisa@simpsons.com'));

        $this->app->visitor()->ip('10.2.123.234');
        $this->assertTrue($this->auth->track('homer@simpsons.com'));
        $this->assertTrue($this->auth->track('marge@simpsons.com'));
        $this->assertTrue($this->auth->track('lisa@simpsons.com'));

        $this->app->visitor()->ip('10.3.123.234');
        $this->assertTrue($this->auth->track('homer@simpsons.com'));
        $this->assertTrue($this->auth->track('marge@simpsons.com'));
        $this->assertTrue($this->auth->track('lisa@simpsons.com', false));
        $this->assertSame('marge@simpsons.com', $this->failedEmail);

        $this->assertTrue($this->auth->track(null));
        $this->assertNull($this->failedEmail);

        $data = [
            'by-ip' => [
                '87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
                    'time'   => MockTime::$time,
                    'trials' => 14
                ],
                '38f0a08519792a17e18a251008f3a116977907f921b0b287c8' => [
                    'time'   => MockTime::$time,
                    'trials' => 13
                ],
                '85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da' => [
                    'time'   => MockTime::$time,
                    'trials' => 4
                ]
            ],
            'by-email' => [
                'homer@simpsons.com' => [
                    'time'   => MockTime::$time,
                    'trials' => 14
                ],
                'marge@simpsons.com' => [
                    'time'   => MockTime::$time,
                    'trials' => 3
                ]
            ]
        ];
        $this->assertSame($data, $this->auth->log());
        $this->assertSame(json_encode($data), file_get_contents($this->fixtures . '/site/accounts/.logins'));
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordValid()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');
        $user = $this->auth->validatePassword('marge@simpsons.com', 'springfield123');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('marge@simpsons.com', $user->email());
        $this->assertNull($this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordInvalid1()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
        } catch (PermissionException $e) {
            $this->assertSame('Invalid login', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertSame('lisa@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordInvalid2()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
        } catch (PermissionException $e) {
            $this->assertSame('Invalid login', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertSame(1, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
        $this->assertSame('marge@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordBlocked()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.2.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('homer@simpsons.com', 'springfield123');
        } catch (PermissionException $e) {
            $this->assertSame('Invalid login', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame('homer@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugInvalid1()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.3.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
        } catch (NotFoundException $e) {
            $this->assertSame('The user "lisa@simpsons.com" cannot be found', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertSame('lisa@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugInvalid2()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.3.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('Wrong password', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertSame(1, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
        $this->assertSame('marge@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugBlocked()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.2.123.234');

        $thrown = false;
        try {
            $this->auth->validatePassword('homer@simpsons.com', 'springfield123');
        } catch (PermissionException $e) {
            $this->assertSame('Rate limit exceeded', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame('homer@simpsons.com', $this->failedEmail);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordWithUnicodeEmail()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');
        $user = $this->auth->validatePassword('test@exämple.com', 'springfield123');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@exämple.com', $user->email());
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordWithPunycodeEmail()
    {
        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');
        $user = $this->auth->validatePassword('test@xn--exmple-cua.com', 'springfield123');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@exämple.com', $user->email());
    }
}
