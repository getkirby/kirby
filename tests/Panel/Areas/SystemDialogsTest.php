<?php

namespace Kirby\Panel\Areas;

use Kirby\Toolkit\Str;

class SystemDialogsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testLicense(): void
	{
		$dialog = $this->dialog('license');
		$props  = $dialog['props'];

		$this->assertSame('k-license-dialog', $dialog['component']);
	}

	public function testRegistration(): void
	{
		$dialog = $this->dialog('registration');
		$props  = $dialog['props'];

		$this->assertFormDialog($dialog);

		$this->assertSame('Please enter your license code', $props['fields']['license']['label']);
		$this->assertSame('Email', $props['fields']['email']['label']);
		$this->assertSame('Activate', $props['submitButton']['text']);
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
		$this->assertSame('Please enter a valid license code', $dialog['error']);
	}

	public function testRegistrationOnSubmitWithInvalidEmail(): void
	{
		$this->submit([
			'license' => 'K3-PRO-' . Str::random(32),
			'email'   => 'mail@'
		]);

		$dialog = $this->dialog('registration');

		$this->assertSame(400, $dialog['code']);
		$this->assertSame('Please enter a valid email address', $dialog['error']);
	}
}
