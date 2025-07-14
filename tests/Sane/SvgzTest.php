<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Svgz::class)]
class SvgzTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Sane.Svgz';

	protected static string $type = 'svgz';

	#[DataProvider('allowedProvider')]
	public function testAllowed(string $file): void
	{
		$fixture = $this->fixture($file);

		Svgz::validateFile($fixture);

		$input     = file_get_contents($fixture);
		$sanitized = Svgz::sanitize($input);
		$decoded   = gzdecode($sanitized);

		$this->assertIsString($decoded);
		$this->assertSame(gzdecode($input), gzdecode($sanitized));
	}

	public static function allowedProvider(): array
	{
		return static::fixtureList('allowed', 'svgz');
	}

	#[DataProvider('invalidProvider')]
	public function testInvalid(string $file): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Could not uncompress gzip data');

		Svgz::validateFile($this->fixture($file));
	}

	public static function invalidProvider(): array
	{
		return static::fixtureList('invalid', 'svgz');
	}

	public function testDisallowedDoctypeEntityAttack(): void
	{
		$fixture   = $this->fixture('disallowed/doctype-entity-attack.svgz');
		$sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

		$this->assertStringEqualsFile($sanitized, gzdecode(Svgz::sanitize(file_get_contents($fixture))));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not define a subset');
		Svgz::validateFile($fixture);
	}

	public function testDisallowedExternalFile(): void
	{
		$fixture   = $this->fixture('disallowed/xlink-subfolder.svg');
		$fixtureZ  = $this->fixture('disallowed/xlink-subfolder.svgz');
		$sanitized = $this->fixture('sanitized/xlink-subfolder.svg');

		$this->assertStringEqualsFile($fixture, gzdecode(Svgz::sanitize(file_get_contents($fixtureZ))));
		$this->assertStringEqualsFile($sanitized, gzdecode(Svgz::sanitize(file_get_contents($fixtureZ), isExternal: true)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');
		Svgz::validateFile($fixtureZ);
	}
}
