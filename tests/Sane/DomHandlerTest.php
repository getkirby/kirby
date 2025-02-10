<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomHandler::class)]
class DomHandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.DomHandler';

	protected static string $type = 'sane';

	public function testSanitize()
	{
		$fixture = '<xml><test attr="value">Hello world</test></xml>';
		$this->assertSame($fixture, DomHandler::sanitize($fixture));

		$fixture   = '<?xml version="1.0"?><xml><test>Hello world</test></xml>';
		$sanitized = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xml><test>Hello world</test></xml>";
		$this->assertSame($sanitized, DomHandler::sanitize($fixture));

		$fixture   = '<?xml version="1.0" standalone="no"?><xml><test>Hello world</test></xml>';
		$sanitized = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n<xml><test>Hello world</test></xml>";
		$this->assertSame($sanitized, DomHandler::sanitize($fixture));

		$string = '<xml><a xlink:href="/another-folder">Very malicious</a></xml>';
		$this->assertSame($string, DomHandler::sanitize($string)); // not external by default
		$this->assertSame('<xml><a>Very malicious</a></xml>', DomHandler::sanitize($string, isExternal: true));
	}

	public function testValidate()
	{
		$this->assertNull(DomHandler::validate('<!DOCTYPE xml><xml><test attr="value">Hello world</test></xml>'));
		$this->assertNull(DomHandler::validate('<xml><a xlink:href="/another-folder">Very malicious</a></xml>'));
	}

	public function testValidateException1()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Unknown URL type');

		DomHandler::validate("<xml>\n<a href='javascript:alert(1)'></a>\n</xml>");
	}

	public function testValidateException2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not reference external files');

		DomHandler::validate("<!DOCTYPE xml SYSTEM \"https://malicious.com/something.dtd\">\n<xml>\n<a href='javascript:alert(1)'></a>\n</xml>");
	}

	public function testValidateException3()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');

		DomHandler::validate('<xml><a xlink:href="/another-folder">Very malicious</a></xml>', isExternal: true);
	}
}
