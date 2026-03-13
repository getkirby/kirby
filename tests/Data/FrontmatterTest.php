<?php

namespace Kirby\Data;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Frontmatter::class)]
class FrontmatterTest extends TestCase
{
	public function testDecodeEmpty(): void
	{
		$this->assertSame([], Frontmatter::decode(null));
		$this->assertSame([], Frontmatter::decode(''));
	}

	public function testDecodeWithArray(): void
	{
		$this->assertSame(
			['this is' => 'an array'],
			Frontmatter::decode(['this is' => 'an array'])
		);
	}

	public function testDecodeStandardFrontmatter(): void
	{
		$string = "---\ntitle: My Title\nuuid: abc123\n---\n";

		$this->assertSame([
			'title' => 'My Title',
			'uuid'  => 'abc123'
		], Frontmatter::decode($string));
	}

	public function testDecodeWithBody(): void
	{
		$string = "---\ntitle: My Title\n---\nThis is the body text.\nIt can contain **Markdown**.\n";

		$this->assertSame([
			'title' => 'My Title',
			'text'  => "This is the body text.\nIt can contain **Markdown**."
		], Frontmatter::decode($string));
	}

	public function testDecodeWithBodyAndTextField(): void
	{
		// body after closing --- wins over text field in YAML block
		$string = "---\ntitle: My Title\ntext: from yaml\n---\nThis is the body.\n";

		$this->assertSame([
			'title' => 'My Title',
			'text'  => 'This is the body.'
		], Frontmatter::decode($string));
	}

	public function testDecodeEmptyBody(): void
	{
		// empty body should not produce a text key
		$string = "---\ntitle: My Title\n---\n";

		$this->assertSame([
			'title' => 'My Title'
		], Frontmatter::decode($string));
	}

	public function testDecodeFallback(): void
	{
		// string without --- delimiters falls back to plain YAML
		$string = "title: My Title\nuuid: abc123\n";

		$this->assertSame([
			'title' => 'My Title',
			'uuid'  => 'abc123'
		], Frontmatter::decode($string));
	}

	public function testDecodeWithWhitespaceOnlyBody(): void
	{
		// whitespace-only body after closing --- should not produce a text key
		$string = "---\ntitle: My Title\n---\n   \n";

		$this->assertSame([
			'title' => 'My Title'
		], Frontmatter::decode($string));
	}

	public function testDecodeWithWindowsLineEndings(): void
	{
		$string = "---\r\ntitle: My Title\r\nuuid: abc123\r\n---\r\n";

		$this->assertSame([
			'title' => 'My Title',
			'uuid'  => 'abc123'
		], Frontmatter::decode($string));
	}

	public function testEncode(): void
	{
		$data = [
			'title' => 'My Title',
			'uuid'  => 'abc123'
		];

		$this->assertSame(
			"---\ntitle: My Title\nuuid: abc123\n---\n",
			Frontmatter::encode($data)
		);
	}

	public function testEncodeWithBody(): void
	{
		$data = [
			'title' => 'My Title',
			'text'  => 'This is the body.'
		];

		$this->assertSame(
			"---\ntitle: My Title\n---\nThis is the body.\n",
			Frontmatter::encode($data)
		);
	}

	public function testEncodeWithEmptyBody(): void
	{
		$data = [
			'title' => 'My Title',
			'text'  => ''
		];

		$this->assertSame(
			"---\ntitle: My Title\n---\n",
			Frontmatter::encode($data)
		);
	}

	public function testEncodeWithWhitespaceOnlyBody(): void
	{
		$data = [
			'title' => 'My Title',
			'text'  => '   '
		];

		$this->assertSame(
			"---\ntitle: My Title\n---\n",
			Frontmatter::encode($data)
		);
	}

	public function testRoundtrip(): void
	{
		$data = [
			'title' => 'My Title',
			'uuid'  => 'abc123'
		];

		$this->assertSame($data, Frontmatter::decode(Frontmatter::encode($data)));

		$dataWithBody = [
			'title' => 'My Title',
			'text'  => 'This is the body text.'
		];

		$this->assertSame($dataWithBody, Frontmatter::decode(Frontmatter::encode($dataWithBody)));
	}
}
