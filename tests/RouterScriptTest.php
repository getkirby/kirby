<?php

namespace Kirby;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Tests the `router.php` script for PHP's built-in server
 */
#[CoversNothing]
class RouterScriptTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/RouterScript';

	protected array $server;

	/**
	 * Who answers the request: PHP's built-in server
	 * as a static file (`server`) or Kirby's `index.php` (`kirby`)
	 */
	protected function route(string $uri): string
	{
		// PHP's built-in server passes the resolved document root
		$_SERVER['DOCUMENT_ROOT'] = realpath(static::TMP . '/site');
		$_SERVER['REQUEST_URI']   = $uri;

		$result = (static fn () => include KIRBY_DIR . '/router.php')();

		return $result === false ? 'server' : 'kirby';
	}

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		$this->setUpTmp();

		F::write(static::TMP . '/site/index.php', '<?php');
		F::write(static::TMP . '/site/asset.txt', 'asset');
		F::write(static::TMP . '/site2/secret.txt', 'secret');
		F::write(static::TMP . '/other/secret.txt', 'secret');
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		$this->tearDownTmp();
		parent::tearDown();
	}

	public function testFileInDocumentRoot(): void
	{
		$this->assertSame('server', $this->route('/asset.txt'));
	}

	public function testFileInSiblingWithSamePrefix(): void
	{
		// existing and missing files must get the same answer
		$this->assertSame('kirby', $this->route('/../site2/secret.txt'));
		$this->assertSame('kirby', $this->route('/%2e%2e/site2/secret.txt'));
		$this->assertSame('kirby', $this->route('/../site2/missing.txt'));
	}

	public function testFileOutsideDocumentRoot(): void
	{
		$this->assertSame('kirby', $this->route('/../other/secret.txt'));
		$this->assertSame('kirby', $this->route('/%2e%2e/other/secret.txt'));
	}

	public function testHomepage(): void
	{
		$this->assertSame('kirby', $this->route('/'));
		$this->assertSame('kirby', $this->route('/.'));
	}

	public function testMissingFile(): void
	{
		$this->assertSame('kirby', $this->route('/missing.txt'));
	}
}
