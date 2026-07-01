<?php

namespace Kirby\Text;

use Kirby\Text\Markdown\Parser;

/**
 * The Markdown class is the public facade for Kirby's Markdown parsing and
 * standardizes the parser API for all Kirby packages.
 *
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
	protected Parser|null $parser = null;

	/**
	 * Returns default values for all
	 * available parser options
	 */
	public function defaults(): array
	{
		return [
			'breaks' => true,
			'safe'   => false
		];
	}

	/**
	 * Creates a new Markdown parser
	 * with the given options
	 */
	public function __construct(array $options = [])
	{
		$this->options = [...$this->defaults(), ...$options];
	}

	/**
	 * Parses the given text and returns the HTML
	 */
	public function parse(
		string|null $text = null,
		bool $inline = false
	): string {
		$this->parser ??= new Parser(
			breaks: $this->options['breaks'],
			safe:   $this->options['safe']
		);

		return $this->parser->parse($text, inline: $inline);
	}
}
