<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\TestCase;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;

class MockChallenge extends Challenge
{
    public static function isAvailable(User $user, string $mode): bool
    {
    }

    public static function create(User $user, array $options): ?string
    {
    }
}

/**
 * @coversDefaultClass \Kirby\Cms\Auth\Challenge
 */
class ChallengeTest extends TestCase
{
    protected $app;
    protected $fixtures;
    protected $session;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = dirname(__DIR__) . '/fixtures/AuthChallengeTest'
            ],
            'users' => [
                [
                    'email' => 'homer@simpsons.com'
                ]
            ]
        ]);

        $this->session = $this->app->session();
    }

    public function tearDown(): void
    {
        $this->session->destroy();
        Dir::remove($this->fixtures);
    }

    /**
     * @covers ::verify
     */
    public function testVerify()
    {
        $user = $this->app->user('homer@simpsons.com');

        $this->assertFalse(MockChallenge::verify($user, '123 456'));

        $this->session->set('kirby.challenge.code', 'test');
        $this->assertFalse(MockChallenge::verify($user, '123 456'));

        $this->session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
        $this->assertTrue(MockChallenge::verify($user, '123456'));
        $this->assertTrue(MockChallenge::verify($user, '12 34 56'));
        $this->assertFalse(MockChallenge::verify($user, '654321'));
    }
}
