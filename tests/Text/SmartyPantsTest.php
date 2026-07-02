<?php

namespace Kirby\Text;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SmartyPants::class)]
class SmartyPantsTest extends TestCase
{
	public function testParse(): void
	{
		$parser   = new SmartyPants();
		$result   = $parser->parse('This is a "test quote"');
		$expected = 'This is a “test quote”';

		$this->assertSame($expected, $result);
	}

	public function testParseEmpty(): void
	{
		$parser = new SmartyPants();

		$this->assertSame('', $parser->parse());
		$this->assertSame('', $parser->parse(''));
	}

	public function testParseHtml(): void
	{
		$parser   = new SmartyPants();
		$result   = $parser->parse('<img alt="Test with &quot;quotes&quot;" src="/test.jpg">');
		$expected = '<img alt="Test with &quot;quotes&quot;" src="/test.jpg">';

		$this->assertSame($expected, $result);
	}

	public function testParseQuotedEntities(): void
	{
		$parser   = new SmartyPants();
		$result   = $parser->parse('This is &quot;quoted&quot;');
		$expected = 'This is “quoted”';

		$this->assertSame($expected, $result);
	}

	public function testDefaults(): void
	{
		$expected = [
			'attr'                       => 1,
			'convert.quot'               => true,
			'doublequote.open'           => '“',
			'doublequote.close'          => '”',
			'doublequote.low'            => '„',
			'singlequote.open'           => '‘',
			'singlequote.close'          => '’',
			'backtick.doublequote.open'  => '“',
			'backtick.doublequote.close' => '”',
			'backtick.singlequote.open'  => '‘',
			'backtick.singlequote.close' => '’',
			'emdash'                     => '—',
			'endash'                     => '–',
			'ellipsis'                   => '…',
			'space'                      => '(?: | |&nbsp;|&#0*160;|&#x0*[aA]0;)',
			'space.emdash'               => ' ',
			'space.endash'               => ' ',
			'space.colon'                => "\u{00A0}",
			'space.semicolon'            => "\u{00A0}",
			'space.marks'                => "\u{00A0}",
			'space.frenchquote'          => "\u{00A0}",
			'space.thousand'             => "\u{00A0}",
			'space.unit'                 => "\u{00A0}",
			'guillemet.leftpointing'     => '«',
			'guillemet.rightpointing'    => '»',
			'geresh'                     => '׳',
			'gershayim'                  => '״',
			'skip'                       => 'pre|code|kbd|script|style|math',
		];

		$parser = new SmartyPants();
		$this->assertSame($expected, $parser->defaults());
	}

	public function testDoubleQuotesOption(): void
	{
		$parser = new SmartyPants([
			'doublequote.open'  => '<',
			'doublequote.close' => '>'
		]);

		$result = $parser->parse('"test"');
		$this->assertSame('<test>', $result);
	}

	public function testSingleQuotesOption(): void
	{
		$parser = new SmartyPants([
			'singlequote.open'  => '<',
			'singlequote.close' => '>'
		]);

		$result = $parser->parse("'test'");
		$this->assertSame('<test>', $result);
	}

	public function testEmDashOption(): void
	{
		$parser = new SmartyPants([
			'emdash' => 'emdash',
		]);

		$result = $parser->parse('--');
		$this->assertSame('emdash', $result);
	}

	public function testEllipsisOption(): void
	{
		$parser = new SmartyPants([
			'ellipsis' => 'ellipsis',
		]);

		$result = $parser->parse('...');
		$this->assertSame('ellipsis', $result);
	}
}
