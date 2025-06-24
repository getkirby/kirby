<?php

namespace Kirby\Content;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Content\VersionId
 */
class VersionIdTest extends TestCase
{
	public function tearDown(): void
	{
		parent::tearDown();

		VersionId::$render = null;
	}

	/**
	 * @covers ::changes
	 * @covers ::value
	 */
	public function testChanges()
	{
		$version = VersionId::changes();

		$this->assertSame('changes', $version->value());
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructWithInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Version ID');

		new VersionId('foo');
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFromString()
	{
		$version = VersionId::from('latest');
		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFromInstance()
	{
		$version = VersionId::from(VersionId::latest());
		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		$version = VersionId::latest();

		$this->assertTrue($version->is('latest'));
		$this->assertTrue($version->is(VersionId::LATEST));
		$this->assertTrue($version->is(VersionId::latest()));
		$this->assertFalse($version->is('changes'));
		$this->assertFalse($version->is(VersionId::CHANGES));
		$this->assertFalse($version->is(VersionId::changes()));
	}

	/**
	 * @covers ::latest
	 * @covers ::value
	 */
	public function testLatest()
	{
		$version = VersionId::latest();

		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$this->assertSame('latest', (string)VersionId::latest());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
