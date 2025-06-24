<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;

/**
 * @covers \Kirby\Sane\Html
 * @todo Add more tests from DOMPurify and the other test classes
 */
class HtmlTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.Html';

	protected static string $type = 'html';

	/**
	 * @dataProvider allowedProvider
	 */
	public function testAllowed(string $file)
	{
		$fixture = $this->fixture($file);

		Html::validateFile($fixture);

		$sanitized = Html::sanitize(file_get_contents($fixture));
		$this->assertStringEqualsFile($fixture, $sanitized);
	}

	public static function allowedProvider()
	{
		return static::fixtureList('allowed', 'html');
	}

	public function testDisallowedExternalFile()
	{
		$fixture   = $this->fixture('disallowed/link-subfolder.html');
		$sanitized = $this->fixture('sanitized/link-subfolder.html');

		$this->assertStringEqualsFile($fixture, Html::sanitize(file_get_contents($fixture)));
		$this->assertStringEqualsFile($sanitized, Html::sanitize(file_get_contents($fixture), isExternal: true));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');
		Html::validateFile($fixture);
	}
}
