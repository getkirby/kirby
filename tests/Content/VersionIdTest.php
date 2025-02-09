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

	public function testAll()
	{
		$list = VersionId::all();

		$this->assertCount(2, $list);
		$this->assertSame('latest', $list[0]->value());
		$this->assertSame('changes', $list[1]->value());
	}

	public function testChanges()
	{
		$version = VersionId::changes();

		$this->assertSame('changes', $version->value());
	}

	public function testConstructWithInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Version ID');

		new VersionId('foo');
	}

	public function testFromString()
	{
		$version = VersionId::from('latest');
		$this->assertSame('latest', $version->value());
	}

	public function testFromInstance()
	{
		$version = VersionId::from(VersionId::latest());
		$this->assertSame('latest', $version->value());
	}

	public function testIs()
	{
		$version = VersionId::latest();

		$this->assertTrue($version->is('latest'));
		$this->assertTrue($version->is(VersionId::LATEST));
		$this->assertFalse($version->is('something-else'));
		$this->assertFalse($version->is(VersionId::CHANGES));
	}

	public function testLatest()
	{
		$version = VersionId::latest();

		$this->assertSame('latest', $version->value());
	}

	public function testRenderString()
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

	public function testRenderInstance()
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

	public function testRenderPreviousValue()
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

	public function testRenderException()
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

	public function testToString()
	{
		$this->assertSame('latest', (string)VersionId::latest());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
