<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;

class AppLocalesTest extends \PHPUnit\Framework\TestCase
{

    public function testLocales()
    {
        $app = App::instance();
        $locales = $app->locales();

        $this->assertInstanceOf(Locales::class, $locales);

        $i = 0;

        foreach ($locales as $locale) {
            $this->assertInstanceOf(Locale::class, $locale);
            $i++;
        }

        $this->assertEquals($i, $locales->count());
    }

    public function testTranslate()
    {
        $app = App::instance();
        $this->assertEquals('Search', $app->translate('search'));
        $this->assertEquals('Search', t('search'));
    }

    public function testTranslateFallback()
    {
        $app = App::instance();
        $this->assertEquals('Cake', $app->translate('not-exist', 'Cake'));
        $this->assertEquals('Cake', t('not-exist', 'Cake'));
    }

    public function testTranslateSetLocale()
    {
        $app = App::instance();
        $this->assertEquals('Das ist ein Testfehler', $app->translate('error.test', null, 'de_DE'));
        $this->assertEquals('Das ist ein Testfehler', t('error.test', null, 'de_DE'));
    }

    public function testTranslateUserLanguage()
    {
        $app = App::instance();

        $this->assertEquals('This is a test error', $app->translate('error.test'));
        $this->assertEquals('This is a test error', t('error.test'));

        $app = new App([
            'user' => new User([
                'email' => 'peter@lustig.de',
                'language' => 'de_DE'
            ])
        ]);

        $this->assertEquals('Das ist ein Testfehler', $app->translate('error.test'));
        $this->assertEquals('Das ist ein Testfehler', t('error.test'));

        // reset app instance
        $app = new App();
    }

    public function testException()
    {
        $exception = new Exception([
            'key'      => 'test',
            'fallback' => 'This would be the fallback error'
        ]);

        $this->assertEquals('error.test', $exception->getKey());
        $this->assertEquals('This is a test error', $exception->getMessage());
    }

    public function testExceptionPinned()
    {
        $exception = new Exception([
            'key'       => 'test',
            'fallback'  => 'This would be the fallback error',
            'translate' => false
        ]);

        $this->assertEquals('error.test', $exception->getKey());
        $this->assertEquals('This would be the fallback error', $exception->getMessage());
    }

    public function testExceptionInvalidKey()
    {
        $exception = new Exception([
            'key'      => 'no-real-key',
            'fallback' => 'This would be the fallback error'
        ]);

        $this->assertEquals('error.no-real-key', $exception->getKey());
        $this->assertEquals('This would be the fallback error', $exception->getMessage());
    }

    public function testExceptionUserLanguage()
    {
        $app = new App([
            'user' => new User([
                'email' => 'peter@lustig.de',
                'language' => 'de_DE'
            ])
        ]);

        $exception = new Exception([
            'key' => 'test'
        ]);

        $this->assertEquals('Das ist ein Testfehler', $exception->getMessage());

        // reset app instance
        $app = new App();
    }

}
