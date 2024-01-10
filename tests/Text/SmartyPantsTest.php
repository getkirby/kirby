<?php

namespace Kirby\Text;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Text\SmartyPants
 */
class SmartyPantsTest extends TestCase
{
	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		$parser   = new SmartyPants();
		$result   = $parser->parse('This is a "test quote"');
		$expected = 'This is a &#8220;test quote&#8221;';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseEmpty()
	{
		$parser = new SmartyPants();

		$this->assertSame('', $parser->parse());
		$this->assertSame('', $parser->parse(''));
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$expected = [
			'attr'                       => 1,
			'doublequote.open'           => '&#8220;',
			'doublequote.close'          => '&#8221;',
			'doublequote.low'            => '&#8222;',
			'singlequote.open'           => '&#8216;',
			'singlequote.close'          => '&#8217;',
			'backtick.doublequote.open'  => '&#8220;',
			'backtick.doublequote.close' => '&#8221;',
			'backtick.singlequote.open'  => '&#8216;',
			'backtick.singlequote.close' => '&#8217;',
			'emdash'                     => '&#8212;',
			'endash'                     => '&#8211;',
			'ellipsis'                   => '&#8230;',
			'space'                      => '(?: | |&nbsp;|&#0*160;|&#x0*[aA]0;)',
			'space.emdash'               => ' ',
			'space.endash'               => ' ',
			'space.colon'                => '&#160;',
			'space.semicolon'            => '&#160;',
			'space.marks'                => '&#160;',
			'space.frenchquote'          => '&#160;',
			'space.thousand'             => '&#160;',
			'space.unit'                 => '&#160;',
			'guillemet.leftpointing'     => '&#171;',
			'guillemet.rightpointing'    => '&#187;',
			'geresh'                     => '&#1523;',
			'gershayim'                  => '&#1524;',
			'skip'                       => 'pre|code|kbd|script|style|math',
		];

		$parser = new SmartyPants();
		$this->assertSame($expected, $parser->defaults());
	}

	/**
	 * @covers ::__construct
	 */
	public function testDoubleQuotesOption()
	{
		$parser = new SmartyPants([
			'doublequote.open'  => '<',
			'doublequote.close' => '>'
		]);

		$result = $parser->parse('"test"');
		$this->assertSame('<test>', $result);
	}

	/**
	 * @covers ::__construct
	 */
	public function testSingleQuotesOption()
	{
		$parser = new SmartyPants([
			'singlequote.open'  => '<',
			'singlequote.close' => '>'
		]);

		$result = $parser->parse("'test'");
		$this->assertSame('<test>', $result);
	}

	/**
	 * @covers ::__construct
	 */
	public function testEmDashOption()
	{
		$parser = new SmartyPants([
			'emdash' => 'emdash',
		]);

		$result = $parser->parse('--');
		$this->assertSame('emdash', $result);
	}

	/**
	 * @covers ::__construct
	 */
	public function testEllipsisOption()
	{
		$parser = new SmartyPants([
			'ellipsis' => 'ellipsis',
		]);

		$result = $parser->parse('...');
		$this->assertSame('ellipsis', $result);
	}
}
