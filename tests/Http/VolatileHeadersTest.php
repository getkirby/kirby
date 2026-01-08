<?php

namespace Kirby\Http;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VolatileHeaders::class)]
class VolatileHeadersTest extends TestCase
{
	protected VolatileHeaders $volatile;

	public function setUp(): void
	{
		$this->volatile = new VolatileHeaders();
	}

	public function testMarkSingleHeader(): void
	{
		$this->volatile->mark('X-Custom');

		$collected = $this->volatile->collect();
		$this->assertArrayHasKey('X-Custom', $collected);
		$this->assertNull($collected['X-Custom']);
	}

	public function testMarkHeaderWithValues(): void
	{
		$this->volatile->mark('Vary', ['Accept', 'Accept-Encoding']);

		$collected = $this->volatile->collect();
		$this->assertSame(['accept', 'accept-encoding'], $collected['Vary']);
	}

	public function testMarkMultipleHeaders(): void
	{
		$this->volatile->mark('X-Custom-1');
		$this->volatile->mark('X-Custom-2');

		$collected = $this->volatile->collect();
		$this->assertArrayHasKey('X-Custom-1', $collected);
		$this->assertArrayHasKey('X-Custom-2', $collected);
	}

	public function testMarkWithEmptyValues(): void
	{
		$this->volatile->mark('Vary', ['', '  ', 'Accept']);

		$collected = $this->volatile->collect();
		$this->assertSame(['accept'], $collected['Vary']);
	}

	public function testMarkWithDuplicateValues(): void
	{
		$this->volatile->mark('Vary', ['Accept', 'Accept']);

		$collected = $this->volatile->collect();
		$this->assertSame(['accept'], $collected['Vary']);
	}

	public function testMarkWithExistingValues(): void
	{
		$this->volatile->mark('Vary', ['Accept']);
		$this->volatile->mark('Vary', ['Accept-Encoding']);

		$collected = $this->volatile->collect();
		$this->assertSame(['accept', 'accept-encoding'], $collected['Vary']);
	}

	public function testMarkDoesNotOverrideNullValue(): void
	{
		$this->volatile->mark('X-Custom');
		$this->volatile->mark('X-Custom', ['value1', 'value2']);

		$collected = $this->volatile->collect();
		$this->assertNull($collected['X-Custom']);
	}

	public function testMarkWithEmptyArray(): void
	{
		$this->volatile->mark('Vary', []);

		$collected = $this->volatile->collect();
		$this->assertArrayNotHasKey('Vary', $collected);
	}

	public function testCollectWithoutCors(): void
	{
		$this->volatile->mark('X-Custom');

		$collected = $this->volatile->collect();
		$this->assertSame(['X-Custom' => null], $collected);
	}

	public function testCollectWithCors(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'allowOrigin' => ['https://example.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$collected = $this->volatile->collect();

		$this->assertArrayHasKey('Access-Control-Allow-Origin', $collected);
		$this->assertArrayHasKey('Vary', $collected);
		$this->assertNull($collected['Access-Control-Allow-Origin']);
		$this->assertSame(['origin'], $collected['Vary']);
	}

	public function testCollectMergesVaryFromCors(): void
	{
		$this->kirby([
			'options' => [
				'cors' => [
					'allowOrigin' => ['https://example.com']
				]
			],
			'server' => [
				'HTTP_ORIGIN' => 'https://example.com'
			]
		]);

		$this->volatile->mark('Vary', ['Accept']);
		$collected = $this->volatile->collect();

		$this->assertSame(['accept', 'origin'], $collected['Vary']);
	}

	public function testStripSingleHeader(): void
	{
		$this->volatile->mark('X-Custom');

		$headers = [
			'X-Custom' => 'value',
			'X-Other' => 'other'
		];

		$stripped = $this->volatile->strip($headers);
		$this->assertArrayNotHasKey('X-Custom', $stripped);
		$this->assertArrayHasKey('X-Other', $stripped);
	}

	public function testStripVaryValues(): void
	{
		$this->volatile->mark('Vary', ['Origin']);

		$headers = [
			'Vary' => 'Accept, Origin, Accept-Encoding'
		];

		$stripped = $this->volatile->strip($headers);
		$this->assertSame('Accept, Accept-Encoding', $stripped['Vary']);
	}

	public function testStripRemovesVaryWhenEmpty(): void
	{
		$this->volatile->mark('Vary', ['Origin']);

		$headers = ['Vary' => 'Origin'];

		$stripped = $this->volatile->strip($headers);
		$this->assertArrayNotHasKey('Vary', $stripped);
	}

	public function testStripWithCustomVolatileArray(): void
	{
		$headers = [
			'X-Custom' => 'value',
			'X-Other' => 'other'
		];

		$customVolatile = ['X-Custom' => null];
		$stripped = $this->volatile->strip($headers, $customVolatile);

		$this->assertArrayNotHasKey('X-Custom', $stripped);
		$this->assertArrayHasKey('X-Other', $stripped);
	}

	public function testStripWithNonExistentVary(): void
	{
		$this->volatile->mark('Vary', ['Origin']);

		$headers = ['X-Other' => 'value'];

		$stripped = $this->volatile->strip($headers);
		$this->assertSame($headers, $stripped);
	}

	public function testStripVaryNormalization(): void
	{
		$this->volatile->mark('Vary', ['origin']);

		$headers = [
			'Vary' => ' Accept ,  Origin,  Accept-Encoding  '
		];

		$stripped = $this->volatile->strip($headers);
		$this->assertSame('Accept, Accept-Encoding', $stripped['Vary']);
	}

	public function testStripVaryCaseInsensitive(): void
	{
		$this->volatile->mark('Vary', ['ORIGIN']);

		$headers = ['Vary' => 'Accept, origin, Accept-Encoding'];

		$stripped = $this->volatile->strip($headers);
		$this->assertSame('Accept, Accept-Encoding', $stripped['Vary']);
	}

	public function testStripMultipleHeaders(): void
	{
		$this->volatile->mark('X-Custom-1');
		$this->volatile->mark('X-Custom-2');

		$headers = [
			'X-Custom-1' => 'value1',
			'X-Custom-2' => 'value2',
			'X-Other' => 'other'
		];

		$stripped = $this->volatile->strip($headers);
		$this->assertArrayNotHasKey('X-Custom-1', $stripped);
		$this->assertArrayNotHasKey('X-Custom-2', $stripped);
		$this->assertArrayHasKey('X-Other', $stripped);
	}
}
