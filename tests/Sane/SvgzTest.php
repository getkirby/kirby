<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;

/**
 * @covers \Kirby\Sane\Svgz
 */
class SvgzTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.Svgz';

	protected static $type = 'svgz';

	/**
	 * @dataProvider allowedProvider
	 */
	public function testAllowed(string $file)
	{
		$fixture = $this->fixture($file);

		$this->assertNull(Svgz::validateFile($fixture));

		$input     = file_get_contents($fixture);
		$sanitized = Svgz::sanitize($input);
		$decoded   = gzdecode($sanitized);

		$this->assertIsString($decoded);
		$this->assertSame(gzdecode($input), gzdecode($sanitized));
	}

	public static function allowedProvider()
	{
		return static::fixtureList('allowed', 'svgz');
	}

	/**
	 * @dataProvider invalidProvider
	 */
	public function testInvalid(string $file)
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Could not uncompress gzip data');

		Svgz::validateFile($this->fixture($file));
	}

	public static function invalidProvider()
	{
		return static::fixtureList('invalid', 'svgz');
	}

	public function testDisallowedDoctypeEntityAttack()
	{
		$fixture   = $this->fixture('disallowed/doctype-entity-attack.svgz');
		$sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

		$this->assertStringEqualsFile($sanitized, gzdecode(Svgz::sanitize(file_get_contents($fixture))));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not define a subset');
		Svgz::validateFile($fixture);
	}

	public function testDisallowedExternalFile()
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
