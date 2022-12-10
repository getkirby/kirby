<?php

namespace Kirby\Text;

use Michelf\SmartyPantsTypographer;

/**
 * Wrapper for Michelf's SmartyPants
 * parser, to improve the configurability
 * of the parser with default options and
 * a simple way to set your own options.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class SmartyPants
{
	/**
	 * Array with all configured options
	 * for the parser
	 */
	protected array $options = [];

	/**
	 * Michelf's parser object
	 */
	protected SmartyPantsTypographer $parser;

	/**
	 * Returns default values for all
	 * available parser options
	 */
	public function defaults(): array
	{
		return [
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
	}

	/**
	 * Creates a new SmartyPants parser
	 * with the given options
	 */
	public function __construct(array $options = [])
	{
		$this->options = array_merge($this->defaults(), $options);
		$this->parser  = new SmartyPantsTypographer($this->options['attr']);

		// configuration
		$this->parser->smart_doublequote_open     = $this->options['doublequote.open'];
		$this->parser->smart_doublequote_close    = $this->options['doublequote.close'];
		$this->parser->smart_singlequote_open     = $this->options['singlequote.open'];
		$this->parser->smart_singlequote_close    = $this->options['singlequote.close'];
		$this->parser->backtick_doublequote_open  = $this->options['backtick.doublequote.open'];
		$this->parser->backtick_doublequote_close = $this->options['backtick.doublequote.close'];
		$this->parser->backtick_singlequote_open  = $this->options['backtick.singlequote.open'];
		$this->parser->backtick_singlequote_close = $this->options['backtick.singlequote.close'];
		$this->parser->em_dash                    = $this->options['emdash'];
		$this->parser->en_dash                    = $this->options['endash'];
		$this->parser->ellipsis                   = $this->options['ellipsis'];
		$this->parser->tags_to_skip               = $this->options['skip'];
		$this->parser->space_emdash               = $this->options['space.emdash'];
		$this->parser->space_endash               = $this->options['space.endash'];
		$this->parser->space_colon                = $this->options['space.colon'];
		$this->parser->space_semicolon            = $this->options['space.semicolon'];
		$this->parser->space_marks                = $this->options['space.marks'];
		$this->parser->space_frenchquote          = $this->options['space.frenchquote'];
		$this->parser->space_thousand             = $this->options['space.thousand'];
		$this->parser->space_unit                 = $this->options['space.unit'];
		$this->parser->doublequote_low            = $this->options['doublequote.low'];
		$this->parser->guillemet_leftpointing     = $this->options['guillemet.leftpointing'];
		$this->parser->guillemet_rightpointing    = $this->options['guillemet.rightpointing'];
		$this->parser->geresh                     = $this->options['geresh'];
		$this->parser->gershayim                  = $this->options['gershayim'];
		$this->parser->space                      = $this->options['space'];
	}

	/**
	 * Parses the given text
	 */
	public function parse(string|null $text = null): string
	{
		// prepare the text
		$text ??= '';
		$text   = str_replace('&quot;', '"', $text);

		// parse the text
		return $this->parser->transform($text);
	}
}
