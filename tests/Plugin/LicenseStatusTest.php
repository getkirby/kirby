<?php

namespace Kirby\Plugin;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LicenseStatus::class)]
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

	public function testFromNull(): void
	{
		$status = LicenseStatus::from(null);

		$this->assertInstanceOf(LicenseStatus::class, $status);
		$this->assertSame('unknown', $status->value());
	}

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

	public function testIcon(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('check', $status->icon());
	}

	public function testLabel(): void
	{
		$status = new LicenseStatus(
			value: 'active',
			icon: 'check',
			label: 'Valid license'
		);

		$this->assertSame('Valid license', $status->label());
	}

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
