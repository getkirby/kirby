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

	public function testFromArray(): void
	{
		$status = LicenseStatus::from([
			'icon'  => 'check',
			'label' => 'Valid license',
			'theme' => 'success',
			'value' => 'active',
		]);

		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame('active', $status->value());
	}

	/**
	 * @covers ::dialog
	 */
	public function testDialog(): void
	{
		$status = new LicenseStatus(
			value: 'missing',
			icon: 'alert',
			label: 'Enter license',
			dialog: $dialog = 'my/dialog'
		);

		$this->assertSame($dialog, $status->dialog());
	}

	/**
	 * @covers ::drawer
	 */
	public function testDrawer(): void
	{
		$status = new LicenseStatus(
			value: 'missing',
			icon: 'alert',
			label: 'Enter license',
			drawer: $drawer = 'my/drawer'
		);

		$this->assertSame($drawer, $status->drawer());
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

		$status = LicenseStatus::from('demo');
		$this->assertSame('demo', $status->value());

		$status = LicenseStatus::from('inactive');
		$this->assertSame('inactive', $status->value());

		$status = LicenseStatus::from('legacy');
		$this->assertSame('legacy', $status->value());

		$status = LicenseStatus::from('missing');
		$this->assertSame('missing', $status->value());

		$status = LicenseStatus::from('unknown');
		$this->assertSame('unknown', $status->value());
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
	 * @covers ::link
	 */
	public function testLink(): void
	{
		$status = new LicenseStatus(
			value: 'missing',
			icon: 'alert',
			label: 'Buy license',
			link: $url = 'https://getkirby.com/buy'
		);

		$this->assertSame($url, $status->link());
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
			'dialog' => null,
			'drawer' => null,
			'icon'   => 'check',
			'label'  => 'Valid license',
			'link'   => null,
			'theme'  => null,
			'value'  => 'active'
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
