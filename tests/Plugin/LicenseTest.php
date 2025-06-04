<?php

namespace Kirby\Plugin;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(License::class)]
class LicenseTest extends TestCase
{
	protected function plugin(): Plugin
	{
		return new Plugin(
			name: 'test/test'
		);
	}

	public function test__toString(): void
	{
		$license = new License(
			plugin: $this->plugin(),
			name: 'Custom license'
		);

		$this->assertSame('Custom license', (string)$license);
	}

	public function testFromArray(): void
	{
		$license = License::from($this->plugin(), [
			'name'   => 'Custom license',
			'link'   => 'https://getkirby.com',
			'status' => 'missing'
		]);
		$this->assertSame('Custom license', $license->name());
		$this->assertSame('https://getkirby.com', $license->link());
		$this->assertSame('missing', $license->status()->value());
	}

	public function testFromClosure(): void
	{
		$license = License::from($this->plugin(), function ($plugin) {
			return new License(
				plugin: $plugin,
				name: 'Custom license',
				status: LicenseStatus::from('active')
			);
		});

		$this->assertSame('Custom license', $license->name());
		$this->assertSame('active', $license->status()->value());
	}

	public function testFromString(): void
	{
		$license = License::from($this->plugin(), 'Custom license');
		$this->assertSame('Custom license', $license->name());
		$this->assertSame('active', $license->status()->value());
	}

	public function testFromNull(): void
	{
		$license = License::from($this->plugin(), null);
		$this->assertSame('-', $license->name());
		$this->assertSame('unknown', $license->status()->value());
	}

	public function testLink(): void
	{
		$license = new License(
			plugin: $this->plugin(),
			name: 'Custom license',
			link: 'https://getkirby.com'
		);

		$this->assertSame('https://getkirby.com', $license->link());
	}

	public function testName(): void
	{
		$license = new License(
			plugin: $this->plugin(),
			name: 'Custom license'
		);

		$this->assertSame('Custom license', $license->name());
	}

	public function testStatus(): void
	{
		$license = new License(
			plugin: $this->plugin(),
			name: 'Custom license',
			status: LicenseStatus::from('missing')
		);

		$this->assertInstanceOf(LicenseStatus::class, $license->status());
		$this->assertSame('missing', $license->status()->value());
	}

	public function testToArray(): void
	{
		$license = new License(
			plugin: $this->plugin(),
			name: 'Custom license',
		);

		$this->assertSame([
			'link'   => null,
			'name'   => 'Custom license',
			'status' => [
				'dialog' => null,
				'drawer' => null,
				'icon'   => 'question',
				'label'  => 'Unknown',
				'link'   => null,
				'theme'  => 'passive',
				'value'  => 'unknown',
			]
		], $license->toArray());
	}
}
