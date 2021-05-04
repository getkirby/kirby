<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Locale;
use Kirby\Toolkit\Str;

class AppTranslationsTest extends TestCase
{
    protected $app;
    protected $fixtures;
    protected $locale = [];
    protected $localeSuffix;

    public function setUp(): void
    {
        $constants = [
            LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY,
            LC_NUMERIC, LC_TIME, LC_MESSAGES
        ];

        // make a backup of the current locale
        foreach ($constants as $constant) {
            $this->locale[$constant] = setlocale($constant, '0');
        }

        // test which locale suffix the system supports
        setlocale(LC_ALL, 'de_DE.' . $this->localeSuffix);
        if (setlocale(LC_ALL, '0') === 'de_DE.' . $this->localeSuffix) {
            $this->localeSuffix = 'utf8';
        } else {
            $this->localeSuffix = 'UTF-8';
        }

        // now set a baseline
        setlocale(LC_ALL, 'C');

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

        Locale::set($this->locale);
        $this->locale = [];
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
                ],
                [
                    'code'         => 'en',
                    'translations' => [
                        'hello' => 'Hello'
                    ]
                ]
            ],
            'translations' => [
                'de' => [
                    'hello' => 'Hallo'
                ]
            ]
        ]);

        I18n::$locale = 'de';

        // translation
        $translation = $app->translation('de');

        $this->assertInstanceOf('Kirby\Cms\Translation', $translation);
        $this->assertIsArray($translation->data());
        $this->assertArrayHasKey('button', $translation->data());
        $this->assertArrayHasKey('hello', $translation->data());
        $this->assertSame('Knopf', $translation->data()['button']);
        $this->assertSame('Hallo', $translation->data()['hello']);
        $this->assertSame('Knopf', t('button'));
        $this->assertSame('Hallo', t('hello'));

        // translations
        $translation = $app->translations()->find('de');

        $this->assertInstanceOf('Kirby\Cms\Translation', $translation);
        $this->assertIsArray($translation->data());
        $this->assertArrayHasKey('button', $translation->data());
        $this->assertArrayHasKey('hello', $translation->data());
        $this->assertSame('Knopf', $translation->data()['button']);
        $this->assertSame('Hallo', $translation->data()['hello']);
        $this->assertSame('Knopf', t('button'));
        $this->assertSame('Hallo', t('hello'));
    }

    public function testTranslationFallback()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'         => 'de-de',
                    'default'      => true,
                    'translations' => [
                        'button1' => 'Knopf1 de-de'
                    ]
                ],
                [
                    'code'         => 'de-at',
                    'translations' => [
                        'button1' => 'Knopf1 de-at'
                    ]
                ]
            ],
            'translations' => [
                'de' => [
                    'button1' => 'Knopf1',
                    'button2' => 'Knopf2'
                ]
            ]
        ]);

        I18n::$locale = 'de-de';
        $this->assertSame('Knopf1 de-de', t('button1'));
        $this->assertSame('Knopf2', t('button2'));
        $this->assertSame('Deutsch', t('translation.name'));

        I18n::$locale = 'de-at';
        $this->assertSame('Knopf1 de-at', t('button1'));
        $this->assertSame('Knopf2', t('button2'));
        $this->assertSame('Deutsch', t('translation.name'));

        I18n::$locale = 'en';
        $this->assertSame('Knopf1 de-de', t('button1'));
        $this->assertSame('Knopf2', t('button2'));
        $this->assertSame('English', t('translation.name'));
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

    public function testLanguageTranslationWithSlugs()
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
                ],
                [
                    'code'         => 'ru',
                    'default'      => false,
                    'translations' => [
                        'button' => 'кнопка'
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

        // making sure that Str slug rules are added on load
        // and don't get altered by `t()` call
        $result = $app->render('de/test');
        $this->assertSame('Knopf', $result->body());
        $this->assertSame('de', $app->language()->code());
        $this->assertSame('kompanija', Str::slug('Компания'));
        $this->assertSame('Knopf', t('button'));
        $this->assertSame('kompanija', Str::slug('Компания'));


        $result = $app->render('en/test');
        $this->assertSame('Button', $result->body());
        $this->assertSame('en', $app->language()->code());
        $this->assertSame('kompanija', Str::slug('Компания'));
        $this->assertSame('Button', t('button'));
        $this->assertSame('kompanija', Str::slug('Компания'));

        $result = $app->render('ru/test');
        $this->assertSame('кнопка', $result->body());
        $this->assertSame('ru', $app->language()->code());
        $this->assertSame('kompaniya', Str::slug('Компания'));
        $this->assertSame('кнопка', t('button'));
        $this->assertSame('kompaniya', Str::slug('Компания'));
    }

    public function testLocaleString()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'locale' => 'de_DE.' . $this->localeSuffix
            ]
        ]);
        $app->setCurrentLanguage();

        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
    }

    public function testLocaleArray()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'locale' => [
                    'LC_ALL'   => 'de_AT.' . $this->localeSuffix,
                    'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
                    LC_NUMERIC => 'de_CH.' . $this->localeSuffix
                ]
            ]
        ]);
        $app->setCurrentLanguage();

        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
        $this->assertSame('de_CH.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
    }

    public function testPanelLanguage()
    {
        // single-language setup
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
        $this->assertSame('en', $app->panelLanguage());

        // override with the panel.language option
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'panel.language' => 'it'
            ]
        ]);
        $this->assertSame('it', $app->panelLanguage());

        // multi-language setup with a simple default language
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'    => 'fr',
                    'default' => true
                ]
            ]
        ]);
        $this->assertSame('fr', $app->panelLanguage());

        // multi-language setup with a default language with country code
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'    => 'de-ch',
                    'default' => true
                ]
            ]
        ]);
        $this->assertSame('de', $app->panelLanguage());

        // override with the panel.language option
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'    => 'fr',
                    'default' => true
                ]
            ],
            'options' => [
                'panel.language' => 'it'
            ]
        ]);
        $this->assertSame('it', $app->panelLanguage());
    }
}
