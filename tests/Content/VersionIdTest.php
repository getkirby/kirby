<?php

namespace Kirby\Content;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionId::class)]
class VersionIdTest extends TestCase
{
	public function tearDown(): void
	{
		parent::tearDown();

		VersionId::$render = null;
	}

	public function testChanges(): void
	{
		$version = VersionId::changes();

		$this->assertSame('changes', $version->value());
	}

	public function testConstructWithInvalidId(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Version ID');

		new VersionId('foo');
	}

	public function testFromString(): void
	{
		$version = VersionId::from('latest');
		$this->assertSame('latest', $version->value());
	}

	public function testFromInstance(): void
	{
		$version = VersionId::from(VersionId::latest());
		$this->assertSame('latest', $version->value());
	}

	public function testIs(): void
	{
		$version = VersionId::latest();

		$this->assertTrue($version->is('latest'));
		$this->assertTrue($version->is(VersionId::LATEST));
		$this->assertTrue($version->is(VersionId::latest()));
		$this->assertFalse($version->is('changes'));
		$this->assertFalse($version->is(VersionId::CHANGES));
		$this->assertFalse($version->is(VersionId::changes()));
	}

	public function testLatest(): void
	{
		$version = VersionId::latest();

		$this->assertSame('latest', $version->value());
	}

	public function testRenderString(): void
	{
		$executed = 0;

		$this->assertNull(VersionId::$render);

		$return = VersionId::render('latest', function () use (&$executed) {
			$executed++;
			$this->assertSame('latest', VersionId::$render->value());

			return 'some string';
		});
		$this->assertSame('some string', $return);

		$this->assertNull(VersionId::$render);

		$return = VersionId::render('changes', function () use (&$executed) {
			$executed += 2;
			$this->assertSame('changes', VersionId::$render->value());

			return 12345;
		});
		$this->assertSame(12345, $return);

		$this->assertNull(VersionId::$render);
		$this->assertSame(3, $executed);
	}

	public function testRenderInstance(): void
	{
		$executed = 0;

		$this->assertNull(VersionId::$render);

		$return = VersionId::render(VersionId::latest(), function () use (&$executed) {
			$executed++;
			$this->assertSame('latest', VersionId::$render->value());

			return 'some string';
		});
		$this->assertSame('some string', $return);

		$this->assertNull(VersionId::$render);

		$return = VersionId::render(VersionId::changes(), function () use (&$executed) {
			$executed += 2;
			$this->assertSame('changes', VersionId::$render->value());

			return 12345;
		});
		$this->assertSame(12345, $return);

		$this->assertNull(VersionId::$render);
		$this->assertSame(3, $executed);
	}

	public function testRenderPreviousValue(): void
	{
		$executed = 0;

		VersionId::$render = VersionId::latest();

		$return = VersionId::render('changes', function () use (&$executed) {
			$executed++;
			$this->assertSame('changes', VersionId::$render->value());

			return 'some string';
		});
		$this->assertSame('some string', $return);

		$this->assertSame('latest', VersionId::$render->value());
		$this->assertSame(1, $executed);
	}

	public function testRenderException(): void
	{
		$executed = 0;

		$this->assertNull(VersionId::$render);

		try {
			VersionId::render(VersionId::latest(), function () use (&$executed) {
				$executed++;
				$this->assertSame('latest', VersionId::$render->value());

				throw new Exception('Something went wrong');
			});
		} catch (Exception $e) {
			$executed += 2;
			$this->assertSame('Something went wrong', $e->getMessage());
		}

		$this->assertNull(VersionId::$render);
		$this->assertSame(3, $executed);
	}

	public function testToString(): void
	{
		$this->assertSame('latest', (string)VersionId::latest());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
