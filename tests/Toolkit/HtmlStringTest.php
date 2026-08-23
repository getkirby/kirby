<?php

namespace Kirby\Toolkit;

use Kirby\Data\Json;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HtmlString::class)]
class HtmlStringTest extends TestCase
{
	protected function setUp(): void
	{
		I18n::$locale       = 'en';
		I18n::$load         = null;
		I18n::$fallback     = 'en';
		I18n::$translations = [];
	}

	protected function tearDown(): void
	{
		I18n::$translations = [];
	}

	public function testJsonSerialize(): void
	{
		$html = new HtmlString('<b>safe</b>');
		$this->assertSame('"<b>safe<\/b>"', json_encode($html));
	}

	public function testJsonEncodeDecode(): void
	{
		$data = HtmlString::resolve([
			'title' => $title = 'untrusted <script>alert(1)</script>',
			'body'  => new HtmlString($body = '<p>trusted</p>')
		]);

		$json    = Json::encode($data);
		$decoded = Json::decode($json);

		$this->assertSame($title, $decoded['title']);
		$this->assertSame($body, $decoded['<body>']);
	}

	public function testResolveRenamesTopLevelKey(): void
	{
		$data = [
			'title' => 'plain',
			'body'  => new HtmlString('<p>html</p>')
		];

		$resolved = HtmlString::resolve($data);

		$this->assertArrayHasKey('title', $resolved);
		$this->assertArrayHasKey('<body>', $resolved);
		$this->assertArrayNotHasKey('body', $resolved);
		$this->assertInstanceOf(HtmlString::class, $resolved['<body>']);
	}

	public function testResolveRecursesIntoNestedArrays(): void
	{
		$data = [
			'view' => [
				'props' => [
					'help' => new HtmlString('<em>hi</em>'),
					'name' => 'plain'
				]
			]
		];

		$resolved = HtmlString::resolve($data);

		$this->assertArrayHasKey('<help>', $resolved['view']['props']);
		$this->assertArrayHasKey('name', $resolved['view']['props']);
		$this->assertArrayNotHasKey('help', $resolved['view']['props']);
	}

	public function testResolveWalksArraysOfObjects(): void
	{
		$data = [
			'options' => [
				['text' => new HtmlString('<b>Bold</b>'), 'value' => 'a'],
				['text' => 'Plain', 'value' => 'b']
			]
		];

		$resolved = HtmlString::resolve($data);

		$this->assertArrayHasKey('<text>', $resolved['options'][0]);
		$this->assertArrayNotHasKey('text', $resolved['options'][0]);
		$this->assertArrayHasKey('text', $resolved['options'][1]);
		$this->assertArrayNotHasKey('<text>', $resolved['options'][1]);
	}

	public function testResolveMarksListOfHtmlStrings(): void
	{
		$resolved = HtmlString::resolve([
			'issues' => [new HtmlString('<b>a</b>'), new HtmlString('<b>b</b>')]
		]);

		$this->assertArrayHasKey('<issues>', $resolved);
		$this->assertArrayNotHasKey('issues', $resolved);
		$this->assertSame(
			'{"<issues>":["<b>a</b>","<b>b</b>"]}',
			Json::encode($resolved)
		);
	}

	public function testResolveDoesNotMarkMixedList(): void
	{
		$resolved = HtmlString::resolve([
			'issues' => [new HtmlString('<b>a</b>'), 'plain']
		]);

		// a single plain string must never end up marked as trusted
		$this->assertArrayNotHasKey('<issues>', $resolved);
	}

	public function testResolveIsIdempotent(): void
	{
		$data = [
			'help'   => new HtmlString('<b>trusted</b>'),
			'issues' => [new HtmlString('<b>a</b>')],
			'text'   => 'plain'
		];

		$once  = HtmlString::resolve($data);
		$twice = HtmlString::resolve($once);

		// a second pass must not turn `<key>` into `<<key>>`
		$this->assertSame(array_keys($once), array_keys($twice));
		$this->assertSame(Json::encode($once), Json::encode($twice));
	}

	public function testResolveLeavesPlainArraysUnchanged(): void
	{
		$data = ['a' => 1, 'b' => ['c' => 2]];
		$this->assertSame($data, HtmlString::resolve($data));
	}

	public function testResolveDoesNotMutateInput(): void
	{
		$data = ['body' => new HtmlString('<p>x</p>')];
		HtmlString::resolve($data);
		$this->assertArrayHasKey('body', $data);
		$this->assertArrayNotHasKey('<body>', $data);
	}

	public function testToString(): void
	{
		$html = new HtmlString('<b>safe</b>');
		$this->assertSame('<b>safe</b>', (string)$html);
	}

	public function testTranslate(): void
	{
		I18n::$translations = [
			'en' => ['confirm' => 'Delete <b>the page</b>?']
		];

		$result = HtmlString::translate('confirm');

		$this->assertInstanceOf(HtmlString::class, $result);
		$this->assertSame('Delete <b>the page</b>?', (string)$result);
	}

	public function testTranslateEscapesValues(): void
	{
		I18n::$translations = [
			'en' => ['greeting' => 'Hello <b>{name}</b>']
		];

		$result = HtmlString::translate('greeting', [
			'name' => '<script>alert(1)</script>'
		]);

		$this->assertSame(
			'Hello <b>&lt;script&gt;alert(1)&lt;/script&gt;</b>',
			(string)$result
		);
	}

	public function testTranslateEscapesQueryResults(): void
	{
		I18n::$translations = [
			'en' => ['greeting' => 'Hello {user.name}']
		];

		// the value is only reachable through the query,
		// so it has to be escaped after it was resolved
		$result = HtmlString::translate('greeting', [
			'user' => ['name' => '<script>alert(1)</script>']
		]);

		$this->assertSame(
			'Hello &lt;script&gt;alert(1)&lt;/script&gt;',
			(string)$result
		);
	}

	public function testTranslateKeepsTrustedValues(): void
	{
		I18n::$translations = [
			'en' => ['greeting' => 'Hello {name}']
		];

		$result = HtmlString::translate('greeting', [
			'name' => new HtmlString('<b>Peter</b>')
		]);

		$this->assertSame('Hello <b>Peter</b>', (string)$result);
	}

	public function testTranslateWithArrayOfTranslations(): void
	{
		$result = HtmlString::translate(
			['en' => 'Hello <b>{name}</b>'],
			['name' => 'Peter']
		);

		$this->assertSame('Hello <b>Peter</b>', (string)$result);
	}

	public function testTranslateWithFallback(): void
	{
		$result = HtmlString::translate(
			'does.not.exist',
			[],
			'<b>Fallback</b>'
		);

		$this->assertSame('<b>Fallback</b>', (string)$result);
	}

	public function testTranslateWithMissingKey(): void
	{
		// the key itself is the last resort, just like in `I18n::template()`
		$this->assertSame(
			'does.not.exist',
			(string)HtmlString::translate('does.not.exist')
		);
	}

	public function testTranslateWithMissingValue(): void
	{
		I18n::$translations = [
			'en' => ['greeting' => 'Hello {name}']
		];

		$this->assertSame('Hello -', (string)HtmlString::translate('greeting'));
	}

	public function testTranslateWithTemplateString(): void
	{
		$result = HtmlString::translate('Hello <b>{name}</b>', [
			'name' => '<script>'
		]);

		$this->assertSame('Hello <b>&lt;script&gt;</b>', (string)$result);
	}

	public function testValue(): void
	{
		$html = new HtmlString('<b>safe</b>');
		$this->assertSame('<b>safe</b>', $html->value());
	}
}
