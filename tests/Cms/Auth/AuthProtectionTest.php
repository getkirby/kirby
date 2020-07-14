<?php

namespace Kirby\Cms;

require_once __DIR__ . '/../mocks.php';

/**
 * @coversDefaultClass Kirby\Cms\Auth
 */
class AuthProtectionTest extends TestCase
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
            ]
        ]);
        Dir::make($this->fixtures . '/site/accounts');

        $this->auth = new Auth($this->app);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    /**
     * @covers ::logfile
     */
    public function testLogfile()
    {
        $this->assertEquals($this->fixtures . '/site/accounts/.logins', $this->auth->logfile());
    }

    /**
     * @covers ::log
     */
    public function testLog()
    {
        copy(__DIR__ . '/fixtures/logins.cleanup.json', $this->fixtures . '/site/accounts/.logins');

        // should delete expired and old entries and add by-email array
        $this->assertEquals([
            'by-ip' => [
                '38f0a08519792a17e18a251008f3a116977907f921b0b287c8' => [
                    'time'   => '9999999999',
                    'trials' => 5
                ]
            ],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileEquals(__DIR__ . '/fixtures/logins.cleanup-cleaned.json', $this->fixtures . '/site/accounts/.logins');

        // should handle missing .logins file
        unlink($this->fixtures . '/site/accounts/.logins');
        $this->assertEquals([
            'by-ip'    => [],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileNotExists($this->fixtures . '/site/accounts/.logins');

        // should handle invalid .logins file
        file_put_contents($this->fixtures . '/site/accounts/.logins', 'some gibberish');
        $this->assertEquals([
            'by-ip'    => [],
            'by-email' => []
        ], $this->auth->log());
        $this->assertFileNotExists($this->fixtures . '/site/accounts/.logins');
    }

    /**
     * @covers ::ipHash
     */
    public function testIpHash()
    {
        $this->app->visitor()->ip('10.1.123.234');

        $this->assertEquals('87084f11690867b977a611dd2c943a918c3197f4c02b25ab59', $this->auth->ipHash());
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
        $this->assertTrue($this->auth->track('lisa@simpsons.com'));

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
                    'trials' => 3
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
        $this->assertEquals($data, $this->auth->log());
        $this->assertEquals(json_encode($data), file_get_contents($this->fixtures . '/site/accounts/.logins'));
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
        $this->assertEquals('marge@simpsons.com', $user->email());
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordInvalid1()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Invalid email or password');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');
        $this->auth->validatePassword('lisa@simpsons.com', 'springfield123');

        $this->assertEquals(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordInvalid2()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Invalid email or password');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.3.123.234');
        $this->auth->validatePassword('marge@simpsons.com', 'invalid-password');

        $this->assertEquals(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertEquals(10, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordBlocked()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Invalid email or password');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');

        $this->app->visitor()->ip('10.2.123.234');
        $this->auth->validatePassword('homer@simpsons.com', 'springfield123');
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugInvalid1()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.3.123.234');
        $this->auth->validatePassword('lisa@simpsons.com', 'springfield123');

        $this->assertEquals(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugInvalid2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The passwords do not match');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.3.123.234');
        $this->auth->validatePassword('marge@simpsons.com', 'invalid-password');

        $this->assertEquals(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
        $this->assertEquals(10, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
    }

    /**
     * @covers ::validatePassword
     */
    public function testValidatePasswordDebugBlocked()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Rate limit exceeded');

        copy(__DIR__ . '/fixtures/logins.json', $this->fixtures . '/site/accounts/.logins');
        $this->app = $this->app->clone([
            'options' => [
                'debug' => true
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->visitor()->ip('10.2.123.234');
        $this->auth->validatePassword('homer@simpsons.com', 'springfield123');
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
