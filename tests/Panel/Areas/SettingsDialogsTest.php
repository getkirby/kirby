<?php

namespace Kirby\Panel\Areas;

class SettingsDialogsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testCreateLanguage(): void
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

    public function testCreateLanguageOnSubmit(): void
    {
        $this->app([
            'options' => [
                'languages' => true
            ]
        ]);

        $this->login();
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

    public function testDeleteLanguage(): void
    {
        $this->installLanguages();
        $this->login();

        $dialog = $this->dialog('languages/de/delete');
        $props  = $dialog['props'];

        $this->assertRemoveDialog($dialog);
        $this->assertSame('Do you really want to delete the language <strong>Deutsch</strong> including all translations? This cannot be undone!', $props['text']);
    }

    public function testDeleteLanguageOnSubmit(): void
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

    public function testRegistrationOnSubmitWithInvalidLicense(): void
    {
        $this->submit([
            'license' => 'K2-1234'
        ]);

        $dialog = $this->dialog('registration');

        $this->assertSame(400, $dialog['code']);
        $this->assertSame('Please enter a valid license key', $dialog['error']);
    }

    public function testRegistrationOnSubmitWithInvalidEmail(): void
    {
        $this->submit([
            'license' => 'K3-PRO-',
            'email'   => 'mail@'
        ]);

        $dialog = $this->dialog('registration');

        $this->assertSame(400, $dialog['code']);
        $this->assertSame('Please enter a valid email address', $dialog['error']);
    }
}
