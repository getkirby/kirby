<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\TestCase;
use Kirby\Email\Email;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass \Kirby\Cms\Auth\EmailChallenge
 */
class EmailChallengeTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        Email::$debug = true;
        Email::$emails = [];
        $_SERVER['SERVER_NAME'] = 'kirby.test';

        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = dirname(__DIR__) . '/fixtures/AuthTest'
            ],
            'site' => [
                'content' => [
                    'title' => 'Test Site'
                ]
            ],
            'users' => [
                [
                    'email' => 'homer@simpsons.com',
                    'name'  => 'Homer'
                ],
                [
                    'email' => 'marge@simpsons.com'
                ],
                [
                    'email'    => 'bart@simpsons.com',
                    'language' => 'de'
                ]
            ]
        ]);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);

        Email::$debug = false;
        Email::$emails = [];
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @covers ::isAvailable
     */
    public function testIsAvailable()
    {
        $user = $this->app->user('homer@simpsons.com');
        $this->assertTrue(EmailChallenge::isAvailable($user, 'login'));
    }

    /**
     * @covers ::create
     */
    public function testCreateLogin()
    {
        $user = $this->app->user('homer@simpsons.com');
        $options = ['mode' => 'login', 'timeout' => 7.3 * 60];

        $code1 = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code1);
        $this->assertSame(6, strlen($code1));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@kirby.test', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['homer@simpsons.com' => 'Homer'], $email->to());
        $this->assertSame('Your login code', $email->subject());
        $this->assertStringContainsString('login code', $email->body()->text());
        $this->assertStringContainsString('Homer', $email->body()->text());
        $this->assertStringContainsString('7 minutes', $email->body()->text());
        $this->assertStringContainsString(
            substr($code1, 0, 3) . ' ' . substr($code1, 3, 3),
            $email->body()->text()
        );

        $code2 = EmailChallenge::create($user, $options);
        $this->assertNotSame($code1, $code2);
    }

    /**
     * @covers ::create
     */
    public function testCreatePathUrl()
    {
        $app = $this->app->clone([
            'options' => [
                'url' => 'https://example.com/test'
            ]
        ]);
        $user = $app->user('homer@simpsons.com');
        $options = ['mode' => 'login', 'timeout' => 7.3 * 60];

        $code = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code);
        $this->assertSame(6, strlen($code));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@example.com', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['homer@simpsons.com' => 'Homer'], $email->to());
        $this->assertSame('Your login code', $email->subject());
        $this->assertStringContainsString('login code', $email->body()->text());
        $this->assertStringContainsString('Homer', $email->body()->text());
        $this->assertStringContainsString('7 minutes', $email->body()->text());
        $this->assertStringContainsString(
            substr($code, 0, 3) . ' ' . substr($code, 3, 3),
            $email->body()->text()
        );
    }

    /**
     * @covers ::create
     */
    public function testCreate2FA()
    {
        $user = $this->app->user('homer@simpsons.com');
        $options = ['mode' => '2fa', 'timeout' => 7.3 * 60];

        $code1 = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code1);
        $this->assertSame(6, strlen($code1));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@kirby.test', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['homer@simpsons.com' => 'Homer'], $email->to());
        $this->assertSame('Your login code', $email->subject());
        $this->assertStringContainsString('login code', $email->body()->text());
        $this->assertStringContainsString('Homer', $email->body()->text());
        $this->assertStringContainsString('7 minutes', $email->body()->text());
        $this->assertStringContainsString(
            substr($code1, 0, 3) . ' ' . substr($code1, 3, 3),
            $email->body()->text()
        );

        $code2 = EmailChallenge::create($user, $options);
        $this->assertNotSame($code1, $code2);
    }

    /**
     * @covers ::create
     */
    public function testCreateReset()
    {
        $user = $this->app->user('marge@simpsons.com');
        $options = ['mode' => 'password-reset', 'timeout' => 7.3 * 60];

        $code1 = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code1);
        $this->assertSame(6, strlen($code1));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@kirby.test', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['marge@simpsons.com' => ''], $email->to());
        $this->assertSame('Your password reset code', $email->subject());
        $this->assertStringContainsString('password reset code', $email->body()->text());
        $this->assertStringContainsString('marge@simpsons.com', $email->body()->text());
        $this->assertStringContainsString('7 minutes', $email->body()->text());
        $this->assertStringContainsString(
            substr($code1, 0, 3) . ' ' . substr($code1, 3, 3),
            $email->body()->text()
        );

        $code2 = EmailChallenge::create($user, $options);
        $this->assertNotSame($code1, $code2);
    }

    /**
     * @covers ::create
     */
    public function testCreateResetUserLanguage()
    {
        $user = $this->app->user('bart@simpsons.com');
        $options = ['mode' => 'password-reset', 'timeout' => 7.3 * 60];

        $code1 = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code1);
        $this->assertSame(6, strlen($code1));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@kirby.test', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['bart@simpsons.com' => ''], $email->to());
        $this->assertSame('Dein Anmeldecode', $email->subject());
        $this->assertStringContainsString('Anmeldecode für das Kirby Panel', $email->body()->text());
        $this->assertStringContainsString('bart@simpsons.com', $email->body()->text());
        $this->assertStringContainsString('7 Minuten', $email->body()->text());
        $this->assertStringContainsString(
            substr($code1, 0, 3) . ' ' . substr($code1, 3, 3),
            $email->body()->text()
        );

        $code2 = EmailChallenge::create($user, $options);
        $this->assertNotSame($code1, $code2);
    }

    /**
     * @covers ::create
     */
    public function testCreateCustom()
    {
        $this->app = $this->app->clone([
            'options' => [
                'auth.challenge.email.from' => 'test@example.com',
                'auth.challenge.email.fromName' => 'Test',
                'auth.challenge.email.subject' => 'Custom subject'
            ],
            'templates' => [
                'emails/auth/login' => dirname(__DIR__) . '/fixtures/auth.email.text.php'
            ]
        ]);

        $user = $this->app->user('homer@simpsons.com');
        $options = ['mode' => 'login', 'timeout' => 7.3 * 60];

        $code = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code);
        $this->assertSame(6, strlen($code));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('test@example.com', $email->from());
        $this->assertSame('Test', $email->fromName());
        $this->assertSame(['homer@simpsons.com' => 'Homer'], $email->to());
        $this->assertSame('Custom subject', $email->subject());
        $this->assertSame(
            "homer@simpsons.com\nTest Site\n7\n" . substr($code, 0, 3) . ' ' . substr($code, 3, 3),
            $email->body()->text()
        );
    }

    /**
     * @covers ::create
     */
    public function testCreateCustomHtml()
    {
        $this->app = $this->app->clone([
            'templates' => [
                'emails/auth/login'      => dirname(__DIR__) . '/fixtures/auth.email.text.php',
                'emails/auth/login.html' => dirname(__DIR__) . '/fixtures/auth.email.html.php'
            ]
        ]);

        $user = $this->app->user('homer@simpsons.com');
        $options = ['mode' => 'login', 'timeout' => 7.3 * 60];

        $code = EmailChallenge::create($user, $options);
        $this->assertStringMatchesFormat('%d', $code);
        $this->assertSame(6, strlen($code));
        $this->assertCount(1, Email::$emails);
        $email = Email::$emails[0];
        $this->assertSame('noreply@kirby.test', $email->from());
        $this->assertSame('Test Site', $email->fromName());
        $this->assertSame(['homer@simpsons.com' => 'Homer'], $email->to());
        $this->assertSame('Your login code', $email->subject());
        $this->assertSame(
            "homer@simpsons.com\nTest Site\n7\n" . substr($code, 0, 3) . ' ' . substr($code, 3, 3),
            $email->body()->text()
        );
        $this->assertSame(
            "HTML: homer@simpsons.com\nTest Site\n7\n" . substr($code, 0, 3) . ' ' . substr($code, 3, 3),
            $email->body()->html()
        );
    }
}
