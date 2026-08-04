<?php

namespace Kirby\Toolkit;

use Kirby\Data\Json;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HtmlString::class)]
class HtmlStringTest extends TestCase
{
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

	public function testValue(): void
	{
		$html = new HtmlString('<b>safe</b>');
		$this->assertSame('<b>safe</b>', $html->value());
	}
}
