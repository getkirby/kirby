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
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Markdown
{
    /**
     * Array with all configured options
     * for the parser
     *
     * @var array
     */
    protected $options = [];

    /**
     * Returns default values for all
     * available parser options
     *
     * @return array
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
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->defaults(), $options);
    }

    /**
     * Parses the given text and returns the HTML
     *
     * @param string|null $text
     * @param bool $inline
     * @return string
     */
    public function parse(string $text = null, bool $inline = false): string
    {
        if ($this->options['extra'] === true) {
            $parser = new ParsedownExtra();
        } else {
            $parser = new Parsedown();
        }

        $parser->setBreaksEnabled($this->options['breaks']);
        $parser->setSafeMode($this->options['safe']);

        if ($inline === true) {
            return @$parser->line($text);
        } else {
            return @$parser->text($text);
        }
    }
}
