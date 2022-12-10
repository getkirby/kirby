<?php

namespace Kirby\Text;

use Parsedown;
use ParsedownExtra;

/**
 * The Markdown class is a wrapper around all sorts of Markdown
 * parser libraries and is meant to standardize the Markdown parser
 * API for all Kirby packages.
 *
 * It uses Parsedown and ParsedownExtra by default.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Markdown
{
	/**
	 * Array with all configured options
	 * for the parser
	 */
	protected array $options = [];

	/**
	 * Returns default values for all
	 * available parser options
	 */
	public function defaults(): array
	{
		return [
			'breaks' => true,
			'extra'  => false,
			'safe'   => false
		];
	}

	/**
	 * Creates a new Markdown parser
	 * with the given options
	 */
	public function __construct(array $options = [])
	{
		$this->options = array_merge($this->defaults(), $options);
	}

	/**
	 * Parses the given text and returns the HTML
	 */
	public function parse(string|null $text = null, bool $inline = false): string
	{
		$parser = match ($this->options['extra']) {
			true    => new ParsedownExtra(),
			default => new Parsedown()
		};

		$parser->setBreaksEnabled($this->options['breaks']);
		$parser->setSafeMode($this->options['safe']);

		if ($inline === true) {
			return @$parser->line($text);
		}

		return @$parser->text($text);
	}
}
