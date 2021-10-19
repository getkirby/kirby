<?php

namespace Kirby\Panel\Areas;

class LanguagesDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->enableMultilang();
        $this->login();
    }

    public function testLanguageCreate(): void
    {
        $this->installLanguages();
        $this->login();

        $dialog = $this->dialog('languages/create');
        $props  = $dialog['props'];

        $this->assertSame('k-language-dialog', $dialog['component']);

        $this->assertSame('Name', $props['fields']['name']['label']);
        $this->assertSame('Code', $props['fields']['code']['label']);
        $this->assertSame('Reading direction', $props['fields']['direction']['label']);
        $this->assertSame('PHP locale string', $props['fields']['locale']['label']);

        $this->assertSame('Add a new language', $props['submitButton']);

        $this->assertSame('', $props['value']['name']);
        $this->assertSame('', $props['value']['code']);
        $this->assertSame('ltr', $props['value']['direction']);
        $this->assertSame('', $props['value']['locale']);
    }

    public function testLanguageCreateOnSubmit(): void
    {
        $this->submit([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'locale' => 'en_US'
        ]);

        $this->assertCount(0, $this->app->languages());

        $dialog = $this->dialog('languages/create');

        $this->assertSame('language.create', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertCount(1, $this->app->languages());
    }

    public function testLanguageDelete(): void
    {
        $this->installLanguages();
        $this->login();

        $dialog = $this->dialog('languages/de/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete the language <strong>Deutsch</strong> including all translations? This cannot be undone!', $props['text']);
    }

    public function testLanguageDeleteOnSubmit(): void
    {
        $this->installLanguages();
        $this->login();
        $this->submit([]);

        $this->assertCount(2, $this->app->languages());

        $dialog = $this->dialog('languages/de/delete');

        $this->assertSame('language.delete', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertCount(1, $this->app->languages());
    }

    public function testLanguageUpdate(): void
    {
        $this->installLanguages();
        $this->login();

        $dialog = $this->dialog('languages/en/update');
        $props  = $dialog['props'];

        $this->assertSame('k-language-dialog', $dialog['component']);

        $this->assertTrue($props['fields']['code']['disabled']);
        $this->assertSame('Save', $props['submitButton']);

        $this->assertSame('English', $props['value']['name']);
        $this->assertSame('en', $props['value']['code']);
        $this->assertSame('ltr', $props['value']['direction']);
        $this->assertSame('en', $props['value']['locale']);
        $this->assertSame([], $props['value']['rules']);
    }

    public function testLanguageUpdateWithLocaleSettings(): void
    {
        $this->app([
            'languages' => [
                'en' => [
                    'code'    => 'en',
                    'default' => true,
                    'name'    => 'English',
                    'locale'  => [
                        LC_ALL      => 'en_US.utf8',
                        LC_COLLATE  => 'en_US.utf8',
                    ]
                ],
                'de' => [
                    'code'    => 'de',
                    'default' => false,
                    'name'    => 'Deutsch'
                ]
            ]
        ]);

        $this->login();

        $dialog = $this->dialog('languages/en/update');
        $props  = $dialog['props'];

        $this->assertSame('info', $props['fields']['locale']['type']);
        $this->assertSame('You are using a custom locale set up. Please modify it in the language file in /site/languages', $props['fields']['locale']['text']);
    }

    public function testLanguageUpdateOnSubmit(): void
    {
        $this->installLanguages();
        $this->login();
        $this->submit([
            'name'      => 'Englisch',
            'direction' => 'rtl',
            'locale'    => 'en_US'
        ]);

        $dialog = $this->dialog('languages/en/update');

        $this->assertSame('language.update', $dialog['event']);
        $this->assertSame(200, $dialog['code']);
        $this->assertSame('Englisch', $this->app->language('en')->name());
        $this->assertSame('rtl', $this->app->language('en')->direction());
        $this->assertSame('en_US', $this->app->language('en')->locale(LC_ALL));
    }

    public function testRegistration(): void
    {
        $dialog = $this->dialog('registration');
        $props  = $dialog['props'];

        $this->assertFormDialog($dialog);

        $this->assertSame('Please enter your license code', $props['fields']['license']['label']);
        $this->assertSame('Email', $props['fields']['email']['label']);
        $this->assertSame('Register', $props['submitButton']);
        $this->assertNull($props['value']['license']);
        $this->assertNull($props['value']['email']);
    }
}
