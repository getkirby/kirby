<?php

namespace Kirby\Uuid;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Uri::class)]
class UriTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Uuid.Uri';

	public static function provider(): array
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
			// TODO: activate for  uuid-block-structure-support
			// ['block://block-id', 'block', 'block-id', null],
			// ['struct://structure-id', 'struct', 'structure-id', null],

			// mixed UUIDs
			['site://a.jpg', 'site', null, 'a.jpg'],
			['page://page-id/a.jpg', 'page', 'page-id', 'a.jpg'],
			['user://user-id/a.jpg', 'user', 'user-id', 'a.jpg'],
			['page://page-id/myField/block-id', 'page', 'page-id', 'myField/block-id']
		];
	}

	#[DataProvider('provider')]
	public function testDomain(string $input, string $scheme, string|null $domain)
	{
		$uri = new Uri($input);
		$this->assertSame($domain, $uri->domain());
	}

	#[DataProvider('provider')]
	public function testHost(string $input, string $scheme, string|null $domain, string|null $path)
	{
		$uri = new Uri($input);
		$this->assertSame($domain ?? '', $uri->host());
	}

	public function testHostSet()
	{
		$uri = new Uri('page://my-id');
		$this->assertSame('my-id', $uri->host());
		$uri->host('my-other-id');
		$this->assertSame('my-other-id', $uri->host());
		$this->assertSame('page://my-other-id', $uri->toString());
	}

	#[DataProvider('provider')]
	public function testPath(string $input, string $scheme, string|null $domain, string|null $path)
	{
		$uri = new Uri($input);
		$this->assertSame($path ?? '', $uri->path()->toString());
	}

	#[DataProvider('provider')]
	public function testToString(string $input)
	{
		$uri = new Uri($input);
		$this->assertSame($input, $uri->toString());
	}

	#[DataProvider('provider')]
	public function testType(string $input, string $scheme)
	{
		$uri = new Uri($input);
		$this->assertSame($scheme, $uri->type());
	}
}
