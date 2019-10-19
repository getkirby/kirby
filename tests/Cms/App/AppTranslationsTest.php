<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;

class AppTranslationsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'language' => 'de'
                ]
            ],
            'user' => 'admin@getkirby.com',
            'translations' => [
                'en' => [
                    'save'       => 'Save',
                    'reset'      => 'Reset',
                    'error.test' => 'This is a test error',
                ],
                'de' => [
                    'save'       => 'Speichern',
                    'error.test' => 'Das ist ein Testfehler',
                ]
            ]
        ]);

        $this->fixtures = __DIR__ . '/fixtures/AppTranslationsTest';
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function app()
    {
        return $this->app;
    }

    public function testTranslations()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
                'translations' => __DIR__ . '/fixtures/translations'
            ]
        ]);

        $translations = $app->translations();

        $this->assertInstanceOf(Translations::class, $translations);

        $i = 0;

        foreach ($translations as $translation) {
            $this->assertInstanceOf(Translation::class, $translation);
            $i++;
        }

        $this->assertEquals($i, $translations->count());
    }

    public function testTranslationFromCurrentLanguage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'         => 'de',
                    'default'      => true,
                    'translations' => [
                        'button' => 'Knopf'
                    ]
                ]
            ],
            'translations' => [
                'de' => [
                ]
            ]
        ]);

        I18n::$locale = 'de';

        $this->assertEquals('Knopf', t('button'));
    }

    public function testSetCurrentTranslation()
    {
        $app = $this->app();

        $this->assertEquals('Save', t('save'));
        $this->assertEquals('Reset', t('reset'));

        $app->setCurrentTranslation('de');

        $this->assertEquals('Speichern', t('save'));
        $this->assertEquals('Reset', t('reset'));
    }

    public function testTranslationInTemplate()
    {
        // create a dummy template
        F::write($this->fixtures . '/test.php', '<?= t("button") ?>');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'languages' => [
                [
                    'code'         => 'de',
                    'default'      => true,
                    'translations' => [
                        'button' => 'Knopf'
                    ]
                ],
                [
                    'code'         => 'en',
                    'default'      => false,
                    'translations' => [
                        'button' => 'Button'
                    ]
                ]
            ],
            'options' => [
                'languages' => true
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ]
            ]
        ]);

        $result = $app->render('de/test');
        $this->assertEquals('Knopf', $result->body());

        $result = $app->render('en/test');
        $this->assertEquals('Button', $result->body());
    }

    public function testExceptionWithoutLanguage()
    {
        I18n::$load = null;
        I18n::$translations = [];

        $exception = new Exception([
            'key'      => 'test',
            'fallback' => $fallbackError = 'This would be the fallback error'
        ]);

        $this->assertEquals('error.test', $exception->getKey());
        $this->assertEquals($fallbackError, $exception->getMessage());
    }

    public function testExceptionWithDefaultLanguage()
    {
        $this->app();

        $exception = new Exception([
            'key' => 'test'
        ]);

        $this->assertEquals('This is a test error', $exception->getMessage());
    }

    public function testExceptionWithTranslation()
    {
        $app = $this->app();
        $app->setCurrentTranslation('de');

        $exception = new Exception([
            'key' => 'test'
        ]);

        $this->assertEquals('Das ist ein Testfehler', $exception->getMessage());
    }

    public function testExceptionPinned()
    {
        $app = $this->app();

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
        $app = $this->app();

        $exception = new Exception([
            'key'      => 'no-real-key',
            'fallback' => 'This would be the fallback error'
        ]);

        $this->assertEquals('error.no-real-key', $exception->getKey());
        $this->assertEquals('This would be the fallback error', $exception->getMessage());
    }
}
