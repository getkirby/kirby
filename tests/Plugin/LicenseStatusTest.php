<?php

namespace Kirby\Plugin;

use Kirby\Cms\TestCase;

/**
 * @coversDefaultClass \Kirby\Plugin\LicenseStatus
 */
class LicenseStatusTest extends TestCase
{
	public function test__toString(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('Valid license', (string)$status);
	}

	public static function defaultsProvider(): array
	{
		return [
			'active' => [
				'key'   => 'active',
				'value' => 'active',
				'icon'  => 'check',
				'label' => 'Valid license',
				'theme' => 'positive'
			],
			'demo'   => [
				'key'   => 'demo',
				'value' => 'demo',
				'icon'  => 'preview',
				'label' => 'Demo',
				'theme' => 'notice'
			],
			'inactive' => [
				'key'   => 'inactive',
				'value' => 'inactive',
				'icon'  => 'clock',
				'label' => 'No new major versions',
				'theme' => 'notice'
			],
			'legacy' => [
				'key'   => 'legacy',
				'value' => 'legacy',
				'icon'  => 'alert',
				'label' => 'Please renew your license',
				'theme' => 'negative'
			],
			'unknown' => [
				'key'   => 'unknown',
				'value' => 'unknown',
				'icon'  => 'question',
				'label' => 'Unknown license',
				'theme' => 'passive'
			]
		];
	}

	/**
	 * @covers ::defaults
	 * @dataProvider defaultsProvider
	 */
	public function testDefaults(string $key, string $value, string $icon, string $label, string|null $theme = null): void
	{
		$defaults = LicenseStatus::defaults();

		$this->assertArrayHasKey($key, $defaults);
		$status = $defaults[$key];

		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame($value, $status->value());
		$this->assertSame($icon, $status->icon());
		$this->assertSame($label, $status->label());
		$this->assertSame($theme, $status->theme());
	}

	/**
	 * @covers ::from
	 */
	public function testFromInstance(): void
	{
		$status = LicenseStatus::from(new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		));

		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame('active', $status->value());
	}

	/**
	 * @covers ::from
	 */
	public function testFromNull(): void
	{
		$status = LicenseStatus::from(null);

		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame('unknown', $status->value());
	}

	/**
	 * @covers ::from
	 */
	public function testFromString(): void
	{
		$status = LicenseStatus::from('active');
		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame('active', $status->value());
	}

	/**
	 * @covers ::icon
	 */
	public function testIcon(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('check', $status->icon());
	}

	/**
	 * @covers ::label
	 */
	public function testLabel(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('Valid license', $status->label());
	}

	/**
	 * @covers ::theme
	 */
	public function testTheme(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license',
			theme: 'success'
		);

		$this->assertSame('success', $status->theme());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame([
			'icon'  => 'check',
			'label' => 'Valid license',
			'theme' => null,
			'value' => 'active'
		], $status->toArray());
	}

	/**
	 * @covers ::value
	 */
	public function testValue(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('active', $status->value());
	}
}
