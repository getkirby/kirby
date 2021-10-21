<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\Auth\Status
 * @covers ::__construct
 * @covers ::setChallenge
 * @covers ::setChallengeFallback
 * @covers ::setEmail
 * @covers ::setKirby
 * @covers ::setStatus
 */
class StatusTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'homer@simpsons.com',
                    'name'  => 'Homer Simpson'
                ]
            ]
        ]);
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'active'
        ]);

        $this->assertSame('active', (string)$status);
    }

    /**
     * @covers ::challenge
     */
    public function testChallenge()
    {
        // no challenge when not in pending status
        $status = new Status([
            'kirby'             => $this->app,
            'challenge'         => 'totp',
            'challengeFallback' => 'email',
            'status'            => 'active'
        ]);
        $this->assertNull($status->challenge());

        // with pending challenge
        $status = new Status([
            'kirby'             => $this->app,
            'challenge'         => 'totp',
            'challengeFallback' => 'email',
            'status'            => 'pending'
        ]);
        $this->assertSame('totp', $status->challenge());

        // with faked challenge
        $status = new Status([
            'kirby'             => $this->app,
            'challenge'         => null,
            'challengeFallback' => 'email',
            'status'            => 'pending'
        ]);
        $this->assertSame('email', $status->challenge());

        // with faked challenge, but without automatic fallback
        $status = new Status([
            'kirby'             => $this->app,
            'challenge'         => null,
            'challengeFallback' => 'email',
            'status'            => 'pending'
        ]);
        $this->assertNull($status->challenge(false));
    }

    /**
     * @covers ::email
     */
    public function testEmail()
    {
        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'inactive'
        ]);
        $this->assertNull($status->email());

        $status = new Status([
            'kirby'  => $this->app,
            'email'  => 'homer@simpsons.com',
            'status' => 'active'
        ]);
        $this->assertSame('homer@simpsons.com', $status->email());
    }

    /**
     * @covers ::status
     * @covers ::setStatus
     */
    public function testStatus()
    {
        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'active'
        ]);
        $this->assertSame('active', $status->status());

        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'impersonated'
        ]);
        $this->assertSame('impersonated', $status->status());

        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'pending'
        ]);
        $this->assertSame('pending', $status->status());

        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'inactive'
        ]);
        $this->assertSame('inactive', $status->status());

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid argument "$props[\'status\']" in method "Status::__construct"');
        $status = new Status([
            'kirby'  => $this->app,
            'status' => 'invalid'
        ]);
    }

    /**
     * @covers ::toArray
     */
    public function testToArray()
    {
        $status = new Status([
            'kirby'             => $this->app,
            'challenge'         => null,
            'challengeFallback' => 'email',
            'email'             => 'homer@simpsons.com',
            'status'            => 'pending'
        ]);

        $this->assertSame([
            'challenge' => 'email',
            'email'     => 'homer@simpsons.com',
            'status'    => 'pending'
        ], $status->toArray());
    }

    /**
     * @covers ::user
     */
    public function testUser()
    {
        // only return active users
        $status = new Status([
            'kirby'  => $this->app,
            'email'  => 'homer@simpsons.com',
            'status' => 'pending'
        ]);
        $this->assertNull($status->user());

        // existing active user
        $status = new Status([
            'kirby'  => $this->app,
            'email'  => 'homer@simpsons.com',
            'status' => 'active'
        ]);
        $this->assertSame('Homer Simpson', $status->user()->name()->value());

        // existing impersonated user
        $status = new Status([
            'kirby'  => $this->app,
            'email'  => 'homer@simpsons.com',
            'status' => 'impersonated'
        ]);
        $this->assertSame('Homer Simpson', $status->user()->name()->value());

        // invalid active user
        $status = new Status([
            'kirby'  => $this->app,
            'email'  => 'invalid@simpsons.com',
            'status' => 'active'
        ]);
        $this->assertNull($status->user());
    }
}
