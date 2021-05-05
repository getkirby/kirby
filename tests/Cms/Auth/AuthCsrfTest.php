<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;

/**
 * @coversDefaultClass Kirby\Cms\Auth
 */
class AuthCsrfTest extends TestCase
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
        ]);

        $this->auth = new Auth($this->app);
    }

    public function tearDown(): void
    {
        $this->app->session()->destroy();
        Dir::remove($this->fixtures);
        $_GET = [];
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromSession1()
    {
        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = [];
        $this->assertFalse($this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromSession2()
    {
        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => ''];
        $this->assertFalse($this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromSession3()
    {
        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => 'session-csrf'];
        $this->assertEquals('session-csrf', $this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromSession4()
    {
        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => 'invalid-csrf'];
        $this->assertFalse($this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromOption1()
    {
        $this->app = $this->app->clone([
            'options' => [
                'api.csrf' => 'option-csrf'
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = [];
        $this->assertFalse($this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromOption2()
    {
        $this->app = $this->app->clone([
            'options' => [
                'api.csrf' => 'option-csrf'
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => 'option-csrf'];
        $this->assertEquals('option-csrf', $this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromOption3()
    {
        $this->app = $this->app->clone([
            'options' => [
                'api.csrf' => 'option-csrf'
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => 'session-csrf'];
        $this->assertFalse($this->auth->csrf());
    }

    /**
     * @covers ::csrf
     */
    public function testCsrfFromOption4()
    {
        $this->app = $this->app->clone([
            'options' => [
                'api.csrf' => 'option-csrf'
            ]
        ]);
        $this->auth = new Auth($this->app);

        $this->app->session()->set('kirby.csrf', 'session-csrf');

        $_GET = ['csrf' => 'invalid-csrf'];
        $this->assertFalse($this->auth->csrf());
    }
}
