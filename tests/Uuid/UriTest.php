<?php

namespace Kirby\Uuid;

/**
 * @coversDefaultClass \Kirby\Uuid\Uri
 */
class UriTest extends TestCase
{
	public function provider()
	{
		// Provider entries:
		// - UUID input / toString output
		// - object
		// - type
		// - domain
		// - path

		return [
			// pure UUIDs
			['site://', 'site', null, null],
			['page://page-id', 'page',  'page-id', null],
			['file://file-id', 'file', 'file-id', null],
			['user://user-id', 'user', 'user-id', null],
			['block://block-id', 'block', 'block-id', null],
			['struct://structure-id', 'struct', 'structure-id', null],

			// mixed UUIDs
			['site://a.jpg', 'site', null, 'a.jpg'],
			['page://page-id/a.jpg', 'page', 'page-id', 'a.jpg'],
			['user://user-id/a.jpg', 'user', 'user-id', 'a.jpg'],
			['page://page-id/myField/block-id', 'page', 'page-id', 'myField/block-id']
		];
	}

	/**
	 * @covers ::domain
	 * @dataProvider provider
	 */
	public function testDomain(string $input, string $scheme, string|null $domain)
	{
		$protocol = new Uri($input);
		$this->assertSame($domain, $protocol->domain());
	}

	/**
	 * @covers ::path
	 * @dataProvider provider
	 */
	public function testPath(string $input, string $scheme, string|null $domain, string|null $path)
	{
		$protocol = new Uri($input);
		$this->assertSame($path ?? '', $protocol->path()->toString());
	}

	/**
	 * @covers ::__construct
	 * @covers ::base
	 * @covers ::toString
	 * @dataProvider provider
	 */
	public function testToString(string $input)
	{
		$protocol = new Uri($input);
		$this->assertSame($input, $protocol->toString());
	}

	/**
	 * @covers ::type
	 * @dataProvider provider
	 */
	public function testType(string $input, string $scheme)
	{
		$protocol = new Uri($input);
		$this->assertSame($scheme, $protocol->type());
	}
}
